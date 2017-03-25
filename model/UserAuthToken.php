<?php

class UserAuthToken {
    public $id;
    public $selector;
    public $validator;
    public $token;
    public $userid;
    public $expires;
    
    public function __construct($id, $selector, $validator, $token, $userid, $expires) {
        $this->id = $id;
        $this->selector = trim($selector);
        $this->validator = trim($validator);
        $this->token = trim($token);
        $this->userid = $userid;
        $this->expires = $expires;
    }
}
?>