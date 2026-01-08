<?php

define("HOSTNAME", "localhost");
define("USERNAME", "root");
define("PASSWORD", "");
define("DATABASE", "football_data_storage");

try {
    $connection = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE);
} catch (mysqli_sql_exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

if (!$connection)
{
    die("Database connection failed: " . mysqli_connect_error());
}
else 
{
    echo "Database connected successfully";
}