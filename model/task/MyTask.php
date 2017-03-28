<?php

class MyTask {
    public $taskId;
    public $taskName;
    public $taskListingPrice;
    public $maxBidAmount;
    public $maxBidUserId;
    public $maxBidUser;
    
    public function __construct($taskId, $taskName, $taskListingPrice, $maxBidAmount, $maxBidUserId, $maxBidUser) {
        $this->taskId = (int)$taskId;
        $this->taskName = trim($taskName);
        $this->taskListingPrice = ConversionHelper::stringToMoney($taskListingPrice);
        $this->maxBidAmount = ConversionHelper::stringToMoney($maxBidAmount);
        $this->maxBidUserId = (int)$maxBidUserId;
        $this->maxBidUser = trim($maxBidUser);
    }
    
}