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
        $this->username = trim($username);
        $this->password = trim($password);
        $this->email = trim($email);
        $this->phone = trim($phone);
        $this->name = trim($name);
        $this->bio = trim($bio);
        $this->createdTime = $createdTime;
        $this->lastLoggedIn = $lastLoggedIn;
        $this->role = trim($role);
    }
}
?>