<?php

function connect_db()
	{
	$server = 'mysql.apps.cviac.com'; // this may be an ip address instead
	$user = 'aramesh';
	$pass = 'cvi@c2016';
	$database = 'cviacappdb'; // name of your database
	$connection = new mysqli($server, $user, $pass, $database);
	return $connection;
	}

?>