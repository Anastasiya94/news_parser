<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Указываем какие классы будут использоватся
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Chrome\ChromeOptions;

//Пподключаем автолоадер классов
require_once('vendor/autoload.php');


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

    function clear_database($_seconds)
    {
        $_from = strtotime("now - " . $_seconds . "seconds");
        $this->db->where('pubDate <', date('Y-m-d H:i:s', $_from));
        $this->db->delete('articles');
    }

    function parsing_data()
    {
        print(">> Clear database (to store only last 30 days)...\n");
        $this->clear_database(2592000);

        print(">> Creating Chrome Driver...\n");

        $_options = new ChromeOptions();
        $_options->addExtensions(array(getcwd().'\Adblock-Plus_v1.13.4.crx'));
        $_caps = DesiredCapabilities::chrome();
        $_caps->setCapability( ChromeOptions::CAPABILITY, $_options);
        $_driver = RemoteWebDriver::create('http://localhost:4444/wd/hub', $_caps, 5000);

        print(">> Starting parse loop...");

        $_news_site_list= array("lenta.ru" => "https://lenta.ru/rss",
            "news.mail.ru" => "https://news.mail.ru/rss",
            "vesti.ru" => "https://www.vesti.ru/vesti.rss");

        try
        {
            foreach ($_news_site_list as $_site => $_rss)
            {
                $_xmlStr = file_get_contents($_rss);
                $_xml = new SimpleXMLElement($_xmlStr);
                $_items = $_xml->xpath('/rss/channel/item');

                print("\n>> Website: " . $_site . ", " . count($_items) . " items ");

                $_count_item = 0;
                $_percentage_border = 10;
                foreach ($_items as $_item_key => $_item)
                {
                    $_articles_preview = array();
                    foreach ($_item as $_articles_key => $_value)
                    {
                        $_articles_preview["site"] = $_site;


                        switch ($_articles_key) {
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
                                $_driver->get($_value->__toString());
                                switch ($_site) {
                                    case "lenta.ru":
                                        $_tag = WebDriverBy::xpath("//*[@itemprop='articleBody']");
                                        $_articles_preview["full_content"] = htmlentities($_driver->findElement($_tag)->getAttribute("innerHTML"));

                                        break;
                                    case "vesti.ru":
                                        $_tag = WebDriverBy::xpath("//*[@class='article__text']");
                                        $_articles_preview["full_content"] = htmlentities($_driver->findElement($_tag)->getAttribute("innerHTML"));
                                        break;
                                    case "news.mail.ru":
                                        $_tag = WebDriverBy::xpath("//*[@class='article__text js-module js-view js-mediator-article']");
                                        $_articles_preview["full_content"] = htmlentities($_driver->findElement($_tag)->getAttribute("innerHTML"));
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
                                if ($_count_item*100/count($_items) > $_percentage_border)
                                {
                                    print($_count_item*100/count($_items) . "% ");
                                    $_percentage_border += 10;
                                }
                                break;
                            } else
                            {
                                print("found identical on " . $_count_item . " item, breaking loop");
                                break 2;
                            }
                        }
                    }
                }
            }
        }
        catch (Exception $_exc)
        {
            print("\n[!] Exception while parsing: " . $_exc->getMessage());
        }

        print("\n>> Finished parse loop, destroying driver...\n");

        $_driver->quit();
        //system("taskkill /F /IM chromedriver.exe");
    }
}
