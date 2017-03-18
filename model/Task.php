<?php
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
        $this->id = $id;
        $this->name = trim($name);
        $this->description = trim($description);
        $this->postalCode = $postalCode;
        $this->location = trim($location);
        $this->taskStartTime = $taskStartTime;
        $this->taskEndTime = $taskEndTime;
        $this->listingPrice = $listingPrice;
        $this->createdTime = $createdTime;
        $this->updatedTime = $updatedTime;
        $this->status = trim($status);
        $this->bidPicked = $bidPicked;
        $this->categoryId = $categoryId;
        $this->creatorId = $creatorId;
    }
}
?>