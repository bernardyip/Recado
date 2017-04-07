<?php

include_once "/ConversionHelper.php";

class TaskDetail {
    public $id;
    public $name;
    public $description;
    public $postalCode;
    public $location;
    public $taskStartTime;
    public $taskEndTime;    
    public $listingPrice;
    public $updatedTime;
    public $categoryId;
    public $category;
    public $creator;
    public $creatorId;
    public $bidPicked;
    public $taskDisplayPicture;
    
    public function __construct($id, $name, $description, $postalCode, $location, 
            $taskStartTime, $taskEndTime, $listingPrice, $updatedTime, 
            $categoryId, $category, $creatorId, $creator, $bidPicked, $taskDisplayPicture) {
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
        
        if ($updatedTime instanceof DateTime) {
            $this->updatedTime = $updatedTime;
        } else {
            $this->updatedTime = new DateTime($updatedTime, new DateTimeZone('Asia/Singapore'));
        }
        $this->categoryId = (int)$categoryId;
        $this->category = trim($category);
        $this->creatorId = (int)$creatorId;
        $this->creator = trim($creator);
        $this->bidPicked = $bidPicked === "t";
        $this->taskDisplayPicture = trim($taskDisplayPicture);
    }
}
?>