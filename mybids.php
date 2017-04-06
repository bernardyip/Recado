<?php

include_once "/model/Bid.php";
include_once "/data/BidDatabase.php";
include_once "/HtmlHelper.php";
include_once "/ConversionHelper.php";

session_start();

// user needs to be logged in
if (!isset($_SESSION['username'])) {
	header('Refresh: 0; URL=http://localhost/login.php?next=' . urlencode("/mybids.php"));
	die();
}

class MyBidsModel {
	public $bidsWon;
	public $bidsLost;
	public $bidsInProgress;
	public $userId;
	
	public function __construct() {
	}
}

class MyBidsView {
	
	private $model;
	private $controller;
	
	public function __construct(MyBidsController $controller, MyBidsModel $model) {
		$this->controller = $controller;
		$this->model = $model;
	}
	
	public function getBidsProgress() {
		$html = "";
		$bidsCount = sizeof($this->model->bidsInProgress);
		$bids = $this->model->bidsInProgress;
		while (bidsCount % 2 !== 0) {
			$bids[$bidCount++] = null;
		}
		
		$rows = sizeof($bids) / 2;
		
		if ($rows == 0) {
			$html = $html . "<br /><p align=\"center\">No Bids In-Progress</p><br />";
		} else {
			for ($i = 0; $i < $rows; $i++) {
				$html = $html . "<br />";
				$html = $html . "<br />";
				$html = $html . "<div class=\"row\" align=\"center\">";
				$html = $html . $this->createBidProgressRow(
						array(
								$bids[$i * 2 + 0],
								$bids[$i * 2 + 1]));
				$html = $html . "</div>";
			}
		}
		
		return $html;
	}
	
	private function createBidProgressRow($bids) {
		$html = "";
		
		foreach ($bids as $bid) {
			$html = $html . "<div class=\"col-lg-6 col-md-6 col-sm-6 col-xs-12\">";
		
			if (is_null($bid)) {
				$html = $html . "<br />";
			} else {
				$html = $html . "<div class=\"tm-tours-box-1\">";
				$html = $html . "<div class=\"tm-tours-box-1-link\">";
				$html = $html . "<div class=\"tm-tours-box-1-link-left\">" . htmlspecialchars($bid->taskName);
				$html = $html . "</div>";
				$html = $html . "<a href=\"#\" class=\"tm-tours-box-1-link-right\">" . ConversionHelper::moneyToString($bid->bidPrice). "</a>";
				$html = $html . "</div>";
				$html = $html . "<div class=\"tm-tours-box-1-info\">";
				$html = $html . "<div class=\"tm-tours-box-1-info-left\">";
				$html = $html . "<p class=\"gray-text\">ending on " . $bid->taskEndDate->format("d F Y") . "</p>";
				$html = $html . "</div>";
				$html = $html . "<div class=\"tm-tours-box-1-info-right\">";
				$html = $html . "<p style=\"overflow: hidden; white-space: nowrap; text-overflow\: ellipsis;\" class=\"tours-1-description\">" . htmlspecialchars($bid->taskDescription) . "</p>";
				$html = $html . "<p class=\"tours-1-description\">";
				$html = $html . "<a href=\"/task_details.php?task=$bid->taskId\" class=\"text-uppercase margin-bottom-20\">View Task</a>";
				$html = $html . "</p>";
				$html = $html . "</div>";
				$html = $html . "</div>";
				$html = $html . "</div>";
			}
			$html = $html . "</div>";
		}
		
		return $html;
	}
	
	public function getBidsWon() {
		$html = "";
		$bidsCount = sizeof($this->model->bidsWon);
		$bids = $this->model->bidsWon;
		while (bidsCount % 2 !== 0) {
			$bids[$bidCount++] = null;
		}
		
		$rows = sizeof($bids) / 2;
		
		if ($rows == 0) {
			$html = $html . "<br /><p align=\"center\">No Bids Won</p><br />";
		} else {
			for ($i = 0; $i < $rows; $i++) {
				$html = $html . "<br />";
				$html = $html . "<br />";
				$html = $html . "<div class=\"row\" align=\"center\">";
				$html = $html . $this->createBidWonRow(
						array(
								$bids[$i * 2 + 0],
								$bids[$i * 2 + 1]));
				$html = $html . "</div><br />";
			}
		}
		
		return $html;
	}
	
