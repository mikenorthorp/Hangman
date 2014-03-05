<?php
// Require database config options
Require('config.php');

// Start user session
session_start();
/* -------------------------- */
/* START VARIABLE SETUP       */
/* -------------------------- */

// Set up define values

// Amount of tries allowed
define('NUM_TRIES', 5);

// Set up username if not set to be the default
if (!isset($_SESSION['username'])) {
	$_SESSION['username'] = "Anon";
}

// Set username to whatever is stored in the session
$username = $_SESSION['username'];

// Set the word to whatever is stored in the session
$word = $_SESSION['word'];

$gameInProgress = 0;
// Check if game is in progress 
if($_SESSION['inProgress'] != 0) {
	$gameInProgress = $_SESSION['inProgress'];
}

// Not admin by default
$isAdmin = 0;
$_SESSION['isAdmin'] = 0;

if($username == "admin") {
	$_SESSION['isAdmin'] = 1;
	$isAdmin = 1;
}

$numLettersGuessed = 0;
// Check the number of letters guessed
if($_SESSION['numLettersGuessed'] != 0) {
	$numLettersGuessed = $_SESSION['numLettersGuessed'];
}

/* -------------------------- */
/*   END VARIABLE SETUP 	  */
/* -------------------------- */

/* Connect to database to display highscore table and other database stuff */
$link = mysqli_connect($db_host, $db_user, $db_pass, $db_name) or die ('Your DB connection has failed or is misconfigured, please enter correct values in the config file and try again');

echo $_SESSION['username'] . " is logged in ";

// If a post request is submitted from the game to start it
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	// Set game to start if user hit play hangman
	if($_POST['start'] == "start") {
		$_SESSION['inProgress'] = 1;
		$gameInProgress = $_SESSION['inProgress'];

		// Grab random word on start and store in session
		// If availible in database
	}
}

// Check if a letter is guessed 
if ($_SERVER["REQUEST_METHOD"] == "GET") {
	$alpha = "";
	if(isset($_GET['guess'])) {
		$alpha = $_GET['guess'];
	}

	echo $alpha;
	// Check if letter is contained in word, if not increase tries, and do checks

}

// Debug
var_dump($_SESSION);
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
	<!-- This displays the word randomly chosen if the game is started -->
	<?php if($inProgress == 1) : ?>
	<div id="word_display">
	</div>
	<?php endif; ?>

	<!-- Shows signup/login or logout depending on user state -->
	<?php if($username === "Anon") : ?>
	<div id="login_signup">
		<a href="signup.php">Signup</a>
		<a href="login.php">Login</a>
	</div>
	<?php else : ?>
	<div id="logout">
		<a href="logout.php">Logout</a>
	</div>
	<?php endif; ?>

	<!-- The display area for the hangman game -->
	<div id="hangman_game">
		<h1> INFX 2670 Assignment 3 - Michael Northorp </h1>

		<!-- This triggers the start of the game, and is hidden if the game is started -->
		<?php if($gameInProgress != 1) : ?>
			<div id="game_trigger">
				<form method="post">
					<input type="hidden" name="start" value="start"/>
					<input type="submit" value="Play Hangman" id="btn">
				</form>
			</div>
		<?php endif; ?>
		<!-- Generate a list of links for alphabet guessing for hangman if game is on -->
		<?php if($gameInProgress == 1) : ?>
		<div id="alpha_list">
			<p> Select a letter below to fill in the word above </p>
			<?php 
				// Loop through whole alphabet and make links to letters
				// Modified from http://stackoverflow.com/questions/19213681/creating-links-for-all-letters-of-the-alphabet
				for ($i = 65; $i <= 90; $i++) {
					// This displays the number as the char in alphabet A-Z
			    	printf('<a href="index.php?guess=%1$s" class="alpha">%1$s</a> ', chr($i));
				}
			?>
			</div>
		<?php endif; ?>
	</div>
</div>

</body>
</html>