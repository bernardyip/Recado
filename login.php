<html>
	<?php 
	include('banner.php');
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$username = pg_escape_string($_POST['username']);
		$password = pg_escape_string($_POST['password']);
		$method = pg_escape_string($_POST['method']);
		$dbcon = pg_connect('host=localhost dbname=postgres user=postgres password=password');
		if ($method == 'login') {
			pg_prepare($dbcon, 'select_user_query', "SELECT username FROM public.user WHERE username=$1 AND password=$2;");
			$result = pg_execute($dbcon, 'select_user_query', array($username, $password));
			if (pg_affected_rows($result) >= 1) { 
				$user = pg_fetch_array($result);
				$_SESSION['username'] = $user['username']; ?>
				<head>
					<meta http-equiv='refresh' content='3; url=http://localhost/' />
				</head>
				<body>
					<h1>Login successful</h1>
					<p><a href="http://localhost/login.php">Redirect</a></p>
	<?php   } else { ?>
				<head>
					<meta http-equiv='refresh' content='1; url=http://localhost/login.php' />
				</head>
				<body>
					<h1>Login failed, please try again</h1>
					<p><a href="http://localhost/login.php">Redirect</a></p>
	<?php 	} 
		} 
	} else { 
		if (isset($_SESSION['username'])) { ?>
			<head>
				<meta http-equiv='refresh' content='0; url=http://localhost' />
			</head>
<?php 	} else {?>
			<body>
				<form action="login.php" method="POST">
					Username: <br />
					<input type="text" name="username" /> <br /><br />
					Password: <br />
					<input type="password" name="password" /> <br /><br />
					<input type="hidden" value="login" name="method"/>
					<input type="submit" value="Log In"/>
				</form>
 <?php }
	} ?>
		</body>
</html>