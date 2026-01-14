<?php
include 'dbcon.php';

if(!isset($_GET['id'])) {
    header("Location: chicken.php?messages=Missing id");
    exit;
}

$id = intval($_GET['id']);
if($id <= 0) {
    header("Location: chicken.php?messages=Invalid id");
    exit;
}

$query = "SELECT * FROM `students` WHERE id = $id LIMIT 1";
$res = mysqli_query($connection, $query);
if(!$res) {
    die("Query failed: " . mysqli_error($connection));
}

$student = mysqli_fetch_assoc($res);
if(!$student) {
    header("Location: chicken.php?messages=Student not found");
    exit;
}

include 'header.php';
?>

<div class="box1">
    <h2>Edit Student</h2>
    <form action="insert_data.php" method="POST" class="edit-form">
        <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
        <div class="form-group">
            <label>First name</label>
            <input type="text" name="f_name" class="form-control" value="<?php echo htmlspecialchars($student['first_name']); ?>">
        </div>
        <div class="form-group">
            <label>Last name</label>
            <input type="text" name="l_name" class="form-control" value="<?php echo htmlspecialchars($student['last_name']); ?>">
        </div>
        <div class="form-group">
            <label>Age</label>
            <input type="number" name="age" class="form-control" value="<?php echo htmlspecialchars($student['age']); ?>">
        </div>
        <div style="margin-top:10px">
            <a href="chicken.php" class="btn btn-secondary">Cancel</a>
            <input type="submit" name="update_student" value="Save Changes" class="btn btn-primary">
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>
