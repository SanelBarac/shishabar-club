<?php  
    class Notification{
		private $con;
		private $user_obj;
		 
	    public function __construct($con, $user){
		    $this->con = $con;
			$this->user_obj = new User ($con, $user);
	     }

	    public function getUnreadNumber(){
	        $userLoggedIn = $this->user_obj->getUsername();
	        $query = mysqli_query($this->con, "SELECT * FROM notifications WHERE viewed='no' AND user_to='$userLoggedIn'");
	        return mysqli_num_rows($query);
        }

        public function insertNotification($post_id, $user_to, $type) {
            $userLoggedIn = $this->user_obj->getUsername();
            $date_time = date("Y-m-d H:i:s");

             switch ($type) {
             	case 'comment':
             		$message = $userLoggedIn . " commented on your post";
             		break;
             	case 'like':
             		$message = $userLoggedIn . " liked your post";
             		break;
                case 'profile_post':
             		$message = $userLoggedIn . " posted on your profile";
             		break;
             	case 'comment_non_owner':
             		$message = $userLoggedIn . " commented on post you commented on";
             		break;
             	case 'profile_comment':
             		$message = $userLoggedIn . " commented on your profile post";
             		break;
             }
           	   
           	 $link = "post.php?id=" . $post_id;

           	 $insert_query = mysqli_query($this->con, "INSERT INTO notifications (user_to, user_from, message, link, date_added, opened, viewed) VALUES ('$user_to', '$userLoggedIn', '$message', '$link', '$date_time', 'no', 'no')");
        }

        public function getNotifications($data, $limit) {

            $page = $data['page'];
            $userLoggedIn = $this->user_obj->getUsername();
            $return_string ="";

            if ($page == 1) 
               $start = 0;
            else
               $start = ($page - 1) * $limit;

            $set_viewed_query = mysqli_query($this->con, "UPDATE notifications SET viewed='yes' WHERE user_to='$userLoggedIn'");

            $query = mysqli_query($this->con, "SELECT * FROM notifications WHERE user_to='$userLoggedIn' ORDER BY id DESC");

            if (mysqli_num_rows($query) == 0) {
            	echo "You have no notifiations!";
            	return;
            }

            $num_iterations = 0; //Number of messages checked
            $count = 1; //Number of messages posted
            
            while ($row = mysqli_fetch_array($query)) {
                if ($num_iterations++ < $start)
                    continue;

                if($count > $limit)
                    break;
                else
                   $count++;

               $user_from = $row['user_from'];
               $user_data_query = mysqli_query($this->con, "SELECT * FROM users WHERE username='$user_from'");
               $user_data = mysqli_fetch_array($user_data_query);

               //Timeframe
		        $date_time_now = date("Y-m-d H:i:s");
		        $start_date = new DateTime($row['date_added']);//Time of post
		        $end_date = new DateTime($date_time_now);//Current time
		        $interval = $start_date->diff($end_date);//Difference between dates

		        if ($interval-> y >= 1) {
		               	   if ($interval == 1) 
		               	   	   $time_message = $interval-> y . "year ago";
		               	   	else
		               	   		$time_message = $interval-> y . "years ago";        	   
		        }elseif ($interval-> m >= 1) {
		               	    if ($interval-> d == 0) {
		               	    	$days = " ago";
		               	    }elseif ($interval-> d == 1) {
		               	    	$days = $interval-> d . " day ago";
		               	    }else{
		               	    	$days = $interval-> d . " days ago";
		               	    }

		               	    if ($interval-> m == 1) {
		               	    	$time_message = $interval-> m . "month" . $days;
		               	    }else{
		               	    	$time_message = $interval-> m . "months" . $days;
		               	    }
		        }elseif ($interval-> d >= 1) {
		               	    if ($interval-> d == 1) {
		               	    	$time_message = "Yesterday";
		               	    }else{
		               	    	$time_message = $interval-> d . " days ago";
		               	    }
		        }elseif ($interval-> h >= 1) {
		               	    if ($interval-> h == 1) {
		               	    	$time_message = $interval-> h . " hour ago";
		               	    }else{
		               	    	$time_message = $interval-> h . " hours ago";
		               	    }
		        }elseif ($interval-> i >= 1) {
		               	    if ($interval-> i == 1) {
		               	    	$time_message = $interval-> i . " minute ago";
		               	    }else{
		               	    	$time_message = $interval-> i . " minutes ago";
		               	    }
		        }else{
		               	    if ($interval-> s < 30) {
		               	    	$time_message = "Just now";
		               	    }else{
		               	    	$time_message = $interval-> s . " seconds ago";
		               	    }
		        }

                $opened = $row['opened'];
                $style = ($row['opened'] == 'no') ? "background-color: #DDEDFF;" : "";

                $return_string .= "<a href='".$row['link']."'>
                                  <div class='resultDisplay resultDisplayNotification' style='".$style."'>
                                  <div class='notificationsProfilePic'>
                                       <img src='".$user_data['profile_pic']."'>
                                  </div>
                                  <p class='timestamp_smaller' id='grey'>".$time_message."</p>".$row['message']."
                                  </div>
                                  </a>";
            }
            //if posts were loaded
            if($count > $limit)
                    $return_string .= "<input type='hidden' class='nextPageDropdownData' value='".($page + 1)."'><input type='hidden' class='noMoreDropdownData' value='false'>";
                else
                    $return_string .= "<input type='hidden' class='noMoreDropdownData' value='true'><p style='text-align: center;'>No more notifications to load!</p>";

            return $return_string;

        }
           
        
	}

 ?>