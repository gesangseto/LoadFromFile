<?php
$servername = "13.251.59.9";
$username = "gesang";
$password = "gesang";
$database = "logger";
$mysqli = mysqli_connect($servername, $username, $password, $database);
mysqli_options($mysqli, MYSQLI_OPT_LOCAL_INFILE, true);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
} else {
    echo "<font color='green'>Connected database successfully</font>";
    echo "<br>";
}
