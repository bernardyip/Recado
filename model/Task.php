<?php

include_once "/ConversionHelper.php";

class Task {
    public $id;
    public $name;
    public $description;
    public $postalCode;
    public $location;
    public $taskStartTime;
    public $taskEndTime;
    public $listingPrice;
    public $createdTime;
    public $updatedTime;
    public $status;
    public $bidPicked;
    public $categoryId;
    public $creatorId;

    public function __construct($id, $name, $description, $postalCode, $location,
            $taskStartTime, $taskEndTime, $listingPrice, $createdTime, $updatedTime,
            $status, $bidPicked, $categoryId, $creatorId) {
                $this->id = (int)$id;
                $this->name = trim($name);
                $this->description = trim($description);
                $this->postalCode = (int)$postalCode;
                $this->location = trim($location);

                if ($taskStartTime instanceof DateTime) {
                    $this->taskStartTime = $taskStartTime;
                } else {
                    $this->taskStartTime = new DateTime($taskStartTime, new DateTimeZone('Asia/Singapore'));
                }

                if ($taskEndTime instanceof DateTime) {
                    $this->taskEndTime = $taskEndTime;
                } else {
                    $this->taskEndTime = new DateTime($taskEndTime, new DateTimeZone('Asia/Singapore'));
                }

                $this->listingPrice = ConversionHelper::stringToMoney($listingPrice);

                if ($createdTime instanceof DateTime) {
                    $this->createdTime = $createdTime;
                } else {
                    $this->createdTime = new DateTime($createdTime, new DateTimeZone('Asia/Singapore'));
                }

                if ($updatedTime instanceof DateTime) {
                    $this->updatedTime = $updatedTime;
                } else {
                    $this->updatedTime = new DateTime($updatedTime, new DateTimeZone('Asia/Singapore'));
                }
                $this->status = trim($status);
                $this->bidPicked = $bidPicked === "t";
                $this->categoryId = (int)$categoryId;
                $this->creatorId = (int)$creatorId;
    }
}
?>