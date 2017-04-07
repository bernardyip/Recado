<?php
include_once '/data/Database.php';
include_once '/model/Task.php';
include_once '/model/task/MyTask.php';
include_once '/model/task/MyBid.php';
include_once '/model/index/TaskOverview.php';
include_once '/model/mytasks/MyTaskInfo.php';
include_once '/model/task_details/TaskDetail.php';

class TaskDatabaseResult extends DatabaseResult {
    const TASK_FIND_SUCCESS = 10;
    const TASK_FIND_FAIL = 19;
    const TASK_CREATE_SUCCESS = 11;
    const TASK_CREATE_FAIL = 18;
    const TASK_UPDATE_SUCCESS = 12;
    const TASK_UPDATE_FAIL = 17;
    const TASK_DELETE_SUCCESS = 13;
    const TASK_DELETE_FAIL = 16;
    
    public $tasks;
    public $count;
    
    public function __construct($status, $tasks, $count) {
        $this->status = $status;
        $this->tasks = $tasks;
        $this->count = $count;
    }
}


class TaskDatabase extends Database {
    
    // SQL Queries
    const SQL_TASK_FIND_MYTASKS_WITH_USERID = "" .
            "SELECT t.id, t.name, t.description, t.task_start_time, t.category_id, b1.amount AS max_bid, u.id AS max_bidder_id, u.username AS max_bidder_username " .
            "FROM public.task t " . 
            "LEFT OUTER JOIN public.bid b1 " .
                "ON t.id = b1.task_id AND b1.amount >= ALL(" . 
                    "SELECT b2.amount FROM public.bid b2 WHERE b1.task_id = b2.task_id) " .
            "LEFT OUTER JOIN public.user u ON b1.user_id = u.id " .
            "WHERE t.creator_id=$1 ORDER BY t.id;";
    
    const SQL_TASKS_FIND_ALL = "".
            "SELECT t.id, t.name, t.description, t.category_id, t.creator_id, u.username " .
            "FROM public.task t INNER JOIN public.user u " .
                "ON t.creator_id = u.id;";
    
    const SQL_TASKDETAILS_FIND_TASK = "" .
            "SELECT t.id, t.name, t.description, t.postal_code, t.location, t.task_start_time, t.task_end_time, t.listing_price, t.updated_time, t.bid_picked, c.id AS category_id, c.name AS category_name, u.id AS user_id, u.name AS username " .
            "FROM public.task t " .
            "INNER JOIN public.category c ON t.category_id = c.id ".
            "INNER JOIN public.user u ON t.creator_id = u.id " .
            "WHERE t.id=$1";
    
    const SQL_TASKDETAILS_FIND_FINALIZED_TASK = "" .
            "SELECT t.id " .
            "FROM public.task t " .
            "WHERE t.id=$1 AND t.bid_picked=true";
    
    const SQL_EDIT_TASK = "" . 
        "UPDATE public.task SET name=$1, description=$2, postal_code=$3, location=$4, " . 
        "task_start_time=$5, task_end_time=$6, listing_price=$7, updated_time=$8, category_id=$9" . 
        "WHERE id=$10 RETURNING created_time, status, bid_picked, creator_id";
    
    const SQL_DELETE_TASK = "" .
        "DELETE FROM public.task t WHERE t.id=$1;";
    
