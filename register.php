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
        strlen($this->bio) > 0 && strlen($this->bio) <= 1000;
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
        return HtmlHelper::makeInput2( "text", "username", htmlspecialchars($this->model->username), "Username", "", true);
    }
    
    public function getPasswordField() {
        return HtmlHelper::makeInput2( "password", "password", "", "Password", "");
    }
    
    public function getConfirmPasswordField() {
        return HtmlHelper::makeInput2( "password", "confirmPassword", "", "Confirm Password", "");
    }
    
    public function getNameField() {
        return HtmlHelper::makeInput2( "text", "name", htmlspecialchars($this->model->name), "Name", "");
    }
    
    public function getEmailField() {
        return HtmlHelper::makeInput2( "email", "email", htmlspecialchars($this->model->email), "Email Address", "");
    }
    
    public function getPhoneField() {
        return HtmlHelper::makeInput2( "tel", "phone", htmlspecialchars($this->model->phone), "Phone Number", "");
    }
    
    public function getBioField() {
        return HtmlHelper::makeTextArea2( "bio", htmlspecialchars($this->model->bio), 3, "Tell us about yourself");
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
        
        $result = $this->userDatabase->register( $this->model->username, $this->model->password, 
                $this->model->email, $this->model->phone, $this->model->name, $this->model->bio );
        if ($result->status === UserDatabaseResult::REGISTER_SUCCESS) {
            $user = $result->user;
            
            $this->model->username = $user->username;
            $this->model->password = "";
            $this->model->message = "Welcome to Recado, " . $user->username . "!";
            $this->model->registerSuccess = true;
            
            header ( "Refresh: 2; URL=" . RegisterController::LOGIN_URL . "?username=" . urlencode($user->username) );
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

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Recado - Register</title>
<!--
Holiday Template
http://www.templatemo.com/tm-475-holiday
-->
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,400italic,600,700' rel='stylesheet' type='text/css'>
	<link href="css/font-awesome.min.css" rel="stylesheet">
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/bootstrap-datetimepicker.min.css" rel="stylesheet">  
	<link href="css/flexslider.css" rel="stylesheet">
	<link href="css/templatemo-style.css" rel="stylesheet">

	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>
<body>
	<!-- Header -->
	<div class="tm-header">
		<div class="container">
			<div class="row">
				<div class="col-lg-6 col-md-4 col-sm-3 tm-site-name-container">
					<a href="index.html" class="tm-site-name">Recado</a>	
				</div>
				<div class="col-lg-6 col-md-8 col-sm-9">
					<div class="mobile-menu-icon">
						<i class="fa fa-bars"></i>
					</div>
					<nav class="tm-nav">
						<ul>
						</ul>
					</nav>		
				</div>				
			</div>
		</div>	  	
	</div>
	
	<!-- white bg -->
	<section class="section-padding-bottom">
		<div class="container">
			<div class="row">
				<div class="tm-section-header section-margin-top">
					<div class="col-lg-4 col-md-3 col-sm-3"><hr></div>
					<div class="col-lg-4 col-md-6 col-sm-6"><h2 class="tm-section-title">Register</h2></div>
					<div class="col-lg-4 col-md-3 col-sm-3"><hr></div>	
				</div>				
			</div>
			<div class="row">
				<!-- contact form -->
				<form action="<?php echo RegisterController::REGISTER_URL?>" 
						onsubmit="return validateForm()"
						method="<?php echo RegisterController::REGISTER_METHOD?>"
						class="tm-contact-form">
					<div class="col-lg-3">
						<br />
					</div> 
					<div class="col-lg-6 col-md-6 tm-contact-form-input">
					<?php if (!$model->registerSuccess) { ?>
						<?php if (!is_null($model->message) && strlen($model->message) > 0) { ?>
						<div class="form-group">
							<label style="color: #FF0000;"><?php echo htmlspecialchars($model->message); ?></label>
						</div>
						<?php } ?>
                        <div class="form-group">
        					<?php echo $view->getUsernameField(); ?> <br />
        					<div name="requiredUsername" style="display:none;"><p style="color:#FF0000;"> This field is required. </p></div> <br />
        				</div>
                        <div class="form-group">
							<?php echo $view->getPasswordField(); ?><br />
							<div name="requiredPassword" style="display:none;"><p style="color:#FF0000;"> This field is required. </p></div> <br />
						</div>
						<div class="form-group">
							<?php echo $view->getConfirmPasswordField(); ?><br />
        					<div name="mismatchPassword" style="display:none;"><p style="color:#FF0000;"> Passwords do not match. </p></div> <br />
        				</div>
						<div class="form-group">
							<?php echo $view->getNameField(); ?><br />
        					<div name="requiredName" style="display:none;"><p style="color:#FF0000;"> This field is required. </p></div> <br />
        				</div>
						<div class="form-group">
							<?php echo $view->getEmailField(); ?><br />
        					<div name="badEmail" style="display:none;"><p style="color:#FF0000;"> Please enter a valid email address. </p></div> <br />
        				</div>
						<div class="form-group">
							<?php echo $view->getPhoneField(); ?><br />
        					<div name="badPhone" style="display:none;"><p style="color:#FF0000;"> Enter a valid phone number. </p></div> <br />
        				</div>
						<div class="form-group">
        					<?php echo $view->getBioField(); ?><br />
        				</div>
						<div class="form-group">
							<button class="tm-submit-btn" type="submit" name="submit">Proceed</button> 
						</div>               
						<br /><br /><p class="text-center">Already have an account? <a href="/login.php" >Login here</a>.</p>
					<?php } else { ?>
						<div class="form-group">
							<p><?php echo htmlspecialchars($model->message); ?></p>
						</div>
					<?php } ?>
					</div>
					<div class="col-lg-3">
						<br />
					</div> 
				</form>
			</div>			
		</div>
	</section>
	<?php 
	   include "footer.php"
	?>
	<script type="text/javascript" src="js/jquery-1.11.2.min.js"></script>      		<!-- jQuery -->
	<script type="text/javascript" src="js/bootstrap.min.js"></script>					<!-- bootstrap js -->
	<script type="text/javascript" src="js/jquery.flexslider-min.js"></script>			<!-- flexslider js -->
	<script type="text/javascript" src="js/templatemo-script.js"></script>      		<!-- Templatemo Script -->
	<script type="text/javascript" src="js/bootstrap-datetimepicker.min.js"></script>	<!-- bootstrap date time picker js, http://eonasdan.github.io/bootstrap-datetimepicker/ -->
	<script type="text/javascript" src="js/moment.js"></script>							<!-- moment.js -->
	
    <script>
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
	
      	// DOM is ready
		$(function() {

        
			// https://css-tricks.com/snippets/jquery/smooth-scrolling/
			$('a[href*=#]:not([href=#])').click(function() {
				if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
					var target = $(this.hash);
					target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
					if (target.length) {
						$('html,body').animate({
							scrollTop: target.offset().top
						}, 1000);
						return false;
					}
				}
			});

		  	// Flexslider
		  	$('.flexslider').flexslider({
		  		controlNav: false,
		  		directionNav: false
		  	});

            $('.date').datetimepicker({
            	format: 'MM/DD/YYYY'
            });
            $('.date-time').datetimepicker();
           
		  });

          
	</script>
</body>
</html>
