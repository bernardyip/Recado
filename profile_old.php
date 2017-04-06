<?php 

include_once 'data/UserDatabase.php';
include_once 'HtmlHelper.php';

session_start();

// user needs to be logged in
if (!isset($_SESSION['username'])) {
    header('Refresh: 0; URL=http://localhost/login.php?next=' . urlencode("/profile.php"));
    die();
}

class ProfileModel {
	public $username;
	public $password;
    public $email;
    public $phone;
    public $name;
    public $bio;
    public $profileEditSuccess = false;
    public $message;
    
    
    public function __construct() {
    }
    
    public function isValid() {
        if (is_null($this->password)) return false;
        if (is_null($this->email)) return false;
        if (is_null($this->phone)) return false;
        if (is_null($this->name)) return false;
        if (is_null($this->bio)) return false;
        
        return strlen($this->password) > 0 &&
        strlen($this->email) > 0 &&
        strlen($this->phone) > 0 &&
        strlen($this->name) > 0 &&
        strlen($this->bio) > 0;
    }
}

class ProfileView {
    private $model;
    private $controller;
    
    public function __construct($controller, $model) {
        $this->controller = $controller;
        $this->model = $model;
    }

    public function getPasswordField() {
        return HtmlHelper::makeInput( "password", "password", "", "", "");
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

class ProfileController {
    const PROFILE_URL = "/profile.php?message=";
    const PROFILE_METHOD = "POST";
    const HOME_URL = "/";
    
    const PROFILE_UPDATE_SUCCESS = "Updated user successfully.";
    const PROFILE_UPDATE_FAIL = "Incorrect password.";
    
    private $model;
    private $userDatabase;
    
    public function __construct($model) {
        $this->model = $model;
        $this->userDatabase = new UserDatabase ();
    }
    
    public function update() {
        $username = pg_escape_string ( $_POST ['username'] );
        $password = pg_escape_string ( $_POST ['password'] );
        $name = pg_escape_string ( $_POST ['name'] );
        $bio = pg_escape_string ( $_POST ['bio'] );
        $email = pg_escape_string ( $_POST ['email'] );
        $phone = pg_escape_string ( $_POST ['phone'] );
                
        $result = $this->userDatabase->update( $this->model->username, $this->model->password, $this->model->name, $this->model->phone, $this->model->bio, $this->model->email);
        if ($result->status === UserDatabaseResult::PROFILE_UPDATE_SUCCESS) {
            $user = $result->user;
            
            $this->model->username = $user->username;
            $this->model->password = "";
            $this->model->name;
            $this->model->bio;
            $this->model->email;
            $this->model->phone;
            $this->model->message = PROFILE_UPDATE_SUCCESS;
            $this->model->profileEditSuccess = true;
            
            header ( "Refresh: 0; URL=" . ProfileController::PROFILE_URL . "?message=" . urlencode($message) );
        } else {
            if ($result->status === UserDatabaseResult::PROFILE_UPDATE_FAILED) {
                $this->model->message = PROFILE_UPDATE_FAIL;
            }
            
            $this->model->username = $username;
            $this->model->password = "";
            $this->model->name = $name;
            $this->model->bio = $bio;
            $this->model->phone = $phone;
            $this->model->email = $email;
            $this->model->profileEditSuccess = false;
            header ( "Refresh: 0; URL=" . ProfileController::PROFILE_URL . "?message=" . urlencode($message) );
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
            if ($_GET ['action'] === 'update') {
                $this->update ();
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
        header ( "Refresh: 0; URL=" . ProfileController::HOME_URL );
        die ();
    }
}

$model = new ProfileModel ();
$controller = new ProfileController ( $model );
$view = new ProfileView ( $controller, $model );

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
			<?php if ($model->profileEditSuccess) { ?>
				<h1>Profile updated.</h1>
				<p><?php echo $model->message; ?></p>
			<?php
			} else {
			?>
				<?php
				include ('banner.php');
				?>
				<form action="<?php echo ProfileController::PROFILE_URL?>"
						onsubmit="return validateForm()"
						method="<?php echo ProfileController::PROFILE_METHOD?>">
						<br /> Password (for verification): <br />
						<?php echo $view->getPasswordField(); ?><br />
						<div name="requiredPassword" style="display:none;"><p style="color:#FF0000;"> This field is required. </p></div> <br />
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
						<br /> <input type="submit" value="Update" />
	</form>
	<p><?php echo htmlspecialchars($model->message); ?></p>
				<?php
			}
			?>
		</body>
</html>