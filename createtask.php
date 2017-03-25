<?php 

include_once "/data/TaskDatabase.php";
include_once "/data/CategoryDatabase.php";
include_once "/HtmlHelper.php";
include_once "/ConversionHelper.php";

session_start();

// user needs to be logged in
if (!isset($_SESSION['username'])) {
    header('Refresh: 0; URL=http://localhost/login.php?next=' . urlencode("/createtask.php"));
    die();
}

class CreateTaskModel {
    public $newTaskName;
    public $newTaskDescription;
    public $newTaskPostalCode;
    public $newTaskLocation;
    public $newTaskStartDateTime;
    public $newTaskEndDateTime;
    public $newTaskListingPrice;
    public $newTaskCategoryId;
    public $newTaskCreatorId;
    
    public $userId;
    
    public $categories;
    public $message;
    
    public function __construct() {
        
    }
    
    public function isValid() {
        if (is_null($this->newTaskName)) return false;
        if (is_null($this->newTaskDescription)) return false;
        if (is_null($this->newTaskPostalCode)) return false;
        if (is_null($this->newTaskLocation)) return false;
        if (is_null($this->newTaskStartDateTime)) return false;
        if (is_null($this->newTaskEndDateTime)) return false;
        if (is_null($this->newTaskListingPrice)) return false;
        if (is_null($this->newTaskCategoryId)) return false;
        if (is_null($this->newTaskCreatorId)) return false;
        
        // start/end date/time validation
        
        return strlen($this->newTaskName) > 0 &&
                strlen($this->newTaskDescription) &&
                preg_match("/^[0-9]{6}$/", $this->newTaskPostalCode) == 1 &&
                strlen($this->newTaskLocation) > 0 &&
                $this->newTaskStartDateTime <= $this->newTaskEndDateTime &&
                strlen($this->newTaskListingPrice) > 0 && $this->newTaskListingPrice > 0 &&
                strlen($this->newTaskCategoryId) > 0 && $this->newTaskCategoryId > 0 &&
                strlen($this->newTaskCreatorId) > 0 && $this->newTaskCreatorId > 0;
    }
    
    public function getStartTime() {
        if (is_null($this->newTaskStartDateTime)) {
            return (new DateTime ( null, new DateTimeZone ( "Asia/Singapore" ) ))->format ( 'Y-m-d\T\0\0:\0\0' );
        } else {
            return $this->newTaskStartDateTime->format ( 'Y-m-d\TH:i' );
        }
    }
    
    public function getEndTime() {
        if (is_null($this->newTaskEndDateTime)) {
            return (new DateTime ( null, new DateTimeZone ( "Asia/Singapore" ) ))->format ( 'Y-m-d\T\2\3:\5\9' );
        } else {
            return $this->newTaskEndDateTime->format ( 'Y-m-d\TH:i' );
        }
    }
}

class CreateTaskView {
    
    private $model;
    private $controller;
    
    public function __construct(CreateTaskController $controller, CreateTaskModel $model) {
        $this->controller = $controller;
        $this->model = $model;
    }
    
    public function getCategoryDropdown() {
        $html = "<select name=category class=\"form-control\">";
        $selectedId = -1;
        if (!is_null($this->model->newTaskCategoryId)) {
            $selectedId = $this->model->newTaskCategoryId; 
        }
        foreach ($this->model->categories as $category) {
            if ($selectedId === $category->id) {
                $html = $html . "<option selected='selected' value='" . $category->id . "'>" . htmlspecialchars($category->name) . "</option>";
            } else {
                $html = $html . "<option value='" . $category->id . "'>" . htmlspecialchars($category->name) . "</option>";
            }
        }
        $html = $html . "</select>";
        return $html;
    }
}

class CreateTaskController {
    const CREATE_TASK_URL = "createtask.php?action=create";
    
    private $model;
    private $taskDatabase;
    private $categoryDatabase;
    
    public function __construct(CreateTaskModel $model) {
        $this->model = $model;
        $this->taskDatabase = new TaskDatabase();
        $this->categoryDatabase = new CategoryDatabase();
    }
    
