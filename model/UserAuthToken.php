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
        $this->selector = $selector;
        $this->validator = $validator;
        $this->token = $token;
        $this->userid = $userid;
        $this->expires = $expires;
    }
}
?>