	private function createBidWonRow($bids) {
		$html = "";
		
		foreach ($bids as $bid) {
			$html = $html . "<div class=\"col-lg-6 col-md-6 col-sm-6 col-xs-12\">";
			
			if (is_null($bid)) {
				$html = $html . "<br />";
			} else {
				$html = $html . "<div class=\"tm-tours-box-1\">";
				$html = $html . "<div class=\"bid-win\">";
				$html = $html . "<div class=\"bid-win-price\">" . htmlspecialchars($bid->taskName);
				$html = $html . "</div>";
				$html = $html . "<a href=\"#\" class=\"bid-win-price\">" . ConversionHelper::moneyToString($bid->bidPrice). "</a>";
				$html = $html . "</div>";
				$html = $html . "<div class=\"tm-tours-box-1-info\">";
				$html = $html . "<div class=\"tm-tours-box-1-info-left\">";
				$html = $html . "<p class=\"gray-text\">won on " . $bid->taskEndDate->format("d F Y") . "</p>";
				$html = $html . "</div>";
				$html = $html . "<div class=\"tm-tours-box-1-info-right\">";
				$html = $html . "<p style=\"overflow: hidden; white-space: nowrap; text-overflow\: ellipsis;\" class=\"tours-1-description\">" . htmlspecialchars($bid->taskDescription) . "</p>";
				$html = $html . "<p class=\"tours-1-description\">";
				$html = $html . "<a href=\"/task_details.php?task=$bid->taskId\" class=\"text-uppercase margin-bottom-20\">View Task</a>";
				$html = $html . "</p>";
				$html = $html . "</div>";
				$html = $html . "</div>";
				$html = $html . "</div>";
			}
			$html = $html . "</div>";
		}

		return $html;
	}
	
	public function getBidsLost() {
		$html = "";
		$bidsCount = sizeof($this->model->bidsLost);
		$bids = $this->model->bidsLost;
		while (bidsCount % 2 !== 0) {
			$bids[$bidCount++] = null;
		}
		
		$rows = sizeof($bids) / 2;
		
		if ($rows == 0) {
			$html = $html . "<br /><p align=\"center\">No Bids Lost</p><br />";
		} else {
			for ($i = 0; $i < $rows; $i++) {
				$html = $html . "<br />";
				$html = $html . "<br />";
				$html = $html . "<div class=\"row\" align=\"center\">";
				$html = $html . $this->createBidLostRow(
						array(
								$bids[$i * 2 + 0],
								$bids[$i * 2 + 1]));
				$html = $html . "</div>";
			}
		}
		
		return $html;
	}
	
	private function createBidLostRow($bids) {
		$html = "";
		
		foreach ($bids as $bid) {
			$html = $html . "<div class=\"col-lg-6 col-md-6 col-sm-6 col-xs-12\">";
			
			if (is_null($bid)) {
				$html = $html . "<br />";
			} else {
				$html = $html . "<div class=\"tm-tours-box-1\">";
				$html = $html . "<div class=\"bid-lost\">";
				$html = $html . "<div class=\"bid-lost-name\">" . htmlspecialchars($bid->taskName);
				$html = $html . "</div>";
				$html = $html . "<a href=\"#\" class=\"bid-lost-price\">" . ConversionHelper::moneyToString($bid->bidPrice). "</a>";
				$html = $html . "</div>";
				$html = $html . "<div class=\"tm-tours-box-1-info\">";
				$html = $html . "<div class=\"tm-tours-box-1-info-left\">";
				$html = $html . "<p class=\"gray-text\">bid lost on " . $bid->taskEndDate->format("d F Y") . "</p>";
				$html = $html . "</div>";
				$html = $html . "<div class=\"tm-tours-box-1-info-right\">";
				$html = $html . "<p class=\"tours-1-description\">" . htmlspecialchars($bid->taskDescription) . "</p>";
				$html = $html . "<p class=\"tours-1-description\">";
				$html = $html . "<a href=\"/task_details.php?task=$bid->taskId\" class=\"text-uppercase margin-bottom-20\">View Task</a>";
				$html = $html . "</p>";
				$html = $html . "</div>";
				$html = $html . "</div>";
				$html = $html . "</div>";
			}
			$html = $html . "</div>";
		}
		
		return $html;
	}

}

