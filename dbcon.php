<?php

define("HOSTNAME", "localhost");
define("USERNAME", "root");
define("PASSWORD", "");
define("DATABASE", "football_data_storage");

$connection = new mysqli(HOSTNAME, USERNAME, PASSWORD, DATABASE);

if (!$connection) {
    die("Database connection failed: " . $connection->connect_error);
}
else 
{
    // echo "Database connected successfully";
}