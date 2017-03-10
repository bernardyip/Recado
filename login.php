<?php

session_start();
	
class LoginModel {
	public $username = "";
	public $password = "";
	public $message = "";
	public $loginSuccess = false;
	
	public function __construct() {
		
	}
}

class LoginView {
	private $model;
	private $controller;
	
	public function __construct($controller, $model) {
		$this->controller = $controller;
		$this->model = $model;
	}
	
	public function getUsernameField() {
		return $this->makeInput("text", "username", "");
	}
	
	public function getPasswordField() {
		return $this->makeInput("password", "password", "");
	}
	
	private function makeInput($type, $name, $value) {
		$html = "<input type=\"$type\" name=\"$name\" value=\"$value\" />";
		return $html;
	}
}

class LoginController {

	const LOGIN_URL = "login.php?action=login";
	const LOGIN_METHOD = "POST";
	const DB_CONN_STR = "host=localhost dbname=postgres user=postgres password=password";
	const DB_SELECT_USER_QUERY = "SELECT id, username, email, phone, name, bio " . 
											"FROM public.user WHERE username=$1 AND password=$2;";
	const DB_UPDATE_LAST_LOGIN_QUERY = "UPDATE public.user SET last_logged_in=$3 WHERE username=$1 AND password=$2;";
	const HOME_URL = "http://localhost/";
	private $model;
	
	public function __construct($model) {
		$this->model = $model;
	}
	
	public function login() {
		$username = pg_escape_string($_POST['username']);
		$password = pg_escape_string($_POST['password']);
		$dbcon = pg_connect(LoginController::DB_CONN_STR);
		pg_prepare($dbcon, 'select_user_query', LoginController::DB_SELECT_USER_QUERY);
			
		$result = pg_execute($dbcon, 'select_user_query', array($username, $password));
		if (pg_affected_rows($result) >= 1) { 
			$user = pg_fetch_array($result);
			$_SESSION['id'] = $user['id'];
			$_SESSION['username'] = $user['username'];
			$_SESSION['email'] = $user['email'];
			$_SESSION['phone'] = $user['phone'];
			$_SESSION['name'] = $user['name'];
			$_SESSION['bio'] = $user['bio'];
			
			$this->model->username = $user['username'];
			$this->model->password = "";
			$this->model->message = "Hello, " . $user['username'] . "!";
			$this->model->loginSuccess = true;
			
			$current_datetime = (new DateTime(null, new DateTimeZone("Asia/Singapore")))->format('Y-m-d\TH:i:s\Z');
			pg_prepare($dbcon, 'update_last_login_query', LoginController::DB_UPDATE_LAST_LOGIN_QUERY);
			$result = pg_execute($dbcon, 'update_last_login_query', array($username, $password, $current_datetime));
			
			header("Refresh: 3; URL=" . LoginController::HOME_URL);
		} else {
			$this->model->message = "Sorry, you have entered an invalid username-password pair.";
			$this->model->loginSuccess = false;
		}
	}
	
	public function handleHttpPost() {
		if (isset($_GET['action'])) {
			if ($_GET['action'] === 'login') {
				$this->login();
			}
		} else {
			// invalid request.
			http_response_code(400);
			die();
		}
	}
	
	public function handleHttpGet() {
		if (isset($_GET['action'])) {
			// request for controller action
			// No controller actions for HTTP GET
		} else {
			// display login page
		}
	}
	
	public function redirectToHome() {
		header("Location: " . LoginController::HOME_URL);
		die();
	}
}

$model = new LoginModel();
$controller = new LoginController($model);
$view = new LoginView($controller, $model);

if (isset($_SESSION['username'])) {
	$controller->redirectToHome();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$controller->handleHttpPost();
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	$controller->handleHttpGet();
}

?>

<html>
	<body>
		<?php if ($model->loginSuccess) { ?>
			<h1>Login Successful, Redirecting...</h1>
			<p><?php echo $model->message; ?></p>
		<?php
		} else { ?>
			<?php 
			include('banner.php'); 
			?>
			<form action="<?php echo LoginController::LOGIN_URL?>" method="<?php echo LoginController::LOGIN_METHOD?>">
				Username: <br />
				<?php echo $view->getUsernameField(); ?> <br/><br/>
				Password: <br />
				<?php echo $view->getPasswordField(); ?><br/><br/>
				<input type="submit" value="Log In"/>
			</form>
			<p><?php echo $model->message; ?></p>
		<?php
		}?>
	</body>
</html>