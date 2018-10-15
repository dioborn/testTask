<?php

class News
{

    private $perPage;           // set in config

    public function __construct($config)
    {
        foreach ($config as $k => $v) {
            if ($k != 'data') {
                $this->{$k} = $v;
            }
        }
    }

    /*
     * Get news list
     */
    public function GetList($page = 1, $filters = array())
    {
        $App = Application::getInstance();

        $from = ($page - 1) * $this->perPage;
        $to = $page * $this->perPage;

        $where = $this->GetWhere($filters);

        $query = "select news.id, news.title, news.text, news.category_id, news.created, count(likes.id) as 'likes_count' from news left join likes on(likes.news_id = news.id)" . $where . " group by news.id order by news.created desc limit " . $from . ", " . $to;
        $news = $App->db->query($query);

        return $news;
    }

    /*
     * Get news counter
     */
    public function GetCount($filters = array())
    {
        $App = Application::getInstance();

        $where = $this->GetWhere($filters);

        $counter = $App->db->query("select count(*) as counter from news" . $where);

        return $counter[0]['counter'];
    }

    private function GetWhere($filters)
    {
        $App = Application::getInstance();

        if (empty($filters)) {
            return '';
        }

        $where = '';

        foreach ($filters as $key => $filter) {

            if ($where) {
                $where .= ' and ';
            } else {
                $where .= ' where ';
            }

            if ($key == 'category_id') {
                $where .= "category_id in(" . $App->db->EscapeString(implode(',', $filter)) . ")";
            }
        }

        return $where;
    }

    /*
     * Add news
     */
    public function Add($data)
    {
        $App = Application::getInstance();

        $id = $App->db->query("insert into news (title, text, category_id, user_id) values ('" . $App->db->EscapeString($data['title']) . "', '" . $App->db->EscapeString($data['text']) . "', " . (int) $data['category_id'] . ", " . (int) $data['user_id'] . ")", 'id');

        return $id;
    }

    /*
     * Get news information by news id
     */
    public function GetById($id)
    {
        $App = Application::getInstance();

        $row = $App->db->query("select news.id, news.title, news.text, news.category_id, news.created, count(likes.id) as 'likes_count' from news left join likes on(likes.news_id = news.id) where news.id=" . (int) $id . " group by news.id");

        if ($row) {
            return $row[0];
        }

        return false;
    }

}
