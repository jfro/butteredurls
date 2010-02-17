<?php
// no need to edit this file, see config.php
ini_set('display_errors', 1);
//error_reporting(1);

// connect
if(DB_DRIVER == 'sqlite')
	$db = new PDO(DB_DRIVER.':'.DB_NAME);
else
	$db = new PDO(DB_DRIVER.':host='.DB_SERVER.';dbname='.DB_NAME, DB_USERNAME, DB_PASSWORD);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
