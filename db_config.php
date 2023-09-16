<?php
$db_host = 'localhost';
$db_username = 'root';
$db_password = '';
$db_name = 'youtube_db';


$db = new mysqli($db_host, $db_username, $db_password, $db_name);


if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
?>