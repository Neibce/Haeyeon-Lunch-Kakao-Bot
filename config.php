<?php
$username = 'rex711';
$password = '{YOUR_PASSWORD}';
$link = mysqli_connect('localhost', $username, $password, 'rex711');

if (!$link)
  exit;

$link->set_charset("utf8");
?>