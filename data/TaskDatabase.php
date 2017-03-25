<?php
include_once '/data/Database.php';
include_once '/model/Task.php';
include_once '/model/task/MyTask.php';
include_once '/model/task/MyBid.php';
include_once '/model/index/TaskOverview.php';
include_once '/model/mytasks/MyTaskInfo.php';

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
            "SELECT t.id, t.name, t.description, t.task_start_time, b1.amount AS max_bid, u.id AS max_bidder_id, u.username AS max_bidder_username " .
            "FROM public.task t " . 
            "LEFT OUTER JOIN public.bid b1 " .
                "ON t.id = b1.task_id AND b1.amount >= ALL(" . 
                    "SELECT b2.amount FROM public.bid b2 WHERE b1.task_id = b2.task_id) " .
            "LEFT OUTER JOIN public.user u ON b1.user_id = u.id " .
            "WHERE t.creator_id=$1 ORDER BY t.id;";
    
    const SQL_TASKS_FIND_ALL = "".
            "SELECT t.id, t.name, t.description, t.creator_id, u.username " .
            "FROM public.task t INNER JOIN public.user u " .
                "ON t.creator_id = u.id;";
    
    const SQL_FIND_TASK_WITH_ID = "SELECT * FROM public.task t WHERE t.id=$1;";
    const SQL_FIND_TASK_WITH_USERID = "SELECT * FROM public.task t WHERE t.creator_id=$1;";
    const SQL_FIND_TASK_WITH_CATEGORY_WITH_LIMIT = "SELECT * FROM public.task t WHERE t.category_id=$1 LIMIT $2;";
    const SQL_FIND_TASK_WITH_CATEGORY = "SELECT * FROM public.task t WHERE t.category_id=$1;";
    const SQL_FIND_TASK_COUNT_WITH_CATEGORY = "SELECT COUNT(*) AS count FROM public.task t WHERE t.category_id=$1;";
    const SQL_FIND_TASK_COUNT = "SELECT COUNT(*) AS count FROM public.task t;";
    const SQL_CREATE_TASK = "INSERT INTO public.task (name, description, postal_code, location, task_start_time, task_end_time, listing_price, created_time, updated_time, status, bid_picked, category_id, creator_id) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13) RETURNING id;";
    const SQL_FIND_TASK_RANDOM = "SELECT t.id, t.name FROM public.task t ORDER BY RANDOM();";
    const SQL_FIND_TASK_RANDOM_WITH_LIMIT = "SELECT t.id, t.name FROM public.task t ORDER BY RANDOM() LIMIT $1;";
    
    
    public function __construct() {
        parent::__construct();
        pg_prepare ( $this->dbcon, 'SQL_TASK_FIND_MYTASKS_WITH_USERID', TaskDatabase::SQL_TASK_FIND_MYTASKS_WITH_USERID );
        pg_prepare ( $this->dbcon, 'SQL_FIND_TASK_WITH_ID', TaskDatabase::SQL_FIND_TASK_WITH_ID );
        pg_prepare ( $this->dbcon, 'SQL_FIND_TASK_WITH_USERID', TaskDatabase::SQL_FIND_TASK_WITH_USERID );
        pg_prepare ( $this->dbcon, 'SQL_FIND_TASK_WITH_CATEGORY_WITH_LIMIT', TaskDatabase::SQL_FIND_TASK_WITH_CATEGORY_WITH_LIMIT );
        pg_prepare ( $this->dbcon, 'SQL_FIND_TASK_WITH_CATEGORY', TaskDatabase::SQL_FIND_TASK_WITH_CATEGORY );
        pg_prepare ( $this->dbcon, 'SQL_FIND_TASK_COUNT_WITH_CATEGORY', TaskDatabase::SQL_FIND_TASK_COUNT_WITH_CATEGORY );
        pg_prepare ( $this->dbcon, 'SQL_FIND_TASK_COUNT', TaskDatabase::SQL_FIND_TASK_COUNT );
        pg_prepare ( $this->dbcon, 'SQL_FIND_TASK_RANDOM', TaskDatabase::SQL_FIND_TASK_RANDOM );
        pg_prepare ( $this->dbcon, 'SQL_FIND_TASK_RANDOM_WITH_LIMIT', TaskDatabase::SQL_FIND_TASK_RANDOM_WITH_LIMIT );
        pg_prepare ( $this->dbcon, 'SQL_TASKS_FIND_ALL', TaskDatabase::SQL_TASKS_FIND_ALL );
        pg_prepare ( $this->dbcon, 'SQL_CREATE_TASK', TaskDatabase::SQL_CREATE_TASK );
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
                        $task['name'], $task['description'], "/img/index-03.jpg",
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
                        "/img/tours-05.jpg", $task['max_bidder_id'], $task['max_bidder_username'], $task['max_bid']);
            }
        }
        
        return new TaskDatabaseResult(TaskDatabaseResult::TASK_FIND_SUCCESS, $tasks, $nrRows);
        
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
                $tasks[$i] = new TaskOverview($task['id'], $task['name'], "/img/index-01.jpg");
            }
        }
        
        return new TaskDatabaseResult(TaskDatabaseResult::TASK_FIND_SUCCESS, $tasks, $nrRows);
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
    
}

?>