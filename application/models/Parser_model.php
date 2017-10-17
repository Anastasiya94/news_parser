<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Parser_model extends CI_Model
{
    function add_article($_data)
    {
        $this->db->insert('articles',$_data);
    }

    function has_identically($_title)
    {
        $this->db->where('title',$_title);
        $_query = $this->db->get('articles');
        return $_query->num_rows() != 0;
    }

    //Проверяет есть ли хоть одна новость в БД
    function has_any_article()
    {
        return $this->db->get('articles')->row() != null;
    }

    //Запись информации о началее обновления в БД
    //А так же очистка БД от устаревших данных (храним последние 30 дней)
    function update_started()
    {
        //Очитска базы
        $_from = strtotime("now - 2592000 seconds");
        $this->db->where('pubDate <', date('Y-m-d H:i:s', $_from));
        $this->db->delete('articles');

        //Начало обновления
        $_data = array(
            'ID' => 1,
            'lastUpdate'  => date('Y-m-d H:i:s', strtotime("now")),
            'currentlyUpdating'  => 1
        );
        $this->db->replace('update_info', $_data);
    }

    //Запись информации об окончании обновления в БД
    function update_finished()
    {
        $this->db->set('currentlyUpdating', '0');
        $this->db->where('ID', 1);
        $this->db->update('update_info');
    }

    //Проверят, обновляется ли БД в данный момент
    function is_updating()
    {
        $_update_info = $this->db->get('update_info')->row();
        return $_update_info->currentlyUpdating == 1;
    }

    function start_parser_if_needed()
    {
        $_update_info = $this->db->get('update_info')->row();
        //При первом запуске информации об обновлениях еще нет
        if ($_update_info == null) { $this->start_parser_by_os(); return; }
        //Если уже идет процесс обновления, то нет необходимости повторять
        if ($_update_info->currentlyUpdating == 1) return;
        //Обновляемся каждые 30 минут
        $_interval = strtotime("now") - strtotime($_update_info->lastUpdate);
        if ($_interval > 1800) $this->start_parser_by_os();
    }

    function start_parser_by_os()
    {
        $this->update_started();
        try {
            //Win
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                pclose(popen('start /B cmd /C "php index.php parser index >NUL 2>NUL"', 'r'));
            //Linux
            } else {
                exec('php index.php parser index > /dev/null 2>/dev/null &');
            }
        } catch (Exception $_exc) {
            echo "\n[!] Exception while parsing: " . $_exc->getMessage();
            $this->update_finished();
        }
    }

    //Вызов из parser.php
    function parsing_data()
    {
        try
        {
            echo ">> Starting parse loop...";
            $_dom = new DOMDocument();

            $_news_site_list = array("lenta.ru" => "https://lenta.ru/rss",
                "news.mail.ru" => "https://news.mail.ru/rss",
                "vesti.ru" => "https://www.vesti.ru/vesti.rss");

            foreach ($_news_site_list as $_site => $_rss)
            {
                $_xmlStr = file_get_contents($_rss);
                $_xml = new SimpleXMLElement($_xmlStr);
                $_items = $_xml->xpath('/rss/channel/item');

                echo "\n>> Website: " . $_site . ", " . count($_items) . " items ";

                $_count_item = 0;
                $_percentage_border = 10;
                foreach ($_items as $_item_key => $_item)
                {
                    try
                    {
                        $_articles_preview = array();
                        foreach ($_item as $_articles_key => $_value)
                        {
                            $_articles_preview["site"] = $_site;

                            switch ($_articles_key)
                            {
                                case "title":
                                case "description":
                                    $_articles_preview[$_articles_key] = htmlentities($_value->__toString());
                                    break;
                                case "pubDate":
                                    $_articles_preview[$_articles_key] = date("Y-m-d H:i:s", strtotime($_value->__toString()));
                                    break;
                                case "enclosure":
                                    $_articles_preview[$_articles_key] = $_value['url']->__toString();
                                    break;
                                case "link":
                                    $_articles_preview[$_articles_key] = $_value->__toString();
                                    libxml_use_internal_errors(true);
                                    $_dom->loadHTMLFile($_value->__toString());
                                    libxml_clear_errors();
                                    $_xpath = new DOMXpath($_dom);

                                    switch ($_site) {
                                        case "lenta.ru":
                                            $_elements = $_xpath->query("//*[@itemprop='articleBody']");
                                            $_htmlString = $_dom->saveHTML($_elements->item(0));
                                            $_articles_preview["full_content"] = htmlentities(iconv('utf-8', 'iso-8859-1//TRANSLIT', $_htmlString));
                                            break;
                                        case "vesti.ru":
                                            $_elements = $_xpath->query("//*[@class='article__text']");
                                            $_htmlString = $_dom->saveHTML($_elements->item(0));
                                            $_articles_preview["full_content"] = htmlentities($_htmlString);
                                            break;
                                        case "news.mail.ru":
                                            $_elements = $_xpath->query("//*[@class='article__text js-module js-view js-mediator-article']");
                                            $_htmlString = $_dom->saveHTML($_elements->item(0));
                                            $_articles_preview["full_content"] = htmlentities($_htmlString);
                                            if (isset($_articles_preview["enclosure"]) == false)
                                                $_articles_preview["enclosure"] = base_url() . "images/mail_ru.png";
                                            break;

                                    }
                                    break;
                            }

                            if (count($_articles_preview) == 7)
                            {
                                $_count_item++;
                                if (!$this->has_identically($_articles_preview["title"]))
                                {
                                    $this->add_article($_articles_preview);
                                    if ($_count_item * 100 / count($_items) > $_percentage_border)
                                    {
                                        echo $_count_item * 100 / count($_items) . "% ";
                                        $_percentage_border += 10;
                                    }
                                    break;
                                }
                                else
                                {
                                    echo "found identical on " . $_count_item . " item, breaking loop";
                                    break 2;
                                }
                            }
                        }
                    }
                    catch (Exception $_exc)
                    {
                        echo "\n[!] Exception while parsing: " . $_exc->getMessage();
                    }
                }
            }

            echo "\n>> Finished parse loop...\n";
        }
        catch (Exception $_exc)
        {
            echo "\n[!] Exception while parsing: " . $_exc->getMessage();
        }

        $this->update_finished();
    }
}
