<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "echat";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
    // echo "No Database Connection";
}
//  else {
//     echo "Connected successfully";
// }


// $conn->close();

?>

