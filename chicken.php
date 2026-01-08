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
                        $id = $row['id'];
                        $first_name = $row['first_name'];
                        $last_name = $row['last_name'];
                        $age = $row['age'];

                        echo "<tr>
                        <td>$id</td>
                        <td>$first_name</td>
                        <td>$last_name</td>
                        <td>$age</td>
                        </tr>";
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