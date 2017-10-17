<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class News_model extends CI_Model {

    function get_articles($_count,$_offset)
    {
        $this->db->order_by('pubDate','desc');
        $_query = $this->db->get('articles',$_count,$_offset);
        return $_query->result_array();
    }

    function get_article_by_id($_id)
    {
        $this->db->where('id',$_id);
        $_query = $this->db->get('articles');
        return $_query->row_array();
    }
    function count_rows()
    {
        return $this->db->count_all('articles');;
    }

    function get_count_views_by_id($_id)
    {
        $this->db->select('count_views');
        $this->db->where('id',$_id);
        $_query = $this->db->get('articles');
        return $_query->row_array();
    }

    function edit_article($_id,$_data)
    {
        $this->db->where('id',$_id);
        $this->db->update('articles',$_data);
    }
}
