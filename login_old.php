<?php

session_start();
include_once 'data/UserDatabase.php';
include_once 'HtmlHelper.php';

class LoginModel {
    public $username = null;
    public $password = null;
    public $rememberMe = null;
    public $message = "";
    public $next;
    public $loginSuccess = false;

    public function __construct() {
    }
    
    public function isValid() {
        if (is_null($this->username)) return false;
        if (is_null($this->password)) return false;
        return strlen($this->username) > 0 && strlen($this->password) > 0;
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
        return HtmlHelper::makeInput( "text", "username", htmlspecialchars($this->model->username), "", "", true);
    }
    
    public function getPasswordField() {
        return HtmlHelper::makeInput( "password", "password", "", "", "");
    }
    
    public function getRememberMe() {
        return HtmlHelper::makeInput( "checkbox", "rememberMe", "Remember Me", "", "");
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
    private $userDatabase;

    public function __construct($model) {
        $this->model = $model;
        $this->userDatabase = new UserDatabase ();
    }
    
    public function login() {
        if ($this->model->isValid()) {
            $result = $this->userDatabase->login ( $this->model->username, $this->model->password );
            if ($result->status === UserDatabaseResult::LOGIN_SUCCESS) {
                $user = $result->user;
                
                if (!is_null($this->model->rememberMe)) {
                    $this->createLoginCookieForUser($user);
                }
                
                $this->setSessionForUser($user);
                $this->respondLoginSuccess($user);
                $this->userDatabase->updateLastLogin ( $user->username, $user->password );

                $this->redirectToNext();
            } else {
                $this->model->message = "Sorry, you have entered an invalid username-password pair.";
                $this->model->loginSuccess = false;
            }
        }
        else {
            return;
        }
    }
    
    public function loginWithCookie() {
        $cookieValue = $_COOKIE[LoginController::COOKIE_NAME];
        $pieces = explode(":", $cookieValue);
        
        if (sizeof($pieces) !== 2) return;
        
        $selector = $pieces[0];
        $validator = $pieces[1];
        
        $result = $this->userDatabase->findUserFromAuthCookie($selector, $validator);
        
        if ($result->status === UserDatabaseResult::AUTH_FIND_SUCCESS) {
            $user = $result->user;
            
            $this->setSessionForUser($user);
            $this->respondLoginSuccess($user);
            $this->userDatabase->updateLastLogin ( $user->username, $user->password );
            
            $this->redirectToNext();
        }
    }
    
    public function logout() {
        $this->removeLoginCookieForUser();
        session_destroy ();
        $this->redirectToHome();
    }
    
    public function getLoginUrl() {
        if (!is_null($this->model->next)) {
            return LoginController::LOGIN_URL . "&next=" . urlencode($this->model->next);
        } else {
            return LoginController::LOGIN_URL;
        }
    }
    
    private function removeLoginCookieForUser() {
        if ( isset ($_COOKIE[LoginController::COOKIE_NAME]) ) {
            setcookie(LoginController::COOKIE_NAME, 
                    $resultAuthCookie->auth->selector . ":" . $resultAuthCookie->auth->validator,
                    time() - LoginController::SECONDS_7_DAYS, "/", "localhost", false, false);
        }
    }
    
    private function createLoginCookieForUser($user) {
        $resultAuthCookie = $this->userDatabase->createAuthCookie($user, LoginController::VALIDATOR_LENGTH);
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
    }
    
    private function respondLoginSuccess($user) {
        $this->model->username = $user->username;
        $this->model->password = "";
        $this->model->message = "Hello, " . $user->username . "!";
        $this->model->loginSuccess = true;
    }
    
    private function redirectToNext() {
        if (!is_null($this->model->next)) {
            header ( "Refresh: 3; URL=" . $this->model->next );
        } else {
            header ( "Refresh: 3; URL=" . LoginController::HOME_URL );
        }
    }
    
    public function handleHttpPost() {
        
        if (isset ( $_POST ['username'] )) {
            $this->model->username = $_POST ['username'];
        }
        if (isset ( $_POST ['password'] )) {
            $this->model->password = $_POST ['password'];
        }
        if (isset ( $_POST ['rememberMe'] )) {
            $this->model->rememberMe = $_POST ['rememberMe'];
        }
        if (isset ( $_GET ['next'] )) {
            $this->model->next = $_GET ['next'];
        }

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
            if ($_GET ['action'] === 'logout') {
                $this->logout ();
                return;
            }
        } 
        if (isset ( $_GET ['username'] )) {
            $this->model->username = $_GET ['username'];
        }
        if (isset ( $_GET ['next'] )) {
            $this->model->next = $_GET ['next'];
        }
        
        if ( isset( $_SESSION ['username'] ) ) {
            $this->redirectToHome ();
        }
        if ( isset( $_COOKIE[LoginController::COOKIE_NAME] ) ) {
            $this->loginWithCookie ();
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

if ($_SERVER ['REQUEST_METHOD'] === 'POST') {
    $controller->handleHttpPost ();
} else if ($_SERVER ['REQUEST_METHOD'] === 'GET') {
    $controller->handleHttpGet ();
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
    var passwordInvalid = password == null || !(/\S/.test(password));

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
			<p><?php echo htmlspecialchars($model->message); ?></p>
		<?php
        } else {
        ?>
			<?php
            include ('banner.php');
            ?>
			<form action="<?php echo $controller->getLoginUrl();?>"
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
			<p><?php echo htmlspecialchars($model->message); ?></p>
		<?php
        }
        ?>
	</body>
</html>