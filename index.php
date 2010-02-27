<?php

include('-/config.php');
include('-/db.php');

$token = (isset($_GET['token']) ? $_GET['token'] : '');

$show_stats = (isset($_GET['stats']) OR strrpos($token, '/stats') !== false);
if (RECORD_URL_STATS OR $show_stats) {
	include('-/stats.php');
}

/*
*	DEVELOPERS:
*	Note the following possible redir_type values:
*	-	'auto' - Automatically assigned slug. 301 redirect on access.
*	-	'custom' - Manually set slug. 301 redirect on access.
*	-	'alias' - Its 'url' is really just another slug. Do a recursive lookup to redirect on access.
*	-	'gone' - Access results in a 410; should never change
*/

// Redirect lookup
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
		die('404: Nothing found for '.htmlentities($token));
	}
}

if(defined('HOMEPAGE_URL') && HOMEPAGE_URL)
	header("Location: ".HOMEPAGE_URL);
exit;