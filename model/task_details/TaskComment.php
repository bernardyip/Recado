<?php

include_once "/ConversionHelper.php";

class TaskComment {
    public $id;
    public $comment;
    public $createdTime;
    public $username;
    
    public function __construct($id, $comment, $createdTime, $username) {
        $this->id = (int)$id;
        $this->comment = trim($comment);
        
        if ($createdTime instanceof DateTime) {
            $this->createdTime = $taskStartTime;
        } else {
            $this->createdTime = new DateTime($createdTime, new DateTimeZone('Asia/Singapore'));
        }
        
        $this->username = trim($username);
    }
}
?>