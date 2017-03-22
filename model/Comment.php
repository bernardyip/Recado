<?php 
class Comment {
    public $id;
    public $comment;
    public $createdTime;
    public $userId;
    public $taskId;
    
    public function __construct($id, $comment, $createdTime, $userId, $taskId) {
        $this->id = (int)$id;
        $this->comment = trim($comment);
        $this->createdTime = new DateTime($createdTime, new DateTimeZone('Asia/Singapore'));
        $this->userId = (int)$userId;
        $this->taskId = (int)$taskId;
    }
}
?>