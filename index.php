<?php

include('-/config.php');
include('-/db.php');
include('-/stats.php');

$token = $_GET['token'];
$show_stats = false;
if(strrpos($token, '/stats')) 
{
	$show_stats = true;
}

// redirect
if (isset($_GET['token']))
{
	// try custom url first
	$stmt = $db->query('SELECT * FROM '.DB_PREFIX.'urls WHERE custom_url=\''.$_GET['token'].'\' LIMIT 1');
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	if(!$row)
	{
		$stmt = $db->query('SELECT * FROM '.DB_PREFIX.'urls WHERE id='.base_convert($_GET['token'], 36, 10).' LIMIT 1');
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
	}

	if ($stmt)
	{
		if ($row)
		{
			if(RECORD_URL_STATS)
				record_stats($db, $row['id']);
			header($_SERVER['SERVER_PROTOCOL'].' 301 Moved Permanently');
			header('Location:'.$row['url']);
			exit();
		}
	}
	else if($_GET['token'] == '')
	{
		if(defined('HOMEPAGE_URL') && HOMEPAGE_URL)
			header("Location: ".HOMEPAGE_URL);
		exit;
	}
}
else
{
	if(defined('HOMEPAGE_URL') && HOMEPAGE_URL)
		header("Location: ".HOMEPAGE_URL);
	exit;
}

// no redirect
header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
header('Status:404');
die('404 Not Found');