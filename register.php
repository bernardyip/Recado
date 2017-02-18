<html>
	<?php 
	include('banner.php');
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$username = pg_escape_string($_POST['username']);
		$password = pg_escape_string($_POST['password']);
		$bio = pg_escape_string($_POST['bio']);
		$method = pg_escape_string($_POST['method']);
		$dbcon = pg_connect('host=localhost dbname=postgres user=postgres password=password');
		if ($method == 'create') {
			pg_prepare($dbcon, 'create_user_query', "INSERT INTO public.user (username, password, name, bio, created_time, role) VALUES ($1 ,$2 ,'name' ,$3 ,'2016-04-25T19:05:32Z' ,'user');");
			$result = pg_execute($dbcon, 'create_user_query', array($username, $password, $bio));
			if (pg_affected_rows($result) >= 1) { ?>
				<head>
					<meta http-equiv='refresh' content='3; url=http://localhost/login.php' />
				</head>
				<body>
					<h1>User created! Redirecting you to the login page...</h1>
					<p><a href="http://localhost/login.php">Redirect</a></p>
	<?php   } else { ?>
				<head>
					<meta http-equiv='refresh' content='3; url=http://localhost/register.php' />
				</head>
				<body>
					<h1>Failed to create the user, please try again</h1>
					<p><a href="http://localhost/register.php">Redirect</a></p>
	<?php 	} 
		} 
	} else { 
		if (isset($_SESSION['username'])) { ?>
			<head>
				<meta http-equiv='refresh' content='0; url=http://localhost' />
			</head>
<?php 	} else {?>
			<body>
				<form action="register.php" method="POST">
					Username: <br />
					<input type="text" name="username" /> <br /><br />
					Password: <br />
					<input type="password" name="password" /> <br /><br />
					Details: <br />
					<input type="textarea" name="bio" /> <br /><br />
					<input type="hidden" value="create" name="method"/>
					<input type="submit" value="Register"/>
				</form>
<?php   } 
	} ?>
			</body>
</html>
