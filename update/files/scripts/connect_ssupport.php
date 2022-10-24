<?php
$host="localhost"; // Host name 
$username="entuser"; // Mysql username 
$password="twSnFbbLW5VI0D8T"; // Mysql password 
$db_name="sales_support"; // Database name 

// Connect to server and select databse.
$ss_conn = mysqli_connect("$host", "$username", "$password")or die("cannot connect"); 

mysqli_select_db($ss_conn, "$db_name")or die("cannot select DB");
?>