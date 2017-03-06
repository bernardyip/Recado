<?php
class User {
	private $dbcon;
	public function __construct($host, $user, $pass, $db) {
		$this->dbcon = new PDO ( "pgsql:host=" . $host . " dbname=" . $db . " user=" . $user . " password=" . $pass );
	}
	public function getUsers() {
		$sqlGetUser = "SELECT * FROM users";
		$sth = $this->dbcon->prepare ($sqlGetUser);
		$sth->execute ();
		return json_encode ( $sth->fetchAll () );
	}
	public function add($user) {
		$sth = $this->dbcon->prepare ( "INSERT INTO users(name, email, mobile, address) VALUES (?, ?, ?, ?)" );
		$sth->execute ( array (
				$user->name,
				$user->email,
				$user->mobile,
				$user->address 
		) );
		return json_encode ( $this->dbcon->lastInsertId () );
	}
	public function delete($user) {
		$sth = $this->dbcon->prepare ( "DELETE FROM users WHERE id=?" );
		$sth->execute ( array (
				$user->id 
		) );
		return json_encode ( 1 );
	}
	public function updateValue($user) {
		$sth = $this->dbcon->prepare ( "UPDATE users SET " . $user->field . "=? WHERE id=?" );
		$sth->execute ( array (
				$user->newvalue,
				$user->id 
		) );
		return json_encode ( 1 );
	}
}
?>