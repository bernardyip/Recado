<?php 

include_once "/model/tasks/TaskInfo.php";
include_once "/data/TaskDatabase.php";
include_once "/HtmlHelper.php";
include_once "/ConversionHelper.php";

session_start();

// user needs to be logged in
if (!isset($_SESSION['username'])) {
    header('Refresh: 0; URL=http://localhost/login.php?next=' . urlencode($_SERVER['REQUEST_URI']));
    die();
}

class MyTasksModel {
    public $tasks;
    public $searchKey;
    public $userId;
    
    public function __construct() {
        
    }
    
    public function isValidForSearch() {
        if (is_null($this->searchKey)) return false;
        return strlen($this->searchKey) > 0;
    }
}

class MyTasksView {
    
    private $model;
    private $controller;
    
    public function __construct(MyTasksController $controller, MyTasksModel $model) {
        $this->controller = $controller;
        $this->model = $model;
    }
    
    public function getSearchInput() {
        return HtmlHelper::makeInput2("text", "searchKey", "", "Search tasks", "");
    }
    
    public function getTasks() {
        $html = "";
        $taskCount = sizeof($this->model->tasks);
        $tasks = $this->model->tasks;
        while ($taskCount % 2 !== 0) {
            $tasks[$taskCount++] = null;
        }
        
        $rows = sizeof($tasks) / 2;
        
        for ($i = 0; $i < $rows; $i++) {
            $html = $html . "<div class=\"row\">";
            $html = $html . $this->createTaskRow(
                                        array(
                                            $tasks[$i * 2 + 0],
                                            $tasks[$i * 2 + 1]));
            $html = $html . "</div><br />";
        }
        
        return $html;
    }
    
    private function createTaskRow($tasks) {
        $html = "";
        
        foreach ($tasks as $task) {
            $html = $html . "<div class=\"col-lg-6 col-md-6 col-sm-6 col-xs-12\">";
            if (is_null($task)) {
                $html = $html . "<br />";
            } else {
                $html = $html . "<div class=\"tm-tours-box-1\">";
                $html = $html . "<img src=\"" . htmlspecialchars($task->taskDisplayPicture) . "\" alt=\"image\" class=\"img-responsive\" style=\"width: 100%;\">";
                $html = $html . "<div class=\"tm-tours-box-1-info\">";
                $html = $html . "<div class=\"tm-tours-box-1-info-left\">";
                $html = $html . "<a href=\"/task_details.php?task=$task->taskId\" class=\"text-uppercase margin-bottom-20\">" . htmlspecialchars($task->taskName) . "</a>";
                $html = $html . "<p class=\"gray-text\">" . $task->taskStartDate->format("d F Y") . "</p>";
                $html = $html . "</div>";
                $html = $html . "<div class=\"tm-tours-box-1-info-right\">";
                $html = $html . "<p class=\"gray-text tours-1-description\">" . htmlspecialchars($task->taskDescription) . "</p>";
                $html = $html . "</div>";
                $html = $html . "</div>";
                $html = $html . "<div class=\"tm-tours-box-1-link\">";
                $html = $html . "<div class=\"tm-tours-box-1-link-left\">Current Max Bid:</div>";
                $html = $html . "<p class=\"tm-tours-box-1-link-right\">";
                if ($task->taskMaxBid > 0) {
                    $html = $html . ConversionHelper::moneyToString($task->taskMaxBid) . "</p>";
                } else {
                    $html = $html . "--</p>";
                }
                $html = $html . "</div>";
                $html = $html . "</div>";
            }
            $html = $html . "</div>";
        }
        
        return $html;
    }
    
    private function createHyperlinkForTaskDetail($taskId, $taskName) {
        return "<a href='/task_details.php?task=$taskId'>$taskName</a>";
    }
}

class MyTasksController {
    const SEARCH_TASK_URL = "tasks.php?action=search";
    const SEARCH_TASK_METHOD = "POST";
    
    private $model;
    private $taskDatabase;
    
    public function __construct(MyTasksModel $model) {
        $this->model = $model;
        $this->taskDatabase = new TaskDatabase();
    }
    
    public function getMyTasks() {
        $tasksResult = $this->taskDatabase->myTasks_getAllByUserId($this->model->userId);
        if ($tasksResult->status === TaskDatabaseResult::TASK_FIND_SUCCESS) {
            $this->model->tasks = $tasksResult->tasks;
        }
    }
    
    public function searchTask() {
        if ($this->model->isValidForSearch()) {
            
        }
    }
    
    public function handleHttpPost() {
        if (isset ( $_POST ['searchKey'] ) ) {
            $this->model->searchKey = $_POST['searchKey'];
        }
        if (isset ( $_GET ['action'] )) {
            if ($_GET ['action'] === 'search') {
                $this->searchTask ();
            }
        } else {
            // invalid request.
            http_response_code ( 400 );
            die ();
        }
    }
    
    public function handleHttpGet() {
        $this->getMyTasks();
    }
}

$model = new MyTasksModel ();
$controller = new MyTasksController ( $model );
$view = new MyTasksView ( $controller, $model );

if (isset ( $_SESSION['id'] ) ) {
    $model->userId = $_SESSION['id'];
} else {
    // should be logged in by here.
    // see top of mytasks.php
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
  <title>Recado - My Tasks</title>
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
	   include "banner.php";
	?>
	

	<!-- gray bg -->	
	<section class="container tm-home-section-1" id="more">
		
		<div class="section-margin-top">
			<div class="row">				
				<div class="tm-section-header">
					<div class="col-lg-3 col-md-3 col-sm-3"><hr></div>
					<div class="col-lg-6 col-md-6 col-sm-6"><h2 class="tm-section-title">My Tasks</h2></div>
					<div class="col-lg-3 col-md-3 col-sm-3"><hr></div>	
				</div>
			</div>

            <div class="row">
				<div class="col-lg-3 col-md-3 col-sm-3"></div>
				<div class="col-lg-6 col-md-6 col-sm-6"><a href="createtask.php" class="link-create-task" style="width: 100%;">Create a new Task</a></div>
				<div class="col-lg-3 col-md-3 col-sm-3"></div>	
            </div>

            <br /><br /><br />
                
            <?php 
            echo $view->getTasks();
            ?>
			
		</div>
	</section>		
	<?php 
	   include 'footer.php';
	?>
	<script type="text/javascript" src="js/jquery-1.11.2.min.js"></script>      		<!-- jQuery -->
  	<script type="text/javascript" src="js/moment.js"></script>							<!-- moment.js -->
	<script type="text/javascript" src="js/bootstrap.min.js"></script>					<!-- bootstrap js -->
	<script type="text/javascript" src="js/bootstrap-datetimepicker.min.js"></script>	<!-- bootstrap date time picker js, http://eonasdan.github.io/bootstrap-datetimepicker/ -->
	<script type="text/javascript" src="js/jquery.flexslider-min.js"></script>
   	<script type="text/javascript" src="js/templatemo-script.js"></script>      		<!-- Templatemo Script -->
	<script>
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
		    $('.flexslider').flexslider({
			    controlNav: false
		    });
	  	});
	</script>
 </body>
 </html>
