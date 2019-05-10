<?php

$login = "chris@gigabyte.software";
$password = "fr%XUtC7Balloon";

$auth = "chris@gigabyte.software:fr%XUtC7Balloon";

echo 'login:- ' . base64_encode($login);
echo '<br>';
echo 'password - ' . base64_encode($password);
echo '<br>';
echo 'login: password - ' . base64_encode($auth);
?>
