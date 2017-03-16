<?php
include_once '/data/Database.php';
include_once '/model/User.php';
include_once '/model/UserAuthToken.php';

class UserDatabaseResult extends DatabaseResult {
    const LOGIN_SUCCESS = 10;
    const LOGIN_BAD_USERNAME_PASSWORD = 11;
    const LOGIN_UPDATE_LAST_LOGIN_SUCCESS = 12;
    const REGISTER_SUCCESS = 20;
    const REGISTER_USERNAME_TAKEN = 21;
    const REGISTER_FAILED = 29;
    const AUTH_CREATE_SUCCESS = 30;
    const AUTH_CREATE_FAIL = 39;
    const AUTH_FIND_SUCCESS = 31;
    const AUTH_FIND_FAIL = 38;
    const AUTH_DELETE_SUCCESS = 32;
    const AUTH_DELETE_FAIL = 37;
    
    public $user;
    public $auth;
    
    public function __construct($status, $user, $auth) {
        $this->status = $status;
        $this->user = $user;
        $this->auth = $auth;
    }
}


class UserDatabase extends Database {
    
    // Constatns
    const SELECTOR_LENGTH = 12;
    
    // SQL Queries
    const SQL_LOGIN_SELECT_USER = "SELECT * FROM public.user WHERE username=$1 AND password=$2;";
    const SQL_LOGIN_UPDATE_LAST_LOGIN = "UPDATE public.user SET last_logged_in=$3 WHERE username=$1 AND password=$2;";
    const SQL_REGISTER_CREATE_USER = "INSERT INTO public.user (username, password, name, bio, created_time, last_logged_in, role) VALUES ($1, $2, $3, $4, $5, $6, 'user') RETURNING id;";
    const SQL_FIND_USER = "SELECT * FROM public.user WHERE username=$1;";
    const SQL_FIND_USERID = "SELECT * FROM public.user WHERE id=$1";
    const SQL_FIND_USER_FROM_AUTH = "SELECT * FROM public.user_auth_tokens WHERE selector=$1 AND token=$2 AND expires <= NOW();";
    const SQL_CREATE_USER_AUTH = "INSERT INTO public.user_auth_tokens(selector, token, userid, expires) VALUES(random_string($1), $2, $3, $4) RETURNING id, selector;";
    const SQL_CREATE_USER_AUTH_T = "INSERT INTO public.user_auth_tokens(selector, token, userid, expires) VALUES($1, $2, $3, $4) RETURNING id, selector;";
    
    public function login($username, $password) {
        pg_prepare ( $this->dbcon, 'SQL_LOGIN_SELECT_USER', UserDatabase::SQL_LOGIN_SELECT_USER );
        $dbResult = pg_execute ( $this->dbcon, 'SQL_LOGIN_SELECT_USER', array (
                $username,
                $password 
        ) );
        
        if (pg_affected_rows ( $dbResult ) >= 1) {
            $user = pg_fetch_array ( $dbResult );
            return new UserDatabaseResult(
                    UserDatabaseResult::LOGIN_SUCCESS, 
                    new User ( $user ['id'], $user ['username'], $user ['password'], 
                                $user ['email'], $user ['phone'], $user ['name'], 
                                $user ['bio'], $user ['created_time'], $user ['last_logged_in'], 
                                $user ['role'] ));
        } else {
            return new UserDatabaseResult(
                    UserDatabaseResult::LOGIN_BAD_USERNAME_PASSWORD, 
                    null);
        }
    }
    
    public function updateLastLogin($username, $password) {
        $current_datetime = (new DateTime ( null, new DateTimeZone ( "Asia/Singapore" ) ))->format ( 'Y-m-d\TH:i:s\Z' );
        pg_prepare ( $this->dbcon, 'SQL_LOGIN_UPDATE_LAST_LOGIN', UserDatabase::SQL_LOGIN_UPDATE_LAST_LOGIN );
        $dbResult = pg_execute ( $this->dbcon, 'SQL_LOGIN_UPDATE_LAST_LOGIN', array (
                $username,
                $password,
                $current_datetime ) );
        
        // always successful
        return new UserDatabaseResult(UserDatabaseResult::LOGIN_UPDATE_LAST_LOGIN_SUCCESS, null);
    }

    public function userExists($username) {
        pg_prepare ( $this->dbcon, 'SQL_FIND_USER', UserDatabase::SQL_FIND_USER );
        $dbResult = pg_execute ( $this->dbcon, 'SQL_FIND_USER', array (
                $username ) );
        return pg_affected_rows ( $dbResult ) >= 1;
    }
    
    public function register($username, $password, $name, $bio) {
        if ($this->userExists($username)) {
            return new UserDatabaseResult(UserDatabaseResult::REGISTER_USERNAME_TAKEN, null);
        } else {
            $current_datetime = (new DateTime ( null, new DateTimeZone ( "Asia/Singapore" ) ))->format ( 'Y-m-d\TH:i:s\Z' );
            pg_prepare ( $this->dbcon, 'SQL_REGISTER_CREATE_USER', UserDatabase::SQL_REGISTER_CREATE_USER );
            $dbResult = pg_execute ( $this->dbcon, 'SQL_REGISTER_CREATE_USER', array (
                    $username,
                    $password,
                    $name,
                    $bio,
                    $current_datetime,
                    $current_datetime ) );
            
            if (pg_affected_rows ( $dbResult ) >= 1) {
                $user = pg_fetch_array ( $dbResult );
                return new UserDatabaseResult(
                        UserDatabaseResult::REGISTER_SUCCESS, 
                        new User($user['id'], $username, $password, "", "", $name, $bio, $current_datetime, $current_datetime, "user"));
            } else {
                return new UserDatabaseResult(UserDatabaseResult::REGISTER_FAILED, null);
            }
        }
        return $result;
    }
    
