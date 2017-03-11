<?php 
class Category {
    public $id;
    public $photo;
    public $name;
    public $description;
    
    public function __construct($id, $photo, $name, $description) {
        $this->id = $id;
        $this->photo = $photo;
        $this->name = $name;
        $this->description = $description;
    }
}
?>