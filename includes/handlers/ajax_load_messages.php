<?php
    include ("../../config/config.php");
    include ("../classes/User.php");
    include ("../classes/Message.php");

    $limit = 6; //Number of messages to be loaded per call

    $message = new Message($con, $_REQUEST['userLoggedIn']);
    echo $message->getConvosDropdown($_REQUEST, $limit);
?>