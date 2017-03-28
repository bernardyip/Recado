<?php

class MyBid {
    public $taskId;
    public $taskName;
    public $bidPrice;
    public $bidTime;
    public $bidSelected;
    
    public function __construct($taskId, $taskName, $bidPrice, $bidTime, $bidSelected) {
        $this->taskId = (int)$taskId;
        $this->taskName = trim($taskName);
        $this->bidPrice = ConversionHelper::stringToMoney($bidPrice);
        $this->bidTime = new DateTime($bidTime, new DateTimeZone('Asia/Singapore'));
        $this->bidSelected = $bidSelected === "t";
    }
    
}