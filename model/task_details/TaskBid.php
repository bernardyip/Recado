<?php

include_once "/ConversionHelper.php";

class TaskBid {
    public $userId;
    public $taskId;
    public $username;
    public $amount;
    
    public function __construct($userId, $taskId, $username, $amount) {
        $this->userId = (int)$userId;
        $this->taskId = (int)$taskId;
        $this->username = trim($username);
        $this->amount = ConversionHelper::stringToMoney($amount);
    }
}
?>