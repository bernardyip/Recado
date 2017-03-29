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
        return HtmlHelper::makeInput2( "text", "username", htmlspecialchars($this->model->username), "Username", "", true);
    }
    
    public function getPasswordField() {
        return HtmlHelper::makeInput2( "password", "password", "", "Password", "");
    }
    
    public function getRememberMe() {
        return HtmlHelper::makeInput2( "checkbox", "rememberMe", "Remember Me", "", "");
    }
}

class LoginController {
    const LOGIN_URL = "login.php?action=login";
    const LOGIN_METHOD = "POST";
    const HOME_URL = "/index.php";
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
            header ( "Refresh: 1; URL=" . $this->model->next );
        } else {
            header ( "Refresh: 1; URL=" . LoginController::HOME_URL );
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

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Recado - Login</title>
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
					<div class="col-lg-4 col-md-6 col-sm-6"><h2 class="tm-section-title">Login</h2></div>
					<div class="col-lg-4 col-md-3 col-sm-3"><hr></div>	
				</div>				
			</div>
			<div class="row">
				<!-- contact form -->
				<form action="<?php echo $controller->getLoginUrl();?>" 
						onSubmit="return validateForm()"
						method="<?php echo LoginController::LOGIN_METHOD?>" 
						class="tm-contact-form">
					<div class="col-lg-3">
						<br />
					</div>
					<div class="col-lg-6 col-md-6 tm-contact-form-input">
					<?php if (!$model->loginSuccess) { ?>
                        <div class="form-group">
                        	<?php echo $view->getUsernameField(); ?>
                        	<div name="requiredUsername" style="display:none;"><p style="color:#FF0000;"> This field is required. </p></div>
						</div>
						<div class="form-group">
							<?php echo $view->getPasswordField(); ?>
							<div name="requiredPassword" style="display:none;"><p style="color:#FF0000;"> This field is required. </p></div>
						</div>
						<div class="form-group">
							<label class="text-center" style="width: 100%;">Remember Me: <?php echo $view->getRememberMe(); ?></label>
						</div>
						<div class="form-group">
							<p><?php echo htmlspecialchars($model->message); ?></p>
						</div>
						<div class="form-group">
							<button class="tm-submit-btn" type="submit" name="submit">Proceed</button> 
						</div>
						<br /><br /><p class="text-center">Don't have an account? <a href="/register.php" >Register here</a>.</p>
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
