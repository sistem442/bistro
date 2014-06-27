<?php
$con=mysqli_connect("localhost","boris", "fubar.909", "bistro");
$mysqli = new mysqli("localhost", "boris", "fubar.909", "bistro");
if ($mysqli->connect_errno) {
    printf("Connect failed: %s\n", $mysqli->connect_error);
    exit();
}
$mysqli->real_query("SET NAMES utf8");
$mysqli->real_query("SET CHARACTER SET utf8");
$mysqli->real_query("SET COLLATION_CONNECTION='utf8_slovenian_ci'");
?>