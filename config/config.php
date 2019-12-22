<?php  
    ob_start(); //Turns on output buffering
    session_start();

    $timezone = date_default_timezone_set("Europe/Sarajevo");

    $con = mysqli_connect("shishabar.club.mysql", "shishabar_club", "XjE2tYxU3MA3vihjbZpFgpqL", "shishabar_club");
	
	if(mysqli_connect_errno()){
	   echo "Failed to connect: " . mysqli_connect_errno();
	}
?>

  