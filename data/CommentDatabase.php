<?php
include_once '/data/Database.php';
include_once '/model/Comment.php';
include_once '/model/task_details/TaskComment.php';

class CommentDatabaseResult extends DatabaseResult {
    const COMMENT_FIND_SUCCESS = 10;
    const COMMENT_FIND_FAIL = 19;
    const COMMENT_CREATE_SUCCESS = 11;
    const COMMENT_CREATE_FAIL = 18;
    const COMMENT_UPDATE_SUCCESS = 12;
    const COMMENT_UPDATE_FAIL = 17;
    const COMMENT_DELETE_SUCCESS = 13;
    const COMMENT_DELETE_FAIL = 16;
    
    public $comments;
    
    public function __construct($status, $comments) {
        $this->status = $status;
        $this->comments = $comments;
    }
}


class CommentDatabase extends Database {
    
    // SQL Queries
    const SQL_TASKDETAILS_FIND_COMMENTS_WITH_TASKID = "" .
            "SELECT c.id, c.comment, c.created_time, c.task_id, u.id AS user_id, u.username " .
            "FROM public.comment c " .
            "INNER JOIN public.user u ON c.user_id = u.id " .
            "WHERE c.task_id=$1 " .
            "ORDER BY c.created_time DESC;";
    
    const SQL_TASKDETAILS_FIND_COMMENT = "" .
            "SELECT c.id, c.comment, c.created_time, c.task_id, u.id AS user_id, u.username " .
            "FROM public.comment c " .
            "INNER JOIN public.user u ON c.user_id = u.id " .
            "WHERE c.id=$1;";
    
    const SQL_TASKDETAILS_ADD_COMMENT_FOR_TASKID_BY_USERID = "" .
            "INSERT INTO public.comment (comment, created_time, user_id, task_id) VALUES " .
            "($1, $2, $3, $4);";
    
    const SQL_TASKDETAILS_EDIT_COMMENT = "" .
            "UPDATE public.comment SET comment=$1, created_time=$2 " .
            "WHERE id=$3;";
    
    const SQL_TASKDETAILS_DELETE_COMMENT = "" .
            "DELETE FROM public.comment WHERE id=$1;";
    
    const SQL_FIND_COMMENTS_WITH_TASKID_WITH_LIMIT = "SELECT * FROM public.comment c WHERE c.task_id=$1 LIMIT $2;";
    const SQL_FIND_COMMENTS_WITH_TASKID = "SELECT * FROM public.comment c WHERE c.task_id=$1;";
    
    public function __construct() {
        parent::__construct();
        pg_prepare ( $this->dbcon, 'SQL_FIND_COMMENTS_WITH_TASKID_WITH_LIMIT', CommentDatabase::SQL_FIND_COMMENTS_WITH_TASKID_WITH_LIMIT );
        pg_prepare ( $this->dbcon, 'SQL_FIND_COMMENTS_WITH_TASKID', CommentDatabase::SQL_FIND_COMMENTS_WITH_TASKID );
        pg_prepare ( $this->dbcon, 'SQL_TASKDETAILS_FIND_COMMENTS_WITH_TASKID', CommentDatabase::SQL_TASKDETAILS_FIND_COMMENTS_WITH_TASKID );
        pg_prepare ( $this->dbcon, 'SQL_TASKDETAILS_ADD_COMMENT_FOR_TASKID_BY_USERID', CommentDatabase::SQL_TASKDETAILS_ADD_COMMENT_FOR_TASKID_BY_USERID );
        pg_prepare ( $this->dbcon, 'SQL_TASKDETAILS_FIND_COMMENT', CommentDatabase::SQL_TASKDETAILS_FIND_COMMENT );
        pg_prepare ( $this->dbcon, 'SQL_TASKDETAILS_EDIT_COMMENT', CommentDatabase::SQL_TASKDETAILS_EDIT_COMMENT );
        pg_prepare ( $this->dbcon, 'SQL_TASKDETAILS_DELETE_COMMENT', CommentDatabase::SQL_TASKDETAILS_DELETE_COMMENT );
    }
    
    public function taskDetails_addComment($taskId, $userId, $comment) {

        $current_datetime = (new DateTime ( null, new DateTimeZone ( "Asia/Singapore" ) ))->format ( 'Y-m-d\TH:i:s\Z' );
        $dbResult = pg_execute ( $this->dbcon, 'SQL_TASKDETAILS_ADD_COMMENT_FOR_TASKID_BY_USERID', array (
                $comment,
                $current_datetime,
                $userId,
                $taskId ) );

        $nrRows = pg_affected_rows ( $dbResult );
        if ($nrRows >= 1) {
            return new BidDatabaseResult(CommentDatabaseResult::COMMENT_CREATE_SUCCESS, null);
        } else {
            return new BidDatabaseResult(CommentDatabaseResult::COMMENT_CREATE_FAIL, null);
        }
    }
    
