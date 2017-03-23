<?php 

include_once '/data/TaskDatabase.php';
include_once '/data/CategoryDatabase.php';
include_once '/model/CategoryTask.php';
include_once '/model/index/TaskOverview.php';
include_once 'HtmlHelper.php';

session_start();

class IndexModel {
    
    public $tasks;
    
    public function __construct() {
    }
}

class IndexView {
    
    private $controller;
    private $model;
    
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
    
    public function getNameField() {
        return HtmlHelper::makeInput2( "text", "name", htmlspecialchars($this->model->name), "Name", "");
    }
    
    public function getEmailField() {
        return HtmlHelper::makeInput2( "email", "email", htmlspecialchars($this->model->email), "Email Address", "");
    }
    
    public function getPhoneField() {
        return HtmlHelper::makeInput2( "tel", "phone", htmlspecialchars($this->model->phone), "Phone Number", "");
    }
    
    public function createLinkForTaskDetail($taskId) {
        $result = "/task_details.php?task=$taskId";
        return $result;
    }
}

class IndexController {
    const TASK_COUNT = 6;
    const LOGIN_URL = "login.php?action=login";
    const LOGIN_METHOD = "POST";
    const REGISTER_URL = "register.php?action=register";
    const REGISTER_METHOD = "POST";
    
    private $model;
    private $taskDatabase;
    private $categoryDatabase;
    
    public function __construct($model) {
        $this->model = $model;
        $this->taskDatabase = new TaskDatabase();
        $this->categoryDatabase = new CategoryDatabase();
    }
    
    public function fetchTasks() {
        $taskResult = $this->taskDatabase->findTasksAtRandom(IndexController::TASK_COUNT);
        if ($taskResult->status === TaskDatabaseResult::TASK_FIND_SUCCESS) {
            $this->model->tasks = $taskResult->tasks;
        }
    }
    
    public function handleHttpPost() {
        // invalid request.
        http_response_code ( 400 );
        die ();
    }
    
    public function handleHttpGet() {
        $this->fetchTasks();
    }
}


