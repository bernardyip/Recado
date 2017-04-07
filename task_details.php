<?php 

include_once "/data/TaskDatabase.php";
include_once "/data/BidDatabase.php";
include_once "/data/CommentDatabase.php";
include_once "/HtmlHelper.php";
include_once "/ConversionHelper.php";

session_start();

// user needs to be logged in
if (!isset($_SESSION['username'])) {
    header('Refresh: 0; URL=http://localhost/login.php?next=' . urlencode($_SERVER['REQUEST_URI']));
    die();
}

class TaskDetailsModel {
    public $taskId;
    public $userId;
    public $task;
    public $comments;
    public $bids;
    public $myBid;
    public $commentToEdit;
    
    public $newComment;
    public $newBid;
    
    public $operationSuccessful;
    public $message;
    public $taskFinalized;
    
    public function __construct() {
        
    }
    
    public function isValidForAddingBid() {
        if (is_null($this->newBid)) return false;
        return $this->newBid >= 0;
    }
    
    public function isValidForAddingComment() {
        if (is_null($this->newComment)) return false;
        return strlen(trim($this->newComment)) > 0;
    }
}

class TaskDetailsView {
    private $controller;
    private $model;
    
    public function __construct(TaskDetailsController $controller, TaskDetailsModel $model) {
        $this->controller = $controller;
        $this->model = $model;
    }

    public function getTimeString($dateTime) {
        if (is_null($dateTime)) {
            return (new DateTime ( null, new DateTimeZone ( "Asia/Singapore" ) ))->format ( 'Y-m-d\T\0\0:\0\0' );
        } else {
            return $dateTime->format ( 'd F Y h:i A' );
        }
    }
    
    public function getComments() {
        $html = "";
        if (!is_null($this->model->comments)) {
            foreach ($this->model->comments as $comment) {
                $html = $html . "<div class=\"tm-testimonial\">" .
                                "<p>" . htmlspecialchars($comment->comment) . "</p>" .
                                "<p class=\"comments-time\">" . $this->getTimeString($comment->createdTime) . "</p>" .
                                "<strong class=\"text-uppercase\">" . htmlspecialchars($comment->username) . "</strong>";
                if ($this->controller->isCreatorOrAdminForComment($comment)) {
                    $html = $html . "<br />(<a href=\"/task_details.php?task=" . $this->model->taskId . "&action=deleteComment&commentId=" . $comment->id . "\">delete</a>/";
                    $html = $html . "<a href=\"/task_details.php?task=" . $this->model->taskId . "&action=editComment&commentId=" . $comment->id . "\">edit</a>)";
                }
                $html = $html . "</div>";
            }
        }
        return $html;
    }
    
    public function getBids() {
        $html = "";
        if (!is_null($this->model->bids)) {
            foreach ($this->model->bids as $bid) {
                $html = $html . "<div class=\"tm-testimonial\">" .
    							"<p class=\"bid-amount\">" . ConversionHelper::moneyToString($bid->amount) . "</p>" .
    		                	"<strong class=\"text-uppercase\">" . htmlspecialchars($bid->username) . "</strong>";
                if ($this->controller->isCreator() && !$this->model->task->bidPicked) {
                    $html = $html . "<br /><a href=\"/task_details.php?task=" . $bid->taskId . 
                                    "&action=selectBid&userId=" . $bid->userId . 
                                    "\" class=\"bid-select\">select bid</a>";
                }
                $html = $html . "</div>";
            }
        }
        return $html;
    }
}

class TaskDetailsController {
    private $model;
    private $taskDatabase;
    private $bidDatabase;
    private $commentDatabase;
    
    public function __construct(TaskDetailsModel $model) {
        $this->model = $model;
        $this->taskDatabase = new TaskDatabase();
        $this->commentDatabase = new CommentDatabase();
        $this->bidDatabase = new BidDatabase();
    }
    
    public function getTask() {
        $taskResult = $this->taskDatabase->taskDetails_getTask($this->model->taskId);
        if ($taskResult->status === TaskDatabaseResult::TASK_FIND_SUCCESS) {
            $this->model->taskFinalized = $this->taskDatabase->taskDetails_isTaskFinalized($this->model->taskId);
            $this->model->task = $taskResult->tasks[0];
            $this->getMyBid();
            $this->getBids();
            $this->getComments();
        } else {
            // fail to find task
            $this->redirectToTasks();
        }
    }
    
