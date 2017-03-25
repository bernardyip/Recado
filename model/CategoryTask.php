<?php

class CategoryTask {

    public $category;
    public $tasks;
    public $totalTaskCount;

    public function __construct($category, $tasks, $totalTaskCount) {
        $this->category = $category;
        $this->tasks = $tasks;
        $this->totalTaskCount = $totalTaskCount;
    }
}

?>