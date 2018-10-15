<?php
$dbName = 'test';
$username = 'root';
$pass = '';
$chunk = 100000;

date_default_timezone_set('UTC');
error_reporting(E_ALL);
ini_set('memory_limit', '128M');
//ini_set('memory_limit', '2048M');
ini_set('display_errors', 'on');

$start = microtime(true);

$db = new PDO('mysql:host=localhost;dbname=' . $dbName, $username, $pass);

$result = array();
$recordNum = 0;       // Records counter
do {
    $sth = $db->prepare("select email from users order by id asc limit " . $recordNum . ", " . $chunk);
    $sth->execute();
    $i = 0;

    while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {

        $recordNum++;
        $i++;

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

} while ($i == $chunk);


$end = microtime(true);

echo "\nDone!\nScript execution time: " . ($end - $start) . " seconds.\nRecords processed: " . $recordNum . "\n";

// Save result
var_dump($result);
