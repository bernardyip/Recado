<?php

class TaskOverview {
    public $taskId;
    public $taskName;
    public $taskDisplayPicture;

    public function __construct($taskId, $taskName, $taskDisplayPicture) {
        $this->taskId = (int)$taskId;
        $this->taskName = trim($taskName);
        $this->taskDisplayPicture = trim($taskDisplayPicture);
    }
}

?>