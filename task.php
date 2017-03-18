<?php 

include_once "model/CategoryTask.php";
include_once "data/TaskDatabase.php";
include_once "data/CategoryDatabase.php";
include_once 'HtmlHelper.php';

class TaskModel {
    public $newTaskName;
    public $newTaskDescription;
    public $newTaskPostalCode;
    public $newTaskLocation;
    public $newTaskStartDateTime;
    public $newTaskEndDateTime;
    public $newTaskListingPrice;
    public $newTaskCategory;
    public $myTasks;
    public $myBids;
    public $searchKey;
    public $categoryTasks;
    
    public function __construct() {
        
    }
    
    public function isValidForCreate() {
        if (is_null($this->$newTaskName)) return false;
        if (is_null($this->$newTaskDescription)) return false;
        if (is_null($this->$newTaskPostalCode)) return false;
        if (is_null($this->$newTaskLocation)) return false;
        if (is_null($this->$newTaskStartDateTime)) return false;
        if (is_null($this->$newTaskEndDateTime)) return false;
        if (is_null($this->$newTaskListingPrice)) return false;
        if (is_null($this->$newTaskCategory)) return false;
        
        return strlen($this->newTaskName) > 0 &&
                strlen($this->$newTaskDescription) &&
                preg_match("/^[0-9]{6}$/", $this->$newTaskPostalCode) == 1 &&
                strlen($this->$newTaskLocation) > 0 &&
                $this->$newTaskStartDateTime < $this->$newTaskEndDateTime &&
                strlen($this->$newTaskListingPrice) > 0 && $this->$newTaskListingPrice > 0;
                strlen($this->$newTaskCategory) > 0 && $this->$newTaskCategory > 0;
    }
    
    public function isValidForSearch() {
        if (is_null($this->searchKey)) return false;
        return strlen($this->searchKey) > 0;
    }
}

class TaskView {
    
    private $model;
    private $controller;
    
    public function __construct($controller, $model) {
        $this->controller = $controller;
        $this->model = $model;
    }
    
    public function getCreateTaskForm() {
        $html = "<form action='" . TaskController::CREATE_TASK_URL . "' method='POST'>";
        $html = $html . HtmlHelper::createTable(
                array (
                   array ( "Name: ", HtmlHelper::makeInput("text", "name", "", "Task Name", "") ),
                   array ( "Description:", HtmlHelper::makeInput("text", "description", "", "Task Description", "") ),
                   array ( "Postal Code:", HtmlHelper::makeInput("number", "postal_code", "", "123456", "Six digit zip code") ),
                   array ( "Location:", HtmlHelper::makeInput("text", "location", "", "Where the task held at?", "") ),
                   array ( "Task Start Date/Time:", HtmlHelper::makeInput("datetime-local", "task_start_time", "", "", "") ),
                   array ( "Task End Date/Time:", HtmlHelper::makeInput("datetime-local", "task_end_time", "", "", "") ),
                   array ( "Listing Price:", HtmlHelper::makeInput("number", "postal_code", "", "123456", "Six digit zip code") ),
                   array ( "Category:", $this->makeCategoryDropdown($model->categoryTasks ) )
                ), null);
        $html = $html . HtmlHelper::makeInput("submit", "", "Create Task!", "", "");
        $html = $html . "</form>";
    }
    
    public function getSearchForm() {
        $html = "<form action='" . TaskController::SEARCH_TASK_URL . "' method='POST'>";
        $html = $html . HtmlHelper::createTable(
                array (
                   array ( HtmlHelper::makeInput("text", "searchKey", "", "Keywords", ""), HtmlHelper::makeInput("submit", "", "Search", "", "") )
                ), null);
        $html = $html . "</form>";
        return $html;
    }
    
    public function getTasks() {
        $html = "";
        foreach ($this->model->categoryTasks as $categoryTask) {
            $html = $html . $this->createHeaderWithCount($categoryTask->category->name, $categoryTask->totalTaskCount);
            foreach ($categoryTask->tasks as $task) {
                $html = $html . $this->createHyperlinkForTask($task);
            }
        }
        return $html;
    }
    
    private function createHeaderWithCount($categoryName, $taskCount) {
        $result = "<h3>" . htmlspecialchars($categoryName) . " ($taskCount)</h3>";
        return $result;
    }
    
    private function createHyperlinkForTask($task) {
        $result = "<a href='/task_details.php?task=$task->id'>" . htmlspecialchars($task->name) . "</a><br />";
        return $result;
    }
    
