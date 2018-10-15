<?php

class Likes
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

    public function GetList($newsId, $page = 1)
    {
        $App = Application::getInstance();

        $from = ($page - 1) * $this->perPage;
        $to = $page * $this->perPage;

        $likes = $App->db->query("select users.id, users.name from likes join users on(likes.user_id = users.id) where likes.news_id=" . (int) $newsId . " limit " . $from . ", " . $to);

        return $likes;
    }

    public function Add($data)
    {
        $App = Application::getInstance();

        $id = $App->db->query("insert into likes (news_id, user_id) values (" . (int) $data['news_id'] . ", " . (int) $data['user_id'] . ")", 'id');

        return $id;
    }

    public function Delete($data)
    {
        $App = Application::getInstance();

        $updated = $App->db->query("delete from likes where news_id = " . (int) $data['news_id'] . " and user_id = " . (int) $data['user_id'], 'updated');

        return $updated;
    }

    public function GetCount($newsId)
    {
        $App = Application::getInstance();

        $count = $App->db->query("select count(id) as counter from likes where news_id=" . (int) $newsId);

        return $count[0]['counter'];
    }

}
