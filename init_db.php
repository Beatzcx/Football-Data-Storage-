<?php
/**
 * init_db.php
 *
 * Simple database initializer for the project.
 * - Creates the `football_data_storage` database (if missing)
 * - Creates the `students` table (if missing)
 * - Optionally inserts a couple of sample rows (commented by default)
 *
 * USAGE:
 * 1) Edit the credentials below if needed.
 * 2) Run once in your browser: http://localhost/xampp/footballDataStorage/Football-Data-Storage-/init_db.php
 * 3) Remove this file after successful run for security.
 */

$host   = 'localhost';
$user   = 'root';
$pass   = 'password'; // change if your MySQL root password differs
$dbname = 'football_data_storage';

// Connect without selecting a DB first
$conn = mysqli_connect($host, $user, $pass);
if (!$conn) {
    die('Connect error: ' . mysqli_connect_error());
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS `" . $dbname . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (!mysqli_query($conn, $sql)) {
    die('Database create failed: ' . mysqli_error($conn));
}

// Select the database
if (!mysqli_select_db($conn, $dbname)) {
    die('Select DB failed: ' . mysqli_error($conn));
}

// Create `students` table
$createTable = "CREATE TABLE IF NOT EXISTS `students` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `age` INT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!mysqli_query($conn, $createTable)) {
    die('Table create failed: ' . mysqli_error($conn));
}

// Optional: insert sample rows (uncomment if you want example data)
/*
$sample = "INSERT INTO `students` (`first_name`,`last_name`,`age`) VALUES
    ('John','Doe',25),
    ('Jane','Smith',30),
    ('Emily','Jones',22)
    ON DUPLICATE KEY UPDATE first_name=VALUES(first_name)";
if (!mysqli_query($conn, $sample)) {
    die('Insert sample data failed: ' . mysqli_error($conn));
}
*/

echo "Database and table are ready.\n";
echo "Delete init_db.php after use for security.\n";

mysqli_close($conn);

?>