    private static function makeCategoryDropdown($categoryTasks) {
        $html = "<select name=category>";
        foreach ($categoryTasks as $categoryTask) {
            $html = $html . "<option value='$categoryTask->category->id'>$categoryTask->category->name</option>";
        }
        $html = $html . "</select>";
        return $html;
    }
}

class TaskController {
    const CREATE_TASK_URL = "task.php?action=create";
    const SEARCH_TASK_URL = "task.php?action=search";
    
    private $model;
    private $taskDatabase;
    private $categoryDatabase;
    
    public function __construct($model) {
        $this->model = $model;
        $this->taskDatabase = new TaskDatabase();
        $this->categoryDatabase = new CategoryDatabase();
    }
    
    public function fetchTasks() {
        $categoryTasks = array();
        $categoryResult = $this->categoryDatabase->findCategoriesLimitTo();
        if ($categoryResult->status === CategoryDatabaseResult::CATEGORY_FIND_SUCCESS) {
            for ($i = 0; $i < $categoryResult->count; $i++) {
                $category = $categoryResult->categories[$i];
                $tasks = null;
                $totalTaskCount = 0;
                $taskResult = $this->taskDatabase->findTasksWithCategoryIdLimitTo($category->id);
                if ($taskResult->status === TaskDatabaseResult::TASK_FIND_SUCCESS) {
                    $tasks = $taskResult->tasks;
                }
                $taskResult = $this->taskDatabase->findTaskCountWithCategoryId($category->id);
                if ($taskResult->status === TaskDatabaseResult::TASK_FIND_SUCCESS) {
                    $totalTaskCount = $taskResult->count;
                }
                $categoryTasks[$i] = new CategoryTask($category, $tasks, $totalTaskCount);
            }
        }
        $this->model->categoryTasks = $categoryTasks;
    }
    
    public function searchTask() {
        
    }
    
    public function createTask() {
        
    }
    
    public function handleHttpPost() {
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
        $this->fetchTasks();
    }
}

$model = new TaskModel ();
$controller = new TaskController ( $model );
$view = new TaskView ( $controller, $model );

if ($_SERVER ['REQUEST_METHOD'] === 'POST') {
    $controller->handleHttpPost ();
} else if ($_SERVER ['REQUEST_METHOD'] === 'GET') {
    $controller->handleHttpGet ();
}

?>

<html>
	<head>
		<title>Recado</title>
		<script type="text/javascript">
			function search() {
				var search = document.getElementById("search").value;
				console.log(search);
			}
		</script>
	</head>
	<body>
<?php   include('banner.php');
		//If not logged in
		if (!isset($_SESSION['username'])) {
			header('Refresh: 0; URL=http://localhost/login.php?next=' . urlencode("/task.php"));
			die();
		}
		
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		    $task_name = $_POST['name'];
		    $task_description = $_POST['description'];
		    $task_postal_code = $_POST['postal_code'];
		    $task_location = $_POST['location'];
		    $task_start_time = (new DateTime($_POST['task_start_time'], new DateTimeZone('Asia/Singapore')))->format('Y-m-d\TH:i:s');
		    $task_end_time = (new Datetime($_POST['task_end_time'], new DateTimeZone('Asia/Singapore')))->format('Y-m-d\TH:i:s');
		    $task_listing_price = $_POST['listing_price'];
		    $task_created_time = (new DateTime(null, new DateTimeZone("Asia/Singapore")))->format('Y-m-d\TH:i:s\Z');
		    $task_category_id = $_POST['category'];
		    $task_creator_username = $_SESSION['id'];
		    
		    $query  = "INSERT INTO public.task ";
		    $query .= "(name, description, postal_code, location, task_start_time, task_end_time, listing_price, created_time, status, category_id, creator_id, bid_picked) ";
		    $query .= "VALUES";
		    $query .= "($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12); ";
		    
		    $parameters = array($task_name, $task_description, $task_postal_code, $task_location, $task_start_time, $task_end_time, $task_listing_price, $task_created_time, 'pending', $task_category_id, $task_creator_username, 'f');
		    
		    $dbcon = pg_connect('host=localhost dbname=postgres user=postgres password=password');
		    pg_prepare($dbcon, 'create_task_query', $query);
		    $result = pg_execute($dbcon, 'create_task_query', $parameters);
		    
		    header('Refresh: 0; URL=http://localhost/task.php?message=' . urlencode('Task created!'));
		    die();
		}
		
		$dbcon = pg_connect('host=localhost dbname=postgres user=postgres password=password');
		pg_prepare($dbcon, 'select_category_query', "SELECT c.id, c.name FROM public.category c");
		$result = pg_execute($dbcon, 'select_category_query', array()); ?>
		
		<h1>Create a task:</h1>
		<form action="task.php" method="POST">
			<table>
    			<tr><td>name : 					</td><td><input type="text" name="name" 						value="test" /></td></tr>
    			<tr><td>description : 			</td><td><input type="text" name="description" 					value="test desc" /></td></tr>
    			<tr><td>postal_code :			</td><td><input type="text" pattern="[0-9]{6}" 					value="123123" title="Six digit zip code" name="postal_code" /></td></tr>
    			<tr><td>location : 				</td><td><input type="text" name="location" 					value="Singapore" /></td></tr>
    			<tr><td>task start date/time : 	</td><td><input type="datetime-local" name="task_start_time"	value="2017-01-02T11:00" /></td></tr>
    			<tr><td>task end date/time :	</td><td><input type="datetime-local" name="task_end_time"		value="2017-01-02T13:00" /></td></tr>
    			<tr><td>listing price :			</td><td><input type="number" name="listing_price"				value="12" /></td></tr>
    			<tr><td>category : 				</td><td>
    			<select name="category">
