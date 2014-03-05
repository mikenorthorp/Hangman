<?php
// Require database config options
Require('config.php');

/* -------------------------- */
/* START VARIABLE DECLERATION */
/* -------------------------- */

// Set up define values

// Amount of tries allowed
define('NUM_TRIES', 5);

$isAdmin = 0;
$gameInProgress = 0;
$numLettersGuessed = 0;


/* -------------------------- */
/*   END VARIABLE DECLERATION */
/* -------------------------- */

/* Connect to database to display highscore table and other database stuff */
$link = mysqli_connect($db_host, $db_user, $db_pass, $db_name) or die ('Your DB connection has failed or is misconfigured, please enter correct values in the config file and try again');

// If a post request is submitted to start the game or go to register / login page
if ($_SERVER["REQUEST_METHOD"] == "POST") {

}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Hangman</title>
	<link rel="stylesheet" href="main.css" type="text/css">
</head>
<body>
<div id="content">
	<div id="word_display">
	</div>
	<div id="login_signup">
		<a href="signup.php">Signup</a>
		<a href="login.php">Login</a>
	</div>
	<div id="hangman_game">
		<h1> INFX 2670 Assignment 3 - Michael Northorp </h1>
	</div>
</div>

</body>
</html>