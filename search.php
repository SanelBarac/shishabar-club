<?php 
include ("includes/header.php");

if(isset($_GET['q'])){
	$query = $_GET['q'];
}else{
	$query = "";
}
 ?>

<div class="main_column column" id="main_column">
	<?php 
         if ($query == "") 
         	echo "You must enter something in the search box.";
         else
         	$users_returned_query = mysqli_query($con, "SELECT * FROM users WHERE username LIKE '$query%' AND user_closed='no'");

         //Check if results were found
         if (mysqli_num_rows($users_returned_query) == 0) 
    	     echo "We can´t find anyone with a username like: " . $query;
    	 else if (mysqli_num_rows($users_returned_query) == 1)
    	 	 echo mysqli_num_rows($users_returned_query) . " username found: <br><br>";
    	 else
    	 	 echo mysqli_num_rows($users_returned_query) . " usernames found: <br><br>";
         
         while ($row = mysqli_fetch_array($users_returned_query)) {
         	 $user_obj = new User($con, $user['username']);

         	 $button = "";
         	 $mutual_friends = "";

         	 if ($user['username'] != $row['username']) {
         	 	//Generate button depending on friendship status
         	 	if ($user_obj->isFriend($row['username'])) 
         	 		$button = "<input type='submit' name='".$row['username']."' class='danger' value='Remove Friend'>";
         	 	elseif ($user_obj->didRecieveRequest($row['username'])) 
         	 		$button = "<input type='submit' name='".$row['username']."' class='warning' value='Respond to Request'>";
         	 	elseif ($user_obj->didSendRequest($row['username'])) 
         	 		$button = "<p>Request Sent</p>";
         	 	else
         	 		$button = "<input type='submit' name='".$row['username']."' class='success' value='Add Friend'>";

         	 	$mutual_friends = $user_obj->getMutualFriends($row['username']) . " friends in common";

         	 	//Button forms
                if (isset($_POST[$row['username']])) {
                    if ($user_obj->isFriend($row['username'])) {
                        $user_obj->removeFriend($row['username']);
                        header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
                    }elseif ($user_obj->didRecieveRequest($row['username'])) {
                        header("Location: requests.php");
                    }elseif ($user_obj->didSendRequest($row['username'])) {
                        
                    }else{
                        $user_obj->sendRequest($row['username']);
                        header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
                    }
                }
         	 }

         	 echo "<div class='search_result'>
                        <div class='searchPageFriendButtons'>
                            <form action='' method='POST'>
                                 ".$button."
                                 <br>
                            </form>
                        </div>

                        <div class='result_profile_pic'>
                             <a href='".$row['username']."'><img src='".$row['profile_pic']."' style='height:100px;'></a>
                        </div>
                        <b><a href='".$row['username']."'>".$row['username']."</a></b>
                        <br><br><br><br><br>
                        ".$mutual_friends."<br>
                   </div>
                   <hr>"
         	 ;
         }
	 ?>
</div>