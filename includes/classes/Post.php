<?php
    class Post{
		 private $con;
		 private $user_obj;
		 
	     public function __construct($con, $user){
		    $this->con = $con;
			$this->user_obj = new User ($con, $user);
	     }
		 
	     public function submitPost($body, $user_to, $imageName){
			$body = strip_tags($body); //removes html tags
			$body = mysqli_real_escape_string($this->con, $body);
			$body = str_replace('\r\n', '\n', $body);
			$body = nl2br($body);

			$check_empty = preg_replace('/\s+/', '', $body);
			
			if ($check_empty != "" || $imageName != "") {
				$body_array = preg_split("/\s+/", $body);

				foreach ($body_array as $key => $value) {
					if (strpos($value, "www.youtube.com/watch?v=") !== false) {
						$link = preg_split("!&!", $value);
						$value = preg_replace("!watch\?v=!", "embed/", $link[0]);
						$value = "<br><iframe src=\'".$value."\' id=\'yt_iframe\'></iframe><br>";
						$body_array[$key] = $value;
					}
				}
				$body = implode(" ", $body_array);

				$date_added = date("Y-m-d H:i:s");
				$added_by = $this->user_obj->getUsername();

				if ($user_to == $added_by) {
							$user_to = "none";
						}	

			    //Insert post	
				$query = mysqli_query($this->con, "INSERT INTO posts (body, added_by, user_to, date_added, user_closed, deleted, image) VALUES ('$body', '$added_by', '$user_to', '$date_added', 'no', 'no', '$imageName')");
				$returned_id = mysqli_insert_id($this->con);

				//Insert notification
				if($user_to != "none"){
                    $notification = new Notification($this->con, $added_by);
                    $notification->insertNotification($returned_id, $user_to, "profile_post");
				}

				//Update post count for user
				$num_posts = $this->user_obj->getNumPosts();
				$num_posts++;
				$update_query = mysqli_query($this->con, "UPDATE users SET num_posts='$num_posts' WHERE username='$added_by'");	
				
			}	
	     }
	     
		public function loadPostsFriends($data, $limit) {
           $page = $data['page'];
           $userLoggedIn = $this->user_obj->getUsername();

           if($page == 1)
           	   $start = 0;
           else
           	   $start = ($page - 1) * $limit;

		   $str = "";
		   $data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' ORDER BY id DESC");

           if (mysqli_num_rows($data_query) > 0) {
           	  $num_iterations = 0; //Number of results checked (not necasserily posted)
           	  $count = 1;
           
			   while($row = mysqli_fetch_array($data_query)){
				   $id = $row['id'];
				   $body = $row['body'];
				   $body = str_replace('\r\n', '\n', $body);
			       $body = nl2br($body);
				   $added_by = $row['added_by'];			
				   $date_added = $row['date_added']; 
				   $imagePath = $row['image']; 
				      	     
			       if($row['user_to'] == "none"){
					   $user_to = "";
			       }else{
					   $user_to_obj = new User($this->con, $row['user_to']);
					   $user_to_username = $user_to_obj->getUsername();
					   $user_to = "to <a href='".$row['user_to']."'><b>".$user_to_username."</b></a>";
				   }

				   //Check if user who posted, has their account closed
				   $added_by_obj = new User($this->con, $added_by);
				   if ($added_by_obj->isClosed()) {
				   	   continue;
				   }

				   $user_logged_obj = new User($this->con, $userLoggedIn);
				   if ($user_logged_obj->isFriend($added_by)){

					   if ($num_iterations++ < $start) 
					   	   continue;
					   
					   //Once 10 posts have been loaded, break
					   if ($count > $limit){ 
					   	   break;
					   }else{
					   	   $count++;
					   }

					   	if ($userLoggedIn == $added_by) {
					   		$delete_button = "<button class='delete_button btn-danger' id='post".$id."'>X</button>";
					   	}else{
					   		$delete_button= "";
                        } 
					   	    
		               $user_details_query = mysqli_query($this->con, "SELECT username, profile_pic FROM users WHERE username='$added_by'");
		               $user_row = mysqli_fetch_array($user_details_query);
		               $username = $user_row['username'];
		               $profile_pic = $user_row['profile_pic'];

		               ?>
					     <script>
				            function toggle<?php echo $id; ?>(){
				            	var target = $(event.target);

				            	if (!target.is("a")) {

				            	    var element = document.getElementById("toggleComment" + <?php echo $id; ?>);
						 
					                if(element.style.display == "block"){
						                element.style.display = "none";
					                }else{
							            element.style.display = "block";
						            }	
				            	}
  
					        }
				         </script>	   
			           <?php

			           $comments_check = mysqli_query($this->con, "SELECT * FROM comments WHERE post_id='$id'");
			           $comments_check_num = mysqli_num_rows($comments_check);

		               //Timeframe
		               $date_time_now = date("Y-m-d H:i:s");
		               $start_date = new DateTime($date_added);//Time of post
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

		               if ($imagePath != "") {
		               	   $imageDiv = "<div class='postedImage'>
                                            <img src='$imagePath'>
		               	                </div>
		               	               ";
		               }else{
		               	   $imageDiv = "";
		               }

					   $str .= '<div class="status_post" onClick="javascript:toggle'.$id.'()">
					                 <div class="post_profile_pic">
									          <img src="'.$profile_pic.'" width="50">	      
									 </div> 
								     <div class="posted_by" style="color:#acacac;">
								          <a href="'.$added_by.'"><b>'.$username.'</b></a> '.$user_to.' &nbsp;&nbsp;&nbsp;&nbsp;'.$time_message.$delete_button.'
								     </div>
								     <div id="post_body">
		                                  '.$body.' 
		                                  <br>
		                                  '.$imageDiv.'
		                                  <br>
		                                  <br>
								     </div>
                                     <div class="newsfeedPostOptions">
                                          Comments('.$comments_check_num.')&nbsp;&nbsp;&nbsp;&nbsp;
                                          <iframe src="like.php?post_id='.$id.'" scrolling="no"></iframe>
                                     </div>
								</div>	
								<div class="post_comment" id="toggleComment'.$id.'" style="display: none;">
                                    <iframe src="comment_frame.php?post_id='.$id.'"	id="comment_frame" frameborder="0"></iframe>
						        </div>	
								<hr>				
					   ';

				 }

				 ?>

				 <script>
		                 $(document).ready(function(){
						      $('#post<?php echo $id; ?>').on('click', function(){
								    
									bootbox.confirm("Are u sure you want to delete this post?", function(result){
									     $.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {result:result});
						            
									     if(result){
										     location.reload();
									     }
									
									});
							  }); 
						 }); 
					 
				 </script>   

				 <?php
					 
			   }
	           if ($count > $limit) {
	           	   $str .= '<input type="hidden" class="nextPage" value="'.($page + 1).'"><input type="hidden" class="noMorePosts" value="false">';
	           }else{
	           	   $str .= '<input type="hidden" class="noMorePosts" value="true"><p style="text-align:center;"> No more posts to show!</p>';
	           }
           }		   
         echo $str;
	   }

	    public function loadProfilePosts($data, $limit) {
           $page = $data['page'];
           $profileUser = $data['profileUsername'];
           $userLoggedIn = $this->user_obj->getUsername();

           if($page == 1)
           	   $start = 0;
           else
           	   $start = ($page - 1) * $limit;

		   $str = "";
		   $data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' AND ((added_by='$profileUser' AND user_to='none') OR user_to='$profileUser') ORDER BY id DESC");

           if (mysqli_num_rows($data_query) > 0) {
           	  $num_iterations = 0; //Number of results checked (not necasserily posted)
           	  $count = 1;
           
			   while($row = mysqli_fetch_array($data_query)){
				   $id = $row['id'];
				   $body = $row['body'];
				   $body = str_replace('\r\n', '\n', $body);
			       $body = nl2br($body);
				   $added_by = $row['added_by'];			
				   $date_added = $row['date_added'];
				   $imagePath = $row['image']; 		         

					   if ($num_iterations++ < $start) 
					   	   continue;
					   
					   //Once 10 posts have been loaded, break
					   if ($count > $limit) 
					   	   break;
					   else
					   	   $count++;

					   	if ($userLoggedIn == $added_by) {
					   		$delete_button = "<button class='delete_button btn-danger' id='post".$id."'>x</button>";
					   	}else{
					   		$delete_button= "";
                        } 
					   	    
		               $user_details_query = mysqli_query($this->con, "SELECT username, profile_pic FROM users WHERE username='$added_by'");
		               $user_row = mysqli_fetch_array($user_details_query);
		               $username = $user_row['username'];
		               $profile_pic = $user_row['profile_pic'];

		               ?>
					     <script>
				            function toggle<?php echo $id; ?>(){
				            	var target = $(event.target);

				            	if (!target.is("a")) {

				            	    var element = document.getElementById("toggleComment" + <?php echo $id; ?>);
						 
					                if(element.style.display == "block"){
						                element.style.display = "none";
					                }else{
							            element.style.display = "block";
						            }	
				            	}
  
					        }
				         </script>	   
			           <?php

			           $comments_check = mysqli_query($this->con, "SELECT * FROM comments WHERE post_id='$id'");
			           $comments_check_num = mysqli_num_rows($comments_check);

		               //Timeframe
		               $date_time_now = date("Y-m-d H:i:s");
		               $start_date = new DateTime($date_added);//Time of post
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

		                if ($imagePath != "") {
		               	   $imageDiv = "<div class='postedImage'>
                                            <img src='$imagePath'>
		               	                </div>
		               	               ";
		               }else{
		               	   $imageDiv = "";
		               }

					   $str .= '<div class="status_post" onClick="javascript:toggle'.$id.'()">
					                 <div class="post_profile_pic">
									          <img src="'.$profile_pic.'" width="50">	      
									 </div> 
								     <div class="posted_by" style="color:#acacac;">
								          <a href="'.$added_by.'"><b>'.$username.'</b></a> &nbsp;&nbsp;&nbsp;&nbsp;'.$time_message.$delete_button.'
								     </div>
								     <div id="post_body">
		                                  '.$body.' 
		                                  <br>
		                                  '.$imageDiv.'
		                                  <br>
		                                  <br>
								     </div>
                                     <div class="newsfeedPostOptions">
                                          Comments('.$comments_check_num.')&nbsp;&nbsp;&nbsp;&nbsp;
                                          <iframe src="like.php?post_id='.$id.'" scrolling="no"></iframe>
                                     </div>
								</div>	
								<div class="post_comment" id="toggleComment'.$id.'" style="display: none;">
                                    <iframe src="comment_frame.php?post_id='.$id.'"	id="comment_frame" frameborder="0"></iframe>
						        </div>	
								<hr>				
					   ';

				 ?>

				 <script>
		                 $(document).ready(function(){
						      $('#post<?php echo $id; ?>').on('click', function(){
								    
									bootbox.confirm("Are u sure you want to delete this post?", function(result){
									     $.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {result:result});
						            
									     if(result){
										     location.reload();
									     }
									
									});
							  }); 
						 }); 
					 
				 </script>   

				 <?php
					 
			   }
	           if ($count > $limit) {
	           	   $str .= '<input type="hidden" class="nextPage" value="'.($page + 1).'"><input type="hidden" class="noMorePosts" value="false">';
	           }else{
	           	   $str .= '<input type="hidden" class="noMorePosts" value="true"><p style="text-align: center;"> No more posts to show!</p>';
	           }
           }		   
         echo $str;
	   }

	   public function getSinglePost($post_id){
           $userLoggedIn = $this->user_obj->getUsername();

           $opened_query = mysqli_query($this->con, "UPDATE notifications SET opened='yes' WHERE user_to='$userLoggedIn' AND link LIKE '%=$post_id'");

		   $str = "";
		   $data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' AND id='$post_id'");

           if (mysqli_num_rows($data_query) > 0) {
           
			    $row = mysqli_fetch_array($data_query);
				$id = $row['id'];
				$body = $row['body'];
				$body = str_replace('\r\n', '\n', $body);
			    $body = nl2br($body);
				$added_by = $row['added_by'];			
				$date_added = $row['date_added'];   
				     
			    if($row['user_to'] == "none"){
				   $user_to = "";
			    }else{
				   $user_to_obj = new User($this->con, $row['user_to']);
				   $user_to_username = $user_to_obj->getUsername();
				   $user_to = "to <a href='".$row['user_to']."'><b>".$user_to_username."</b></a>";
				}

				//Check if user who posted, has their account closed
				$added_by_obj = new User($this->con, $added_by);
				if ($added_by_obj->isClosed()) {
				   return;
				}

				$user_logged_obj = new User($this->con, $userLoggedIn);
				if ($user_logged_obj->isFriend($added_by)){
					   
					if ($userLoggedIn == $added_by) {
						$delete_button = "<button class='delete_button btn-danger' id='post".$id."'>X</button>";
					}else{
						$delete_button= "";
                    } 
					   	    
		            $user_details_query = mysqli_query($this->con, "SELECT username, profile_pic FROM users WHERE username='$added_by'");
		            $user_row = mysqli_fetch_array($user_details_query);
		            $username = $user_row['username'];
		            $profile_pic = $user_row['profile_pic'];

		            ?>
					    <script>
				            function toggle<?php echo $id; ?>(){
				            	var target = $(event.target);

				            	if (!target.is("a")) {

				            	    var element = document.getElementById("toggleComment" + <?php echo $id; ?>);
						 
					                if(element.style.display == "block"){
						                element.style.display = "none";
					                }else{
							            element.style.display = "block";
						            }	
				            	}
  
					        }
				         </script>	   
			        <?php

			        $comments_check = mysqli_query($this->con, "SELECT * FROM comments WHERE post_id='$id'");
			        $comments_check_num = mysqli_num_rows($comments_check);

		            //Timeframe
		            $date_time_now = date("Y-m-d H:i:s");
		            $start_date = new DateTime($date_added);//Time of post
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

					$str .= '<div class="status_post" onClick="javascript:toggle'.$id.'()">
					              <div class="post_profile_pic">
									    <img src="'.$profile_pic.'" width="50">	      
							       </div> 
								   <div class="posted_by" style="color:#acacac;">
								        <a href="'.$added_by.'"><b>'.$username.'</b></a> '.$user_to.' &nbsp;&nbsp;&nbsp;&nbsp;'.$time_message.$delete_button.'
								   </div>
								   <div id="post_body">
		                                  '.$body.' 
		                                <br>
		                                <br>
		                                <br>
								   </div>
                                   <div class="newsfeedPostOptions">
                                          Comments('.$comments_check_num.')&nbsp;&nbsp;&nbsp;&nbsp;
                                          <iframe src="like.php?post_id='.$id.'" scrolling="no"></iframe>
                                   </div>
							 </div>	
							 <div class="post_comment" id="toggleComment'.$id.'" style="display: none;">
                                    <iframe src="comment_frame.php?post_id='.$id.'"	id="comment_frame" frameborder="0"></iframe>
						     </div>	
							 <hr>				
					   ';

				 ?>

				 <script>
		                 $(document).ready(function(){
						      $('#post<?php echo $id; ?>').on('click', function(){
								    
									bootbox.confirm("Are u sure you want to delete this post?", function(result){
									     $.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {result:result});
						            
									     if(result){
										     location.reload();
									     }
									
									});
							  }); 
						 }); 
					 
				 </script>   

				 <?php
				 }else{
				 	echo "<p>You can not see this post because you are not friends with this person.</p>";
				 	return;
				 }

           }else{
           	   echo "<p>No post found. If u clicked a link, it may be broken.</p>";
			   return;
           }		   
         echo $str;
	   }

	}
    
?>