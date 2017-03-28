<?php

class UserAuthToken {
    public $id;
    public $selector;
    public $validator;
    public $token;
    public $userid;
    public $expires;
    
    public function __construct($id, $selector, $validator, $token, $userid, $expires) {
        $this->id = (int)$id;
        $this->selector = trim($selector);
        $this->validator = trim($validator);
        $this->token = trim($token);
        $this->userid = (int)$userid;
        if ($expires instanceof DateTime) {
            $this->expires = $expires;
        } else {
            $this->expires = new DateTime($expires, new DateTimeZone('Asia/Singapore'));
        }
    }
}
?>