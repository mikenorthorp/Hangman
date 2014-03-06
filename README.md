README
======

README and code base best viewed on [Github](https://github.com/mikenorthorp/Hangman)

You can view the live site here [https://web.cs.dal.ca/~northorp/infx2670_a3/](https://web.cs.dal.ca/~northorp/infx2670_a3/)

This is an assignment for my Server Side Scripting class INFX2670 Assignment 3

It is a hangman game that allows the user to play a simple game of hangman. The user can create an account to play and keep track of their personal score, or play as a non-registered user and be part of a collective high score. There are admin functions to upload a word list so the game is playable. MySQL is used to store user data and game data and sessions are used in PHP to keep track of the current users session and game progress.

Requirements
------------

This program requires `php` and `apache` and `mySQL` to be installed on the server you are running this on. As well as 
support for mysqli in php (must have that extension enabled and working).

This program is made to run on the bluenose.cs.dal.ca servers but should work any apache based server with PHP and MySQL installed.

Installation
------------

1. Copy files into working directory.
2. Make sure apache and mySQL is running
4. Import the .sql file export of the database to your own mySQL installation.
5. Users are entered with wins/losses already, as well as some sample words.
6. Go to the page you set it up on.


Making the Website Do Things
----------------------------
1. You can play the game without logging in, it is a basic hangman game. The word is displayed in the top left though.
2. Click login or signup to make an account or log into an existing account.
3. Login with admin/admin to use the admin account, which allows you to add a word list, or reset scores.
4. When not logged in your score will be saved to the Anon account, else it will save to your user.
5. You can view the highscore table whenever you want, which shows the top 10.
6. You will lose once you incorrectly guess 5 times.
7. Have fun!

Citations
=========
Lecture notes used. Bootstrap used for some CSS, and subtlepatterns used for background image. 

Images found from http://javascript.about.com/library/blhang3.htm for hangman pictures




