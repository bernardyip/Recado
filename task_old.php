<?php 

include_once "model/CategoryTask.php";
include_once "data/TaskDatabase.php";
include_once "data/CategoryDatabase.php";
include_once "data/BidDatabase.php";
include_once "HtmlHelper.php";
include_once "ConversionHelper.php";

session_start();

// user needs to be logged in
if (!isset($_SESSION['username'])) {
    header('Refresh: 0; URL=http://localhost/login.php?next=' . urlencode("/task.php"));
    die();
}

class TaskBid {
    public $task;
    public $bids;
    public $earliestMaxBid;
    public $latestMaxBid;
    
    public function __construct($task, $bids, $earliestMaxBid, $latestMaxBid) {
        $this->task = $task;
        $this->bids = $bids;
        $this->earliestMaxBid = $earliestMaxBid;
        $this->latestMaxBid = $latestMaxBid;
    }
}

class TaskModel {
    public $newTaskName;
    public $newTaskDescription;
    public $newTaskPostalCode;
    public $newTaskLocation;
    public $newTaskStartDateTime;
    public $newTaskEndDateTime;
    public $newTaskListingPrice;
    public $newTaskCategoryId;
    public $newTaskCreatorId;
    
    public $createdTask;
    public $taskCreationResult;
    
    public $userId;
    public $myTasks;
    public $myBids;
    
    public $searchKey;
    
    public $categoryTasks;
    
    public function __construct() {
        
    }
    
    public function isValidForCreate() {
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
    
    public function isValidForSearch() {
        if (is_null($this->searchKey)) return false;
        return strlen($this->searchKey) > 0;
    }
    
    public function clearNewTaskFields() {
        $this->newTaskName = null;
        $this->newTaskDescription = null;
        $this->newTaskPostalCode = null;
        $this->newTaskLocation = null;
        $this->newTaskStartDateTime = null;
        $this->newTaskEndDateTime = null;
        $this->newTaskListingPrice = null;
        $this->newTaskCategoryId = null;
        $this->newTaskCreatorId = null;
    }
}

class TaskView {
    
    private $model;
    private $controller;
    
    public function __construct(TaskController $controller, TaskModel $model) {
        $this->controller = $controller;
        $this->model = $model;
    }
    
    public function getCreateTaskForm() {
        $html = "<form action='" . TaskController::CREATE_TASK_URL . "' method='POST'>";
        $html = $html . HtmlHelper::createTable(
                array (
                   array ( "Name: ", HtmlHelper::makeInput("text", "name", $this->model->newTaskName, "Task Name", "") ),
                   array ( "Description:", HtmlHelper::makeInput("text", "description", $this->model->newTaskDescription, "Task Description", "") ),
                   array ( "Postal Code:", HtmlHelper::makeInput("number", "postal_code", $this->model->newTaskPostalCode, "123456", "Six digit zip code") ),
                   array ( "Location:", HtmlHelper::makeInput("text", "location", $this->model->newTaskLocation, "Where the task held at?", "") ),
                   array ( "Task Start Date/Time:", HtmlHelper::makeInput("datetime-local", "task_start_time", $this->getStartTime(), "", "") ),
                   array ( "Task End Date/Time:", HtmlHelper::makeInput("datetime-local", "task_end_time", $this->getEndTime(), "", "") ),
                   array ( "Listing Price ($):", HtmlHelper::makeMoneyInput("listing_price", $this->model->newTaskListingPrice, "15.99", "") ),
                   array ( "Category:", $this->makeCategoryDropdown($this->model->categoryTasks ) ),
                ), null);
        $html = $html . HtmlHelper::makeInput("submit", "", "Create Task!", "", "");
        $html = $html . "<p>" . htmlspecialchars($this->model->taskCreationResult) . "</p>";
        $html = $html . "</form>";
        return $html;
    }
    
    private function getStartTime() {
        if (is_null($this->model->newTaskStartDateTime)) {
            return (new DateTime ( null, new DateTimeZone ( "Asia/Singapore" ) ))->format ( 'Y-m-d\T\0\0:\0\0' );
        } else {
            return $this->model->newTaskStartDateTime->format ( 'Y-m-d\TH:i' );
        }
    }
    