    public function fetchCategories() {
        $categoriesResult = $this->categoryDatabase->findCategories();
        if ($categoriesResult->status === CategoryDatabaseResult::CATEGORY_FIND_SUCCESS) {
            $this->model->categories = $categoriesResult->categories;
        }
    }
    
    public function createTask() {
        if ($this->model->isValid()) {
            $createdTask = $this->taskDatabase->createTask(
                    $this->model->newTaskName, $this->model->newTaskDescription, 
                    $this->model->newTaskPostalCode, $this->model->newTaskLocation, 
                    $this->model->newTaskStartDateTime, $this->model->newTaskEndDateTime, 
                    $this->model->newTaskListingPrice, $this->model->newTaskCategoryId, 
                    $this->model->newTaskCreatorId);
            if ($createdTask->status === TaskDatabaseResult::TASK_CREATE_SUCCESS) {
                $this->redirectToCreatedTask($createdTask->tasks[0]);
            } else {
                $this->model->message = "Task creation failed :(";
            }
        }
    }
    
    private function redirectToCreatedTask($createdTask) {
        header ( "Refresh: 1; URL=/task_details.php?task=" . $createdTask->id );
        die();
    }
    
    public function handleHttpPost() {
        if (isset ( $_POST ['name'] ) ) {
            $this->model->newTaskName = $_POST['name'];
        }
        if (isset ( $_POST ['description'] ) ) {
            $this->model->newTaskDescription = $_POST['description'];
        }
        if (isset ( $_POST ['postal_code'] ) ) {
            $this->model->newTaskPostalCode = $_POST['postal_code'];
        }
        if (isset ( $_POST ['location'] ) ) {
            $this->model->newTaskLocation = $_POST['location'];
        }
        if (isset ( $_POST ['task_start_time'] ) ) {
            $this->model->newTaskStartDateTime = new DateTime($_POST['task_start_time'], 
                    new DateTimeZone('Asia/Singapore'));
        }
        if (isset ( $_POST ['task_end_time'] ) ) {
            $this->model->newTaskEndDateTime = new DateTime($_POST['task_end_time'], 
                    new DateTimeZone('Asia/Singapore'));
        }
        if (isset ( $_POST ['listing_price'] ) ) {
            $this->model->newTaskListingPrice = $_POST['listing_price'];
        }
        if (isset ( $_POST ['category'] ) ) {
            $this->model->newTaskCategoryId = $_POST['category'];
        }
        
        if (isset ( $_GET ['action'] )) {
            if ($_GET ['action'] === 'create') {
                $this->createTask ();
            } else if ($_GET ['action'] === 'search') {
                $this->searchTask ();
            }
        } else {
            // invalid request.
            http_response_code ( 400 );
            die ();
        }
    }
    
    public function handleHttpGet() {
    }
}

$model = new CreateTaskModel ();
$controller = new CreateTaskController ( $model );
$view = new CreateTaskView ( $controller, $model );


if (isset ( $_SESSION['id'] ) ) {
    $model->newTaskCreatorId = $model->userId = $_SESSION['id'];
} else {
    // should be logged in by here.
    // see top of task.php
    return;
}

