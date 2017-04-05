<?php
include_once 'data/UserDatabase.php';
include_once '/model/User.php';
include_once 'HtmlHelper.php';

session_start ();

// user needs to be logged in
if (! isset ( $_SESSION ['username'] )) {
	header ( 'Refresh: 0; URL=http://localhost/login.php?next=' . urlencode ( "/profile.php" ) );
	die ();
}
class ProfileModel {
	public $password;
	public $email;
	public $phone;
	public $name;
	public $bio;
	public $profileRetrieveSuccess = false;
	public $profileEditSuccess = false;
	public $message;
	public $userDatabase;
	
	
	public function __construct() {
		$this->userDatabase = new UserDatabase ();
		$this->getUser();
	}
	public function isValid() {
		if (is_null ( $this->password ))
			return false;
		if (is_null ( $this->email ))
			return false;
		if (is_null ( $this->phone ))
			return false;
		if (is_null ( $this->name ))
			return false;
		if (is_null ( $this->bio ))
			return false;
		
		return strlen ( $this->password ) > 0 && strlen ( $this->email ) > 0 && strlen ( $this->phone ) > 0 && strlen ( $this->name ) > 0 && strlen ( $this->bio ) > 0;
	}
	
	private function getUser() {		
		$result = $this->userDatabase->getUser($_SESSION ['username']);
		if ($result->status === UserDatabaseResult::PROFILE_RETRIEVE_SUCCESS) {
			$this->profileRetrieveSuccess = true;
			$user = new User( $result->user['id'], $result->user['username'], $result->user['password'],
					$result->user['email'], $result->user['phone'], $result->user['name'],
					$result->user['bio'], $result->user['created_time'], $result->user['last_logged_in'],
					$result->user['role'] );
			
			$this->email = $user->email;
			$this->phone = $user->phone;
			$this->name = $user->name;
			$this->bio = $user->bio;
			
		} else {
			$this->profileRetrieveSuccess = false;
			$this->model->message = "Sorry, System error. Please contact administrator";
		}
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
		return HtmlHelper::makeInput2 ( "password", "password", "", "", "" );
	}
	public function getNameField() {
		return HtmlHelper::makeInput2 ( "text", "name", htmlspecialchars ( $this->model->name ), "", "" );
	}
	public function getEmailField() {
		return HtmlHelper::makeInput2 ( "email", "email", htmlspecialchars ( $this->model->email ), "", "" );
	}
	public function getPhoneField() {
		return HtmlHelper::makeInput2 ( "tel", "phone", htmlspecialchars ( $this->model->phone ), "", "" );
	}
	public function getBioField() {
		return HtmlHelper::makeTextArea2 ( "bio", htmlspecialchars ( $this->model->bio ), "", "" );
	}
}
class ProfileController {
	const PROFILE_URL = "/profile.php";
	const PROFILE_METHOD = "POST";
	const HOME_URL = "/index.php";
	const PROFILE_UPDATE_SUCCESS = "Profile updated successfully.";
	const PROFILE_UPDATE_FAIL = "Incorrect password.";
	private $model;
	private $userDatabase;
	public function __construct($model) {
		$this->model = $model;
		$this->userDatabase = $model->userDatabase;
	}
	public function update() {
		$username = pg_escape_string ( $_SESSION ['username'] );
		$password = pg_escape_string ( $_POST ['password'] );
		$name = pg_escape_string ( $_POST ['name'] );
		$bio = pg_escape_string ( $_POST ['bio'] );
		$email = pg_escape_string ( $_POST ['email'] );
		$phone = pg_escape_string ( $_POST ['phone'] );
		
		$result = $this->userDatabase->update ( $username, $password, $name, $phone, $bio, $email);
		if ($result->status === UserDatabaseResult::PROFILE_UPDATE_SUCCESS) {
			$user = $result->user;
			
			$this->model->password = "";
			$this->model->name;
			$this->model->bio;
			$this->model->email;
			$this->model->phone;
			$this->model->message = ProfileController::PROFILE_UPDATE_SUCCESS;
			$this->model->profileEditSuccess = true;
			
			header ( "Refresh: 3; URL=" . ProfileController::PROFILE_URL );
		} else {
			if ($result->status === UserDatabaseResult::PROFILE_UPDATE_FAILED) {
				$this->model->message = ProfileController::PROFILE_UPDATE_FAIL;
			}
			
			$this->model->username = $username;
			$this->model->password = "";
			$this->model->name = $name;
			$this->model->bio = $bio;
			$this->model->phone = $phone;
			$this->model->email = $email;
			$this->model->profileEditSuccess = false;
			header ( "Refresh: 3; URL=" . ProfileController::PROFILE_URL );
		}
	}
	public function handleHttpPost() {
		if (isset ( $_SESSION ['username'] )) {
			$this->model->username = $_SESSION ['username'];
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
		
		$this->update ();
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
		header ( "Refresh: 3; URL=" . ProfileController::HOME_URL );
		die ();
	}
}

$model = new ProfileModel ();
$controller = new ProfileController ( $model );
$view = new ProfileView ( $controller, $model );

if (isset ( $_SESSION ['id'] )) {
	$model->userId = $_SESSION ['id'];
} else {
	// should be logged in by here.
	// see top of profile.php
	return;
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
<title>Recado - Profile</title>
<!--
Holiday Template
http://www.templatemo.com/tm-475-holiday
-->
<link
	href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,400italic,600,700'
	rel='stylesheet' type='text/css'>
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


<body class="tm-gray-bg">
	<!-- Header -->
  	<?php
			include "banner.php";
			?>

	<!-- gray bg -->
	<section class="container tm-home-section-1" id="more">
		<div class="section-margin-top">
			<div class="row">
				<div class="tm-section-header">
					<div class="col-lg-4 col-md-3 col-sm-3">
						<hr>
					</div>
					<div class="col-lg-4 col-md-6 col-sm-6">
						<h2 class="tm-section-title">Profile</h2>
					</div>
					<div class="col-lg-4 col-md-3 col-sm-3">
						<hr>
					</div>
				</div>
			</div>
			<?php if ($model->profileRetrieveSuccess) { ?>
			<div class="row">
				<!-- contact form -->
				<form action="<?php echo ProfileController::PROFILE_URL?>"
					onsubmit="return validateForm()"
					method="<?php echo ProfileController::PROFILE_METHOD?>"
					class="tm-contact-form">
					<div class="col-lg-3">
						<br />
					</div>
					<div class="col-lg-6 col-md-6 tm-contact-form-input">
					<?php if (!$model->profileEditSuccess) { ?>
						<?php if (!is_null($model->message) && strlen($model->message) > 0) { ?>
						<div class="form-group">
							<label style="color: #FF0000;"><?php echo htmlspecialchars($model->message); ?></label>
						</div>
						<?php } ?>
                        <div class="form-group">
							<br /> Password (for verification): <br />
                        	<?php echo $view->getPasswordField(); ?>
							<div name="requiredPassword" style="display: none;">
								<p style="color: #FF0000;">This field is required.</p>
							</div>
						</div>
						<div class="form-group">
							<br /> Name: <br />
							<?php echo $view->getNameField(); ?>
        					<div name="requiredName" style="display: none;">
								<p style="color: #FF0000;">This field is required.</p>
							</div>
						</div>
						<div class="form-group">
							<br /> E-mail: <br />
							<?php echo $view->getEmailField(); ?>
        					<div name="badEmail" style="display: none;">
								<p style="color: #FF0000;">Please enter a valid email address.</p>
							</div>
						</div>
						<div class="form-group">
							<br /> Phone: <br />
							<?php echo $view->getPhoneField(); ?>
        					<div name="badPhone" style="display: none;">
								<p style="color: #FF0000;">Enter a valid phone number.</p>
							</div>
						</div>
						<div class="form-group">
							<br /> Bio Details: <br />
        					<?php echo $view->getBioField(); ?>
        				</div>
						<div class="form-group">
							<button class="tm-submit-btn" type="submit" name="update">Update</button>
						</div>               
					<?php } else { ?>
						<div class="form-group">
							<p><?php echo htmlspecialchars($model->message); ?></p>
						</div>
					<?php } ?>
					</div>
				</form>
			</div>
			<?php } else { ?>
				<div class="form-group">
					<p><?php echo htmlspecialchars($model->message);
						redirectToHome();
					?></p>
				</div>
			<?php } ?>
		</div>
	</section>
	<?php
	include "footer.php"?>
	<script type="text/javascript" src="js/jquery-1.11.2.min.js"></script>
	<!-- jQuery -->
	<script type="text/javascript" src="js/bootstrap.min.js"></script>
	<!-- bootstrap js -->
	<script type="text/javascript" src="js/jquery.flexslider-min.js"></script>
	<!-- flexslider js -->
	<script type="text/javascript" src="js/templatemo-script.js"></script>
	<!-- bootstrap date time picker js, http://eonasdan.github.io/bootstrap-datetimepicker/ -->
	<script type="text/javascript" src="js/moment.js"></script>
	<!-- moment.js -->

	<script>
        function validateForm() {
        	var valid = true;
        	if (!validateMandatoryFields()) valid = false;
        	if (!validateEmail()) valid = false;
        	if (!validatePhoneNumber()) valid = false;
        	return valid;
        }
    
        function validateMandatoryFields() {
        	var valid = true;
            var name = document.getElementsByName("name")[0].value;
            var nameInvalid = name == null || !(/\S/.test(name));
            var password = document.getElementsByName("password")[0].value;
            var passwordInvalid = password == null || !(/\S/.test(password));
            
            if (passwordInvalid) {
                document.getElementsByName("requiredPassword")[0].style.display = "block";
                document.getElementsByName("password")[0].style.borderColor = "#E34234";
                valid = false;
            } else {
                document.getElementsByName("requiredPassword")[0].style.display = "none";
                document.getElementsByName("password")[0].style.borderColor = "initial";
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
		});
		
		// Load Flexslider when everything is loaded.
		$(window).load(function() {	  		
		    $('.flexslider').flexslider({
			    controlNav: false
		    });
	  	});
          
	</script>
</body>
</html>