    private function getMyBid() {
        $bidsResult = $this->bidDatabase->taskDetails_getMyBid($this->model->taskId, $this->model->userId);
        if ($bidsResult->status === BidDatabaseResult::BID_FIND_SUCCESS) {
            if (sizeof($bidsResult->bids) > 0) {
                $this->model->myBid = $bidsResult->bids[0]->amount;
            } else {
                $this->model->myBid = 0;
            }
        }
    }
    
    private function getBids() {
        if ($this->model->task->bidPicked) {
            $bidsResult = $this->bidDatabase->taskDetails_getWinningBid($this->model->taskId);
            if ($bidsResult->status === BidDatabaseResult::BID_FIND_SUCCESS) {
                $this->model->bids = $bidsResult->bids;
            }
        } else {
            $bidsResult = $this->bidDatabase->taskDetails_getBids($this->model->taskId);
            if ($bidsResult->status === BidDatabaseResult::BID_FIND_SUCCESS) {
                $this->model->bids = $bidsResult->bids;
            }
        }
    }
    
    private function getComments() {
        $commentsResult = $this->commentDatabase->taskDetails_getComments($this->model->taskId);
        if ($commentsResult->status === CommentDatabaseResult::COMMENT_FIND_SUCCESS) {
            $this->model->comments = $commentsResult->comments;
        }
    }
    
    private function getComment($commentId) {
        $commentsResult = $this->commentDatabase->taskDetails_getComment($commentId);
        if ($commentsResult->status === CommentDatabaseResult::COMMENT_FIND_SUCCESS) {
            $this->model->commentToEdit = $commentsResult->comments[0];
            if ($this->model->commentToEdit->taskId !== $this->model->taskId) {
                $this->redirectToThisTask();
            }
        } else {
            $this->redirectToThisTask();
        }
    }
    
    private function placeBid() {
        if ($this->model->isValidForAddingBid() && !$this->taskDatabase->taskDetails_isTaskFinalized($this->model->taskId)) {
            $bidsResult = $this->bidDatabase->taskDetails_placeBid(
                    $this->model->taskId, $this->model->userId, $this->model->newBid);
            if ($bidsResult->status === BidDatabaseResult::BID_CREATE_SUCCESS) {
                if ($this->model->newBid === 0) {
                    $this->model->message = "Successfully removed bid!";
                } else {
                    $this->model->message = "Successfully placed bid!";
                }
                $this->model->operationSuccessful = true;
            } else {
                if ($this->model->newBid === 0) {
                    $this->model->message = "Failed to remove bid :(";
                } else {
                    $this->model->message = "Failed to place bid :(";
                }
                $this->model->operationSuccessful = false;
            }
        }
    }
    
    private function addComment() {
        if ($this->model->isValidForAddingComment()) {
            $commentsResult = $this->commentDatabase->taskDetails_addComment(
                    $this->model->taskId, $this->model->userId, $this->model->newComment);
            if ($commentsResult->status === CommentDatabaseResult::COMMENT_CREATE_SUCCESS) {
                $this->model->message = "Successfully added a new comment!";
                $this->model->operationSuccessful = true;
            } else {
                $this->model->message = "Failed to add a new comment :(";
                $this->model->operationSuccessful = false;
            }
        }
    }
    
    private function deleteComment($commentId) {
        if ($this->isCreatorOrAdminForComment($this->model->commentToEdit)) {
            $deleteResult = $this->commentDatabase->taskDetails_deleteComment($commentId);
            if ($deleteResult->status === CommentDatabaseResult::COMMENT_DELETE_SUCCESS) {
                $this->model->operationSuccessful = true;
                $this->model->message = "Successfully deleted comment!";
            } else {
                $this->model->operationSuccessful = false;
                $this->model->message = "Failed to delete comment :(";
            }
        }
    }
    
    private function editComment($commentId, $edittedComment) {
        if ($this->isCreatorOrAdminForComment($this->model->commentToEdit)) {
            $editResult = $this->commentDatabase->taskDetails_editComment($commentId, $edittedComment);
            if ($editResult->status === CommentDatabaseResult::COMMENT_UPDATE_SUCCESS) {
                $this->model->operationSuccessful = true;
                $this->model->message = "Successfully editted comment!";
            } else {
                $this->model->operationSuccessful = false;
                $this->model->message = "Failed to edit comment :(";
            }
            $this->redirectToThisTask();
        }
    }
    
    private function deleteTask() {
        if ($this->isCreatorOrAdmin() && !$this->model->task->bidPicked) {
            $taskResult = $this->taskDatabase->deleteTask($this->model->taskId);
            if ($taskResult->status === TaskDatabaseResult::TASK_DELETE_SUCCESS) {
                $this->redirectToTasks();
            } else {
                $this->model->operationSuccessful = false;
                $this->model->message = "Failed to delete task :(";
            }
        }
    }
    
