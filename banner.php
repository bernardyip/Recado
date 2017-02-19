<?php
	session_start();
	if(!isset($_SESSION['username'])) {?>
		<a href="register.php">Register</a> | <a href="login.php">Login</a>
<?php } else {?>
    	<h1>Welcome <?=$_SESSION['username'] ?></h1>
    	<ul>
    		<li><a href="http://localhost">Home</a></li>
    		<li><a href="profile.php">Profile</a>
    		<li><a href="task.php">View Tasks</a>
    		<li><a href="logout.php">Logout</a></li>
    	</ul>
    	=========================================================================
<?php }?>