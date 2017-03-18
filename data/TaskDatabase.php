<?php
include_once '/data/Database.php';
include_once '/model/Task.php';

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
    const SQL_FIND_TASK_WITH_CATEGORY_WITH_LIMIT = "SELECT * FROM public.task t WHERE t.category_id=$1 LIMIT $2";
    const SQL_FIND_TASK_WITH_CATEGORY = "SELECT * FROM public.task t WHERE t.category_id=$1";
    const SQL_FIND_TASK_COUNT_WITH_CATEGORY = "SELECT COUNT(*) AS count FROM public.task t WHERE t.category_id=$1";
    const SQL_FIND_TASK_COUNT = "SELECT COUNT(*) AS count FROM public.task t";
    
    public function __construct() {
        parent::__construct();
        pg_prepare ( $this->dbcon, 'SQL_FIND_TASK_WITH_CATEGORY_WITH_LIMIT', TaskDatabase::SQL_FIND_TASK_WITH_CATEGORY_WITH_LIMIT );
        pg_prepare ( $this->dbcon, 'SQL_FIND_TASK_WITH_CATEGORY', TaskDatabase::SQL_FIND_TASK_WITH_CATEGORY );
        pg_prepare ( $this->dbcon, 'SQL_FIND_TASK_COUNT_WITH_CATEGORY', TaskDatabase::SQL_FIND_TASK_COUNT_WITH_CATEGORY );
        pg_prepare ( $this->dbcon, 'SQL_FIND_TASK_COUNT', TaskDatabase::SQL_FIND_TASK_COUNT );
    }
    
    public function findTasksWithCategoryIdLimitTo($categoryId, $count = 0) {
        
        $dbResult = null;
        if ($count > 0) {
            $dbResult = pg_execute ( $this->dbcon, 'SQL_FIND_TASK_WITH_CATEGORY_WITH_LIMIT', array (
                    $categoryId,
                    $count
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
    
}

?>