    private function selectBid($userId) {
        if ($this->isCreator() && !$this->model->task->bidPicked) {
            $bidsResult = $this->bidDatabase->taskDetails_selectBid($this->model->taskId, $userId);
            if ($bidsResult->status === BidDatabaseResult::BID_UPDATE_SUCCESS) {
                $this->model->operationSuccessful = true;
                $this->model->message = "Successfully selected bid!";
            } else {
                $this->model->operationSuccessful = false;
                $this->model->message = "Failed to select bid :(";
            }
        }
    }
    
    public function isCreatorOrAdminForComment($comment) {
        return $this->model->userId === 1 || 
                $this->model->task->creatorId === $this->model->userId ||
                $comment->userId === $this->model->userId;
    }
    
    public function isCreatorOrAdmin() {
        return $this->model->userId === 1 || $this->isCreator();
    }
    
    public function isCreator() {
        return $this->model->task->creatorId === $this->model->userId;
    }
    
    public function handleHttpPost() {
        if (isset ($_POST ['newBid'])) {
            $this->model->newBid = floatval($_POST ['newBid']);
        }
        if (isset ($_POST ['newComment'])) {
            $this->model->newComment = $_POST['newComment'];
        }
        if (isset ( $_GET ['action'] )) {
            if ($_GET ['action'] === 'placeBid') {
                $this->placeBid();
            } else if ($_GET ['action'] === 'addComment') {
                $this->addComment();
            } else if ($_GET ['action'] === 'editComment') {
                if (isset($_GET['commentId'])) {
                    $commentId = (int)$_GET['commentId'];
                    $this->getComment($commentId);
                    $this->editComment($commentId, $this->model->newComment);
                }
            } 
            $this->refreshModel();
        } else {
            // invalid request.
            http_response_code ( 400 );
            die ();
        }
    }
    
    private function refreshModel() {
        $this->getTask();
    }
    
    public function handleHttpGet() {
        
        if (isset ( $_GET ['action'] )) {
            if ($_GET ['action'] === 'delete') {
                $this->deleteTask();
            } else if ($_GET ['action'] === 'selectBid') {
                if (isset($_GET['userId'])) {
                    $userId = (int)$_GET['userId'];
                    $this->selectBid($userId);
                    $this->refreshModel();
                }
            } else if ($_GET ['action'] === 'deleteComment') {
                if (isset($_GET['commentId'])) {
                    $commentId = (int)$_GET['commentId'];
                    $this->getComment($commentId);
                    $this->deleteComment($commentId);
                    $this->refreshModel();
                }
            } else if ($_GET ['action'] === 'editComment') {
                if (isset($_GET['commentId'])) {
                    $commentId = (int)$_GET['commentId'];
                    $this->getComment($commentId);
                }
            }
        }
    }
    
    public function redirectToThisTask() {
        header ( "Refresh: 0; URL=/task_details.php?task=" . $this->model->taskId );
        die();
    }
    
    public function redirectToTasks() {
        header ( "Refresh: 0; URL=/tasks.php" );
        die();
    }
    
    public function getBidUrl() {
        return "/task_details.php?task=" . $this->model->taskId . "&action=placeBid";
    }
    
    public function getCommentUrl() {
        return "/task_details.php?task=" . $this->model->taskId . "&action=addComment";
    }
    
    public function getEditCommentUrl() {
        return "/task_details.php?task=" . $this->model->taskId . "&action=editComment&commentId=" . (int)$_GET['commentId'];
    }
    
    public function getDeleteTaskUrl() {
        return "/task_details.php?task=" . $this->model->taskId . "&action=delete";
    }
    
    public function getEditTaskUrl() {
        return "/edittask.php?task=" . $this->model->taskId;
    }
}

$model = new TaskDetailsModel();
$controller = new TaskDetailsController($model);
$view = new TaskDetailsView($controller, $model);

if (isset ( $_SESSION['id'] ) ) {
    $model->userId = $_SESSION['id'];
} else {
    // should be logged in by here.
    // see top of task_details.php
    return;
}
        
if (!isset($_GET['task'])) {
    $controller->redirectToTasks();
} else {
    $model->taskId = intval($_GET['task']);
}
if ($model->taskId <= 0) {
    $controller->redirectToTasks();
}

