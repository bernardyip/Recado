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
    
    public function getRememberMe() {
        return $this->makeInput( "checkbox", "rememberMe", "Remember Me" );
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
    const VALIDATOR_LENGTH = 20;
    const SECONDS_7_DAYS = 604800; //60 * 60 * 24 * 7, 7 days
    const COOKIE_NAME = "remember";
    
    private $model;

    public function __construct($model) {
        $this->model = $model;
    }
    
    public function login() {
        $username = pg_escape_string ( $_POST ['username'] );
        $password = pg_escape_string ( $_POST ['password'] );
        $rememberMe = $_POST ['rememberMe'];
        $userDatabase = new UserDatabase ();
        $result = $userDatabase->login ( $username, $password );
        if ($result->status === UserDatabaseResult::LOGIN_SUCCESS) {
            $user = $result->user;
            
            if (!is_null($rememberMe)) {
                createLoginCookieForUser($user);
            }
            
            $this->setSessionForUser($user);
            
            $userDatabase->updateLastLogin ( $user->username, $user->password );
            
            header ( "Refresh: 3; URL=" . LoginController::HOME_URL );
        } else {
            $this->model->message = "Sorry, you have entered an invalid username-password pair.";
            $this->model->loginSuccess = false;
        }
    }
    
    public function loginWithCookie() {
        $cookieValue = $_COOKIE[LoginController::COOKIE_NAME];
        $pieces = explode(":", $cookieValue);
        
        if (sizeof($pieces) !== 2) return;
        
        $selector = $pieces[0];
        $validator = $pieces[1];
        $userDatabase = new UserDatabase();
        
        $result = $userDatabase->findUserFromAuthCookie($selector, $validator);
        
        if ($result->status === UserDatabaseResult::AUTH_FIND_SUCCESS) {
            $user = $result->user;
            
            $this->setSessionForUser($user);
            
            header ( "Refresh: 3; URL=" . LoginController::HOME_URL );
        }
    }
    
    public function logout() {
        $this->removeLoginCookieForUser();
        session_destroy ();
        $this->redirectToHome();
    }
    
    private function removeLoginCookieForUser() {
        if ( isset ($_COOKIE[LoginController::COOKIE_NAME]) ) {
            setcookie(LoginController::COOKIE_NAME, 
                    $resultAuthCookie->auth->selector . ":" . $resultAuthCookie->auth->validator,
                    time() - LoginController::SECONDS_7_DAYS, "/", "localhost", false, false);
        }
    }
    
    private function createLoginCookieForUser($user) {
        $resultAuthCookie = $userDatabase->createAuthCookie($user, LoginController::VALIDATOR_LENGTH);
        if ($resultAuthCookie->status === UserDatabaseResult::AUTH_CREATE_SUCCESS) {
            // successfully associated token, keep a cookie for the client
            setcookie(LoginController::COOKIE_NAME, 
                    $resultAuthCookie->auth->selector . ":" . $resultAuthCookie->auth->validator,
                    time() + LoginController::SECONDS_7_DAYS, "/", "localhost", false, false);
        }
    }
    
    private function setSessionForUser($user) {
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
    }
    
    public function handleHttpPost() {
        if (isset ( $_GET ['action'] )) {
            if ($_GET ['action'] === 'login') {
                $this->login ();
            }
        } 

        // invalid request.
        http_response_code ( 400 );
        die ();
    }
    
    public function handleHttpGet() {
        if (isset ( $_GET ['action'] )) {
            if ($_GET ['action'] === 'logout') {
                $this->logout ();
            }
        }

        // display login page
    }
    
    public function redirectToHome() {
        header ( "Refresh: 0; URL=" . LoginController::HOME_URL );
        die ();
    }
}

$model = new LoginModel ();
$controller = new LoginController ( $model );
$view = new LoginView ( $controller, $model );

if ($_SERVER ['REQUEST_METHOD'] === 'POST') {
    $controller->handleHttpPost ();
} else if ($_SERVER ['REQUEST_METHOD'] === 'GET') {
    $controller->handleHttpGet ();
}

if ( isset( $_SESSION ['username'] ) ) {
    $controller->redirectToHome ();
} else if ( isset( $_COOKIE[LoginController::COOKIE_NAME] ) ) {
    $controller->loginWithCookie ();
}

?>

<html>
<head>
<script type="text/javascript">

function validateForm() {
	var valid = true;
    var username = document.getElementsByName("username")[0].value;
    var usernameInvalid = username == null || !(/\S/.test(username));
    var password = document.getElementsByName("password")[0].value;
    var passwordInvalid = name == null || !(/\S/.test(name));

    if (usernameInvalid) {
        document.getElementsByName("requiredUsername")[0].style.display = "block";
        document.getElementsByName("username")[0].style.borderColor = "#E34234";
        valid = false;
    } else {
        document.getElementsByName("requiredUsername")[0].style.display = "none";
        document.getElementsByName("username")[0].style.borderColor = "initial";
    }

    if (passwordInvalid) {
        document.getElementsByName("requiredPassword")[0].style.display = "block";
        document.getElementsByName("password")[0].style.borderColor = "#E34234";
        valid = false;
    } else {
        document.getElementsByName("requiredPassword")[0].style.display = "none";
        document.getElementsByName("password")[0].style.borderColor = "initial";
    }

    return valid;
}

</script>
</head>
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
				  onsubmit="return validateForm()"
				  method="<?php echo LoginController::LOGIN_METHOD?>">
				Username: <br />
				<?php echo $view->getUsernameField(); ?> <br />
				<div name="requiredUsername" style="display:none;"><p style="color:#FF0000;"> This field is required. </p></div> <br />
				<br /> Password: <br />
				<?php echo $view->getPasswordField(); ?><br />
				<div name="requiredPassword" style="display:none;"><p style="color:#FF0000;"> This field is required. </p></div> <br />
				<br /> Remember Me: <?php echo $view->getRememberMe(); ?><br />
				<br /> <input type="submit" value="Log In" />
			</form>
			<p>Don't have an account? <a href="/register.php" >Register here</a>.</p>
			<p><?php echo $model->message; ?></p>
		<?php
        }
        ?>
	</body>
</html>