$model = new IndexModel ();
$controller = new IndexController ( $model );
$view = new IndexView ( $controller, $model );

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
  <title>Recado</title>
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
  <body class="tm-gray-bg">
  	<!-- Header -->	
  	<?php 
	   include "banner.php"
	?>
	
	<!-- Banner -->
	<section class="tm-banner">
		<!-- Flexslider -->
		<div class="flexslider flexslider-banner">
		  <ul class="slides">
		    <li>
			    <div class="tm-banner-inner">
					<h1 class="tm-banner-title">Get Stuff <span class="tm-yellow-text">Done</span></h1>
					<p class="tm-banner-subtitle">At the best prices</p>
					<a href="#more" class="tm-banner-link">Learn More</a>	
				</div>
				<img src="img/banner-1.jpg" alt="Image" />	
		    </li>
		    <li>
			    <div class="tm-banner-inner">
					<h1 class="tm-banner-title">Earn <span class="tm-yellow-text">Quick</span> Cash</h1>
					<p class="tm-banner-subtitle">By completing tasks</p>
					<a href="#more" class="tm-banner-link">Learn More</a>	
				</div>
		      <img src="img/banner-2.jpg" alt="Image" />
		    </li>
		    <li>
			    <div class="tm-banner-inner">
					<h1 class="tm-banner-title">We Love <span class="tm-yellow-text">CS2102</span></h1>
					<p class="tm-banner-subtitle">Just putting this here for fun!</p>
					<a href="#more" class="tm-banner-link">Learn More</a>	
				</div>
		      <img src="img/banner-3.jpg" alt="Image" />
		    </li>
		  </ul>
		</div>	
	</section>

	<!-- gray bg -->	
	<section class="container tm-home-section-1" >
		<div class="row">
			<div class="col-lg-4 col-md-4 col-sm-6">
				<!-- Nav tabs -->
				<?php if (!isset($_SESSION['username'])) { ?>
				<div class="tm-home-box-1">
					<ul class="nav nav-tabs tm-white-bg" role="tablist" id="hotelCarTabs">
					    <li role="presentation" class="active">
					    	<a href="#hotel" aria-controls="hotel" role="tab" data-toggle="tab">Log In</a>
					    </li>
					    <li role="presentation">
					    	<a href="#car" aria-controls="car" role="tab" data-toggle="tab">Register</a>
					    </li>
					</ul>

					<!-- Tab panes -->
					<div class="tab-content">
					    <div role="tabpanel" class="tab-pane fade in active tm-white-bg" id="hotel">
					    	<div class="tm-search-box effect2">
								<form action="<?php echo IndexController::LOGIN_URL?>"
										onSubmit="return validateLogin()"
										method="<?php echo IndexController::LOGIN_METHOD?>" class="hotel-search-form">
									<div class="tm-form-inner">
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
									</div>							
						            <div class="form-group tm-yellow-gradient-bg text-center">
						            	<button type="submit" name="submit" class="tm-yellow-btn">Log in</button>
						            </div>  
								</form>
							</div>
					    </div>
					    <div role="tabpanel" class="tab-pane fade tm-white-bg" id="car">
							<div class="tm-search-box effect2">
								<form action="<?php echo IndexController::REGISTER_URL?>"
										method="<?php echo IndexController::REGISTER_METHOD?>" 
										class="hotel-search-form">
									<div class="tm-form-inner">
                                        <div class="form-group">
                        					<?php echo $view->getUsernameField(); ?>
                        				</div>
                						<div class="form-group">
                							<?php echo $view->getNameField(); ?>
                        				</div>
                						<div class="form-group">
                							<?php echo $view->getEmailField(); ?>
                        				</div>
                						<div class="form-group">
                							<?php echo $view->getPhoneField(); ?>
                        				</div>
									</div>							
						            <div class="form-group tm-yellow-gradient-bg text-center">
						            	<button type="submit" name="submit" class="tm-yellow-btn">Register</button>
						            </div>  
								</form>
							</div>
					    </div>				    
					</div>
				</div>								
				<?php } else if (sizeof($model->tasks) >= 1) { 
				    $task = $model->tasks[0];
				    ?>
				<div class="tm-home-box-1 tm-home-box-1-2 tm-home-box-1-center">
					<img src="<?php echo $task->taskDisplayPicture?>" alt="image" class="img-responsive">
					<a href="<?php echo $view->createLinkForTaskDetail($task->taskId)?>">
						<div class="tm-yellow-gradient-bg tm-city-price-container">
							<span><?php echo $task->taskName ?></span>
						</div>	
					</a>			
				</div>
				<?php } else { ?>
				<br />
				<?php } ?>
			</div>

			<div class="col-lg-4 col-md-4 col-sm-6">
				<?php if (sizeof($model->tasks) >= 2) { 
				    $task = $model->tasks[1];
				?> 
				<div class="tm-home-box-1 tm-home-box-1-2 tm-home-box-1-center">
					<img src="<?php echo $task->taskDisplayPicture?>" alt="image" class="img-responsive">
					<a href="<?php echo $view->createLinkForTaskDetail($task->taskId)?>">
						<div class="tm-green-gradient-bg tm-city-price-container">
							<span><?php echo $task->taskName ?></span>
						</div>	
					</a>			
				</div>
				<?php } else { ?>
				<br />
				<?php } ?>
			</div>
			
			<div class="col-lg-4 col-md-4 col-sm-6">
				<?php if (sizeof($model->tasks) >= 3) { 
				    $task = $model->tasks[2];
				?> 
				<div class="tm-home-box-1 tm-home-box-1-2 tm-home-box-1-center">
					<img src="<?php echo $task->taskDisplayPicture?>" alt="image" class="img-responsive">
					<a href="<?php echo $view->createLinkForTaskDetail($task->taskId)?>">
						<div class="tm-red-gradient-bg tm-city-price-container">
							<span><?php echo $task->taskName ?></span>
						</div>	
					</a>			
				</div>
				<?php } else { ?>
				<br />
				<?php } ?>			
			</div>
		</div>
		
		<?php if (sizeof($model->tasks) >= 4) {?>
		<br /><br /><br />
		<div class="row">
			<div class="col-lg-4 col-md-4 col-sm-6">
				<?php if (sizeof($model->tasks) >= 4) { 
				    $task = $model->tasks[4];
				?> 
				<div class="tm-home-box-1 tm-home-box-1-2 tm-home-box-1-center">
					<img src="<?php echo $task->taskDisplayPicture?>" alt="image" class="img-responsive">
					<a href="<?php echo $view->createLinkForTaskDetail($task->taskId)?>">
						<div class="tm-yellow-gradient-bg tm-city-price-container">
							<span><?php echo $task->taskName ?></span>
						</div>	
					</a>			
				</div>
				<?php } else { ?>
				<br />
				<?php } ?>			
			</div>

			<div class="col-lg-4 col-md-4 col-sm-6">
				<?php if (sizeof($model->tasks) >= 5) { 
				    $task = $model->tasks[4];
				?> 
				<div class="tm-home-box-1 tm-home-box-1-2 tm-home-box-1-center">
					<img src="<?php echo $task->taskDisplayPicture?>" alt="image" class="img-responsive">
					<a href="<?php echo $view->createLinkForTaskDetail($task->taskId)?>">
						<div class="tm-green-gradient-bg tm-city-price-container">
							<span><?php echo $task->taskName ?></span>
						</div>	
					</a>			
				</div>
				<?php } else { ?>
				<br />
				<?php } ?>			
			</div>
			
			<div class="col-lg-4 col-md-4 col-sm-6">
				<?php if (sizeof($model->tasks) >= 6) { 
				    $task = $model->tasks[5];
				?> 
				<div class="tm-home-box-1 tm-home-box-1-2 tm-home-box-1-center">
					<img src="<?php echo $task->taskDisplayPicture?>" alt="image" class="img-responsive">
					<a href="<?php echo $view->createLinkForTaskDetail($task->taskId)?>">
						<div class="tm-red-gradient-bg tm-city-price-container">
							<span><?php echo $task->taskName ?></span>
						</div>	
					</a>			
				</div>
				<?php } else { ?>
				<br />
				<?php } ?>						
			</div>
		</div>
		<?php } ?>
		
		
		<div class="section-margin-top" id="more">
			<div class="row">				
				<div class="tm-section-header">
					<div class="col-lg-3 col-md-3 col-sm-3"><hr></div>
					<div class="col-lg-6 col-md-6 col-sm-6"><h2 class="tm-section-title">HOW RECADO WORKS</h2></div>
					<div class="col-lg-3 col-md-3 col-sm-3"><hr></div>	
				</div>
			</div>
			

			<div class="row">
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="tm-about-box-1">
						<a href="#"><img src="img/about-4.jpg" alt="img" class="tm-about-box-1-img"></a>
						<h3 class="tm-about-box-1-title">Choose A Task</h3>
						<p class="margin-bottom-15 gray-text">That fits your schedule</p>
						
					</div>
				</div>
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="tm-about-box-1">
						<a href="#"><img src="img/about-5.jpg" alt="img" class="tm-about-box-1-img"></a>
						<h3 class="tm-about-box-1-title">Place a Bid</h3>
						<p class="margin-bottom-15 gray-text">Let us know how much you are willing to do the task for</p>
						
					</div>
				</div>
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="tm-about-box-1">
						<a href="#"><img src="img/about-6.jpg" alt="img" class="tm-about-box-1-img"></a>
						<h3 class="tm-about-box-1-title">Get Stuff Done</h3>
						<p class="margin-bottom-15 gray-text">And cash out!</p>
					
					</div>
				</div>
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="tm-about-box-1">
						<a href="#"><img src="img/about-7.jpg" alt="img" class="tm-about-box-1-img"></a>
						<h3 class="tm-about-box-1-title">Create a Task</h3>
						<p class="margin-bottom-15 gray-text">You can also put up tasks for people to do for you.</p>
				
					</div>
				</div>
			</div>				


		
		</div>
	</section>		
	<?php 
	   include "footer.php"
	?>
	<script type="text/javascript" src="js/jquery-1.11.2.min.js"></script>      		<!-- jQuery -->
  	<script type="text/javascript" src="js/moment.js"></script>							<!-- moment.js -->
	<script type="text/javascript" src="js/bootstrap.min.js"></script>					<!-- bootstrap js -->
	<script type="text/javascript" src="js/bootstrap-datetimepicker.min.js"></script>	<!-- bootstrap date time picker js, http://eonasdan.github.io/bootstrap-datetimepicker/ -->
	<script type="text/javascript" src="js/jquery.flexslider-min.js"></script>
<!--
	<script src="js/froogaloop.js"></script>
	<script src="js/jquery.fitvid.js"></script>
-->
   	<script type="text/javascript" src="js/templatemo-script.js"></script>      		<!-- Templatemo Script -->
	<script>

        function validateLogin() {
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
	
		// HTML document is loaded. DOM is ready.
		$(function() {

			$('#hotelCarTabs a').click(function (e) {
			  e.preventDefault()
			  $(this).tab('show')
			})

        	$('.date').datetimepicker({
            	format: 'MM/DD/YYYY'
            });
            $('.date-time').datetimepicker();

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

//	For images only
		    $('.flexslider').flexslider({
			    controlNav: false
		    });


	  	});
	</script>
 </body>
 </html>