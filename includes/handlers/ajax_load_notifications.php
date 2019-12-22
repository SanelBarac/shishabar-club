<?php
    include ("../../config/config.php");
    include ("../classes/User.php");
    include ("../classes/Notification.php");

    $limit = 6; //Number of messages to be loaded per call

    $notification = new Notification($con, $_REQUEST['userLoggedIn']);
    echo $notification->getNotifications($_REQUEST, $limit);
?>