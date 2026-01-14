<?php include 'header.php'; ?>

<?php
?>
<?php include 'dbcon.php'; ?>


    <div class = "box1">
    <h2>ALL STUDENTS </h2>
    <link rel="stylesheet" type="text/css" href="style.css">

    <button id="hi", class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">ADD STUDENTS</button>
            <thead>
                
                <tr>
                    <th>ID</th>
                    <th>FIRST NAME</th>
                    <th>LAST NAME</th>
                    <th>AGE</th>
                    <th>UPDATE</th>
                    <th>DELETE</th>
                </tr>
      
                <?php
                $query = "SELECT * FROM `students`";

                $result = mysqli_query($connection, $query);

                if(!$result)
                {
                    die("Query Failed " . mysqli_error($connection));
                }
                else
                {
                    while($row = mysqli_fetch_assoc($result))
                    {
                        ?>
                       <tr>
                        <td> <?php echo $row['id']; ?></td>
                        <td> <?php echo $row['first_name']; ?> </td>
                        <td> <?php echo $row['last_name']; ?> </td>
                        <td> <?php echo $row['age']; ?> </td>
                        <td> <a href="edit_student.php?id=<?php echo $row['id']; ?>" class="btn btn-success">UPDATE</a> </td>
                        <td> <a href="delete_student.php?id=<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Delete this student?')">DELETE</a> </td>
                          </tr>

                          <?php
                    }
                }

        
                
                ?>
                
<h6><?php if(isset($_GET['messages'])) { echo $_GET['messages']; } ?></h6>

                <!-- Modal -->
                 <form action = "insert_data.php" method="POST">
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">ADD STUDENTS</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="form-group">
            <label for = "f_name"> First Name</label>
            <input type="text" class="form-control" name="f_name">
        </div>
        <div class="form-group">
            <label for = "l_name"> Last Name</label>
            <input type="text" class="form-control" name="l_name">
      </div>
       <div class="form-group">
            <label for = "age"> Age</label>
            <input type="text" class="form-control" name="age">
      </div>
              
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <input type="submit" class="btn btn-primary" name="add_student" value="ADD">
      </div>
    </div>
  </div>
</div>
</form>
               <?php include 'footer.php'; ?>