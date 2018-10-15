<?php
define("SENGIN", realpath(dirname(__FILE__) . '/'));
define("LEVEL_USER", 1);
define("LEVEL_AUTHOR", 2);
define("LEVEL_ADMIN", 5);

$Config = array(
    'global' => array(
        'CookieDomain' => '/',
        'CookieExpire' => 24 * 60 * 60 * 365,
        'Session' => array(),
    ),
    /* Autoload classes */
    'autoload' => array('user'),
    /* controller manager */
    'controller' => array(
        'path' => dirname(__FILE__) . '/controllers/',
        'map' => array(
            'user' => 'User',
            'news' => 'News',
            'likes' => 'Likes',
        )
    ),
    /* Including classes */
    'classes' => array(
        'db' => 'Database',
        'cache' => 'AppCache',
        'news' => 'News',
        'likes' => 'Likes',
        'user' => 'User',
        'categories' => 'Categories',
    ),
    /** Database */
    'db' => array(
        'DB_ADDR' => '127.0.0.10',
        'DB_NAME' => 'news_portal',
        'DB_USER' => 'root',
        'DB_PASS' => '',
        'DB_CHARSET' => 'utf8'
    ),
    'news' => array(
        'perPage' => 10
    ),
    'likes' => array(
        'perPage' => 20
    ),
    /** User */
    'User' => array(
    ),
);
