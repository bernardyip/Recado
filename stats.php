<?php 

include_once "/data/TaskDatabase.php";
include_once "/data/BidDatabase.php";
include_once "/data/UserDatabase.php";
include_once "/HtmlHelper.php";
include_once "/ConversionHelper.php";

session_start();

// user needs to be logged in
if (!isset($_SESSION['username'])) {
    header('Refresh: 0; URL=http://localhost/login.php?next=' . urlencode("/stats.php"));
    die();
}

class StatsModel {
    public $totalTasks;
	public $totalBids;
	public $averageBid;
	public $totalOnline;
	public $totalUsers;
	public $openTasks;
	public $etcTasks;
	public $cleaningTasks;
	public $deliveryTasks;
	public $fixingTasks;
	
    public function __construct() {
        
    }
    
}

class StatsView {
    private $controller;
    private $model;
    
    public function __construct(StatsController $controller, StatsModel $model) {
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
    

}

class StatsController {
    private $model;
    private $taskDatabase;
    private $userDatabase;
	private $bidDatabase;
    
    public function __construct(StatsModel $model) {
        $this->model = $model;
        $this->taskDatabase = new TaskDatabase();
        $this->userDatabase = new UserDatabase();
        $this->bidDatabase = new BidDatabase();
		$this->getStats();
    }
    
	public function getStats() {
		$this->getBidStats();
		$this->getUserStats();;
		$this->getTaskStats();
	}
    
    public function getBidStats() {
        $bidsResult = $this->bidDatabase->findAverageBid();
        if ($bidsResult->status === BidDatabaseResult::BID_FIND_SUCCESS) {            
            $this->model->averageBid = $bidsResult->bids;            
        }
		$bidsResult = $this->bidDatabase->findTotalBids();
        if ($bidsResult->status === BidDatabaseResult::BID_FIND_SUCCESS) {            
            $this->model->totalBids = $bidsResult->bids;            
        }
    }
	
	public function getUserStats() {
		$this->model->totalOnline = $this->userDatabase->getOnlineUserCount();
		$this->model->totalUsers = $this->userDatabase->getTotalUserCount();
	}
    
	public function getTaskStats() {
		$taskResult = $this->taskDatabase->findTaskCount();
        if ($taskResult->status === TaskDatabaseResult::TASK_FIND_SUCCESS) {
            $this->model->totalTasks = $taskResult->count;
        }	
		$taskResult = $this->taskDatabase->findTasksWithCategoryId(1, 0);
        if ($taskResult->status === TaskDatabaseResult::TASK_FIND_SUCCESS) {
            $this->model->etcTasks = $taskResult->count;
        }
		$taskResult = $this->taskDatabase->findTasksWithCategoryId(2, 0);
        if ($taskResult->status === TaskDatabaseResult::TASK_FIND_SUCCESS) {
            $this->model->cleaningTasks = $taskResult->count;
        }
		$taskResult = $this->taskDatabase->findTasksWithCategoryId(3, 0);
        if ($taskResult->status === TaskDatabaseResult::TASK_FIND_SUCCESS) {
            $this->model->deliveryTasks = $taskResult->count;
        }
		$taskResult = $this->taskDatabase->findTasksWithCategoryId(4, 0);
        if ($taskResult->status === TaskDatabaseResult::TASK_FIND_SUCCESS) {
            $this->model->fixingTasks = $taskResult->count;
        }
		$taskResult = $this->taskDatabase->findBiddableTaskCount();
        if ($taskResult->status === TaskDatabaseResult::TASK_FIND_SUCCESS) {
			$this->model->openTasks = $taskResult->count;
        }
	}
    
}

$model = new StatsModel();
$controller = new StatsController($model);
$view = new StatsView($controller, $model);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Page</title>
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
				        <li><a href="profile.html">Profile</a></li>
                        <li><a href="searchtasks.html">Search</a></li>
				  	    <li><a href="#" class="active">My Tasks</a></li>
						<li><a href="mybids.html">My Bids</a></li>
                        <li><a href="logout.html">Log Out</a></li> 
                        </ul>
					</nav>		
	  			</div>				
  			</div>
  		</div>	  	
  	</div>
	
	
	<!-- gray bg -->	
	<section class="container tm-home-section-1" id="more">
		