    private function getEndTime() {
        if (is_null($this->model->newTaskEndDateTime)) {
            return (new DateTime ( null, new DateTimeZone ( "Asia/Singapore" ) ))->format ( 'Y-m-d\T\2\3:\5\9' );
        } else {
            return $this->model->newTaskEndDateTime->format ( 'Y-m-d\TH:i' );
        }
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
    
    public function getMyTasks() {
        $html = "";
        if (!is_null($this->model->myTasks)) {
            $lastTaskId = -1;
            foreach ($this->model->myTasks as $myTask) {
                if ($lastTaskId !== $myTask->taskId) {
                    if ($lastTaskId !== -1) $html = $html . "<br />";
                    $lastTaskId = $myTask->taskId;
                    $html = $html . $this->createHyperlinkForTaskDetail($myTask->taskId, $myTask->taskName);
                    $html = $html . " --> Offering " . ConversionHelper::moneyToString($myTask->taskListingPrice) . ", ";
                    $html = $html . " Current max bid: " . ConversionHelper::moneyToString($myTask->maxBidAmount) . " by $myTask->maxBidUser";
                } else {
                    $html = $html . ", $myTask->maxBidUser";
                }
            }
        }
        return $html;
    }
    
    public function getMyBids() {
        $html = "";
        if (!is_null($this->model->myBids)) {
            foreach ($this->model->myBids as $myBid) {
                $html = $html . $this->createHyperlinkForTaskDetailMyBids($myBid);
            }
        }
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
    
    private function createHyperlinkForTaskDetailMyBids($bid) {
        $taskName = $this->controller->getTaskNameWithId($bid->taskId);
        $result = $this->createHyperlinkForTaskDetail($bid->taskId, $taskName) . " --> bidded " .
        ConversionHelper::moneyToString($bid->amount) . " @ " . $bid->bidTime->format('Y-m-d\TH:i');
        
        if ($bid->selected) {
            $result = $result . " (Won)";
        } else {
            $result = $result . " (Pending)";
        }
        $result = $result . "<br />";
        return $result;
    }
    
    private function createHyperlinkForTaskDetail($taskId, $taskName) {
        return "<a href='/task_details.php?task=$taskId'>$taskName</a>";
    }
    
    private function createHyperlinkForTask($task) {
        $result = "<a href='/task_details.php?task=$task->id'>" . htmlspecialchars($task->name) . "</a><br />";
        return $result;
    }
    
    private function makeCategoryDropdown($categoryTasks) {
        $html = "<select name=category>";
        $selectedId = -1;
        if (!is_null($this->model->newTaskCategoryId)) {
            $selectedId = $this->model->newTaskCategoryId; 
        }
        foreach ($categoryTasks as $categoryTask) {
            if ($selectedId === $categoryTask->category->id) {
                $html = $html . "<option selected='selected' value='" . $categoryTask->category->id . "'>" . htmlspecialchars($categoryTask->category->name) . "</option>";
            } else {
                $html = $html . "<option value='" . $categoryTask->category->id . "'>" . htmlspecialchars($categoryTask->category->name) . "</option>";
            }
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
    private $bidDatabase;
    
    public function __construct(TaskModel $model) {
        $this->model = $model;
        $this->taskDatabase = new TaskDatabase();
        $this->categoryDatabase = new CategoryDatabase();
        $this->bidDatabase = new BidDatabase();
    }
    
    public function getTaskNameWithId($taskId) {
        $taskResult = $this->taskDatabase->findTaskWithId($taskId);
        if ($taskResult->status === TaskDatabaseResult::TASK_FIND_SUCCESS) {
            return $taskResult->tasks[0]->name;
        } else {
            return "";
        }
    }
    
    public function fetchMyTasks() {
        $taskBids = array();
        $taskResult = $this->taskDatabase->task_myTasks($this->model->userId);
        if ($taskResult->status === TaskDatabaseResult::TASK_FIND_SUCCESS) {
            $this->model->myTasks = $taskResult->tasks;
        }
    }
    
    private function getBidsByTask($task) {
        $bidResult = $this->bidDatabase->findBidsWithTaskId($task->id);
        if ($bidResult->status === BidDatabaseResult::BID_FIND_SUCCESS) {
            return $bidResult->bids;
        } else {
            return null;
        }
    }
    
    private function getEarliestMaxBidByTask($task) {
        $bidResult = $this->bidDatabase->findBidsForTaskIdWithMaxAmountAndEarliestDate($task->id);
        if ($bidResult->status === BidDatabaseResult::BID_FIND_SUCCESS) {
            return $bidResult->bids[0];
        } else {
            return null;
        }
    }
    
    private function getLatestMaxBidByTask($task) {
        $bidResult = $this->bidDatabase->findBidsForTaskIdWithMaxAmountAndLatestDate($task->id);
        if ($bidResult->status === BidDatabaseResult::BID_FIND_SUCCESS) {
            return $bidResult->bids[0];
        } else {
            return null;
        }
    }
    
    public function fetchMyBids() {
        $bidResult = $this->bidDatabase->findBidsWithUserId($this->model->userId);
        if ($bidResult->status === BidDatabaseResult::BID_FIND_SUCCESS) {
            $this->model->myBids = $bidResult->bids;
        }
    }
    
    public function fetchTasks() {
        $categoryTasks = array();
        $categoryResult = $this->categoryDatabase->findCategories();
        if ($categoryResult->status === CategoryDatabaseResult::CATEGORY_FIND_SUCCESS) {
            for ($i = 0; $i < $categoryResult->count; $i++) {
                $category = $categoryResult->categories[$i];
                $tasks = null;
                $totalTaskCount = 0;
                $taskResult = $this->taskDatabase->findTasksWithCategoryId($category->id);
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
        if ($this->model->isValidForSearch()) {
            
        }
    }
    
    public function createTask() {
        if ($this->model->isValidForCreate()) {
            $createdTask = $this->taskDatabase->createTask(
                    $this->model->newTaskName, $this->model->newTaskDescription, 
                    $this->model->newTaskPostalCode, $this->model->newTaskLocation, 
                    $this->model->newTaskStartDateTime, $this->model->newTaskEndDateTime, 
                    $this->model->newTaskListingPrice, $this->model->newTaskCategoryId, 
                    $this->model->newTaskCreatorId);
            if ($createdTask->status === TaskDatabaseResult::TASK_CREATE_SUCCESS) {
                $this->model->taskCreationResult = "Successfully created task!";
                $this->model->clearNewTaskFields();
            } else {
                $this->model->taskCreationResult = "Task creation failed :(";
            }
        }
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
        if (isset ( $_POST ['searchKey'] ) ) {
            $this->model->searchKey = $_POST['searchKey'];
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

$model = new TaskModel ();
$controller = new TaskController ( $model );
$view = new TaskView ( $controller, $model );


if (isset ( $_SESSION['id'] ) ) {
    $model->newTaskCreatorId = $model->userId = $_SESSION['id'];
} else {
    // should be logged in by here.
    // see top of task.php
    return;
}

$controller->fetchTasks();
$controller->fetchMyBids();
$controller->fetchMyTasks();

if ($_SERVER ['REQUEST_METHOD'] === 'POST') {
    $controller->handleHttpPost ();
} else if ($_SERVER ['REQUEST_METHOD'] === 'GET') {
    $controller->handleHttpGet ();
}

?>

<html>
	<head>
		<title>Recado</title>
	</head>
	<body>
		<?php
		include('banner.php');
		?>
		
		<h1>Create a task:</h1>
		<?php
		echo $view->getCreateTaskForm();
		?>
		
		<h1>My Tasks:</h1>
		<?php
		echo $view->getMyTasks();
		?>
			
		<h1>My Bids:</h1>
		<?php
		echo $view->getMyBids();
		?>
		
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