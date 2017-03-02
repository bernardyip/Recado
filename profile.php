<html>
	<head>
		<title>Recado</title>
		<?php 
		include('banner.php');
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$password = pg_escape_string($_POST['password']);
			$email = pg_escape_string($_POST['email']);
			$phone = pg_escape_string($_POST['phone']);
			$name = pg_escape_string($_POST['name']);
			$bio = pg_escape_string($_POST['bio']);
			$method = pg_escape_string($_POST['method']);
			if ($method == "update") {
				$dbcon = pg_connect('host=localhost dbname=postgres user=postgres password=password');
				pg_prepare($dbcon, 'update_user_query', "UPDATE public.user SET bio=$3,email=$4,phone=$5,name=$6 WHERE username=$1 AND password=$2;");
				$result = pg_execute($dbcon, 'update_user_query', array($_SESSION["username"], $password, $bio, $email, $phone, $name));
				if (pg_affected_rows($result) >= 1) {
					$_SESSION['email'] = $email;
					$_SESSION['phone'] = $phone;
					$_SESSION['name'] = $name;
					$_SESSION['bio'] = $bio;
					header('Refresh: 0; URL=http://localhost/profile.php?message=' . urlencode("Updated user successfully"));
					die();
				} else {
					header('Refresh: 0; URL=http://localhost/profile.php?message=' . urlencode("Incorrect password."));
					die();
				}
			}
			die();
		}?>
	</head>
	<body>
		<?php 
		//If not logged in
		if (!isset($_SESSION['username'])) {
			header('Refresh: 0; URL=http://localhost/login.php');
			die();
		}
		?>
		<form action="profile.php" method="POST">
			Password (for verification): <br />
			<input type="password" name="password" /> <br /><br />
			Email: <br />
			<input type="email" name="email" value="<?=trim($_SESSION['email'])?>" /> <br /><br />
			Phone: <br />
			<input type="tel" name="phone" value="<?=trim($_SESSION['phone'])?>"/> <br /><br />
			Name: <br />
			<input type="text" name="name" value="<?=trim($_SESSION['name'])?>"/> <br /><br />
			Bio: <br />
			<input type="textarea" name="bio" value="<?=trim($_SESSION['bio'])?>"/> <br /><br />
			<input type="hidden" value="update" name="method"/>
			<input type="submit" value="Update details"/>
		</form>
		<?=pg_escape_string($_GET["message"])?>
	</body>
</html>