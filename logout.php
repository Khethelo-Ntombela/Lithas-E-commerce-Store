<?php
session_start(); // start the session so we can find it
session_unset(); // remove all session variables
session_destroy(); //destroy the session entirely

// this redirect back to login page
header("Location: login.html"); 
exit();
?>