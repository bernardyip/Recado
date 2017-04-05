<?php
include_once '/data/Database.php';
include_once '/model/Bid.php';
include_once '/model/task_details/TaskBid.php';

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
    const SQL_TASKDETAILS_FIND_BID_WITH_TASKID = "" .
            "SELECT b.user_id, b.task_id, u.username, b.amount " .
            "FROM public.bid b " .
            "INNER JOIN public.user u ON b.user_id = u.id " .
            "WHERE b.task_id=$1 " .
            "ORDER BY b.amount DESC;";
    
    const SQL_TASKDETAILS_ADD_BID_BY_USER_FOR_TASK = "" .
            "INSERT INTO public.bid (amount, bid_time, selected, user_id, task_id) VALUES " .
            "($1, $2, $3, $4, $5);";
    
    const SQL_TASKDETAILS_UPDATE_BID_BY_USER_FOR_TASK = "" .
            "UPDATE public.bid SET amount=$1, bid_time=$2 WHERE user_id=$3 AND task_id=$4 ";
    
    const SQL_TASKDETAILS_REMOVE_BID_BY_USER_FOR_TASK = "" .
            "DELETE FROM public.bid b WHERE b.user_id=$1 AND b.task_id=$2 ";
    
    const SQL_TASKDETAILS_FIND_BID_BY_USERID_TASKID = "" .
            "SELECT b.user_id, b.task_id, u.username, b.amount " .
            "FROM public.bid b " .
            "INNER JOIN public.user u ON b.user_id = u.id " .
            "WHERE b.task_id=$1 AND b.user_id=$2;";
    
    const SQL_FIND_BID_WITH_TASKID_WITH_LIMIT = "SELECT * FROM public.bid b WHERE b.task_id=$1 LIMIT $2;";
    const SQL_FIND_BID_WITH_TASKID = "SELECT * FROM public.bid b WHERE b.task_id=$1;";
    const SQL_FIND_BID_WITH_USERID_WITH_LIMIT = "SELECT * FROM public.bid b WHERE b.user_id=$1 LIMIT $2;";
    const SQL_FIND_BID_WITH_USERID = "SELECT * FROM public.bid b WHERE b.user_id=$1;";
    const SQL_FIND_BID_WITH_TASKID_MAX_AMOUNT_AND_EARLIEST = "SELECT * FROM public.bid b WHERE b.task_id=$1 ORDER BY b.amount DESC, b.bid_time LIMIT 1;";
    const SQL_FIND_BID_WITH_TASKID_MAX_AMOUNT_AND_LATEST = "SELECT * FROM public.bid b WHERE b.task_id=$1 ORDER BY b.amount DESC, b.bid_time DESC LIMIT 1;";
	const SQL_GET_AVERAGE_BID = "SELECT ROUND(AVG(amount::numeric), 2) AS average FROM public.bid;";
	const SQL_COUNT_TOTAL_BIDS = "SELECT COUNT(*) AS total from public.bid;";
    
    public function __construct() {
        parent::__construct();
        pg_prepare ( $this->dbcon, 'SQL_FIND_BID_WITH_TASKID_WITH_LIMIT', BidDatabase::SQL_FIND_BID_WITH_USERID_WITH_LIMIT );
        pg_prepare ( $this->dbcon, 'SQL_FIND_BID_WITH_TASKID', BidDatabase::SQL_FIND_BID_WITH_USERID );
        pg_prepare ( $this->dbcon, 'SQL_FIND_BID_WITH_USERID_WITH_LIMIT', BidDatabase::SQL_FIND_BID_WITH_USERID_WITH_LIMIT );
        pg_prepare ( $this->dbcon, 'SQL_FIND_BID_WITH_USERID', BidDatabase::SQL_FIND_BID_WITH_USERID );
        pg_prepare ( $this->dbcon, 'SQL_FIND_BID_WITH_TASKID_MAX_AMOUNT_AND_EARLIEST', BidDatabase::SQL_FIND_BID_WITH_TASKID_MAX_AMOUNT_AND_EARLIEST );
        pg_prepare ( $this->dbcon, 'SQL_FIND_BID_WITH_TASKID_MAX_AMOUNT_AND_LATEST', BidDatabase::SQL_FIND_BID_WITH_TASKID_MAX_AMOUNT_AND_LATEST );
		pg_prepare ( $this->dbcon, 'SQL_GET_AVERAGE_BID', BidDatabase::SQL_GET_AVERAGE_BID );
		pg_prepare ( $this->dbcon, 'SQL_COUNT_TOTAL_BIDS', BidDatabase::SQL_COUNT_TOTAL_BIDS );
        pg_prepare ( $this->dbcon, 'SQL_TASKDETAILS_FIND_BID_WITH_TASKID', BidDatabase::SQL_TASKDETAILS_FIND_BID_WITH_TASKID );
        pg_prepare ( $this->dbcon, 'SQL_TASKDETAILS_ADD_BID_BY_USER_FOR_TASK', BidDatabase::SQL_TASKDETAILS_ADD_BID_BY_USER_FOR_TASK );
        pg_prepare ( $this->dbcon, 'SQL_TASKDETAILS_UPDATE_BID_BY_USER_FOR_TASK', BidDatabase::SQL_TASKDETAILS_UPDATE_BID_BY_USER_FOR_TASK );
        pg_prepare ( $this->dbcon, 'SQL_TASKDETAILS_FIND_BID_BY_USERID_TASKID', BidDatabase::SQL_TASKDETAILS_FIND_BID_BY_USERID_TASKID );
        pg_prepare ( $this->dbcon, 'SQL_TASKDETAILS_REMOVE_BID_BY_USER_FOR_TASK', BidDatabase::SQL_TASKDETAILS_REMOVE_BID_BY_USER_FOR_TASK );
    }
    
    private function bidExists($taskId, $userId) {
        $dbResult = pg_execute ( $this->dbcon, 'SQL_TASKDETAILS_FIND_BID_BY_USERID_TASKID', array (
                $taskId,
                $userId
        ) );
        return pg_affected_rows ( $dbResult ) > 0;
    }
    
    public function taskDetails_placeBid($taskId, $userId, $amount) {
        
        $dbResult = null;

        $current_datetime = (new DateTime ( null, new DateTimeZone ( "Asia/Singapore" ) ))->format ( 'Y-m-d\TH:i:s\Z' );
        
        if ($this->bidExists($taskId, $userId)) {
            if ($amount === 0) {
                $dbResult = pg_execute ( $this->dbcon, 'SQL_TASKDETAILS_REMOVE_BID_BY_USER_FOR_TASK', array (
                        $amount,
                        $current_datetime,
                        $userId,
                        $taskId ) );
            } else {
                $dbResult = pg_execute ( $this->dbcon, 'SQL_TASKDETAILS_UPDATE_BID_BY_USER_FOR_TASK', array (
                        $amount,
                        $current_datetime,
                        $userId,
                        $taskId ) );
            }
        } else {
            $dbResult = pg_execute ( $this->dbcon, 'SQL_TASKDETAILS_ADD_BID_BY_USER_FOR_TASK', array (
                    $amount,
                    $current_datetime,
                    "f",
                    $userId,
                    $taskId ) );
        }

        $nrRows = pg_affected_rows ( $dbResult );
        if ($nrRows >= 1) {
            return new BidDatabaseResult(BidDatabaseResult::BID_CREATE_SUCCESS, null);
        } else {
            return new BidDatabaseResult(BidDatabaseResult::BID_CREATE_FAIL, null);
        }
    }
    
    public function taskDetails_getMyBid($taskId, $userId) {

        $dbResult = pg_execute ( $this->dbcon, 'SQL_TASKDETAILS_FIND_BID_BY_USERID_TASKID', array (
                $taskId,
                $userId
        ) );

        $bids = null;
        $nrRows = pg_affected_rows ( $dbResult );
        if ($nrRows >= 1) {
            $bids = array();
            for ($i = 0; $i < $nrRows; $i++) {
                $bid = pg_fetch_array( $dbResult );
                $bids[$i] = new TaskBid($bid['user_id'], $bid['task_id'], $bid['username'], $bid['amount']);
            }
        }
        
        return new BidDatabaseResult(BidDatabaseResult::BID_FIND_SUCCESS, $bids);
    }
    
    public function taskDetails_getBids($taskId) {
        
        $dbResult = null;
        if (false) {
            // NOT IMPLEMENTED
            $dbResult = pg_execute ( $this->dbcon, '', array (
                    $taskId,
                    $limitTo
            ) );
        } else {
            $dbResult = pg_execute ( $this->dbcon, 'SQL_TASKDETAILS_FIND_BID_WITH_TASKID', array (
                    $taskId
            ) );
        }

        $bids = null;
        $nrRows = pg_affected_rows ( $dbResult );
        if ($nrRows >= 1) {
            $bids = array();
            for ($i = 0; $i < $nrRows; $i++) {
                $bid = pg_fetch_array( $dbResult );
                $bids[$i] = new TaskBid($bid['user_id'], $bid['task_id'], $bid['username'], $bid['amount']);
            }
        }
        
        return new BidDatabaseResult(BidDatabaseResult::BID_FIND_SUCCESS, $bids);
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
	
	public function findAverageBid() {
        $dbResult = pg_execute ( $this->dbcon, 'SQL_GET_AVERAGE_BID', array (
        ) );

		$bids = 0;
        if (pg_affected_rows ( $dbResult ) >= 1) {
            $bid = pg_fetch_array( $dbResult );
			$bids = $bid['average'];           
        }
        
        return new BidDatabaseResult(BidDatabaseResult::BID_FIND_SUCCESS, $bids);
    }
    
	public function findTotalBids() {
        $dbResult = pg_execute ( $this->dbcon, 'SQL_COUNT_TOTAL_BIDS', array (
        ) );

		$bids = 0;
        if (pg_affected_rows ( $dbResult ) >= 1) {
            $bid = pg_fetch_array( $dbResult );
			$bids = $bid['total'];           
        }
        
        return new BidDatabaseResult(BidDatabaseResult::BID_FIND_SUCCESS, $bids);
    }
	
}

?>