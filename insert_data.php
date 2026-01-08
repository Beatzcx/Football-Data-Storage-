<?php

if(isset($_POST['add_student']))
{
    include 'dbcon.php';

    $fname = $_POST['f_name'];
    $lname = $_POST['l_name'];
    $age = $_POST['age'];
    
        if(trim($fname) === "")
        {
            header("Location: chicken.php?messages=You need to fill First Name");
        }

    }

?>