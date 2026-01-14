<?php
if(!isset($_GET['id'])) {
    header("Location: chicken.php?messages=Missing id");
    exit;
}

include 'dbcon.php';

$id = intval($_GET['id']);
if($id <= 0) {
    header("Location: chicken.php?messages=Invalid id");
    exit;
}

$query = "DELETE FROM `students` WHERE id = $id";
$res = mysqli_query($connection, $query);
if(!$res) {
    die("Delete failed: " . mysqli_error($connection));
}

header("Location: chicken.php?messages=Student Deleted");
exit;
