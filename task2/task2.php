<?php
$dbName = 'test';
$username = 'root';
$pass = '';
$limit = 100000;

date_default_timezone_set('UTC');
error_reporting(E_ALL);
ini_set('memory_limit', '128M');
//ini_set('memory_limit', '2048M');
ini_set('display_errors', 'on');

$start = microtime(true);

$db = new PDO('mysql:host=localhost;dbname=' . $dbName, $username, $pass);

$result = array();
$recordsCount = 0;
$lastId = 0;
do {
    $sth = $db->prepare("select id, email from users where id > " . $lastId . " order by id asc limit " . $limit);
    $sth->execute();
    $i = 0;

    while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {

        $i++;
        $recordsCount++;
        $lastId = $row['id'];

        // Field is empty
        if (!$row['email']) {
            continue;
        }

        // Get emails array
        $emails = explode(',', $row['email']);

        foreach ($emails as $email) {
            $email = trim($email);

            $parts = explode('@', $email);

            if (empty($parts[1])) {
                continue;
            }

            $domain = $parts[1];

            if (empty($result[$domain])) {
                $result[$domain] = 0;
            }

            $result[$domain] ++;
        }
    }

} while ($i == $limit);


$end = microtime(true);

echo "\nDone!\nScript execution time: " . ($end - $start) . " seconds.\nRecords processed: " . $recordsCount . "\n";

// Save result
var_dump($result);
