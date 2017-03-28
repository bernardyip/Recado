<?php

class MyTaskInfo {
    public $taskId;
    public $taskName;
    public $taskDescription;
    public $taskStartDate;
    public $taskDisplayPicture;
    public $taskMaxBidderId;
    public $taskMaxBidderUsername;
    public $taskMaxBid;
    
    public function __construct($taskId, $taskName, $taskDescription, $taskStartDate, $taskDisplayPicture, $taskMaxBidderId, $taskMaxBidderUsername, $taskMaxBid) {
        $this->taskId = (int)$taskId;
        $this->taskName = trim($taskName);
        $this->taskDescription = trim($taskDescription);
        if ($taskStartDate instanceof DateTime) {
            $this->taskStartDate = $taskStartDate;
        } else {
            $this->taskStartDate = new DateTime($taskStartDate, new DateTimeZone('Asia/Singapore'));
        }
        $this->taskDisplayPicture = trim($taskDisplayPicture);
        if (is_null($taskMaxBidderId)) {
            $this->taskMaxBidderId = 0;
            $this->taskMaxBid = 0;
        } else {
            $this->taskMaxBidderId = (int)$taskMaxBidderId;
            $this->taskMaxBidderUsername = (int)$taskMaxBidderUsername;
            $this->taskMaxBid = ConversionHelper::stringToMoney($taskMaxBid);
        }
    }
}
?>