<?php
class User {
	private $dbcon;
	public function __construct($host, $user, $pass, $db) {
		$this->dbcon = new PDO ( "pgsql:host=" . $host . " dbname=" . $db . " user=" . $user . " password=" . $pass );
	}
	public function getUsers() {
		$sqlGetUser = "SELECT * FROM users";
		$result = $this->dbcon->prepare ($sqlGetUser);
		$result->execute ();
		return json_encode ( $result->fetchAll () );
	}
	public function add($user) {
		$result = $this->dbcon->prepare ( "INSERT INTO users(name, email, mobile, address) VALUES (?, ?, ?, ?)" );
		$result->execute ( array (
				$user->name,
				$user->email,
				$user->mobile,
				$user->address 
		) );
		return json_encode ( $this->dbcon->lastInsertId () );
	}
	public function delete($user) {
		$result = $this->dbcon->prepare ( "DELETE FROM users WHERE id=?" );
		$result->execute ( array (
				$user->id 
		) );
		return json_encode ( 1 );
	}
	public function updateValue($user) {
		$result = $this->dbcon->prepare ( "UPDATE users SET " . $user->field . "=? WHERE id=?" );
		$result->execute ( array (
				$user->newvalue,
				$user->id 
		) );
		return json_encode ( 1 );
	}
}
?>