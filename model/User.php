<?php

class User {
    public $id;
    public $username;
    public $password;
    public $email;
    public $phone;
    public $name;
    public $bio;
    public $createdTime;
    public $lastLoggedIn;
    public $role;
    
    public function __construct($id, $username, $password, $email, $phone, $name, $bio, $createdTime, $lastLoggedIn, $role) {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->email = $email;
        $this->phone = $phone;
        $this->name = $name;
        $this->bio = $bio;
        $this->createdTime = $createdTime;
        $this->lastLoggedIn = $lastLoggedIn;
        $this->role = $role;
    }
}
?>