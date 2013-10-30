<?php

$data .= $_SERVER['SERVER_NAME']."\n";
$data .= print_r($_POST, true);
$data .= print_r($_GET, true);
$data .= "-----\n";

file_put_contents('data', $data);

?>
