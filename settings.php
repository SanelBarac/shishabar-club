<?php 
include ("includes/header.php");
include ("includes/form_handlers/settings_handler.php");
 ?>

<div class="main_column column">
	<h4><b><?php echo $user['username'];?></b> Account Settings</h4>
	<?php 
         echo "<img src='".$user['profile_pic']."' class='small_profile_pic'>";
	 ?>
	 <br>
	 <br>
	 <a href="upload.php">Upload new profile picture</a><br><br><hr>

	 <h5>Change Password</h5>
     <form action="settings.php" method="POST">
	 	Old Password: <input type="password" name="old_password" id="settings_input"><br>
	 	New Password: <input type="password" name="new_password_1" id="settings_input"><br>
	 	New Password Again: <input type="password" name="new_password_2" id="settings_input"><br>
	 	<?php echo $password_message; ?>
	 	<input type="submit" class="info settings_submit" name="update_password" id="close_account" value="Update Password">
	 </form><hr>

	 <h5>Close Account</h5>
	 <form action="settings.php" method="POST">
	 	<input type="submit" class="danger settings_submit" name="close_account" id="close_account" value="Close Account">
	 </form>

</div>