class MyBidsController {
	private $model;
	private $bidDatabase;
	
	public function __construct(MyBidsModel $model) {
		$this->model = $model;
		$this->bidDatabase = new BidDatabase();
	}
	
	public function getMyBids() {
		$bidsResultWon = $this->bidDatabase->taskDetails_getMyBidsWon($this->model->userId);
		if ($bidsResultWon->status === BidDatabaseResult::BID_FIND_SUCCESS) {
			$this->model->bidsWon = $bidsResultWon->bids;
		}
		$bidsResultLost = $this->bidDatabase->taskDetails_getMyBidsLost($this->model->userId);
		if ($bidsResultLost->status === BidDatabaseResult::BID_FIND_SUCCESS) {
			$this->model->bidsLost = $bidsResultLost->bids;
		}
		$bidsResultInProgress = $this->bidDatabase->taskDetails_getMyBidsInProgress($this->model->userId);
		if ($bidsResultInProgress->status === BidDatabaseResult::BID_FIND_SUCCESS) {
			$this->model->bidsInProgress = $bidsResultInProgress->bids;
		}
	}
	
	public function handleHttpPost() {
		// invalid request.
		http_response_code ( 400 );
		die ();
	}
	
	public function handleHttpGet() {
		$this->getMyBids();
	}
}

$model = new MyBidsModel ();
$controller = new MyBidsController ( $model );
$view = new MyBidsView ( $controller, $model );

if (isset ( $_SESSION['id'] ) ) {
	$model->userId = $_SESSION['id'];
} else {
	// should be logged in by here.
	// see top of mybids.php
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
  <title>Recado - My Bids</title>
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
					<div class="col-lg-6 col-md-6 col-sm-6"><h2 class="tm-section-title">My Bids</h2></div>
					<div class="col-lg-3 col-md-3 col-sm-3"><hr></div>	
				</div>
			</div>
                
            <div class="row">
				<ul class="nav nav-tabs tm-tabs-bg" role="tablist" id='bidsTabs'>
				    <li role="presentation" class="active">
				    	<a href="#progress" aria-controls="progress" role="tab" data-toggle="tab">BIDS IN-PROGRESS</a>
				    </li>
				    <li role="presentation">
				    	<a href="#won" aria-controls="won" role="tab" data-toggle="tab">BIDS WON</a>
				    </li>
				    <li role="presentation">
				    	<a href="#lost" aria-controls="lost" role="tab" data-toggle="tab">BIDS LOST</a>
				    </li>
				</ul>
				
				<div class="tab-content">
				    <div role="tabpanel" class="tab-pane fade in active tm-tabs-bg" id="progress">
						<?php 
						echo $view->getBidsProgress();
						?>
					</div>
					<div role="tabpanel" class="tab-pane fade tm-tabs-bg" id="won">
						<?php 
			            echo $view->getBidsWon();
			            ?>
			        </div>
					<div role="tabpanel" class="tab-pane fade tm-tabs-bg" id="lost">
			            <?php 
			            echo $view->getBidsLost();
			            ?>	
					</div>
				</div>

	
            </div>
			
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

			$('#bidsTabs a').click(function (e) {
			  e.preventDefault()
			  $(this).tab('show')
			});
           
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