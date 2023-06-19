<?php


     
        // Database info
    $servername = "186.67.203.131";
    $username = "oraculoGP";
    $password = "0r4cul0GP";    
    $dbname = "OraculoGP";
    
    // Check connection
    $conn = mysqli_connect($servername, $username, $password, $dbname);
        if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
        }




?> 
