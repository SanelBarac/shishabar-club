<?php
    include ("../../config/config.php");
    include ("../../includes/classes/User.php");

    $query =  $_POST['query'];
    $userLoggedIn =  $_POST['userLoggedIn'];

    $users_returned_query = mysqli_query($con, "SELECT * FROM users WHERE username LIKE '$query%' AND user_closed='no' LIMIT 8");

    if ($query != "") {
    	while ($row = mysqli_fetch_array($users_returned_query)) {
    		$user = new User($con, $userLoggedIn);

    		if ($row['username'] != $userLoggedIn) {
    			$mutual_friends = $user->getMutualFriends($row['username']) . " friends in common";
    		}else{
    			$mutual_friends = "";
    		}

    		echo "<div class='resultDisplay'>
    		          <a href='".$row['username']."' style='color: #1485BD'>
    		              <div class='liveSearchProfilePic'>
                               <img src='".$row['profile_pic']."'>
    		              </div>
    		              <div class='liveSearchText'>
    		                   <b><p style='font-size: 14px;'>".$row['username']."</p></b>
    		                   <p id='grey' style='font-size: 14px;'>".$mutual_friends."</p>
    		              </div>
    		          </a>
    		      </div>"
    		;
    	}
    }
    
?>