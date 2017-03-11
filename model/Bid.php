<?php 
class Bid {
    public $id;
    public $amount;
    public $bidTime;
    public $selected;
    public $userId;
    public $taskId;
    
    public function __construct($id, $amount, $bidTime, $selected, $userId, $taskId) {
        $this->id = $id;
        $this->amount = $amount;
        $this->bidTime = $bidTime;
        $this->selected = $selected;
        $this->userId = $userId;
        $this->taskId = $taskId;
    }
}
?>