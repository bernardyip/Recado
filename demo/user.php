<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$username = pg_escape_string($_POST['username']);
	$password = pg_escape_string($_POST['password']);
	$bio = pg_escape_string($_POST['bio']);
	$method = pg_escape_string($_POST['method']);
	$dbcon = pg_connect('host=localhost dbname=postgres user=postgres password=password');
	if ($method == 'create') {
		pg_prepare($dbcon, 'create_user_query', "INSERT INTO public.user (username, password, name, bio, created_time, role) VALUES ($1 ,$2 ,'name' ,$3 ,'2016-04-25T19:05:32Z' ,'user');");
		$result = pg_execute($dbcon, 'create_user_query', array($username, $password, $bio));
		if (pg_affected_rows($result) >= 1) {
			print 'User created!';
		} else {
			print 'Failed to create user';
		}
	} elseif ($method == 'update') {
		pg_prepare($dbcon, 'update_user_query', "UPDATE public.user SET bio=$3 WHERE username=$1 AND password=$2;");
		$result = pg_execute($dbcon, 'update_user_query', array($username, $password, $bio));
		if (pg_affected_rows($result) >= 1) {
			print 'User updated!';
		} else {
			print 'Failed to update user';
		}
	} elseif ($method == 'delete') {
		pg_prepare($dbcon, 'delete_user_query', "DELETE FROM public.user WHERE username=$1 AND password=$2");
		$result = pg_execute($dbcon, 'delete_user_query', array($username, $password));
		if (pg_affected_rows($result) >= 1) {
			print 'User deleted!';
		} else {
			print 'Failed to delete user';
		}
	}
?>
	<meta http-equiv='refresh' content='0; url=http://localhost/user.php' />
	<p><a href="http://localhost/user.php">Redirect</a></p>
<?php 
} else {
	$dbcon = pg_connect('host=localhost dbname=postgres user=postgres password=password');
	pg_prepare($dbcon, 'select_user_query', 'SELECT username, bio FROM public.user');
	$result = pg_execute($dbcon, 'select_user_query', array());
?>
	<html>
		<head>
			<title>localhost</title>
			<script src="js/test.js"></script>
		</head>
		<body>
		 <p> helloworld!</p>
			<table border='1'>
				<tr>
					<th>Username</th>
					<th>Details</th>
				</tr>
				<tr>
				<?php while ($row = pg_fetch_row($result)) { 
					$username = $row[0];
					$details = $row[1];
				?>
					<tr>
						<td><?=$username ?></td>
						<td><?=$details ?></td>
					</tr>
				<?php } ?>
			</table>
			<br />
			<h2> Register now! </h2>
			<form action="user.php" method="POST">
				Username: <br />
				<input type="text" name="username" /> <br /><br />
				Password: <br />
				<input type="password" name="password" /> <br /><br />
				Details: <br />
				<input type="textarea" name="bio" /> <br /><br />
				<input type="hidden" value="create" name="method"/>
				<input type="submit" value="Register"/>
			</form>
			<h2> Update a user's details (Password required) </h2>
			<form action="user.php" method="POST">
				Username: <br />
				<input type="text" name="username" /> <br /><br />
				Password: <br />
				<input type="password" name="password" /> <br /><br />
				Details: <br />
				<input type="textarea" name="bio" /> <br /><br />
				<input type="hidden" value="update" name="method"/>
				<input type="submit" value="Update details"/>
			</form>
			<h2> Delete a user (Password required)</h2>
			<form action="user.php" method="POST">
				Username: <br />
				<input type="text" name="username" /> <br /><br />
				Password: <br />
				<input type="password" name="password" /> <br /><br />
				<input type="hidden" value="delete" name="method"/>
				<input type="submit" value="Delete user"/>
			</form>
		</body>
	</html>
<?php 
}
?>