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

$numFailedGuess = 0;
// Check the number of letters guessed
if($_SESSION['numFailedGuess'] != 0) {
	$numFailedGuess = $_SESSION['numFailedGuess'];
}

// This gets written too if there are any errors and displayed on the page.
$gameError = "";

/* -------------------------- */
/*   END VARIABLE SETUP 	  */
/* -------------------------- */

/* Connect to database to display highscore table and other database stuff */
$link = mysqli_connect($db_host, $db_user, $db_pass, $db_name) or die ('Your DB connection has failed or is misconfigured, please enter correct values in the config file and try again');

// If a post request is submitted from the game to start it
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	// Set game to start if user hit play hangman
	if($_POST['start'] == "start") {
		$_SESSION['inProgress'] = 1;
		$gameInProgress = $_SESSION['inProgress'];

		// Grab random word on start and store in session
		// If availible in database
		$query = "SELECT * FROM words";
		$result = mysqli_query($link, $query);
		$numRows = mysqli_num_rows($result);

		// Check if any words are returned
		if ($numRows == 0) {
			$gameError = "No words availible, please login as Admin and upload some words.";
			// Alerts the user that no words are availbile via an alert popup
			echo "<script type='text/javascript'>alert('$gameError');</script>";

			// Turn the game off
			$_SESSION['inProgress'] = 0;
			$gameInProgress = $_SESSION['inProgress'];
		} else {
			// Grab a word random word from database and set it


			// Grab a random word from a random row returned
			$word = "Test";

			// Set the session variable for the word
			$_SESSION['word'] = $word;
		}
	}
}

// Check if a letter is guessed only if game is in progress (stop people from running GET after game over)
if ($_SERVER["REQUEST_METHOD"] == "GET" && $gameInProgress == 1) {
	$alpha = "";
	if(isset($_GET['guess'])) {
		$alpha = $_GET['guess'];

		// Increase the number of letters if less than 5 and incorrect, end game if fails are 5
		if($numFailedGuess == 5) {
			// Set as a loss for user

			// Reset session except for the user

			// Display a message telling the user they lost
		} else {
			// Check if guessed letter is in word
			$correct = 1;
			


			// If not in word increase fails
			if ($correct == 1) {
				$numFailedGuess++;
				$_SESSION['numFailedGuess'] = $numFailedGuess;	
			}	
		}
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
	<h1> INFX 2670 Assignment 3 - Michael Northorp </h1>
	<!-- This displays the word randomly chosen if the game is started -->
	<?php if($gameInProgress == 1) : ?>
	<div id="word_display">
		<p> The word to guess is <?php echo $word; ?> </p>
	</div>
	<?php endif; ?>

	<!-- Shows signup/login or logout depending on user state -->
	<?php if($username === "Anon") : ?>
	<div id="login_signup">
		<p> You are not logged in </p>
		<a href="signup.php">Signup</a>
		<a href="login.php">Login</a>
	</div>
	<?php else : ?>
	<div id="logout">
		<p> You are logged in as <?php echo $username; ?> </p>
		<a href="logout.php">Logout</a>
	</div>
	<?php endif; ?>

	<!-- The display area for the hangman game -->
	<div id="hangman_game">
		<h2> Hangman Game </h2>
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
		<div id="hangman_image">
			<img src="images/hang<?php echo $numFailedGuess ?>.gif">
		</div>
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