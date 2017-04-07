<?php

include_once "/ConversionHelper.php";

class BidDetails {
	public $taskId;
	public $taskName;
	public $taskDescription;
	public $taskEndDate;
	public $bidPrice;
	
	public function __construct($taskId, $taskName, $taskDescription, $bidTimeEnd, $bidPrice) {
		$this->taskId = (int)$taskId;
		$this->taskName = trim($taskName);
		$this->taskDescription = trim($taskDescription);
		$this->taskEndDate = new DateTime($bidTimeEnd, new DateTimeZone('Asia/Singapore'));
		$this->bidPrice = ConversionHelper::stringToMoney($bidPrice);
	}
	
}
?>