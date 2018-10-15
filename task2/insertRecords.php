<?php
$dbName = 'test';
$username = 'root';
$pass = '';

$recordsCount = 1000000;
$domains = array(
    'google.com',
    'ya.ru',
    'yandex.ru',
    'mail.ru'
);


date_default_timezone_set('UTC');
error_reporting(E_ALL);
ini_set('memory_limit', '256M');
ini_set('display_errors','on');


$db = new PDO('mysql:host=localhost;dbname=' . $dbName, $username, $pass);

for ($i = 0; $i < $recordsCount; $i++) {
    $name = generateRandomString(rand(3, 10));
    $gender = rand(0, 1);
    $email = '';
    for ($k = rand(0, 4); $k > 0; $k --) {
        $email .= $email ? ', ' : '';
        $email .= generateRandomString(rand(3, 10)) . '@' . $domains[rand(0, count($domains) - 1)];
    }
    $sth = $db->prepare("insert into users (name, gender, email) values('" . $name . "', '" . $gender . "', '" . $email . "')");
    $sth->execute();
}
function generateRandomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
