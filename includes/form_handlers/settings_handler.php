<?php 

if(isset($_POST['update_password'])){
	$old_password = strip_tags($_POST['old_password']);
	$new_password_1 = strip_tags($_POST['new_password_1']);
    $new_password_2 = strip_tags($_POST['new_password_2']);
	
	$password_query = mysqli_query($con, "SELECT password FROM users WHERE username='$userLoggedIn'");
	$row = mysqli_fetch_array($password_query);
	$db_password = $row['password'];

	if (md5($old_password) == $db_password) {
		if ($new_password_1 == $new_password_2) {
			if (strlen($new_password_1) <= 4) {
				$password_message = "Sorry, your password must be greater than 4 characters!<br><br>";
			}else{
				$new_password_md5 = md5($new_password_1);
                $password_query = mysqli_query($con, "UPDATE users SET password='$new_password_md5' WHERE username='$userLoggedIn'");
                $password_message = "Password has been changed!<br><br>";
			}
		}else{
			$password_message = "Your new passwords don´t match!<br><br>";
		}
	}else{
		$password_message = "The old password is incorrect!<br><br>";
	}		       
}else{
	$password_message = "";
}

if(isset($_POST['close_account'])){
	header("Location: close_account.php");
}

?>
