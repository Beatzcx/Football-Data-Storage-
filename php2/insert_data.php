<?php

if(isset($_POST['add_student']) || isset($_POST['update_student']))
{
    include 'dbcon.php';

    $fname = mysqli_real_escape_string($connection, trim($_POST['f_name'] ?? ''));
    $lname = mysqli_real_escape_string($connection, trim($_POST['l_name'] ?? ''));
    $age = mysqli_real_escape_string($connection, trim($_POST['age'] ?? ''));

    if($fname === '')
    {
        header("Location: chicken.php?messages=You need to fill First Name");
        exit;
    }

    if(isset($_POST['add_student']))
    {
        $query = "INSERT INTO `students` (first_name, last_name, age) VALUES ('$fname', '$lname', '$age')";
        $result = mysqli_query($connection, $query);

        if(!$result)
        {
            die("Query Failed " . mysqli_error($connection));
        }
        else
        {
            header("Location: chicken.php?messages=Student Added Successfully");
            exit;
        }
    }

    if(isset($_POST['update_student']))
    {
        $id = intval($_POST['id'] ?? 0);
        if($id <= 0)
        {
            header("Location: chicken.php?messages=Invalid student id");
            exit;
        }

        $query = "UPDATE `students` SET first_name='$fname', last_name='$lname', age='$age' WHERE id=$id";
        $result = mysqli_query($connection, $query);

        if(!$result)
        {
            die("Update Failed " . mysqli_error($connection));
        }
        else
        {
            header("Location: chicken.php?messages=Student Updated Successfully");
            exit;
        }
    }

}

?>