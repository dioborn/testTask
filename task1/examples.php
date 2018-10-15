<?php
$apiUrl = 'http://localhost/tests/photoCountryTask/task1/api.php';
$user_id = 1;
$catgory_id = 1;


/* Get News */
$url = $apiUrl . '?request=news&action=getlist&categories[]=1&categories[]=3&id=1';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$newsList = curl_exec($ch);
curl_close($ch);
echo '<b>Get News:</b> GET / ' . $url . '<br/><br/>';
echo $newsList;
echo '<br/><br/><br/>';


/* Add News */
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, array(
    'request' => 'news',
    'action' => 'add',
    'title' => 'title',
    'text' => 'text',
    'category' => $catgory_id,
    'user_id' => $user_id
));
$newNews = curl_exec($ch);
curl_close($ch);
echo '<b>Add News:</b> POST<br/><br/>';
echo $newNews;
echo '<br/><br/><br/>';

/* Set like */
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, array(
    'request' => 'likes',
    'action' => 'like',
    'id' => json_decode($newNews, true)['data']['id']
));
$setLike = curl_exec($ch);
curl_close($ch);
echo '<b>Set like:</b> POST<br/><br/>';
echo $setLike;
echo '<br/><br/><br/>';

/* Get likes list */
$url = $apiUrl . '?request=likes&action=getlist&id=' . json_decode($newNews, true)['data']['id'];
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$likesList = curl_exec($ch);
curl_close($ch);
echo '<b>Get likes list:</b> GET / ' . $url . '<br/><br/>';
echo $likesList;
echo '<br/><br/><br/>';

/* Unset like */
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, array(
    'request' => 'likes',
    'action' => 'unlike',
    'id' => json_decode($newNews, true)['data']['id']
));
$unsetLike = curl_exec($ch);
curl_close($ch);
echo '<b>Set like:</b> POST<br/><br/>';
echo $unsetLike;
echo '<br/><br/><br/>';


/* Error example */
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, array(
    'request' => 'news',
    'action' => 'add'
));
$newNews = curl_exec($ch);
curl_close($ch);
echo '<b>Error example (try to add news):</b> POST<br/><br/>';
echo $newNews;
echo '<br/><br/><br/>';