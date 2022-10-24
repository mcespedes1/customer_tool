<?php
$host="localhost"; // Host name 
$username="entuser"; // Mysql username 
$password="twSnFbbLW5VI0D8T"; // Mysql password 
$db_name="entitlement"; // Database name 

// Connect to server and select databse.
$conn = mysqli_connect("$host", "$username", "$password")or die("cannot connect"); 

mysqli_select_db($conn, "$db_name")or die("cannot select DB");
?>