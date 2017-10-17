<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Pages extends CI_Controller {

    public function index()
    {
        //Запуск парсера при устаревании базы
        $this->load->model("parser_model");
        $this->parser_model->start_parser_if_needed();

        //Проверка на отсутствие записей в базе
        if (!$this->parser_model->has_any_article()) {
            echo "В данный момент в БД нет статей, база данных обновляется, попробуйте обновить страницу через несколько секунд";
            return;
        }

        //Загрузка страницы
        $this->load->model("news_model");
        $config['base_url'] = base_url().'index.php/pages/index/';

        $config['total_rows'] = $this->news_model->count_rows();
        $config['per_page'] = 4;
        $config['full_tag_open'] = '';
        $config['full_tag_close'] = '';
        $config['cur_tag_open'] = "<li class='active'><a href='#'> ";
        $config['cur_tag_close'] = "</a></li>";
        $config['first_link'] = "«";
        $config['last_link'] = "»";
        $config['next_tag_open'] = "<li>";
        $config['next_tag_close'] = "</li>";
        $config['prev_tag_open'] = "<li>";
        $config['prev_tag_close'] = "</li>";
        $config['last_tag_open'] = "<li>";
        $config['last_tag_close'] = "</li>";
        $config['first_tag_open'] = "<li>";
        $config['first_tag_close'] = "</li>";
        $config['num_tag_open'] = "<li>";
        $config['num_tag_close'] = "</li>";

        $this->pagination->initialize($config);
        $_data["articles"]= $this->news_model->get_articles($config['per_page'],$this->uri->segment(3));
        $this->load->view('preview_news_list', $_data);
    }

    public function article($_id)
    {
        $this->load->model("news_model");
        $_count = $this->news_model->get_count_views_by_id($_id);
        $_count['count_views']++;
        $this->news_model->edit_article($_id,$_count);
        $_data = $this->news_model->get_article_by_id($_id);
        $this->load->view('full_news', $_data);
    }
}
?>
