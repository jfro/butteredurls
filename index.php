<?php

include('-/config.php');
include('-/db.php');

$token = (isset($_GET['token']) ? $_GET['token'] : '');

$show_stats = false;
if(strrpos($token, '/stats')) //at end of url
{
	$show_stats = true;
}
if (RECORD_URL_STATS OR $show_stats) {
	include('-/stats.php');
}

// redirect
while($token != '') // Loop so we can handle aliases
{
	// Look up slug
	// TODO: Use PDO::prepare in "The other index.php"
	$stmt = $db->prepare('SELECT * FROM '.DB_PREFIX.'urls WHERE custom_url = :slug LIMIT 1');
	$stmt->execute(array('slug'=>$token));
	$row = $stmt->fetch(PDO::FETCH_ASSOC);


	if ($stmt AND $row)
	{
		if(RECORD_URL_STATS)
			record_stats($db, $row['id']);
		if($row['redir_type'] == 'gone') {
			header($_SERVER['SERVER_PROTOCOL'].' 410 Gone');
			die('The redirection in question no longer exists.');
		} elseif($row['redir_type'] == 'alias') {
			// Handle aliases, and watch out for infinite loops
			if($row['url'] != $token)
			{
				$token = $row['url'];
				continue;
			}
			else {
				// Incorrectly configured. "Should never happen"
				$token = '';
				break;
			}
		} else {
			// Handle standard redirections, both custom and auto-assigned
			header($_SERVER['SERVER_PROTOCOL'].' 301 Moved Permanently');
			header('Location:'.$row['url']);
			exit();
		}
		//Unreachable, thanks to "else"
	}
	else 
	{
		// 404!
		// no redirect
		header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
		header('Status:404');
		die('404: Nothing found');
	}
}

if(defined('HOMEPAGE_URL') && HOMEPAGE_URL)
	header("Location: ".HOMEPAGE_URL);
exit;