<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Parser extends CI_Controller
{
    //php index.php parser index
    public function index()
    {
        $this->load->model("parser_model");
        set_time_limit(0);
        $this->parser_model->parsing_data();
    }
}