<?php 

include_once '/data/TaskDatabase.php';
include_once '/data/CategoryDatabase.php';
include_once '/model/CategoryTask.php';

session_start();

class IndexModel {
    
    public $categoryTasks;
    
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
    
    public function getTasks() {
        $html = "";
        foreach ($this->model->categoryTasks as $categoryTask) {
            $html = $html . $this->createHeaderWithCount($categoryTask->category->name, $categoryTask->totalTaskCount);
            foreach ($categoryTask->tasks as $task) {
                $html = $html . $this->createHyperlinkForTask($task);
            }
            if ($categoryTask->totalTaskCount > 3) {
                $html = $html . "and more...<br />";
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
}

class IndexController {
    const TASKS_PER_CATEGORY = 3;
    
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
        $categoryResult = $this->categoryDatabase->findCategories();
        if ($categoryResult->status === CategoryDatabaseResult::CATEGORY_FIND_SUCCESS) {
            for ($i = 0; $i < $categoryResult->count; $i++) {
                $category = $categoryResult->categories[$i];
                $tasks = null;
                $totalTaskCount = 0;
                $taskResult = $this->taskDatabase->findTasksWithCategoryId($category->id, IndexController::TASKS_PER_CATEGORY);
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

<html>
<head>
<title>Recado</title>
</head>
<body>
	<?php include('banner.php') ?>
	<h1>Welcome to Recado</h1>
	<p>$$$$EZPZ MONEY$$$$ SIGN UP NOW ABOVE AND REAPS THE BENEFIT OF YOUR LIFETIME</p>
	<p>Here are some tasks for you to see:</p>
		<?php
		echo $view->getTasks();
		?>
	</body>
</html>