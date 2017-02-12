<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$username = pg_escape_string($_POST['username']);
	$password = pg_escape_string($_POST['password']);
	$bio = pg_escape_string($_POST['bio']);
	$method = pg_escape_string($_POST['method']);
	$dbcon = pg_connect('host=localhost dbname=postgres user=postgres password=password');
	if ($method == 'create') {
		$query = "INSERT INTO public.user VALUES (100, '" . $username . "', '" . $password . "', 'name', '" . $bio . "', 0);"; 
		$result = pg_query($dbcon, $query);
		print 'User created!';
	} elseif ($method == 'update') {
		$query = "UPDATE public.user SET bio='" . $bio . "' WHERE username='" . $username . "' AND password='" . $password . "';"; 
		$result = pg_query($dbcon, $query);
		print 'User Updated!';
	} elseif ($method == 'delete') {
		$query = "DELETE FROM public.user WHERE username = '" . $username . "' AND password = '" . $password . "';";
		$result = pg_query($dbcon, $query);
		print 'User Deleted!';
	}
}
?>
<meta http-equiv="refresh" content="0; url=http://localhost" />
<p><a href="http://localhost">Redirect</a></p>