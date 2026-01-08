<?php include 'header.php'; ?>
<?php include 'dbcon.php'; ?>

            <thead>
                <tr>
                    <th>ID</th>
                    <th>FIRST NAME</th>
                    <th>LAST NAME</th>
                    <th>AGE</th>
                </tr>
            </thead>
            <tbody>
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
                          </tr>

                          <?php
                    }
                }
                
                ?>
                <tr>
                 <td>3</td>
                 <td>John</td>
                    <td>Doe</td>
                    <td>25</td>
                </tr>
                <tr>
                 <td>4</td>
                 <td>Jane</td>
                    <td>Smith</td>
                    <td>30</td>
                </tr>
                <tr>
                 <td>5</td>
                 <td>Emily</td>
                    <td>Jones</td>
                    <td>22</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>