<?php

class Categories
{
    public function GetList()
    {
        $App = Application::getInstance();

        $query = "select * from categories";
        $categories = $App->db->query($query);

        return $categories;
    }

}
