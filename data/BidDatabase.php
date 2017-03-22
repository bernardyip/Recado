<?php
include_once '/data/Database.php';
include_once '/model/Bid.php';

class BidDatabaseResult extends DatabaseResult {
    const BID_FIND_SUCCESS = 10;
    const BID_FIND_FAIL = 19;
    const BID_CREATE_SUCCESS = 11;
    const BID_CREATE_FAIL = 18;
    const BID_UPDATE_SUCCESS = 12;
    const BID_UPDATE_FAIL = 17;
    const BID_DELETE_SUCCESS = 13;
    const BID_DELETE_FAIL = 16;
    
    public $bids;
    
    public function __construct($status, $bids) {
        $this->status = $status;
        $this->bids = $bids;
    }
}


class BidDatabase extends Database {
    
    // SQL Queries
    const SQL_FIND_BID_WITH_TASKID_WITH_LIMIT = "SELECT * FROM public.bid b WHERE b.task_id=$1 LIMIT $2;";
    const SQL_FIND_BID_WITH_TASKID = "SELECT * FROM public.bid b WHERE b.task_id=$1;";
    const SQL_FIND_BID_WITH_USERID_WITH_LIMIT = "SELECT * FROM public.bid b WHERE b.user_id=$1 LIMIT $2;";
    const SQL_FIND_BID_WITH_USERID = "SELECT * FROM public.bid b WHERE b.user_id=$1;";
    const SQL_FIND_BID_WITH_TASKID_MAX_AMOUNT_AND_EARLIEST = "SELECT * FROM public.bid b WHERE b.task_id=$1 ORDER BY b.amount DESC, b.bid_time LIMIT 1;";
    const SQL_FIND_BID_WITH_TASKID_MAX_AMOUNT_AND_LATEST = "SELECT * FROM public.bid b WHERE b.task_id=$1 ORDER BY b.amount DESC, b.bid_time DESC LIMIT 1;";
    
    public function __construct() {
        parent::__construct();
        pg_prepare ( $this->dbcon, 'SQL_FIND_BID_WITH_TASKID_WITH_LIMIT', BidDatabase::SQL_FIND_BID_WITH_USERID_WITH_LIMIT );
        pg_prepare ( $this->dbcon, 'SQL_FIND_BID_WITH_TASKID', BidDatabase::SQL_FIND_BID_WITH_USERID );
        pg_prepare ( $this->dbcon, 'SQL_FIND_BID_WITH_USERID_WITH_LIMIT', BidDatabase::SQL_FIND_BID_WITH_USERID_WITH_LIMIT );
        pg_prepare ( $this->dbcon, 'SQL_FIND_BID_WITH_USERID', BidDatabase::SQL_FIND_BID_WITH_USERID );
        pg_prepare ( $this->dbcon, 'SQL_FIND_BID_WITH_TASKID_MAX_AMOUNT_AND_EARLIEST', BidDatabase::SQL_FIND_BID_WITH_TASKID_MAX_AMOUNT_AND_EARLIEST );
        pg_prepare ( $this->dbcon, 'SQL_FIND_BID_WITH_TASKID_MAX_AMOUNT_AND_LATEST', BidDatabase::SQL_FIND_BID_WITH_TASKID_MAX_AMOUNT_AND_LATEST );
    }
    
    public function task_myBids($userId) {
        
    }
    
    public function findBidsForTaskIdWithMaxAmountAndEarliestDate($taskId) {
        
        $dbResult = null;
        $dbResult = pg_execute ( $this->dbcon, 'SQL_FIND_BID_WITH_TASKID_MAX_AMOUNT_AND_EARLIEST', array (
                $taskId,
                $limitTo
        ) );

        $bids = null;
        $nrRows = pg_affected_rows ( $dbResult );
        if ($nrRows > 0) {
            $bids = array();
            $bid = pg_fetch_array( $dbResult );
            $bids[0] = new Bid($bid['id'], $bid['amount'], $bid['bid_time'], 
                    $bid['selected'], $bid['user_id'], $bid['task_id']);
        }
        
        return new BidDatabaseResult(BidDatabaseResult::BID_FIND_SUCCESS, $bids);
        
    }
    
    public function findBidsForTaskIdWithMaxAmountAndLatestDate($taskId) {
        
        $dbResult = null;
        $dbResult = pg_execute ( $this->dbcon, 'SQL_FIND_BID_WITH_TASKID_MAX_AMOUNT_AND_LATEST', array (
                $taskId,
                $limitTo
        ) );

        $bids = null;
        $nrRows = pg_affected_rows ( $dbResult );
        if ($nrRows > 0) {
            $bids = array();
            $bid = pg_fetch_array( $dbResult );
            $bids[0] = new Bid($bid['id'], $bid['amount'], $bid['bid_time'], 
                    $bid['selected'], $bid['user_id'], $bid['task_id']);
        }
        
        return new BidDatabaseResult(BidDatabaseResult::BID_FIND_SUCCESS, $bids);
        
    }
    
    public function findBidsWithTaskId($taskId, $limitTo = 0) {
        
        $dbResult = null;
        if ($limitTo > 0) {
            $dbResult = pg_execute ( $this->dbcon, 'SQL_FIND_BID_WITH_TASKID_WITH_LIMIT', array (
                    $taskId,
                    $limitTo
            ) );
        } else {
            $dbResult = pg_execute ( $this->dbcon, 'SQL_FIND_BID_WITH_TASKID', array (
                    $taskId
            ) );
        }

        $bids = null;
        $nrRows = pg_affected_rows ( $dbResult );
        if ($nrRows >= 1) {
            $bids = array();
            for ($i = 0; $i < $nrRows; $i++) {
                $bid = pg_fetch_array( $dbResult );
                $bids[$i] = new Bid($bid['id'], $bid['amount'], $bid['bid_time'], 
                        $bid['selected'], $bid['user_id'], $bid['task_id']);
            }
        }
        
        return new BidDatabaseResult(BidDatabaseResult::BID_FIND_SUCCESS, $bids);
    }
    
    public function findBidsWithUserId($userId, $limitTo = 0) {
        
        $dbResult = null;
        if ($limitTo > 0) {
            $dbResult = pg_execute ( $this->dbcon, 'SQL_FIND_BID_WITH_USERID_WITH_LIMIT', array (
                    $userId,
                    $limitTo
            ) );
        } else {
            $dbResult = pg_execute ( $this->dbcon, 'SQL_FIND_BID_WITH_USERID', array (
                    $userId
            ) );
        }

        $bids = null;
        $nrRows = pg_affected_rows ( $dbResult );
        if ($nrRows >= 1) {
            $bids = array();
            for ($i = 0; $i < $nrRows; $i++) {
                $bid = pg_fetch_array( $dbResult );
                $bids[$i] = new Bid($bid['id'], $bid['amount'], $bid['bid_time'], 
                        $bid['selected'], $bid['user_id'], $bid['task_id']);
            }
        }
        
        return new BidDatabaseResult(BidDatabaseResult::BID_FIND_SUCCESS, $bids);
    }
    
}

?>