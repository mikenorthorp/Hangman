<?php
// Require database config options
require_once('config.php');

/* -------------------------- */
/* START VARIABLE DECLERATION */
/* -------------------------- */


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
	<div id="hangman_game">
		<h1> INFX 2670 Assignment 3 - Michael Northorp </h1>
	</div>
</div>

</body>
</html>