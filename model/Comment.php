<?php 
class Comment {
    public $id;
    public $comment;
    public $createdTime;
    public $userId;
    public $taskId;
    
    public function __construct($id, $comment, $createdTime, $userId, $taskId) {
        $this->id = $id;
        $this->comment = trim($comment);
        $this->createdTime = $createdTime;
        $this->userId = $userId;
        $this->taskId = $taskId;
    }
}
?>