<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration | PHP</title>
</head>
<body>
<div>
<?php
if(isset($_POST['create'])){ 
echo 'User Registered Successfully.'; 

}





?>
</div>




<div>
<form action="registration.php" method="post">
    <div class="container">
    <h1>User Registration</h1>
    <label for="username">Username:</label><br>
    <input type="text" id="username" name="username" required><br><br>
    
    <label for="email">Email:</label><br>
    <input type="email" id="email" name="email" required><br><br>

     <label for="phonenumber">Phone Number:</label><br>
    <input type="phonenumber" id="phonenumber" name="phonenumber" required><br><br>
    
    
    <label for="password">Password:</label><br>
    <input type="password" id="password" name="password" required><br><br>
    
    <input type="submit" name="create" value="Register">



</form>

</div>



</body>
</html>