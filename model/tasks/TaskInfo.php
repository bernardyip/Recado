<?php

class TaskInfo {
    public $taskId;
    public $taskName;
    public $taskDescription;
    public $taskDisplayPicture;
    public $taskCreatorId;
    public $taskCreatorUsername;
    
    public function __construct($taskId, $taskName, $taskDescription, $taskDisplayPicture, $taskCreatorId, $taskCreatorUsername) {
        $this->taskId = (int)$taskId;
        $this->taskName = trim($taskName);
        $this->taskDescription = trim($taskDescription);
        $this->taskDisplayPicture = trim($taskDisplayPicture);
        $this->taskCreatorId = (int)$taskCreatorId;
        $this->taskCreatorUsername = trim($taskCreatorUsername);
    }
}
?>