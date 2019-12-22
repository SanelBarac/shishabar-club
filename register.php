<?php
    require 'config/config.php';
	
	$username = "";
	$email = "";
	$password = "";
	$password2 = "";
	$date = "";
	$error_array = array();

	if (isset($_POST['register_button'])) {
		
		$username = strip_tags($_POST['reg_username']);
		$email = str_replace(' ', '_', $email);
		$_SESSION['reg_username'] = $username;
	    
		$email = strip_tags($_POST['reg_email']);
		$email = str_replace(' ', '', $email);
		$_SESSION['reg_email'] = $email;
		
		$password = strip_tags($_POST['reg_password']);
		$_SESSION['reg_password'] = $password;
		
	    $password2 = strip_tags($_POST['reg_password2']);
		$_SESSION['reg_password2'] = $password2;
		
		$date = date("Y-m-d");

        if(filter_var($email, FILTER_VALIDATE_EMAIL)){
		    $email = filter_var($email, FILTER_VALIDATE_EMAIL);
		
		    $email_check = mysqli_query($con, "SELECT email FROM users WHERE email='$email'");
		    $num_rows = mysqli_num_rows($email_check);
		
	        if($num_rows > 0){
                array_push($error_array, "<span>Email already in use!</span><br>");
	        }
		
	        }else{
		     array_push($error_array, "<span>Invalid email format!</span><br>");
	        }
		
	    if(strlen($username) > 50 || strlen($username) < 2){
			array_push($error_array, "<span>Username must be between 2 and 50 characters!</span><br>");
		}
		
		if($password != $password2){
			array_push($error_array, "<span>Your passwords do not match!</span><br>");
	    }else{
		     if(preg_match('/[^A-Za-z0-9]/', $password)){
				 array_push($error_array, "<span>Your password can only contain alphabet characters or numbers!</span><br>");
			 }
	         else if(strlen($password) > 30 || strlen($password) < 5){
			 array_push($error_array, "<span>Pasword must be between 5 and 30 characters!</span><br>");
		     }
	    }
		
		if(empty($error_array)){
		   $password = md5($password);
		   $username_check = mysqli_query($con, "SELECT username FROM users WHERE username='$username'");
	     
		   $i=0;
		   
		   while(mysqli_num_rows($username_check) != 0){
			   $i++;
			   $username = $username.$i;
			   $username_check = mysqli_query($con, "SELECT username FROM users WHERE username='$username'");
		   }
		   
		   //Profile picture assignment
		   $profile_pic = "assets/images/profile_pics/defaults/default_profile_man.jpg"; 

		   $query = mysqli_query($con, "INSERT INTO users (username, email, password, signup_date, profile_pic, friend_array) VALUES ('$username', '$email', 
		   	'$password', '$date', '$profile_pic', ',')");
		   
		   array_push($error_array, "<span style='color: #14C800;'>Registration successful! Please login.</span><br>");

		   //Clear session variables 
		   $_SESSION['reg_username'] = "";
		   $_SESSION['reg_email'] = "";

		}
	
	}
	
	if (isset($_POST['login_button'])) {
		$username = $_POST['log_username'];
		$_SESSION['log_username'] = $username;
		
		$password = md5($_POST['log_password']);
		
		$login_query = mysqli_query($con, "SELECT * FROM users WHERE username='$username' AND password='$password'");
	    $check_login_query = mysqli_num_rows($login_query);
	
		if($check_login_query == 1){
            $row = mysqli_fetch_array($login_query);
			$username = $row['username'];
			
			$user_closed_querry = mysqli_query($con, "SELECT * FROM users WHERE username='$username' AND user_closed='yes'");
			
			if(mysqli_num_rows($user_closed_querry) == 1){
                $reopen_account = mysqli_query($con, "UPDATE users SET user_closed='no' WHERE username='$username'");
		    }
			
			$_SESSION['username'] = $username;
			header("Location: index.php");
			exit();
	    }else{
			array_push($error_array, "<span>Username or password was incorrect!</span><br>");	
		}	
	}
	
?>

<!DOCTYPE html>
<html>
<head>
	<title>Registration Form</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="assets/css/register_style.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
	<script src="assets/js/register.js"></script>
</head>
<body>
      <?php 
		   if(isset($_POST['register_button'])){
		       if(!in_array("<span style='color: #14C800;'>Registration successful! Please login.</span><br>", $error_array)){
			       echo '
			          <script>
		                 $(document).ready(function(){
			                $("#first").hide();
						    $("#second").show();
		                 });
				      </script>
			       ';
		        }
		   }
		?>
	    <div class="wrapper">
          
	            <div class="login_box">
				    <div class="login_header">
			            <h1>Shisha Bar</h1>
						Login or register
				    </div>
					<div id="first">
	                <form action="register.php" method="post" > 
		                <input type="text" name="log_username" placeholder="Username..." value="<?php 
                             if (isset($_SESSION['log_username'])){
				             echo $_SESSION['log_username'];
			                 } 
			                 ?>" required>
			            <br>
			            <input type="password" name="log_password" placeholder="Password..." required>
			            <br>
			            <input type="submit" name="login_button" value="Login">
			            <br>
						<?php if (in_array("<span>Username or password was incorrect!</span><br>", $error_array)) echo "<span>Username or password was incorrect!</span><br>";
			                  else if (in_array("<span style='color: #14C800;'>Registration successful! Please login.</span><br>", $error_array)) echo "<span style='color: #14C800;'>Registration successful! Please login.</span><br>"; ?>
						<a href="#" id="signup" class="signup">Need an account? Register here!</a><hr>
						<a href="#" id="forgot_password" class="signup">Forgot password? Click here!</a>
		            </form>
					</div>
	                <div id="second">
	                <form action="register.php" method="post" > 
		                 <input type="text" name="reg_username" placeholder="Username..." value="<?php 
                          if (isset($_SESSION['reg_username'])){
				          echo $_SESSION['reg_username'];
			              }			
			              ?>" required>
			             <br>
			             <?php if (in_array("<span>Username must be between 2 and 50 characters!</span><br>", $error_array)) echo "<span>Username must be between 2 and 50 characters!</span><br>"; ?>		
		               	 <input type="email" name="reg_email" placeholder="Email..." value="<?php 
                         if (isset($_SESSION['reg_email'])){
				         echo $_SESSION['reg_email'];
			             }			
			             ?>" required>
			             <br>
			             <?php if (in_array("<span>Email already in use!</span><br>",$error_array)) echo "<span>Email already in use!</span><br>";
			                   else if (in_array("<span>Invalid email format!</span><br>",$error_array)) echo "<span>Invalid email format!</span><br>"; ?>
			
			             <input type="password" name="reg_password" placeholder="Password..." required>
			             <br>
			             <input type="password" name="reg_password2" placeholder="Confirm Password..." required>
			             <br>
			             <?php if (in_array("<span>Your passwords do not match!</span><br>",$error_array)) echo "<span>Your passwords do not match!</span><br>";
			                   else if (in_array("<span>Your password can only contain alphabet characters or numbers!</span><br>",$error_array)) echo "<span>Your password can only contain alphabet characters or numbers!</span><br>";
			                   else if (in_array("<span>Pasword must be between 5 and 30 characters!</span><br>",$error_array)) echo "<span>Pasword must be between 5 and 30 characters!</span><br>"; ?>
			
			             <input type="submit" name="register_button" value="Create Account">
			             <br>      
		                 <a href="#" id="signin" class="signin">Already have an account? Sign in here!</a>
					</form>
					</div>
		   </div>
	  </div>

</body>
</html>