		<div class="section-margin-top">
			<div class="row">				
				<div class="tm-section-header">
					<div class="col-lg-3 col-md-3 col-sm-3"><hr></div>
					<div class="col-lg-6 col-md-6 col-sm-6"><h2 class="tm-section-title">Recado Statistics</h2></div>
					<div class="col-lg-3 col-md-3 col-sm-3"><hr></div>	
				</div>
			</div>

             <!-- START row of 1 -->
			<div class="row">
                <div class="tm-section-header">
					<div class="col-lg-6 col-md-6 col-sm-6"><h2 class="tm-section-title">QUICK SUMMARY</h2></div>
					</div>
				<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
					<div class="tm-tours-box-1">
					
						<div class="tm-tours-box-1-link">
							<div class="tm-tours-box-1-link-left">
								Total Tasks: 
							</div>
							<a href="#" class="tm-tours-box-1-link-right">
								<?php 	echo $model->totalTasks; ?>						
							</a>							
						</div>
						<div class="tm-tours-box-1-link">
							<div class="tm-tours-box-1-link-left">
								Total Bids: 
							</div>
							<a href="#" class="tm-tours-box-1-link-right">
								<?php 	echo $model->totalBids; ?> 								
							</a>							
						</div>
						<div class="tm-tours-box-1-link">
							<div class="tm-tours-box-1-link-left">
								Average Bid Amount: 
							</div>
							<a href="#" class="tm-tours-box-1-link-right">														
								$<?php 	echo $model->averageBid; ?>                               								
							</a>							
						</div>
						<div class="tm-tours-box-1-info">
							<div class="tm-tours-box-1-info-left">
								<p class="text-uppercase margin-bottom-20">TASK NAME</p>	
								<p class="gray-text">28 March 2084</p>
							</div>
							<div class="tm-tours-box-1-info-right">
								<p class="gray-text tours-1-description">TASK DESCRIPTIONNNNNN</p>	
							</div>							
						</div>
					</div>					
				</div>

				<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
					 
					<div class="tm-tours-box-1">
					<div id="chart1"></div>          
				
					</div>					
				</div>					
				</div>

            <!-- END row of 1 --> 
            
			   <!-- START row of 2 -->
			<div class="row">
                <div class="tm-section-header">
					<div class="col-lg-6 col-md-6 col-sm-6"><h2 class="tm-section-title">ON RECADO</h2></div>
					</div>
				<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
					<div class="tm-tours-box-1">
					
						<div class="tm-tours-box-1-link">
							<div class="tm-tours-box-1-link-left">
								current online users: 
							</div>
							<a href="#" class="tm-tours-box-1-link-right">
								<?php 	echo $model->totalOnline; ?>			
							</a>							
						</div>
						<div class="tm-tours-box-1-link">
							<div class="tm-tours-box-1-link-left">
								total users: 
							</div>
							<a href="#" class="tm-tours-box-1-link-right">
								<?php 	echo $model->totalUsers; ?>				
							</a>							
						</div>
						<div class="tm-tours-box-1-link">
							<div class="tm-tours-box-1-link-left">
								tasks open for bidding: 
							</div>
							<a href="#" class="tm-tours-box-1-link-right">
								<?php 	echo $model->openTasks; ?>								
							</a>							
						</div>
						
						<div class="tm-tours-box-1-info">
							<div class="tm-tours-box-1-info-left">
								<p class="text-uppercase margin-bottom-20">TASK NAME</p>	
								<p class="gray-text">28 March 2084</p>
							</div>
							<div class="tm-tours-box-1-info-right">
								<p class="gray-text tours-1-description">TASK DESCRIPTIONNNNNN</p>	
							</div>							
						</div>
					</div>					
				</div>

				<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
					 
					<div class="tm-tours-box-1">
					<div id="chart2"></div>          
				
