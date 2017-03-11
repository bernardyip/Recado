<?php
include_once '/data/Database.php';
include_once '/model/User.php';

class UserDatabase extends Database {
    
    // SQL Queries
    const SQL_LOGIN_SELECT_USER = "SELECT * FROM public.user WHERE username=$1 AND password=$2;";
    const SQL_LOGIN_UPDATE_LAST_LOGIN = "UPDATE public.user SET last_logged_in=$3 WHERE username=$1 AND password=$2;";
    public function login($username, $password) {
        pg_prepare ( $this->dbcon, 'SQL_LOGIN_SELECT_USER', UserDatabase::SQL_LOGIN_SELECT_USER );
        $result = pg_execute ( $this->dbcon, 'SQL_LOGIN_SELECT_USER', array (
                $username,
                $password 
        ) );
        
        if (pg_affected_rows ( $result ) >= 1) {
            $user = pg_fetch_array ( $result );
            
            $user ['id'];
            $user ['username'];
            $user ['password'];
            $user ['email'];
            $user ['phone'];
            $user ['name'];
            $user ['bio'];
            $user ['created_time'];
            $user ['last_logged_in'];
            $user ['role'];
            
            return new User ( $user ['id'], $user ['username'], $user ['password'], $user ['email'], $user ['phone'], $user ['name'], $user ['bio'], $user ['created_time'], $user ['last_logged_in'], $user ['role'] );
        } else {
            return null;
        }
    }
    
    public function updateLastLogin($username, $password) {
        $current_datetime = (new DateTime ( null, new DateTimeZone ( "Asia/Singapore" ) ))->format ( 'Y-m-d\TH:i:s\Z' );
        pg_prepare ( $this->dbcon, 'SQL_LOGIN_UPDATE_LAST_LOGIN', UserDatabase::SQL_LOGIN_UPDATE_LAST_LOGIN );
        $result = pg_execute ( $this->dbcon, 'SQL_LOGIN_UPDATE_LAST_LOGIN', array (
                $username,
                $password,
                $current_datetime 
        ) );
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