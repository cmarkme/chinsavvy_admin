<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// connection.php
// This is the updated code for illuminate database 4.0

$CI = & get_instance();

$config = $CI->db; // Get the DB object

$pdo = new PDO('mysql:host='.$config->hostname.';dbname='.$config->database, $config->username, $config->password);


$drivers = array(
	'mysql' => '\Illuminate\Database\MySqlConnection',
	'pgsql' => '\Illuminate\Database\PostgresConnection',
	'sqlite' => '\Illuminate\Database\SQLiteConnection',
);

$conn = new $drivers['mysql']($pdo, $config->database, $config->dbprefix);


$resolver = new Illuminate\Database\ConnectionResolver;
$resolver->addConnection('default', $conn);
$resolver->setDefaultConnection('default');


\Illuminate\Database\Eloquent\Model::setConnectionResolver($resolver);

return $conn;