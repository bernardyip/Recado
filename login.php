<html>
	<?php 
	include('banner.php');
	//If logged in already
	if (isset($_SESSION['username'])) {
		header('Refresh: 0; URL=http://localhost/');
		die();
	}
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$username = pg_escape_string($_POST['username']);
		$password = pg_escape_string($_POST['password']);
		$method = pg_escape_string($_POST['method']);
		$dbcon = pg_connect('host=localhost dbname=postgres user=postgres password=password');
		if ($method == 'login') {
			pg_prepare($dbcon, 'select_user_query', "SELECT username, email, phone, name, bio FROM public.user WHERE username=$1 AND password=$2;");
			$result = pg_execute($dbcon, 'select_user_query', array($username, $password));
			if (pg_affected_rows($result) >= 1) { 
				$user = pg_fetch_array($result);
				$_SESSION['username'] = $user['username'];
				$_SESSION['email'] = $user['email'];
				$_SESSION['phone'] = $user['phone'];
				$_SESSION['name'] = $user['name'];
				$_SESSION['bio'] = $user['bio'];
				?>
				<head>
					<meta http-equiv='refresh' content='1; url=http://localhost/' />
				</head>
				<body>
					<h1>Login successful</h1>
					<p><a href="http://localhost/login.php">Redirect</a></p>
	<?php   } else { 
				header('Refresh: 0; URL=http://localhost/login.php?message=' . urlencode("Incorrect username/password"));
				die();
			} 
		} 
	} else { ?>
		<body>
			<form action="login.php" method="POST">
				Username: <br />
				<input type="text" name="username" /> <br /><br />
				Password: <br />
				<input type="password" name="password" /> <br /><br />
				<input type="hidden" value="login" name="method"/>
				<input type="submit" value="Log In"/>
			</form>
			<p><?=pg_escape_string($_GET['message'])?></p>
 <?php 
	} ?>
		</body>
</html>