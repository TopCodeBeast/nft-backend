<?php
$servername = "localhost";
$username = "assessmentsdb";
$password = "Dreamz@2020#";
$dbname="assessments";
$conn = new mysqli($servername, $username, $password, $dbname);
$conn->query("SET NAMES utf8");
mysqli_set_charset($conn,"utf8");
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
// $con=mysqli_connect("localhost","sicsappc_devteam","reinforcement@db","sicsappc_rize");
// // Check connection
// if (mysqli_connect_errno())
// {
//     echo "Failed to connect to MySQL: " . mysqli_connect_error();
// }

    $postData = file_get_contents('php://input');
    //perform your processing here, e.g. log to file....
    $file = fopen("log.txt", "w"); //url fopen should be allowed for this to occur
    if(fwrite($file, $postData) === FALSE)
    {
        // fwrite("Error: no data written");
    }
    $result = json_decode($postData,true);
    
    fclose($file);
?>