    public function createAuthCookie($user, $validatorLength) {

        $expires = new DateTime ( null, new DateTimeZone ( "Asia/Singapore" ) );
        $expires->add(new DateInterval("P7D"));
        $validator = $this->generateRandomString($validatorLength);
        $token = hash("sha256", $validator);
        $auth = new UserAuthToken(-1, "", $validator, $token, $user->id, $expires);
        
        pg_prepare ( $this->dbcon, 'SQL_CREATE_USER_AUTH', UserDatabase::SQL_CREATE_USER_AUTH );
        
        $threshold = 10;
        $attempts = 0;
        $dbResult = null;
        
        do {
            $dbResult = pg_execute ( $this->dbcon, 'SQL_CREATE_USER_AUTH', array (
                    UserDatabase::SELECTOR_LENGTH, // SELECTOR_LENGTH
                    $auth->token,
                    $auth->userid,
                    $auth->expires->format ( 'Y-m-d\TH:i:s\Z' ) ) );
            
            if (pg_affected_rows ( $dbResult ) >= 1) {
    
                $authResult = pg_fetch_array ( $dbResult );
                $auth->id = $authResult['id'];
                $auth->selector = $authResult['selector'];
    
                $result = new UserDatabaseResult(
                        UserDatabaseResult::AUTH_CREATE_SUCCESS,
                        $user,
                        $auth);
                break;
            } else {
                $result = new UserDatabaseResult(
                        UserDatabaseResult::AUTH_CREATE_FAIL,
                        $user,
                        $auth);
                $attempts++;
            }
        } while ($attempts < $threshold);
        
        return $result;
    }
    
    public function findUserFromAuthCookie($selector, $validator) {
        
        $token = hash("sha256", $validator);
        $auth = new UserAuthToken(-1, $selector, $validator, $token, -1, null);
        
        pg_prepare ( $this->dbcon, 'SQL_FIND_USER_FROM_AUTH', UserDatabase::SQL_FIND_USER_FROM_AUTH );
        $dbResult = pg_execute ( $this->dbcon, 'SQL_FIND_USER_FROM_AUTH', array (
                $selector, 
                $token ) );
        
        if (pg_affected_rows ( $dbResult ) >= 1) {

            $authResult = pg_fetch_array ( $dbResult );
            $auth->expires = $authResult['expires'];
            $auth->id = $authResult['id'];
            $auth->userid = $authResult['userid'];
            
            $userid = $authResult['userid'];
            $user = $this->findUserFromId($userid);
            
            if (!is_null($user)) {
                return new UserDatabaseResult(
                        UserDatabaseResult::AUTH_FIND_SUCCESS, 
                        $user,
                        $auth);
            } else {
                return new UserDatabaseResult(
                        UserDatabaseResult::AUTH_FIND_FAIL, 
                        null, 
                        $auth);
            }
        } else {
            return new UserDatabaseResult(
                    UserDatabaseResult::AUTH_FIND_FAIL, 
                    null, 
                    $auth);
        }
    }
    
    private function generateRandomString($length = 10) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $index = rand(0, $charactersLength - 1);
            $randomString .= $characters[$index];
        }
        return $randomString;
    }
    
    private function findUserFromId($id) {

        pg_prepare ( $this->dbcon, 'SQL_FIND_USERID', UserDatabase::SQL_FIND_USERID );
        $dbResult = pg_execute ( $this->dbcon, 'SQL_LOGIN_SELECT_USER', array (
                $id ) );
        if (pg_affected_rows ( $dbResult ) >= 1) {
            $user = pg_fetch_array ( $dbResult );
            return new User ( $user ['id'], $user ['username'], $user ['password'], 
                                $user ['email'], $user ['phone'], $user ['name'], 
                                $user ['bio'], $user ['created_time'], $user ['last_logged_in'], 
                                $user ['role'] );
        } else {
            return null;
        }
        
    }
    /*
     * public function getUsers() {
     * $sqlGetUser = "SELECT * FROM users";
     * $result = $this->dbcon->prepare ($sqlGetUser);
     * $result->execute ();
     * return json_encode ( $result->fetchAll () );
     * }
     * public function add($user) {
     * $result = $this->dbcon->prepare ( "INSERT INTO users(name, email, mobile, address) VALUES (?, ?, ?, ?)" );
     * $result->execute ( array (
     * $user->name,
     * $user->email,
     * $user->mobile,
     * $user->address
     * ) );
     * return json_encode ( $this->dbcon->lastInsertId () );
     * }
     * public function delete($user) {
     * $result = $this->dbcon->prepare ( "DELETE FROM users WHERE id=?" );
     * $result->execute ( array (
     * $user->id
     * ) );
     * return json_encode ( 1 );
     * }
     * public function updateValue($user) {
     * $result = $this->dbcon->prepare ( "UPDATE users SET " . $user->field . "=? WHERE id=?" );
     * $result->execute ( array (
     * $user->newvalue,
     * $user->id
     * ) );
     * return json_encode ( 1 );
     * }
     */
}

?>