$controller->getTask();

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
  <title>Recado - Task Details</title>
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
	   include 'banner.php'
	?>

	<!-- white bg -->
	<section class="tm-white-bg section-padding-bottom">
		<div class="container">
		<?php if (isset($_GET['action']) && $_GET ['action'] === 'editComment') {?>
			<div class="row">
				<div class="tm-section-header section-margin-top">
					<div class="col-lg-4 col-md-3 col-sm-3"><hr></div>
					<div class="col-lg-4 col-md-6 col-sm-6">
						<h2 class="tm-section-title"><?php echo htmlspecialchars($model->task->name) ?></h2>
						<br /><br />
                        <div class="tm-testimonial">
                            <form action="<?php echo $controller->getEditCommentUrl();?>"
                            	  onsubmit=""
                            	  method="POST">
                            	  <?php 
                            	  echo HtmlHelper::makeTextArea2( "newComment", htmlspecialchars($model->commentToEdit->comment), 3, "Give a comment");
                            	  ?>
                                <button type="submit" name="submit" class="tm-green-btn">Make changes</button>
                            </form>
                            
                        </div>
					</div>
					<div class="col-lg-4 col-md-3 col-sm-3"><hr></div>	
				</div>				
			</div>
		<?php } else { ?>
			<div class="row">
				<div class="tm-section-header section-margin-top">
					<div class="col-lg-4 col-md-3 col-sm-3"><hr></div>
					<div class="col-lg-4 col-md-6 col-sm-6">
						<h2 class="tm-section-title"><?php echo htmlspecialchars($model->task->name) ?></h2>
						<br /><br />
						<?php if (!is_null($model->message) && strlen($model->message) > 0) { ?>
						<?php     if ($model->operationSuccessful) {?>
										<label class="col-lg-12 text-center" style="color: #2ec66b;"><?php echo htmlspecialchars($model->message); ?></label>
						<?php     } else { ?>
										<label class="col-lg-12 text-center" style="color: #FF0000;"><?php echo htmlspecialchars($model->message); ?></label>
						<?php     } ?>
						<?php } ?>
					</div>
					<div class="col-lg-4 col-md-3 col-sm-3"><hr><br />
						<?php if ($controller->isCreatorOrAdmin()) { ?>
    					<div class="tm-tours-box-1" style="margin-bottom: 0px;">
    						<div class="tm-tours-box-1-link">
    							<?php if ($model->task->bidPicked) { ?>
    							<p class="tm-tours-box-1-link-right" style="width: 50%; background-color:#b0b0b0; color:#808080">
    								Edit
    							</p>
    							<p class="tm-tours-box-1-link-right" style="width: 50%; background-color:#b0b0b0; color:#808080">
    								Delete
    							</p>
    							<?php } else { ?>
    							<a href="<?php echo $controller->getEditTaskUrl() ?>" class="tm-tours-box-1-link-right" style="width: 50%;">
    								Edit
    							</a>
    							<a href="<?php echo $controller->getDeleteTaskUrl() ?>" class="tm-tours-box-1-link-right" style="width: 50%;">
    								Delete
    							</a>
    							<?php } ?>
    						</div>
    					</div>
    					<?php } ?>
					</div>	
				</div>				
			</div>
			<div class="row">
				<!-- Testimonial -->
				<div class="col-lg-12">
					<div class="col-lg-3">
    					<div class="tm-testimonials-box" style="height: 100%;">
    						<h3 class="tm-testimonials-title">Bids</h3>
    						<div class="tm-testimonials-content">
    
                            <!-- bid box start -->
                            	<div class="tm-testimonial">
                                    <form action="<?php echo $controller->getBidUrl();?>"
                                    	  onsubmit=""
                                    	  method="POST">
    								    <?php if ($model->taskFinalized) { ?>
                                        	<button type="submit" name="submit" class="tm-yellow-btn" disabled>Bidding Closed</button>
    								    <?php } else { ?>
    								    	<?php echo HtmlHelper::makeMoneyInput3("newBid", htmlspecialchars($model->myBid), "Submit a bid", "", "0.00") ?>
                                        	<button type="submit" name="submit" class="tm-yellow-btn">Submit a Bid</button>
    								    <?php } ?>
                                    </form>
    		                		
    							</div>
                            <!-- bid box end -->    
                            <?php 
                                if ($model->taskFinalized) {
                                    echo "<strong class=\"text-uppercase\">Winning Bid:</strong>";
                                }
                                echo $view->getBids();
                            ?>
    							                                     	
    						</div>
    					</div>
					</div>
					<div class="col-lg-4">
    					<div class="tm-testimonials-box-transparent" style="height: 100%; width: 100%;">
                            <div class="tm-about-box-2-img">
                                <img src="<?php echo $model->task->taskDisplayPicture ?>" alt="image" />
                                <p> &nbsp;</p> <!-- cant use <br /> if not will break css -->
                                <div class="tm-comments-box" style="height: 100%;">
                                    <h3 class="tm-comments-title">Comments</h3>
                                    <div class="tm-comments-content">
                                   	<?php 
                                   	    echo $view->getComments();
                                   	?>
                                   	
                                    <!-- comment box start -->
                                        <div class="tm-testimonial">
                                            <form action="<?php echo $controller->getCommentUrl();?>"
                                            	  onsubmit=""
                                            	  method="POST">
                                            	  <?php 
                                            	  echo HtmlHelper::makeTextArea2( "newComment", "", 3, "Give a comment");
                                            	  ?>
                                                <button type="submit" name="submit" class="tm-green-btn">Send</button>
                                            </form>
                                            
                                        </div>
                                    <!-- comment box end -->   
                                    
                                    </div>
                                </div>
                            </div>
                        </div>
					</div>
					<div class="col-lg-5">
    					<div class="tm-testimonials-box-transparent" style="height: 100%; width: 100%;">
    						<div class="tm-about-box-2-text">
    							<h3 class="tm-about-box-2-title"><?php echo htmlspecialchars($model->task->name) ?></h3>
                                <p class="tm-about-box-2-description gray-text">Category: <?php echo htmlspecialchars($model->task->category) ?></p>
                                <p class="tm-about-box-2-description"><b>LISTING PRICE: <?php echo ConversionHelper::moneyToString($model->task->listingPrice) ?></b></p>
                                <p class="tm-about-box-2-description"><?php echo htmlspecialchars($model->task->description) ?></p>
                                <p class="tm-about-box-2-description"><?php echo $view->getTimeString($model->task->taskStartTime) ?> to <?php echo $view->getTimeString($model->task->taskEndTime) ?></p>
                                <p class="tm-about-box-2-description">Location: <?php echo htmlspecialchars($model->task->location) ?> <br /> S<?php echo $model->task->postalCode ?></p>
    
                                <div id="google-map"></div>
                                <br />
    			                <p class="tm-about-box-2-footer gray-text">
                                    last updated at <?php echo $view->getTimeString($model->task->updatedTime) ?> by <?php echo htmlspecialchars($model->task->creator) ?>
    						</div>
						</div>
					</div>
				</div>							
			</div>			
		</div>
		<?php } ?>
	</section>
	<?php 
	   include 'footer.php'
	?>
	<script type="text/javascript" src="js/jquery-1.11.2.min.js"></script>      		<!-- jQuery -->
  	<script type="text/javascript" src="js/bootstrap.min.js"></script>					<!-- bootstrap js -->
  	<script type="text/javascript" src="js/jquery.flexslider-min.js"></script>			<!-- flexslider js -->
  	<script type="text/javascript" src="js/templatemo-script.js"></script>      		<!-- Templatemo Script -->
	<script>

    /* Google map
      	------------------------------------------------*/
      	var map = '';
      	var center;
		var geocoder;
		var results;
		var status;
		var lat = 1.2967436; //default
		var lng = 103.7744816; //default 
		var address = '<?php echo $model->task->postalCode ?>'; //insert zipcode here

		function initialize() {
			google.maps.visualRefresh = true;
			geocoder = new google.maps.Geocoder();

            geocoder.geocode( { 'address': address }, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    lat = results[0].geometry.location.lat();
                    lng = results[0].geometry.location.lng();
                    console.log('hereee' + lat + ' lng ' + lng);
                    
                    drawMap();
                }
                else {
                	console.log("no geocode found");
                }
            });
         
		}
        var data;
     
        function drawMap(){

        	var mapOptions = {
              	zoom: 18,
              	center: new google.maps.LatLng(lat, lng),
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
	        script.src = 'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&key=AIzaSyC-7O4_OGbw3MEe-PCjVxNM-zcsB04UOWE' + '&callback=initialize';
	        document.body.appendChild(script);
	    }
	    
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

            // Google Map
		  	loadGoogleMap();	  	
		});
		$(window).load(function(){
			// Flexsliders
		  	$('.flexslider.flexslider-banner').flexslider({
			    controlNav: false
		    });
		  	$('.flexslider').flexslider({
		    	animation: "slide",
		    	directionNav: false,
		    	slideshow: false
		  	});
		});
	</script>
 </body>
 </html>
