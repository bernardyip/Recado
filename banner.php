<?php
	session_start();
	if(!isset($_SESSION['username'])) {?>
	<a href="register.php">Register</a> | <a href="login.php">Login</a>
<?php } else {?>
	<h1>Welcome <?=$_SESSION['username'] ?></h1>
	<a href="logout.php">Logout</a>
<?php }?>