					</div>					
				</div>					
				</div>

            <!-- END row of 2 --> 
            </div>
	</section>		
	
	
	<footer class="tm-black-bg">
		<div class="container">
			<div class="row">
				<p class="tm-copyright-text">Copyright &copy; 2084 Your Company Name</p>
			</div>
		</div>		
	</footer>
	<script type="text/javascript" src="js/jquery-1.11.2.min.js"></script>      		<!-- jQuery -->
  	<script type="text/javascript" src="js/moment.js"></script>							<!-- moment.js -->
	<script type="text/javascript" src="js/bootstrap.min.js"></script>					<!-- bootstrap js -->
	<script type="text/javascript" src="js/bootstrap-datetimepicker.min.js"></script>	<!-- bootstrap date time picker js, http://eonasdan.github.io/bootstrap-datetimepicker/ -->
	<script type="text/javascript" src="js/jquery.flexslider-min.js"></script>
   	<script type="text/javascript" src="js/templatemo-script.js"></script>      		<!-- Templatemo Script -->
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
 <script>
		// HTML document is loaded. DOM is ready.
		$(function() {
//HIGHCHART THEME START

'use strict';
/* global document */
// Load the fonts
Highcharts.createElement('link', {
   href: 'https://fonts.googleapis.com/css?family=Arial',
   rel: 'stylesheet',
   type: 'text/css'
}, null, document.getElementsByTagName('head')[0]);

Highcharts.theme = {
   colors: ['#2b908f', '#90ee7e', '#f45b5b', '#7798BF', '#aaeeee', '#ff0066', '#eeaaee',
      '#55BF3B', '#DF5353', '#7798BF', '#aaeeee'],
   chart: {
      backgroundColor: {
         linearGradient: { x1: 0, y1: 0, x2: 1, y2: 1 },
         stops: [
            [0, '#2a2a2b'],
            [1, '#3e3e40']
         ]
      },
      style: {
         fontFamily: '\'Unica One\', sans-serif'
      },
      plotBorderColor: '#606063'
   },
   title: {
      style: {
         color: '#E0E0E3',
         textTransform: 'uppercase',
         fontSize: '20px'
      }
   },
   subtitle: {
      style: {
         color: '#E0E0E3',
         textTransform: 'uppercase'
      }
   },
   xAxis: {
      gridLineColor: '#707073',
      labels: {
         style: {
            color: '#E0E0E3'
         }
      },
      lineColor: '#707073',
      minorGridLineColor: '#505053',
      tickColor: '#707073',
      title: {
         style: {
            color: '#A0A0A3'

         }
      }
   },
   yAxis: {
      gridLineColor: '#707073',
      labels: {
         style: {
            color: '#E0E0E3'
         }
      },
      lineColor: '#707073',
      minorGridLineColor: '#505053',
      tickColor: '#707073',
      tickWidth: 1,
      title: {
         style: {
            color: '#A0A0A3'
         }
      }
   },
   tooltip: {
      backgroundColor: 'rgba(0, 0, 0, 0.85)',
      style: {
         color: '#F0F0F0'
      }
   },
   plotOptions: {
      series: {
         dataLabels: {
            color: '#B0B0B3'
         },
         marker: {
            lineColor: '#333'
         }
      },
      boxplot: {
         fillColor: '#505053'
      },
      candlestick: {
         lineColor: 'white'
      },
      errorbar: {
         color: 'white'
      }
   },
   legend: {
      itemStyle: {
         color: '#E0E0E3'
      },
      itemHoverStyle: {
         color: '#FFF'
      },
      itemHiddenStyle: {
         color: '#606063'
      }
   },
   credits: {
      style: {
         color: '#666'
      }
   },
   labels: {
      style: {
         color: '#707073'
      }
   },

   drilldown: {
      activeAxisLabelStyle: {
         color: '#F0F0F3'
      },
      activeDataLabelStyle: {
         color: '#F0F0F3'
      }
   },

   navigation: {
      buttonOptions: {
         symbolStroke: '#DDDDDD',
         theme: {
            fill: '#505053'
         }
      }
   },

   // scroll charts
   rangeSelector: {
      buttonTheme: {
         fill: '#505053',
         stroke: '#000000',
         style: {
            color: '#CCC'
         },
         states: {
            hover: {
               fill: '#707073',
               stroke: '#000000',
               style: {
                  color: 'white'
               }
            },
            select: {
               fill: '#000003',
               stroke: '#000000',
               style: {
                  color: 'white'
               }
            }
         }
      },
      inputBoxBorderColor: '#505053',
      inputStyle: {
         backgroundColor: '#333',
         color: 'silver'
      },
      labelStyle: {
         color: 'silver'
      }
   },

   navigator: {
      handles: {
         backgroundColor: '#666',
         borderColor: '#AAA'
      },
      outlineColor: '#CCC',
      maskFill: 'rgba(255,255,255,0.1)',
      series: {
         color: '#7798BF',
         lineColor: '#A6C7ED'
      },
      xAxis: {
         gridLineColor: '#505053'
      }
   },

   scrollbar: {
      barBackgroundColor: '#808083',
      barBorderColor: '#808083',
      buttonArrowColor: '#CCC',
      buttonBackgroundColor: '#606063',
      buttonBorderColor: '#606063',
      rifleColor: '#FFF',
      trackBackgroundColor: '#404043',
      trackBorderColor: '#404043'
   },

   // special colors for some of the
   legendBackgroundColor: 'rgba(0, 0, 0, 0.5)',
   background2: '#505053',
   dataLabelsColor: '#B0B0B3',
   textColor: '#C0C0C0',
   contrastTextColor: '#F0F0F3',
   maskColor: 'rgba(255,255,255,0.3)'
};

// Apply the theme
Highcharts.setOptions(Highcharts.theme);

//HIGHCHART THEME END

//CHART 1 START
Highcharts.chart('chart1', {

    title: {
        text: 'Tasks created'
    },

    subtitle: {
        text: 'over the past week'
    },

    yAxis: {
        title: {
            text: 'number of tasks'
        }
    },

	xAxis: {

		categories: ['today-4', 'today-3', 'today-2', 'today-1', 'today date']
	},

    legend: {
        layout: 'vertical',
        align: 'right',
        verticalAlign: 'middle'
    },

    plotOptions: {
        series: {
            pointStart: 0
        }
    },

    series: [{
        name: 'Cleaning',
        data: [43934, 52503, 57177, 69658,  154175]
    }, {
        name: 'Delivery',
        data: [24916, 24064, 29742, 29851, 40434]
    }, {
        name: 'Fixing',
        data: [11744, 17722, 16005, 19771, 39387]
    }, {
        name: 'Everything Else',
        data: [12908, 5948, 8105, 11248, 18111]
    }]

});

//CHART 1 END

//CHART 2 START
Highcharts.chart('chart2', {
    chart: {
        plotBackgroundColor: null,
        plotBorderWidth: 0,
        plotShadow: false
    },
    title: {
        text: 'Tasks by category',
        align: 'center',
        verticalAlign: 'top',
        y: 40
    },
    tooltip: {
        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
    },
    plotOptions: {
        pie: {
            dataLabels: {
                enabled: true,
                distance: -50,
                style: {
                    fontWeight: 'bold',
                    color: 'white'
                }
            },
            startAngle: -90,
            endAngle: 90,
            center: ['50%', '75%']
        }
    },
    series: [{
        type: 'pie',
        name: 'Browser share',
        innerSize: '50%',
        data: [
            ['Delivery', <?php echo round(($model->deliveryTasks/$model->totalTasks), 2); ?>],
            ['Cleaning', <?php echo round(($model->cleaningTasks/$model->totalTasks), 2); ?>],
            ['Fixing', <?php echo round(($model->fixingTasks/$model->totalTasks), 2); ?>],
            ['Everything Else', <?php echo round(($model->etcTasks/$model->totalTasks), 2); ?>]
          
        ]
    }]
});
//CHART2 END
		});
		
		
	</script>
 </body>
 </html>