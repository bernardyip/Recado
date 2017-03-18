<?php 

include_once 'data/UserDatabase.php';
include_once 'HtmlHelper.php';

class RegisterModel {
    public $username;
    public $password;
    public $email;
    public $phone;
    public $name;
    public $bio;
    public $registerSuccess = false;
    public $message;
    
    public function __construct() {
    }
    
    public function isValid() {
        if (is_null($this->username)) return false;
        if (is_null($this->password)) return false;
        if (is_null($this->email)) return false;
        if (is_null($this->phone)) return false;
        if (is_null($this->name)) return false;
        if (is_null($this->bio)) return false;
        
        return strlen($this->username) > 0 &&
        strlen($this->password) > 0 &&
        strlen($this->email) > 0 &&
        strlen($this->phone) > 0 &&
        strlen($this->name) > 0 &&
        strlen($this->bio) > 0;
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
        return HtmlHelper::makeInput( "text", "username", htmlspecialchars($this->model->username), "", "", true);
    }
    
    public function getPasswordField() {
        return HtmlHelper::makeInput( "password", "password", "", "", "");
    }
    
    public function getConfirmPasswordField() {
        return HtmlHelper::makeInput( "password", "confirmPassword", "", "", "");
    }
    
    public function getNameField() {
        return HtmlHelper::makeInput( "text", "name", htmlspecialchars($this->model->name), "", "");
    }
    
    public function getEmailField() {
        return HtmlHelper::makeInput( "email", "email", htmlspecialchars($this->model->email), "", "");
    }
    
    public function getPhoneField() {
        return HtmlHelper::makeInput( "tel", "phone", htmlspecialchars($this->model->phone), "", "");
    }
    
    public function getBioField() {
        return HtmlHelper::makeInput( "text", "bio", htmlspecialchars($this->model->bio), "", "");
    }
}

class RegisterController {
    const REGISTER_URL = "register.php?action=register";
    const REGISTER_METHOD = "POST";
    const LOGIN_URL = "/login.php";
    
    private $model;
    private $userDatabase;
    
    public function __construct($model) {
        $this->model = $model;
        $this->userDatabase = new UserDatabase ();
    }
    
