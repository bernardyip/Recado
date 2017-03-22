<?php 

include_once "/ConversionHelper.php";

class Bid {
    public $id;
    public $amount;
    public $bidTime;
    public $selected;
    public $userId;
    public $taskId;
    
    public function __construct($id, $amount, $bidTime, $selected, $userId, $taskId) {
        $this->id = (int)$id;
        $this->amount = ConversionHelper::stringToMoney($amount);
        $this->bidTime = new DateTime($bidTime, new DateTimeZone('Asia/Singapore'));
        $this->selected = $selected === "t";
        $this->userId = (int)$userId;
        $this->taskId = (int)$taskId;
    }
}
?>