<?php
include_once 'FileSeekableIterator.php';
include_once 'LogSeekableIterator.php';

$fileName = 'test.log';
echo 'FileSeekableIterator:<br/>';
try {

    $fileIterator = new FileSeekableIterator($fileName);

    $fileIterator->seek(1);
    echo $fileIterator->current(), "<br/>";
    echo var_export($fileIterator->valid(), true) . "<br/>";
    echo $fileIterator->current() . "<br/>";
    $fileIterator->next();
    echo $fileIterator->current(), "<br/>";
    echo $fileIterator->key(), "<br/>";
    $fileIterator->seek(100);
    echo $fileIterator->current() . "<br/>";
    $fileIterator->seek(-100);
    echo $fileIterator->current() . "<br/>";
    echo var_export($fileIterator->valid(), true) . "<br/>";
    $fileIterator->rewind();
    echo $fileIterator->current() . "<br/>";
    $fileIterator->seek('1.4');
    echo $fileIterator->current(), "<br/>";
//    $fileIterator->seek('lul');
} catch (ErrorException $e) {
    echo $e->getMessage();
}

echo "<br/><br/><br/>";

$logName = 'log.log';
echo '<br/>LogSeekableIterator:<br/>';
try {

    $logIterator = new LogSeekableIterator($logName);

    echo var_export($logIterator->current()) . "<br/>";
    $logIterator->next();
    echo var_export($logIterator->current()), "<br/>";
    echo $logIterator->key(), "<br/>";
    $logIterator->seek(100);
    echo var_export($logIterator->current()) . "<br/>";
    echo var_export($logIterator->valid(), true) . "<br/>";
    $logIterator->rewind();
    echo var_export($logIterator->current()) . "<br/>";
    $logIterator->saveMap('map.txt') . "<br/>";
} catch (ErrorException $e) {
    echo $e->getMessage();
}