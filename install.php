<?php
set_include_path(get_include_path().PATH_SEPARATOR.dirname(__FILE__).'/-/library');
require_once '-/config.php';
require_once '-/db.php';
require_once 'Migrator.php';
require_once 'QueryTools.php';

if(isset($_GET['start']))
{
	$start = $_GET['start'];
}
else
	$start = 1;

$migrator = new Migrator($db, dirname(__FILE__).'/-/migrations');
$migrator->migrate($start);

print 'Done, delete install.php';
