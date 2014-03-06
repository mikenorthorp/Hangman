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

// Set the word to whatever is stored in the session if it is set
if(isset($_SESSION['word'])) {
	$word = $_SESSION['word'];
}

$gameInProgress = 0;
// Check if game is in progress 
if(isset($_SESSION['inProgress'])) {
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
if(isset($_SESSION['numFailedGuess'])) {
	$numFailedGuess = $_SESSION['numFailedGuess'];
}

// This gets written too if there are any errors and displayed on the page.
$gameError = "";

// This gets written if there is a win or loss message for the user
$gameMessage = "";

/* -------------------------- */
/*   END VARIABLE SETUP 	  */
/* -------------------------- */

/* Connect to database to display highscore table and other database stuff */
$link = mysqli_connect($db_host, $db_user, $db_pass, $db_name) or die ('Your DB connection has failed or is misconfigured, please enter correct values in the config file and try again');

// If a post request is submitted from the game to start it
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	// Set game to start if user hit play hangman
	if(isset($_POST['start'])) {
		$_SESSION['inProgress'] = 1;
		$gameInProgress = $_SESSION['inProgress'];

		// Grab random word on start and store in session
		// If availible in database
		$query = "SELECT * FROM words";
		$result = mysqli_query($link, $query);
		$numRows = mysqli_num_rows($result);

		// Get a random word from the list to grab from
		$wordIndex = rand(1, $numRows);

		// Check if any words are returned
		if ($numRows == 0) {
			$gameError = "No words availible, please login as Admin and upload some words.";

			// Turn the game off
			$_SESSION['inProgress'] = 0;
			$gameInProgress = $_SESSION['inProgress'];
		} else {
			// Grab a word random word from database and set it
			$query = "SELECT word FROM words WHERE id=" . $wordIndex;
			$result = mysqli_query($link, $query);
			$row = mysqli_fetch_assoc($result);

			// Grab the word from the random unique ID picked
			$word = $row['word'];

			// Set the session variable for the word
			$_SESSION['word'] = $word;

			// Get length of word
			$_SESSION['wordLength'] = strlen($word);

			// Set up an empty array with all spaces in place of word
			$_SESSION['hiddenWord'] = array_fill(0, $_SESSION['wordLength'], '_');
		}
	}

	// Reset the game if the user chooses too
	if(isset($_POST['reset'])) {
		$isReset = 1;
		$isLoss = 1;
	}

	// If a word list is uploaded
	if (isset($_POST['submit_files'])) {
		$tmpName = $_FILES['upload_file']['tmp_name'];
		$handle = fopen($tmpName, "r");
		if ($handle) {
			// Remove all current words from word list
	    	$query = "DELETE FROM words";
			mysqli_query($link, $query);

			// Reset auto increment
			$query = "ALTER TABLE words AUTO_INCREMENT = 1";
			mysqli_query($link, $query);
			
		    while (($line = fgets($handle)) !== false) {
		        // Upload each word to the database
		        $line = preg_replace('/\s+/', '', $line);
		        $line = strtoupper($line);
				$query = "INSERT INTO words (word) VALUES ('" . mysqli_real_escape_string($link, $line) . "')";
				mysqli_query($link, $query);
		    }
		}
	}
}

// Check if game over and dont do following
$gameOver = 0;
if(isset($_SESSION['gameOver'])) {
	if ($_SESSION['gameOver'] == 1) {
		$gameOver = 1;
	}
}
// Check if a letter is guessed only if game is in progress (stop people from running GET after game over)
if ($_SERVER["REQUEST_METHOD"] == "GET" && $gameInProgress == 1 && $gameOver == 0) {
	$alpha = "";
	if(isset($_GET['guess'])) {
		$alpha = $_GET['guess'];

		// Check if guessed letter is in word
		$correct = 1;

		// If letter is not in word, count as incorrect guess.
		if (strpos($word, $alpha) === false) {
			$correct = 0;
		} else {
			// If letter is in word, loop through full word and fill in value where it should go
			for($i = 0; $i < $_SESSION['wordLength']; $i++) {
				if($word[$i] == $alpha) {
					$_SESSION['hiddenWord'][$i] = $word[$i];
				}	
			}
		}

		// If not in word increase fails
		if ($correct != 1) {
			$numFailedGuess++;
			$_SESSION['numFailedGuess'] = $numFailedGuess;	
		} else {
			// Check for win condition if guess is correct by checking for _ in hidden word
			if (strpos(implode("", $_SESSION['hiddenWord']), '_') === false) {
				$isWin = 1;
				$_SESSION['isWin'] = 1;
				$_SESSION['gameOver'] = 1;
				$gameMessage = "You have won! Hit reset to play again and see if you are on the highscore board!";
			}
		}

		// Check if they failed their last guess 
		if($numFailedGuess == 5) {
			// Set as a loss for user
			$isLoss = 1;
			$_SESSION['isLoss'] = 1;
			$_SESSION['gameOver'] = 1;
			// Display a message telling the user they lost
			$gameMessage = "You have lost! Hit reset to play again!";
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

	// Set game over to not over
	$_SESSION['gameOver'] = 0;

	// Check if resetting after a win, so dont count as loss
	if(isset($_SESSION['isWin'])) {
		// No win detected so reset is because of loss or just a reset press
		if($_SESSION['isWin'] != 1) {
			$isLoss = 1;
		}
		// Reset win condition detected so don't count as loss and reset win to 0
		$_SESSION['isWin'] = 0;
	} else {
		// There is a loss an no win has happened yet
		$isLoss = 1;
	}

	// Reset loss condition as well if set
	if(isset($_SESSION['isLoss'])) {
		$_SESSION['isLoss'] = 0;
	}

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
	<?php if($gameInProgress === 1) : ?>
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
				<br><span class="error"><?php echo $gameError; ?></span>
				<form method="post">
					<input type="hidden" name="start" value="start"/>
					<input type="submit" value="Play Hangman" id="btn">
				</form>
			</div>
		<?php endif; ?>
		<!-- Generate a list of links for alphabet guessing for hangman if game is on -->
		<?php if($gameInProgress == 1) : ?>
		<div id="hangman_image">
			<img src="images/hang<?php echo $numFailedGuess; ?>.gif">
			<br><span class="game_message"><?php echo $gameMessage; ?></span>
		</div>

		<!-- This displays the guessed word when correct letters are guessed, as well as blank spaces at the start -->
		<div id="guessed_word">
		<?php
			// Display the hidden word with same length as word to guess
			// But has underscores if letter is not guessed
			$hiddenWord = $_SESSION['hiddenWord'];
			echo "<p>";
			for($i = 0; $i < $_SESSION['wordLength']; $i++) {
				echo "$hiddenWord[$i] ";
			}
			echo "</p>";
		?>
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

	<!-- Allow admin to upload a list of words that replaces current word list -->
	<?php if($username === "admin") : ?>
	<div id="word_upload">
		<form method="post" enctype="multipart/form-data">
			<div id="upload">
				<p><label for="upload_file">Upload a Word List</label></p>
				<input type="file" id="upload_file" name="upload_file"><br>
			</div>
			<input type="submit" name="submit_files" value="Upload Word List" id="btn">
		</form>
	</div>
	<?php endif; ?>
</div>

</body>
</html>