    public function taskDetails_editComment($commentId, $newComment) {

        $current_datetime = (new DateTime ( null, new DateTimeZone ( "Asia/Singapore" ) ))->format ( 'Y-m-d\TH:i:s\Z' );
        $dbResult = pg_execute ( $this->dbcon, 'SQL_TASKDETAILS_EDIT_COMMENT', array (
                $newComment,
                $current_datetime,
                $commentId
        ) );

        $nrRows = pg_affected_rows ( $dbResult );
        if ($nrRows >= 1) {
            return new BidDatabaseResult(CommentDatabaseResult::COMMENT_UPDATE_SUCCESS, null);
        } else {
            return new BidDatabaseResult(CommentDatabaseResult::COMMENT_UPDATE_FAIL, null);
        }
    }
    
    public function taskDetails_deleteComment($commentId) {

        $dbResult = pg_execute ( $this->dbcon, 'SQL_TASKDETAILS_DELETE_COMMENT', array (
                $commentId
        ) );

        $nrRows = pg_affected_rows ( $dbResult );
        if ($nrRows >= 1) {
            return new BidDatabaseResult(CommentDatabaseResult::COMMENT_DELETE_SUCCESS, null);
        } else {
            return new BidDatabaseResult(CommentDatabaseResult::COMMENT_DELETE_FAIL, null);
        }
    }
    
    public function taskDetails_getComment($commentId) {

        $dbResult = pg_execute ( $this->dbcon, 'SQL_TASKDETAILS_FIND_COMMENT', array (
                $commentId
        ) );

        $comments = null;
        $nrRows = pg_affected_rows ( $dbResult );
        if ($nrRows >= 1) {
            $comments = array();
            for ($i = 0; $i < $nrRows; $i++) {
                $comment = pg_fetch_array( $dbResult );
                $comments[$i] = new TaskComment($comment['id'], $comment['comment'], 
                        $comment['created_time'], $comment['user_id'], $comment['task_id'], $comment['username']);
            }
            return new CommentDatabaseResult(CommentDatabaseResult::COMMENT_FIND_SUCCESS, $comments);
        }
        
        return new CommentDatabaseResult(CommentDatabaseResult::COMMENT_FIND_FAIL, null);
    }
    
    public function taskDetails_getComments($taskId) {
        
        $dbResult = null;
        if (false) {
            // NOT IMPLEMENTED
            $dbResult = pg_execute ( $this->dbcon, '', array (
                    $taskId,
                    $limitTo
            ) );
        } else {
            $dbResult = pg_execute ( $this->dbcon, 'SQL_TASKDETAILS_FIND_COMMENTS_WITH_TASKID', array (
                    $taskId
            ) );
        }

        $comments = null;
        $nrRows = pg_affected_rows ( $dbResult );
        if ($nrRows >= 1) {
            $comments = array();
            for ($i = 0; $i < $nrRows; $i++) {
                $comment = pg_fetch_array( $dbResult );
                $comments[$i] = new TaskComment($comment['id'], $comment['comment'], 
                        $comment['created_time'], $comment['user_id'], $comment['task_id'], $comment['username']);
            }
        }
        
        return new CommentDatabaseResult(CommentDatabaseResult::COMMENT_FIND_SUCCESS, $comments);
    }
    
    public function findCommentsWithTaskId($taskId, $limitTo = 0) {
        
        $dbResult = null;
        if ($limitTo > 0) {
            $dbResult = pg_execute ( $this->dbcon, 'SQL_FIND_COMMENTS_WITH_TASKID_WITH_LIMIT', array (
                    $taskId,
                    $limitTo
            ) );
        } else {
            $dbResult = pg_execute ( $this->dbcon, 'SQL_FIND_COMMENTS_WITH_TASKID', array (
                    $taskId
            ) );
        }

        $comments = null;
        $nrRows = pg_affected_rows ( $dbResult );
        if ($nrRows >= 1) {
            $comments = array();
            for ($i = 0; $i < $nrRows; $i++) {
                $comment = pg_fetch_array( $dbResult );
                $comments[$i] = new Comment($comment['id'], $comment['comment'], $comment['created_time'], $comment['user_id'], $comment['task_id']);
            }
        }
        
        return new CommentDatabaseResult(CommentDatabaseResult::COMMENT_FIND_SUCCESS, $comments);
    }
    
}

?>