    public function register() {
        $username = pg_escape_string ( $_POST ['username'] );
        $password = pg_escape_string ( $_POST ['password'] );
        $name = pg_escape_string ( $_POST ['name'] );
        $bio = pg_escape_string ( $_POST ['bio'] );
        
        $result = $this->userDatabase->register( $this->model->username, $this->model->password, $this->model->name, $this->model->bio );
        if ($result->status === UserDatabaseResult::REGISTER_SUCCESS) {
            $user = $result->user;
            
            $this->model->username = $user->username;
            $this->model->password = "";
            $this->model->message = "Welcome to Recado, " . $user->username . "!";
            $this->model->registerSuccess = true;
            
            header ( "Refresh: 3; URL=" . RegisterController::LOGIN_URL . "?username=" . urlencode($user->username) );
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
        
        if (isset ( $_POST ['username'] )) {
            $this->model->username = $_POST ['username'];
        }
        if (isset ( $_POST ['password'] )) {
            $this->model->password = $_POST ['password'];
        }
        if (isset ( $_POST ['email'] )) {
            $this->model->email = $_POST ['email'];
        }
        if (isset ( $_POST ['phone'] )) {
            $this->model->phone = $_POST ['phone'];
        }
        if (isset ( $_POST ['name'] )) {
            $this->model->name = $_POST ['name'];
        }
        if (isset ( $_POST ['bio'] )) {
            $this->model->bio = $_POST ['bio'];
        }
        
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
	var valid = true;
	if (!validateMandatoryFields()) valid = false;
	if (!passwordsMatch()) valid = false;
	if (!validateEmail()) valid = false;
	if (!validatePhoneNumber()) valid = false;

	return valid;
}

function validateMandatoryFields() {
	var valid = true;
    var username = document.getElementsByName("username")[0].value;
    var usernameInvalid = username == null || !(/\S/.test(username));
    var name = document.getElementsByName("name")[0].value;
    var nameInvalid = name == null || !(/\S/.test(name));

    if (usernameInvalid) {
        document.getElementsByName("requiredUsername")[0].style.display = "block";
        document.getElementsByName("username")[0].style.borderColor = "#E34234";
        valid = false;
    } else {
        document.getElementsByName("requiredUsername")[0].style.display = "none";
        document.getElementsByName("username")[0].style.borderColor = "initial";
    }

    if (nameInvalid) {
        document.getElementsByName("requiredName")[0].style.display = "block";
        document.getElementsByName("name")[0].style.borderColor = "#E34234";
        valid = false;
    } else {
        document.getElementsByName("requiredName")[0].style.display = "none";
        document.getElementsByName("name")[0].style.borderColor = "initial";
    }

    return valid;
}

function validateEmail() {
    var email = document.getElementsByName("email")[0].value;
    var regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	var valid = regex.test(email);
	if (valid) {
        document.getElementsByName("badEmail")[0].style.display = "none";
        document.getElementsByName("email")[0].style.borderColor = "initial";
	} else {
        document.getElementsByName("badEmail")[0].style.display = "block";
        document.getElementsByName("email")[0].style.borderColor = "#E34234";
	}
    return valid; 
}

function validatePhoneNumber() {
    var phoneNumber = document.getElementsByName("phone")[0].value;
	var regex = /^[0-9]{8}$/;
    var valid = regex.test(phoneNumber);
    if(valid) {
        document.getElementsByName("badPhone")[0].style.display = "none";
        document.getElementsByName("phone")[0].style.borderColor = "initial";
	}  
	else {
        document.getElementsByName("badPhone")[0].style.display = "block";
        document.getElementsByName("phone")[0].style.borderColor = "#E34234";
	}
	return valid;
}
	
function passwordsMatch() {
    var password = document.getElementsByName("password")[0].value;
    var confirmPassword = document.getElementsByName("confirmPassword")[0].value;
    var passwordInvalid = password == null || !(/\S/.test(password));
    if (passwordInvalid) {
        document.getElementsByName("requiredPassword")[0].style.display = "block";
        document.getElementsByName("password")[0].style.borderColor = "#E34234";
        document.getElementsByName("mismatchPassword")[0].style.display = "none";
        document.getElementsByName("confirmPassword")[0].style.borderColor = "initial";
        return false;
    } else {
        document.getElementsByName("requiredPassword")[0].style.display = "none";
        document.getElementsByName("password")[0].style.borderColor = "initial";
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
				<div name="requiredUsername" style="display:none;"><p style="color:#FF0000;"> This field is required. </p></div> <br />
				<br /> Password: <br />
				<?php echo $view->getPasswordField(); ?><br />
				<div name="requiredPassword" style="display:none;"><p style="color:#FF0000;"> This field is required. </p></div> <br />
				<br /> Confirm Password: <br />
				<?php echo $view->getConfirmPasswordField(); ?><br />
				<div name="mismatchPassword" style="display:none;"><p style="color:#FF0000;"> Passwords do not match. </p></div> <br />
				<br /> Name: <br />
				<?php echo $view->getNameField(); ?><br />
				<div name="requiredName" style="display:none;"><p style="color:#FF0000;"> This field is required. </p></div> <br />
				<br /> E-mail: <br />
				<?php echo $view->getEmailField(); ?><br />
				<div name="badEmail" style="display:none;"><p style="color:#FF0000;"> Please enter a valid email address. </p></div> <br />
				<br /> Phone: <br />
				<?php echo $view->getPhoneField(); ?><br />
				<div name="badPhone" style="display:none;"><p style="color:#FF0000;"> Enter a valid phone number. </p></div> <br />
				<br /> Details: <br />
				<?php echo $view->getBioField(); ?><br />
				<br /> <input type="submit" value="Register" />
			</form>
			<p><?php echo htmlspecialchars($model->message); ?></p>
		<?php
        }
        ?>
	</body>
</html>