    const SQL_FIND_TASK_WITH_ID = "SELECT * FROM public.task t WHERE t.id=$1;";
    const SQL_FIND_TASK_WITH_USERID = "SELECT * FROM public.task t WHERE t.creator_id=$1;";
    const SQL_FIND_TASK_WITH_CATEGORY_WITH_LIMIT = "SELECT * FROM public.task t WHERE t.category_id=$1 LIMIT $2;";
    const SQL_FIND_TASK_WITH_CATEGORY = "SELECT * FROM public.task t WHERE t.category_id=$1;";
    const SQL_FIND_TASK_COUNT_WITH_CATEGORY = "SELECT COUNT(*) AS count FROM public.task t WHERE t.category_id=$1;";
    const SQL_FIND_TASK_COUNT = "SELECT COUNT(*) AS count FROM public.task t;";
    const SQL_FIND_RECENTLY_CREATED_COUNT = "SELECT COUNT(*) from public.task t WHERE t.category_id=$1 AND t.created_time::date = (CURRENT_DATE - INTERVAL '1 day' * $2);";
    const SQL_CREATE_TASK = "INSERT INTO public.task (name, description, postal_code, location, task_start_time, task_end_time, listing_price, created_time, updated_time, status, bid_picked, category_id, creator_id) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13) RETURNING id;";
    const SQL_FIND_TASK_TITLE_OR_DESCRIPTION = "SELECT * FROM public.task t INNER JOIN public.user u ON u.id = t.creator_id WHERE (t.name ILIKE $1 OR t.description ILIKE $1) AND t.category_id IN ($2, $3, $4, $5);";
    const SQL_FIND_TASK_CATEGORY = "SELECT * FROM public.task t INNER JOIN public.user u ON u.id = t.creator_id WHERE t.category_id IN ($2, $3, $4, $1);";
    const SQL_FIND_TASK_RANDOM = "SELECT t.id, t.name, t.category_id FROM public.task t ORDER BY RANDOM();";
    const SQL_FIND_TASK_RANDOM_WITH_LIMIT = "SELECT t.id, t.name, t.category_id FROM public.task t ORDER BY RANDOM() LIMIT $1;";
    const SQL_FIND_BIDDABLE_TASK = "SELECT COUNT(*) as count FROM public.task t WHERE t.bid_Picked = 'false';";

    public function __construct() {
        parent::__construct();
        pg_prepare ( $this->dbcon, 'SQL_TASK_FIND_MYTASKS_WITH_USERID', TaskDatabase::SQL_TASK_FIND_MYTASKS_WITH_USERID );
        pg_prepare ( $this->dbcon, 'SQL_FIND_TASK_WITH_ID', TaskDatabase::SQL_FIND_TASK_WITH_ID );
        pg_prepare ( $this->dbcon, 'SQL_FIND_TASK_WITH_USERID', TaskDatabase::SQL_FIND_TASK_WITH_USERID );
        pg_prepare ( $this->dbcon, 'SQL_FIND_TASK_WITH_CATEGORY_WITH_LIMIT', TaskDatabase::SQL_FIND_TASK_WITH_CATEGORY_WITH_LIMIT );
        pg_prepare ( $this->dbcon, 'SQL_FIND_TASK_WITH_CATEGORY', TaskDatabase::SQL_FIND_TASK_WITH_CATEGORY );
        pg_prepare ( $this->dbcon, 'SQL_FIND_TASK_COUNT_WITH_CATEGORY', TaskDatabase::SQL_FIND_TASK_COUNT_WITH_CATEGORY );
        pg_prepare ( $this->dbcon, 'SQL_FIND_TASK_COUNT', TaskDatabase::SQL_FIND_TASK_COUNT );
        pg_prepare ( $this->dbcon, 'SQL_FIND_RECENTLY_CREATED_COUNT', TaskDatabase::SQL_FIND_RECENTLY_CREATED_COUNT );
        pg_prepare ( $this->dbcon, 'SQL_FIND_BIDDABLE_TASK', TaskDatabase::SQL_FIND_BIDDABLE_TASK );
        pg_prepare ( $this->dbcon, 'SQL_FIND_TASK_RANDOM', TaskDatabase::SQL_FIND_TASK_RANDOM );
        pg_prepare ( $this->dbcon, 'SQL_FIND_TASK_RANDOM_WITH_LIMIT', TaskDatabase::SQL_FIND_TASK_RANDOM_WITH_LIMIT );
        pg_prepare ( $this->dbcon, 'SQL_TASKS_FIND_ALL', TaskDatabase::SQL_TASKS_FIND_ALL );
        pg_prepare ( $this->dbcon, 'SQL_CREATE_TASK', TaskDatabase::SQL_CREATE_TASK );
        pg_prepare ( $this->dbcon, 'SQL_FIND_TASK_TITLE_OR_DESCRIPTION', TaskDatabase::SQL_FIND_TASK_TITLE_OR_DESCRIPTION);
        pg_prepare ( $this->dbcon, 'SQL_FIND_TASK_CATEGORY', TaskDatabase::SQL_FIND_TASK_CATEGORY);
        pg_prepare ( $this->dbcon, 'SQL_TASKDETAILS_FIND_TASK', TaskDatabase::SQL_TASKDETAILS_FIND_TASK );
        pg_prepare ( $this->dbcon, 'SQL_TASKDETAILS_FIND_FINALIZED_TASK', TaskDatabase::SQL_TASKDETAILS_FIND_FINALIZED_TASK );
        pg_prepare ( $this->dbcon, 'SQL_EDIT_TASK', TaskDatabase::SQL_EDIT_TASK );
        pg_prepare ( $this->dbcon, 'SQL_DELETE_TASK', TaskDatabase::SQL_DELETE_TASK );
    }
    
