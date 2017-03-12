<?php 

session_start();
include_once 'data/UserDatabase.php';

class RegisterModel {
    public $username;
    public $password;
    public $email;
    public $phone;
    public $name;
    public $bio;
    public $registerSuccess;
    public $message;
    
    public function __construct() {
        
    }
}

class RegisterView {
    private $model;
    private $controller;
    
    public function __construct($controller, $model) {
        $this->controller = $controller;
        $this->model = $model;
    }
    
    public function getUsernameField() {
        return $this->makeInput ( "text", "username", $this->model->username, true );
    }
    
    public function getPasswordField() {
        return $this->makeInput ( "password", "password", "" );
    }
    
    public function getConfirmPasswordField() {
        return $this->makeInput ( "password", "confirmPassword", "" );
    }
    
    public function getNameField() {
        return $this->makeInput ( "text", "name", $this->model->name );
    }
    
    public function getEmailField() {
        return $this->makeInput ( "email", "email", $this->model->email );
    }
    
    public function getPhoneField() {
        return $this->makeInput ( "tel", "phone", "" );
    }
    
    public function getBioField() {
        return $this->makeInput ( "text", "bio", $this->model->bio );
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

class RegisterController {
    const REGISTER_URL = "register.php?action=register";
    const REGISTER_METHOD = "POST";
    const HOME_URL = "/";
    
    private $model;
    
    public function __construct($model) {
        $this->model = $model;
    }
    
    public function register() {
        $username = pg_escape_string ( $_POST ['username'] );
        $password = pg_escape_string ( $_POST ['password'] );
        $name = pg_escape_string ( $_POST ['name'] );
        $bio = pg_escape_string ( $_POST ['bio'] );
        
        $userDatabase = new UserDatabase ();
        $result = $userDatabase->register( $username, $password, $name, $bio );
        if ($result->status === UserDatabaseResult::REGISTER_SUCCESS) {
            $user = $result->user;
            $_SESSION ['id'] = $user->id;
            $_SESSION ['username'] = $user->username;
            $_SESSION ['email'] = $user->email;
            $_SESSION ['phone'] = $user->phone;
            $_SESSION ['name'] = $user->name;
            $_SESSION ['bio'] = $user->bio;
            
            $this->model->username = $user->username;
            $this->model->password = "";
            $this->model->message = "Welcome to Recado, " . $user->username . "!";
            $this->model->registerSuccess = true;
            
            header ( "Refresh: 3; URL=" . RegisterController::HOME_URL );
        } else {
            if ($result->status === UserDatabaseResult::REGISTER_USERNAME_TAKEN) {
                $this->model->message = "Sorry, the username you have entered is already taken.";
            } else if ($result->status === UserDatabaseResult::REGISTER_FAILED) {
                $this->model->message = "An unexpected error has occured, please try again later.";
            }
            $this->model->username = $username;
            $this->model->password = "";
            $this->model->name = $name;
            $this->model->bio = $bio;
            $this->model->registerSuccess = false;
        }
    }
    
    public function handleHttpPost() {
        if (isset ( $_GET ['action'] )) {
            if ($_GET ['action'] === 'register') {
                $this->register ();
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
        header ( "Refresh: 0; URL=" . RegisterController::HOME_URL );
        die ();
    }
}

$model = new RegisterModel ();
$controller = new RegisterController ( $model );
$view = new RegisterView ( $controller, $model );

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
<head>
<script type="text/javascript">
function validateForm() {
	if (!passwordsMatch()) return false;
}

function passwordsMatch() {
    var password = document.getElementsByName("password")[0].value;
    var confirmPassword = document.getElementsByName("confirmPassword")[0].value;
    alert(document.getElementsByName("password")[0].style.borderColor);
    if (password != confirmPassword) {
        document.getElementsByName("mismatchPassword")[0].style.display = "block";
        document.getElementsByName("password")[0].style.borderColor = "#E34234";
        document.getElementsByName("confirmPassword")[0].style.borderColor = "#E34234";
        return false;
    }
    else {
        document.getElementsByName("mismatchPassword")[0].style.display = "none";
        document.getElementsByName("password")[0].style.borderColor = "initial";
        document.getElementsByName("confirmPassword")[0].style.borderColor = "initial";
		return true;
    }
}
</script>
</head>
	<body>
		<?php if ($model->registerSuccess) { ?>
			<h1>Register Successful, Redirecting...</h1>
			<p><?php echo $model->message; ?></p>
		<?php
        } else {
        ?>
			<?php
            include ('banner.php');
            ?>
			<form action="<?php echo RegisterController::REGISTER_URL?>"
				  onsubmit="return validateForm()"
				  method="<?php echo RegisterController::REGISTER_METHOD?>">
				Username: <br />
				<?php echo $view->getUsernameField(); ?> <br />
				<br /> Password: <br />
				<?php echo $view->getPasswordField(); ?><br />
				<br /> Confirm Password: <br />
				<?php echo $view->getConfirmPasswordField(); ?><br />
				<div name="mismatchPassword" style="display:none;"><p style="font-color:#FF0000;"> Passwords do not match. </p></div> <br />
				<br /> Name: <br />
				<?php echo $view->getNameField(); ?><br />
				<br /> E-mail: <br />
				<?php echo $view->getEmailField(); ?><br />
				<br /> Phone: <br />
				<?php echo $view->getPhoneField(); ?><br />
				<br /> Details: <br />
				<?php echo $view->getBioField(); ?><br />
				<br /> <input type="submit" value="Register" />
			</form>
			<p><?php echo $model->message; ?></p>
		<?php
        }
        ?>
	</body>
</html>
