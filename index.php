<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

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

// Checks if there is a loss or win
$isLoss = 0;
$isWin = 0;

// Checks if there is a reset of the game
$isReset = 0;

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

	// Reset the game if the user chooses too
	if($_POST['reset'] == "reset") {
		$isReset = 1;
	}
}

// Check if a letter is guessed only if game is in progress (stop people from running GET after game over)
if ($_SERVER["REQUEST_METHOD"] == "GET" && $gameInProgress == 1) {
	$alpha = "";
	if(isset($_GET['guess'])) {
		$alpha = $_GET['guess'];

		// Check if guessed letter is in word
		$correct = 1;

		// If letter is not in word, count as incorrect guess.
		if (strpos($word, $alpha) === false) {
			$correct = 0;
		}

		// If not in word increase fails
		if ($correct != 1) {
			$numFailedGuess++;
			$_SESSION['numFailedGuess'] = $numFailedGuess;	
		}	

		// Check if they failed their last guess 
		if($numFailedGuess == 5) {
			echo "Test";
			// Set as a loss for user
			$isLoss = 1;
			// Reset session except for the user
			$isReset = 1;
			// Display a message telling the user they lost
			$message = "You have lost!";
			echo "<script type='text/javascript'>alert('$message');</script>";
		} 
	}
}

// Check if user reset the game
if($isReset == 1) {
	// Reset guesses to 0
	$_SESSION['numFailedGuess'] = 0;
	$numFailedGuess = 0;
	// Turn game off
	$_SESSION['inProgress'] = 0;
	$gameInProgress = 0;

	// Set as a loss
	$isLoss = 1;
}

// If a loss is detected add to losses
if($isLoss == 1) {
	// Get current users losses
	$query = "SELECT * FROM users WHERE username='" . mysqli_real_escape_string($link, $username) . "'";
	$result = mysqli_query($link, $query);
	$row = mysqli_fetch_assoc($result);

	// Increase losses of user
	$currentLosses = $row['losses'];
	$currentLosses++;

	// Add to losses of current user
	$query = "UPDATE users SET losses=" . $currentLosses . " WHERE username='" . mysqli_real_escape_string($link, $username) . "'";
	mysqli_query($link, $query);

	// Set isLoss to 0
	$isLoss = 0;
}

// If a win is detected add to wins
if($isWin == 1) {
	// Get current users wins
	$query = "SELECT * FROM users WHERE username='" . mysqli_real_escape_string($link, $username) . "'";
	$result = mysqli_query($link, $query);
	$row = mysqli_fetch_assoc($result);

	// Increase wins of user
	$currentWins = $row['wins'];
	$currentWins++;

	// Add to wins of current user
	$query = "UPDATE users SET wins=" . $currentWins . " WHERE username='" . mysqli_real_escape_string($link, $username) . "'";
	mysqli_query($link, $query);

	// Set isWin to 0
	$isWin = 0;
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

		<!-- This displays the guessed word when correct letters are guessed, as well as blank spaces at the start -->
		<div id="guessed_word">

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
		<div id="reset">
			<form method="post">
				<p> Resetting the game will count as a loss! </p>
				<input type="hidden" name="reset" value="reset"/>
				<input type="submit" value="Reset Game" id="btn">
			</form>
		</div>
		<?php endif; ?>
	</div>

	<!-- Allow admin to upload a list of words -->
	<?php if($username === "admin") : ?>
	<div id="word_upload">

	</div>
	<?php endif; ?>
</div>

</body>
</html>