    public function task_myTasks($userId) {
        $dbResult = pg_execute ( $this->dbcon, 'SQL_TASK_FIND_MYTASKS_WITH_USERID', array (
                $userId
            ) );

        $tasks = null;
        $nrRows = pg_affected_rows ( $dbResult );
        if ($nrRows > 0) {
            $tasks = array();
            for ($i = 0; $i < $nrRows; $i++) {
                $task = pg_fetch_array( $dbResult );
                $tasks[$i] = new MyTask($task['id'], $task['name'], 
                        $task['listing_price'], $task['amount'], $task['uid'], $task['username']);
            }
        }
        
        return new TaskDatabaseResult(TaskDatabaseResult::TASK_FIND_SUCCESS, $tasks, $nrRows);
        
    }
    
    public function tasks_getAll() {
        $dbResult = pg_execute ( $this->dbcon, 'SQL_TASKS_FIND_ALL', array (
            ) );

        $tasks = null;
        $nrRows = pg_affected_rows ( $dbResult );
        if ($nrRows > 0) {
            $tasks = array();
            for ($i = 0; $i < $nrRows; $i++) {
                $task = pg_fetch_array( $dbResult );
                $tasks[$i] = new TaskInfo($task['id'], 
                        $task['name'], $task['description'], $this->getDisplayPicturePath($task['id'], $task['category_id']),
                        $task['creator_id'], $task['username']);
            }
        }
        
        return new TaskDatabaseResult(TaskDatabaseResult::TASK_FIND_SUCCESS, $tasks, $nrRows);
    }
    
    
    public function myTasks_getAllByUserId($userId) {
        $dbResult = pg_execute ( $this->dbcon, 'SQL_TASK_FIND_MYTASKS_WITH_USERID', array (
                $userId
            ) );

        $tasks = null;
        $nrRows = pg_affected_rows ( $dbResult );
        if ($nrRows > 0) {
            $tasks = array();
            for ($i = 0; $i < $nrRows; $i++) {
                $task = pg_fetch_array( $dbResult );
                $tasks[$i] = new MyTaskInfo($task['id'], $task['name'], $task['description'], $task['task_start_time'], 
                        $this->getDisplayPicturePath($task['id'], $task['category_id']), $task['max_bidder_id'], $task['max_bidder_username'], $task['max_bid']);
            }
        }
        
        return new TaskDatabaseResult(TaskDatabaseResult::TASK_FIND_SUCCESS, $tasks, $nrRows);
        
    }
    
