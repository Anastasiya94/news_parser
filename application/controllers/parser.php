<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Parser extends CI_Controller
{
    //php index.php parser index
    public function index()
    {
        $this->load->model("parser_model");
        set_time_limit(0);
        while(true)
        {
            try {
                $this->parser_model->parsing_data();
            }catch (Exception $_exc) {
                print("Выброшено исключение: " .  $_exc->getMessage() . "\n");
            }
            $sec = rand(50,120);
            print("------WAITING " . $sec . " seconds for the next attempt---------\n");
            sleep($sec);
        }
    }
}