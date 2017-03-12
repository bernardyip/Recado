<?php

class DatabaseResult {
    // holds the status of the result of the current operation.
    public $status;
    
    public function __construct() {
        
    }
}

class Database {
    
    // Database connection constants
    const USERNAME = "postgres";
    const PASSWORD = "password";
    const HOST = "localhost";
    const DATABASE_NAME = "postgres";
    
    // protected variables
    protected $dbcon;
    
    public function __construct() {
        $connectionString = "host=" . Database::HOST . " dbname=" . Database::DATABASE_NAME . " user=" . Database::USERNAME . " password=" . Database::PASSWORD;
        $this->dbcon = pg_connect ( $connectionString );
    }
}
?>