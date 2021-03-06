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
    const PROFILE_UPDATE_SUCCESS = 24;
    const PROFILE_UPDATE_FAILED = 33;
    const PROFILE_RETRIEVE_SUCCESS = 26;
    const PROFILE_RETRIEVE_FAILURE = 35;
    
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
    const SQL_LOGIN_SELECT_USER = "SELECT * FROM public.user u WHERE u.username=$1 AND u.password=$2;";
    const SQL_LOGIN_UPDATE_LAST_LOGIN = "UPDATE public.user SET last_logged_in=$3 WHERE username=$1 AND password=$2;";
    const SQL_REGISTER_CREATE_USER = "INSERT INTO public.user (username, password, email, phone, name, bio, created_time, last_logged_in, role) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, 'user') RETURNING id;";
    const SQL_PROFILE_UPDATE_USER = "UPDATE public.user SET bio=$3,email=$4,phone=$5,name=$6 WHERE username=$1 AND password=$2;";
    const SQL_FIND_USER = "SELECT * FROM public.user u WHERE u.username=$1;";
    const SQL_FIND_USERID = "SELECT * FROM public.user u WHERE u.id=$1;";
    const SQL_FIND_USER_FROM_AUTH = "SELECT * FROM public.user_auth_tokens t WHERE t.selector=$1 AND t.token=$2 AND t.expires >= NOW();";
    const SQL_CREATE_USER_AUTH = "INSERT INTO public.user_auth_tokens(selector, token, userid, expires) VALUES(random_string($1), $2, $3, $4) RETURNING id, selector;";
	const SQL_COUNT_ONLINE_USERS = "SELECT COUNT(*) AS online FROM public.user_auth_tokens;";
	const SQL_COUNT_TOTAL_USERS = "SELECT COUNT(*) AS total FROM public.user;";
    
    public function __construct() {
        parent::__construct();
        pg_prepare ( $this->dbcon, 'SQL_LOGIN_SELECT_USER', UserDatabase::SQL_LOGIN_SELECT_USER );
        pg_prepare ( $this->dbcon, 'SQL_LOGIN_UPDATE_LAST_LOGIN', UserDatabase::SQL_LOGIN_UPDATE_LAST_LOGIN );        
        pg_prepare ( $this->dbcon, 'SQL_FIND_USER', UserDatabase::SQL_FIND_USER );
        pg_prepare ( $this->dbcon, 'SQL_REGISTER_CREATE_USER', UserDatabase::SQL_REGISTER_CREATE_USER );
        pg_prepare ( $this->dbcon, 'SQL_PROFILE_UPDATE_USER', UserDatabase::SQL_PROFILE_UPDATE_USER );
        pg_prepare ( $this->dbcon, 'SQL_CREATE_USER_AUTH', UserDatabase::SQL_CREATE_USER_AUTH );
        pg_prepare ( $this->dbcon, 'SQL_FIND_USER_FROM_AUTH', UserDatabase::SQL_FIND_USER_FROM_AUTH );
        pg_prepare ( $this->dbcon, 'SQL_FIND_USERID', UserDatabase::SQL_FIND_USERID );
		pg_prepare ( $this->dbcon, 'SQL_COUNT_ONLINE_USERS', UserDatabase::SQL_COUNT_ONLINE_USERS );
		pg_prepare ( $this->dbcon, 'SQL_COUNT_TOTAL_USERS', UserDatabase::SQL_COUNT_TOTAL_USERS );
    }
    
    public function login($username, $password) {
        $_username = pg_escape_string ( $username );
        $_password = pg_escape_string ( $password );
        
        $dbResult = pg_execute ( $this->dbcon, 'SQL_LOGIN_SELECT_USER', array (
                $_username,
                $_password
        ) );
        
        if (pg_affected_rows ( $dbResult ) >= 1) {
            $user = pg_fetch_array ( $dbResult );
            return new UserDatabaseResult(
                    UserDatabaseResult::LOGIN_SUCCESS, 
                    new User ( $user ['id'], $user ['username'], $user ['password'], 
                                $user ['email'], $user ['phone'], $user ['name'], 
                                $user ['bio'], $user ['created_time'], $user ['last_logged_in'], 
                                $user ['role'] ),
                    null);
        } else {
            return new UserDatabaseResult(
                    UserDatabaseResult::LOGIN_BAD_USERNAME_PASSWORD, 
                    null,
                    null);
        }
    }
    
    public function updateLastLogin($username, $password) {
        $_username = pg_escape_string ( $username );
        $_password = pg_escape_string ( $password );
        
        $current_datetime = (new DateTime ( null, new DateTimeZone ( "Asia/Singapore" ) ))->format ( 'Y-m-d\TH:i:s\Z' );
        $dbResult = pg_execute ( $this->dbcon, 'SQL_LOGIN_UPDATE_LAST_LOGIN', array (
                $_username,
                $_password,
                $current_datetime ) );
        
        // always successful
        return new UserDatabaseResult(UserDatabaseResult::LOGIN_UPDATE_LAST_LOGIN_SUCCESS, null, null);
    }

    public function userExists($username) {
        $_username = pg_escape_string ( $username );

        $dbResult = pg_execute ( $this->dbcon, 'SQL_FIND_USER', array (
                $_username ) );
        return pg_affected_rows ( $dbResult ) >= 1;
    }
    
    public function register($username, $password, $email, $phone, $name, $bio) {
        $_username = pg_escape_string ( $username );
        $_password = pg_escape_string ( $password );
        $_name = pg_escape_string ( $name );
        $_bio = pg_escape_string ( $bio );
        $_email = pg_escape_string( $email );
        $_phone = pg_escape_string( $phone );
        
        if ($this->userExists($username)) {
            return new UserDatabaseResult(UserDatabaseResult::REGISTER_USERNAME_TAKEN, null, null);
        } else {
            $current_datetime = (new DateTime ( null, new DateTimeZone ( "Asia/Singapore" ) ))->format ( 'Y-m-d\TH:i:s\Z' );
            $dbResult = pg_execute ( $this->dbcon, 'SQL_REGISTER_CREATE_USER', array (
                    $_username,
                    $_password,
                    $_email,
                    $_phone,
                    $_name,
                    $_bio,
                    $current_datetime,
                    $current_datetime ) );
            
            if (pg_affected_rows ( $dbResult ) >= 1) {
                $user = pg_fetch_array ( $dbResult );
                return new UserDatabaseResult(
                        UserDatabaseResult::REGISTER_SUCCESS, 
                        new User($user['id'], $username, $password, "", "", $name, $bio, $current_datetime, $current_datetime, "user"),
                        null);
            } else {
                return new UserDatabaseResult(UserDatabaseResult::REGISTER_FAILED, null, null);
            }
        }
        return $result;
    }

    public function getUser($username) {
    	$_username = pg_escape_string ( $username );
    	
    	$dbResult = pg_execute ( $this->dbcon, 'SQL_FIND_USER', array (
    			$_username ) );
    	if (pg_affected_rows ( $dbResult ) >= 1) {
    		$user = pg_fetch_array ( $dbResult );
    		return new UserDatabaseResult(UserDatabaseResult::PROFILE_RETRIEVE_SUCCESS, $user, null);
    	} else {
    		return new UserDatabaseResult(UserDatabaseResult::PROFILE_RETRIEVE_FAILURE, $user, null);
    	}
    }
    
    public function update($username, $password, $name, $phone, $bio, $email) {
    	$_username = pg_escape_string ( $username );
    	$_password = pg_escape_string ( $password );
    	$_name = pg_escape_string ( $name );
    	$_phone = pg_escape_string ( $phone );
    	$_bio = pg_escape_string ( $bio );
    	$_email = pg_escape_string ( $email );
    
    	$dbResult = pg_execute ( $this->dbcon, 'SQL_PROFILE_UPDATE_USER', array (
    			$_username,
    			$_password,
    			$_bio,
    			$_email,
    			$_phone,
    			$_name ) );
    
    	if (pg_affected_rows ( $dbResult ) >= 1) {
    		$user = pg_fetch_array ( $dbResult );
    		return new UserDatabaseResult(
    				UserDatabaseResult::PROFILE_UPDATE_SUCCESS,
    				new User($user['id'], $username, $password, $email, $phone, $name, $bio, $current_datetime, $current_datetime, "user"),
    				null);
    	} else {
    		return new UserDatabaseResult(UserDatabaseResult::PROFILE_UPDATE_FAILED, null, null);
    	}
    	
    	return $result;
    }
    
    public function createAuthCookie($user, $validatorLength) {

        $expires = new DateTime ( null, new DateTimeZone ( "Asia/Singapore" ) );
        $expires->add(new DateInterval("P7D"));
        $validator = $this->generateRandomString($validatorLength);
        $token = hash("sha256", $validator);
        $auth = new UserAuthToken(-1, "", $validator, $token, $user->id, $expires);
        
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
        $_selector = pg_escape_string ( $selector );
        
        $token = hash("sha256", $validator);
        $auth = new UserAuthToken(-1, $selector, $validator, $token, -1, null);
        
        $dbResult = pg_execute ( $this->dbcon, 'SQL_FIND_USER_FROM_AUTH', array (
                $_selector, 
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

        $dbResult = pg_execute ( $this->dbcon, 'SQL_FIND_USERID', array (
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
	
	public function getOnlineUserCount() {       
        $dbResult = pg_execute ( $this->dbcon, 'SQL_COUNT_ONLINE_USERS', array (
        ) );

        $count = 0;
        if (pg_affected_rows ( $dbResult ) >= 1) {
            $result = pg_fetch_array( $dbResult );
            $count = $result['online'];
        }
        return $count;
    }
	
	public function getTotalUserCount() {       
        $dbResult = pg_execute ( $this->dbcon, 'SQL_COUNT_TOTAL_USERS', array (
        ) );

        $count = 0;
        if (pg_affected_rows ( $dbResult ) >= 1) {
            $result = pg_fetch_array( $dbResult );
            $count = $result['total'];
        }
        return $count;
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