<?php               while ($row = pg_fetch_array($result)) { ?>
    				    <option value="<?=$row['id'] ?>"><?=$row['name'] ?></option>
<?php               } ?>
    			</select></td></tr>
			</table>
			<input type="submit" value="Create Task!" />
		</form>
		
		<h1>My Tasks:</h1>
		<?php 
    		$query  = "SELECT t.id as task_id, temp.user_id, temp.username, temp.max_bid, t.name, t.listing_price ";
    		$query .= "FROM public.task t ";
    		$query .= "LEFT OUTER JOIN  ";
    		$query .= 	"(SELECT b1.task_id, b1.amount as max_bid, b1.user_id, u.username, t.name, t.listing_price ";
    		$query .= 	"FROM public.task t ";
    		$query .= 	"INNER JOIN public.bid b1 ON t.id = b1.task_id ";
    		$query .= 	"INNER JOIN ( ";
    		$query .= 	"SELECT b2.task_id, MAX(b2.amount) ";
    		$query .= 	"FROM public.bid b2 ";
    		$query .= 	"GROUP BY b2.task_id ";
    		$query .= ") max_bid ON b1.task_id = max_bid.task_id AND b1.amount=max_bid.max ";
    		$query .= "INNER JOIN public.user u ON b1.user_id = u.id) temp ON temp.task_id = t.id AND temp.name = t.name AND temp.listing_price = t.listing_price ";
    		$query .= "WHERE t.creator_id = $1 ";
    		$query .= "ORDER BY t.id; ";
			$dbcon = pg_connect('host=localhost dbname=postgres user=postgres password=password');
			pg_prepare($dbcon, 'my_query', $query);
			$result = pg_execute($dbcon, 'my_query', array($_SESSION['id']));
			$task_id = -1;
			while ($row = pg_fetch_array($result)) { 
				if ($task_id != intval($row['task_id'])) { //new task
					echo $task_id!=-1? "<br />" : ""; //break after the 2nd row onwards
					$task_id = intval($row['task_id']); 
					$max_bid = "";
					if ($row['max_bid'] != '') {
					    $max_bid = ", Current max bid: " . $row['max_bid'] . " by " . $row['username'];
					}
					?>
					<a href="task_details.php?task=<?=$row['task_id']?>"><?=$row['name']?></a> --> Offering <?=$row['listing_price']?> <?=$max_bid ?>
<?php 			} else {
					echo ", " . $row['username'];
				}
			} ?>
		<h1>My Bids:</h1>
		<?php 
		$dbcon = pg_connect('host=localhost dbname=postgres user=postgres password=password');
		pg_prepare($dbcon, 'bid_details_query', "SELECT b.id, b.bid_time, b.amount, b.selected, t.name as task_name, t.id as task_id FROM bid b INNER JOIN task t ON b.task_id = t.id WHERE b.user_id=$1");
		$result = pg_execute($dbcon, 'bid_details_query', array($_SESSION['id']));
		while ($row = pg_fetch_array($result)) { ?>
			<a href="task_details.php?task=<?=$row['task_id']?>"><?=$row['task_name']?></a> --> bidded $<?=$row['amount']?> @ <?=(new datetime($row['bid_time']))->format('Y-m-d H:i:s')?> <?=$row['selected']=='t'? '(Won)' : '(Pending)';?> <br />
<?php   } ?>
		
		<h2>Search</h2>
		<?php
		echo $view->getSearchForm();
		?>
		
		<h1>All Tasks:</h1>
		<?php
		echo $view->getTasks();
		?>
	</body>
</html>