    public function taskDetails_isTaskFinalized($taskId) {
        $dbResult = pg_execute ( $this->dbcon, 'SQL_TASKDETAILS_FIND_FINALIZED_TASK', array (
                $taskId
            ) );

        $nrRows = pg_affected_rows ( $dbResult );
        if ($nrRows > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    public function taskDetails_getTask($taskId) {
        
        $dbResult = pg_execute ( $this->dbcon, 'SQL_TASKDETAILS_FIND_TASK', array (
                $taskId
            ) );

        $tasks = null;
        $nrRows = pg_affected_rows ( $dbResult );
        if ($nrRows > 0) {
            $tasks = array();
            for ($i = 0; $i < $nrRows; $i++) {
                $task = pg_fetch_array( $dbResult );
                $tasks[$i] = new TaskDetail($task['id'], $task['name'], $task['description'], 
                        $task['postal_code'], $task['location'], $task['task_start_time'], $task['task_end_time'], 
                        $task['listing_price'], $task['updated_time'], $task['category_id'], $task['category_name'], $task['user_id'], $task['username'],
                        $task['bid_picked'], $this->getDisplayPicturePath($task['id'], $task['category_id']));
            }
            return new TaskDatabaseResult(TaskDatabaseResult::TASK_FIND_SUCCESS, $tasks, $nrRows);
        }
            return new TaskDatabaseResult(TaskDatabaseResult::TASK_FIND_FAIL, $tasks, $nrRows);
    }
    
    public function findTaskWithId($taskId) {
        $dbResult = pg_execute ( $this->dbcon, 'SQL_FIND_TASK_WITH_ID', array (
                    $taskId
            ) );

        $tasks = null;
        $nrRows = pg_affected_rows ( $dbResult );
        if ($nrRows > 0) {
            $tasks = array();
            $task = pg_fetch_array( $dbResult );
            $tasks[0] = new Task($task['id'], $task['name'], $task['description'],
                    $task['postal_code'], $task['location'], $task['task_start_time'], 
                    $task['task_end_time'], $task['listing_price'], $task['created_time'], 
                    $task['updated_time'], $task['status'], $task['bid_picked'], $task['category_id'], 
                    $task['creator_id']);
            return new TaskDatabaseResult(TaskDatabaseResult::TASK_FIND_SUCCESS, $tasks, $nrRows);
        } else {
            return new TaskDatabaseResult(TaskDatabaseResult::TASK_FIND_FAIL, $tasks, $nrRows);
        }
        
    }
    
    public function findTasksWithUserId($userId) {
        $dbResult = pg_execute ( $this->dbcon, 'SQL_FIND_TASK_WITH_USERID', array (
                $userId
            ) );

        $tasks = null;
        $nrRows = pg_affected_rows ( $dbResult );
        if ($nrRows > 0) {
            $tasks = array();
            $task = pg_fetch_array( $dbResult );
            $tasks[0] = new Task($task['id'], $task['name'], $task['description'],
                    $task['postal_code'], $task['location'], $task['task_start_time'], 
                    $task['task_end_time'], $task['listing_price'], $task['created_time'], 
                    $task['updated_time'], $task['status'], $task['bid_picked'], $task['category_id'], 
                    $task['creator_id']);
        }
        
        return new TaskDatabaseResult(TaskDatabaseResult::TASK_FIND_SUCCESS, $tasks, $nrRows);
    }
    
    public function findTasksAtRandom($limitTo = 0) {
        
        $dbResult = null;
        if ($limitTo > 0) {
            $dbResult = pg_execute ( $this->dbcon, 'SQL_FIND_TASK_RANDOM_WITH_LIMIT', array (
                    $limitTo
            ) );
        } else {
            $dbResult = pg_execute ( $this->dbcon, 'SQL_FIND_TASK_RANDOM', array (
            ) );
        }

        $tasks = null;
        $nrRows = pg_affected_rows ( $dbResult );
        if ($nrRows >= 1) {
            $tasks = array();
            for ($i = 0; $i < $nrRows; $i++) {
                $task = pg_fetch_array( $dbResult );
                $tasks[$i] = new TaskOverview($task['id'], $task['name'], $this->getDisplayPicturePath($task['id'], $task['category_id']));
            }
        }
        
        return new TaskDatabaseResult(TaskDatabaseResult::TASK_FIND_SUCCESS, $tasks, $nrRows);
    }
    
    private function getDisplayPicturePath($taskId, $categoryId) {
        $documentRoot = $_SERVER['DOCUMENT_ROOT'];
        $taskDisplayPicture = $documentRoot . "\\img\\task\\$taskId.jpg";
        $defaultDisplayPicture = $documentRoot . "\\img\\category\\$categoryId.jpg";
        if (file_exists($taskDisplayPicture)) return "/img/task/$taskId.jpg";
        else return "/img/category/$categoryId.jpg";
    }
    
    public function findTasksWithCategoryId($categoryId, $limitTo = 0) {
        
        $dbResult = null;
        if ($limitTo > 0) {
            $dbResult = pg_execute ( $this->dbcon, 'SQL_FIND_TASK_WITH_CATEGORY_WITH_LIMIT', array (
                    $categoryId,
                    $limitTo
            ) );
        } else {
            $dbResult = pg_execute ( $this->dbcon, 'SQL_FIND_TASK_WITH_CATEGORY', array (
                    $categoryId
            ) );
        }

        $tasks = null;
        $nrRows = pg_affected_rows ( $dbResult );
        if ($nrRows >= 1) {
            $tasks = array();
            for ($i = 0; $i < $nrRows; $i++) {
                $task = pg_fetch_array( $dbResult );
                $tasks[$i] = new Task($task['id'], $task['name'], $task['description'],
                        $task['postal_code'], $task['location'], $task['task_start_time'], 
                        $task['task_end_time'], $task['listing_price'], $task['created_time'], 
                        $task['updated_time'], $task['status'], $task['bid_picked'], $task['category_id'], 
                        $task['creator_id']);
            }
        }
        
        return new TaskDatabaseResult(TaskDatabaseResult::TASK_FIND_SUCCESS, $tasks, $nrRows);
    }
    
    public function findTaskCountWithCategoryId($categoryId) {
        $dbResult = pg_execute ( $this->dbcon, 'SQL_FIND_TASK_COUNT_WITH_CATEGORY', array (
                $categoryId
        ) );

        $count = 0;
        if (pg_affected_rows ( $dbResult ) >= 1) {
            $result = pg_fetch_array( $dbResult );
            $count = $result['count'];
        }
        
        return new TaskDatabaseResult(TaskDatabaseResult::TASK_FIND_SUCCESS, array(), $count);
    }
	
	public function findRecentlyCreatedTaskCount($categoryId, $days) {
        $dbResult = pg_execute ( $this->dbcon, 'SQL_FIND_RECENTLY_CREATED_COUNT', array (
                $categoryId,
				$days
        ) );

        $count = 0;
        if (pg_affected_rows ( $dbResult ) >= 1) {
            $result = pg_fetch_array( $dbResult );
            $count = $result['count'];
        }
        
        return new TaskDatabaseResult(TaskDatabaseResult::TASK_FIND_SUCCESS, array(), $count);
    }
    
    public function findTaskCount() {
        $dbResult = pg_execute ( $this->dbcon, 'SQL_FIND_TASK_COUNT', array (
        ) );

        $count = 0;
        if (pg_affected_rows ( $dbResult ) >= 1) {
            $result = pg_fetch_array( $dbResult );
            $count = $result['count'];
        }
        
        return new TaskDatabaseResult(TaskDatabaseResult::TASK_FIND_SUCCESS, array(), $count);
    }
	
	public function findBiddableTaskCount() {
        $dbResult = pg_execute ( $this->dbcon, 'SQL_FIND_BIDDABLE_TASK', array (
        ) );

        $count = 0;
        if (pg_affected_rows ( $dbResult ) >= 1) {
            $result = pg_fetch_array( $dbResult );
            $count = $result['count'];
        }
        
        return new TaskDatabaseResult(TaskDatabaseResult::TASK_FIND_SUCCESS, array(), $count);
    }
    
    public function deleteTask($taskId) {
        
        $dbResult = pg_execute ( $this->dbcon, 'SQL_DELETE_TASK', array (
                $taskId
        ) );
        
        if (pg_affected_rows($dbResult) > 0) {
            $result = pg_fetch_array( $dbResult );
            return new TaskDatabaseResult(
                    TaskDatabaseResult::TASK_DELETE_SUCCESS, 
                    null, 1);
        } else {
            return new TaskDatabaseResult(TaskDatabaseResult::TASK_DELETE_FAIL, null, 0);
        }
    }
    
    public function createTask($name, $description, $postalCode, $location, $taskStartTime,
            $taskEndTime, $listingPrice, $categoryId, $creatorId) {
        
        $createdTime = $updatedTime = new DateTime ( null, new DateTimeZone ( "Asia/Singapore" ) );
        $status = 'pending';
        $dbResult = pg_execute ( $this->dbcon, 'SQL_CREATE_TASK', array (
                $name, $description, $postalCode, $location, $taskStartTime->format ( 'Y-m-d\TH:i:s\Z' ), 
                $taskEndTime->format ( 'Y-m-d\TH:i:s\Z' ), $listingPrice, 
                $createdTime->format ( 'Y-m-d\TH:i:s\Z' ), $updatedTime->format ( 'Y-m-d\TH:i:s\Z' ), 
                $status, "f", $categoryId, $creatorId
        ) );
        
        if (pg_affected_rows($dbResult) > 0) {
            $result = pg_fetch_array( $dbResult );
            return new TaskDatabaseResult(
                    TaskDatabaseResult::TASK_CREATE_SUCCESS, 
                    array(
                        new Task($result['id'], $name, $description, $postalCode, $location, 
                                $taskStartTime, $taskEndTime, $listingPrice, $createdTime, 
                                $updatedTime, $status, false, $categoryId, $creatorId)
                    ), 1);
        } else {
            return new TaskDatabaseResult(TaskDatabaseResult::TASK_CREATE_FAIL, null, 0);
        }
    }
    
    public function findTask($searchTerm, $cleaning, $delivery, $fixing, $everything_else) {
        $categoryFilter = "";
        if ($cleaning == "") {
            $cleaning = 0;
        }
        if ($delivery == "") {
            $delivery = 0;
        }
        if ($fixing == "") {
            $fixing = 0;
        }
        if ($everything_else == "") {
            $everything_else = 0;
        }
        
        if ($searchTerm == null) {
            $dbResult = pg_execute ( $this->dbcon, 'SQL_FIND_TASK_CATEGORY', array (
                            $cleaning, $delivery, $fixing, $everything_else
            ) );
        } else {
            $searchTerm = "%" . $searchTerm . "%";
            $dbResult = pg_execute ( $this->dbcon, 'SQL_FIND_TASK_TITLE_OR_DESCRIPTION', array (
                            $searchTerm, $cleaning, $delivery, $fixing, $everything_else
            ) );
        }
        
        $tasks = null;
        $nrRows = pg_affected_rows ( $dbResult );
        if ($nrRows > 0) {
            $tasks = array();
            for ($i = 0; $i < $nrRows; $i++) {
                $task = pg_fetch_array( $dbResult );
                $tasks[$i] = new TaskInfo($task['id'],
                                $task['name'], $task['description'], $this->getDisplayPicturePath($task['id'], $task['category_id']),
                                $task['creator_id'], $task['username']);
            }
        }
        
        return new TaskDatabaseResult(TaskDatabaseResult::TASK_FIND_SUCCESS, $tasks, $nrRows);
    }

    public function editTask($id, $name, $description, $postalCode, $location, $taskStartTime,
            $taskEndTime, $listingPrice, $categoryId) {
        
        $updatedTime = new DateTime ( null, new DateTimeZone ( "Asia/Singapore" ) );
        $dbResult = pg_execute ( $this->dbcon, 'SQL_EDIT_TASK', array (
                $name, $description, $postalCode, $location, $taskStartTime->format ( 'Y-m-d\TH:i:s\Z' ), 
                $taskEndTime->format ( 'Y-m-d\TH:i:s\Z' ), $listingPrice, 
                $updatedTime->format ( 'Y-m-d\TH:i:s\Z' ), 
                $categoryId, $id
        ) );
        
        if (pg_affected_rows($dbResult) > 0) {
            $result = pg_fetch_array( $dbResult );
            return new TaskDatabaseResult(
                    TaskDatabaseResult::TASK_UPDATE_SUCCESS, 
                    array(
                        new Task($id, $name, $description, $postalCode, $location, 
                                $taskStartTime, $taskEndTime, $listingPrice, $result['created_time'], 
                                $updatedTime, $result['status'], $result['bid_picked'], $categoryId, $result['creator_id'])
                    ), 1);
        } else {
            return new TaskDatabaseResult(TaskDatabaseResult::TASK_UPDATE_FAIL, null, 0);
        }
    }
    
}

?>