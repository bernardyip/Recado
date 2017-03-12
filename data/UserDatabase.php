<?php
include_once '/data/Database.php';
include_once '/model/User.php';

class UserDatabaseResult extends DatabaseResult {
    const LOGIN_SUCCESS = 10;
    const LOGIN_BAD_USERNAME_PASSWORD = 11;
    const LOGIN_UPDATE_LAST_LOGIN_SUCCESS = 12;
    const REGISTER_SUCCESS = 20;
    const REGISTER_USERNAME_TAKEN = 21;
    const REGISTER_FAILED = 29;
    
    public $user;
    
    public function __construct($status, $user) {
        $this->status = $status;
        $this->user = $user;
    }
}

class UserDatabase extends Database {
    
    // SQL Queries
    const SQL_LOGIN_SELECT_USER = "SELECT * FROM public.user WHERE username=$1 AND password=$2;";
    const SQL_LOGIN_UPDATE_LAST_LOGIN = "UPDATE public.user SET last_logged_in=$3 WHERE username=$1 AND password=$2;";
    const SQL_REGISTER_CREATE_USER = "INSERT INTO public.user (username, password, name, bio, created_time, last_logged_in, role) VALUES ($1, $2, $3, $4, $5, $6, 'user') RETURNING id;";
    const SQL_FIND_USER = "SELECT * FROM public.user WHERE username=$1;";
    
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