$controller->fetchCategories();

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
	<title>Holiday - Contact</title>
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
	<?php 
	   include 'banner.php';
	?>
	
	<!-- white bg -->
	<section class="section-padding-bottom">
		<div class="container">
			<div class="row">
				<div class="tm-section-header section-margin-top">
					<div class="col-lg-4 col-md-3 col-sm-3"><hr></div>
					<div class="col-lg-4 col-md-6 col-sm-6"><h2 class="tm-section-title">Create a Task</h2></div>
					<div class="col-lg-4 col-md-3 col-sm-3"><hr></div>	
				</div>				
			</div>
			<div class="row">
				<!-- contact form -->
				<form action="<?php echo CreateTaskController::CREATE_TASK_URL?>" method="POST"
						onsubmit="";
						class="tm-contact-form">
					<div class="col-lg-6 col-md-6">
						<div id="google-map"></div>
					</div> 
					<div class="col-lg-6 col-md-6 tm-contact-form-input">
						<?php if (!is_null($model->message) && strlen($model->message) > 0) { ?>
						<div class="form-group">
							<label style="color: #FF0000;"><?php echo htmlspecialchars($model->message); ?></label>
						</div>
						<?php } ?>
                        <div class="form-group">
							<?php echo HtmlHelper::makeInput2("text", "name", htmlspecialchars($model->newTaskName), "Task Name", "") ?>
						</div>
                        <div class="form-group">
							<?php echo HtmlHelper::makeInput2("datetime-local", "task_start_time", $model->getStartTime(), "", "") ?>
						</div>
                        <div class="form-group">
							<?php echo HtmlHelper::makeInput2("datetime-local", "task_end_time", $model->getEndTime(), "", "") ?>
						</div>
						
						<!-- DatePickers, to use or not to use 
                        <div class="input-group date" id="datetimepicker1">
                            <input type='text' class="form-control" placeholder="Start Date" />
                            <span class="input-group-addon">
                                <span class="fa fa-calendar"></span>
                            </span>
						</div>
                        <br />       
                        <div class="input-group date" id="datetimepicker2">
                            <input type='text' class="form-control" placeholder="End Date" />
                            <span class="input-group-addon">
                                <span class="fa fa-calendar"></span>
                            </span>
						</div>
                        <br />
						 -->
					
                        <div class="form-group">
                        	<?php echo HtmlHelper::makeInput2("text", "location", htmlspecialchars($model->newTaskLocation), "Where the task held at?", "") ?>
						</div>
						<div class="form-group">
							<?php echo HtmlHelper::makeInput2("number", "postal_code", htmlspecialchars($model->newTaskPostalCode), "Postal Code", "Six digit zip code")?>
						</div>
						<div class="form-group">
							<?php echo HtmlHelper::makeMoneyInput2("listing_price", htmlspecialchars($model->newTaskListingPrice), "Listing Price", "")?>
						</div>
						<div class="form-group">
							<?php echo HtmlHelper::makeTextArea2("description", htmlspecialchars($model->newTaskDescription), 6, "Enter some description for your task")?>
						</div>
                        <div class="form-group">
							<?php echo $view->getCategoryDropdown()?>
			          	</div>			
						<div class="form-group">
							<button class="tm-submit-btn" type="submit" name="submit">Create a Task</button> 
						</div>               
					</div>
				</form>
			</div>			
		</div>
	</section>
	<?php 
	   include 'footer.php';
	?>
	<script type="text/javascript" src="js/jquery-1.11.2.min.js"></script>      		<!-- jQuery -->
	<script type="text/javascript" src="js/bootstrap.min.js"></script>					<!-- bootstrap js -->
	<script type="text/javascript" src="js/jquery.flexslider-min.js"></script>			<!-- flexslider js -->
	<script type="text/javascript" src="js/templatemo-script.js"></script>      		<!-- Templatemo Script -->
	<script type="text/javascript" src="js/moment.js"></script>							<!-- moment.js -->
	<script type="text/javascript" src="js/bootstrap-datetimepicker.min.js"></script>	<!-- bootstrap date time picker js, http://eonasdan.github.io/bootstrap-datetimepicker/ -->
	
    <script>
		/* Google map
      	------------------------------------------------*/
      	var map = '';
      	var center;

      	function initialize() {
	        var mapOptions = {
	          	zoom: 14,
	          	center: new google.maps.LatLng(1.2967436, 103.7744816),
	          	scrollwheel: false
        	};
        
	        map = new google.maps.Map(document.getElementById('google-map'),  mapOptions);

	        google.maps.event.addDomListener(map, 'idle', function() {
	          calculateCenter();
	        });
        
	        google.maps.event.addDomListener(window, 'resize', function() {
	          map.setCenter(center);
	        });
      	}

	    function calculateCenter() {
	        center = map.getCenter();
	    }

	    function loadGoogleMap(){
	        var script = document.createElement('script');
	        script.type = 'text/javascript';
	        script.src = 'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&' + 'callback=initialize';
	        document.body.appendChild(script);
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

		  	// Google Map
		  	loadGoogleMap();

            $('.date').datetimepicker({
            	format: 'DD/MM/YYYY'
            });
            $('.date-time').datetimepicker();
           
		  });

          
	</script>
</body>
</html>
