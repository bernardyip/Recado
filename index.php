<html>
<head>
	<title>localhost</title>
	<script src="js/test.js"></script>
</head>
<body>
 <p> helloworld!</p>
<?php
	$dbcon = pg_connect('host=localhost dbname=postgres user=postgres password=password');
	$result = pg_query($dbcon, 'SELECT username, bio from public.user');
?>
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