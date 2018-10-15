<?php

// Заглушка, реализация данного функционала не требовалась
class User
{
    public function __construct($config)
    {
        foreach ($config as $k => $v) {
            if ($k != 'data') {
                $this->{$k} = $v;
            }
        }

        $this->Info = new UserInfo();
    }

    public function GetLevel()
    {
        return 5;
    }

    public function GetId()
    {
        return 1;
    }

}

class UserInfo
{
    function __construct()
    {
        
    }

    function __set($name, $value)
    {
        $this->{$name} = $value;
    }

    function __get($name)
    {
        $this->{$name} = false;
        return false;
    }

}
