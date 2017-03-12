<?php

session_start();
include_once 'data/UserDatabase.php';

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
        return $this->makeInput ( "text", "username", "", true );
    }
    
    public function getPasswordField() {
        return $this->makeInput ( "password", "password", "" );
    }
    
    private function makeInput($type, $name, $value, $autofocus = false) {
        $html = "<input type=\"$type\" name=\"$name\" value=\"$value\"";
        if ($autofocus) {
            $html = $html . " autofocus ";
        }
        $html = $html . "/>";
        return $html;
    }
}

class LoginController {
    const LOGIN_URL = "login.php?action=login";
    const LOGIN_METHOD = "POST";
    const HOME_URL = "/";
    
    private $model;

    public function __construct($model) {
        $this->model = $model;
    }
    
    public function login() {
        $username = pg_escape_string ( $_POST ['username'] );
        $password = pg_escape_string ( $_POST ['password'] );
        $userDatabase = new UserDatabase ();
        $result = $userDatabase->login ( $username, $password );
        if ($result->status === UserDatabaseResult::LOGIN_SUCCESS) {
            $user = $result->user;
            $_SESSION ['id'] = $user->id;
            $_SESSION ['username'] = $user->username;
            $_SESSION ['email'] = $user->email;
            $_SESSION ['phone'] = $user->phone;
            $_SESSION ['name'] = $user->name;
            $_SESSION ['bio'] = $user->bio;
            
            $this->model->username = $user->username;
            $this->model->password = "";
            $this->model->message = "Hello, " . $user->username . "!";
            $this->model->loginSuccess = true;
            
            $userDatabase->updateLastLogin ( $user->username, $user->password );
            
            header ( "Refresh: 3; URL=" . LoginController::HOME_URL );
        } else {
            $this->model->message = "Sorry, you have entered an invalid username-password pair.";
            $this->model->loginSuccess = false;
        }
    }
    
    public function handleHttpPost() {
        if (isset ( $_GET ['action'] )) {
            if ($_GET ['action'] === 'login') {
                $this->login ();
            }
        } else {
            // invalid request.
            http_response_code ( 400 );
            die ();
        }
    }
    
    public function handleHttpGet() {
        if (isset ( $_GET ['action'] )) {
            // request for controller action
            // No controller actions for HTTP GET
        } else {
            // display login page
        }
    }
    
    public function redirectToHome() {
        header ( "Refresh: 0; URL=" . LoginController::HOME_URL );
        die ();
    }
}

$model = new LoginModel ();
$controller = new LoginController ( $model );
$view = new LoginView ( $controller, $model );

if (isset ( $_SESSION ['username'] )) {
    $controller->redirectToHome ();
}

if ($_SERVER ['REQUEST_METHOD'] === 'POST') {
    $controller->handleHttpPost ();
} else if ($_SERVER ['REQUEST_METHOD'] === 'GET') {
    $controller->handleHttpGet ();
}

?>

<html>
	<body>
		<?php if ($model->loginSuccess) { ?>
			<h1>Login Successful, Redirecting...</h1>
			<p><?php echo $model->message; ?></p>
		<?php
        } else {
        ?>
			<?php
            include ('banner.php');
            ?>
			<form action="<?php echo LoginController::LOGIN_URL?>"
				  method="<?php echo LoginController::LOGIN_METHOD?>">
				Username: <br />
				<?php echo $view->getUsernameField(); ?> <br />
				<br /> Password: <br />
				<?php echo $view->getPasswordField(); ?><br />
				<br /> <input type="submit" value="Log In" />
			</form>
			<p><?php echo $model->message; ?></p>
		<?php
        }
        ?>
	</body>
</html>