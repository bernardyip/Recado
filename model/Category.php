<?php 
class Category {
    public $id;
    public $photo;
    public $name;
    public $description;
    
    public function __construct($id, $photo, $name, $description) {
        $this->id = (int)$id;
        $this->photo = trim($photo);
        $this->name = trim($name);
        $this->description = trim($description);
    }
}
?>