<?php

/**
*	
*	TODO: Handle updating edge cases with a log function so we don't guess forever.
*	1,2,4,8...
*
*	TODO: Never just insert an ID
*	
*/
include('config.php');
include('db.php');
include('stats.php');

define('BCURLS_VERSION',	'1.1.1');

define('BCURLS_DOMAIN', 	preg_replace('#^www\.#', '', $_SERVER['SERVER_NAME']));
define('BCURLS_URL', 	str_replace('-/index.php', '', 'http://'.BCURLS_DOMAIN.$_SERVER['PHP_SELF']));

//don't reveal db prefix over HTTP. 16 chars is more than enough to avoid collisions
define('COOKIE_NAME', 	substr(md5(DB_PREFIX.COOKIE_SALT), 0, 16).'auth'); 
define('COOKIE_VALUE',	md5(USERNAME.PASSWORD.COOKIE_SALT));
define('COOKIE_DOMAIN', '.'.BCURLS_DOMAIN);

if (!defined('API_SALT')) define('API_SALT', 'B75jk4K25M5U7hTAP1'); // added in lessn 1.0.5
define('API_KEY', md5(USERNAME.PASSWORD.API_SALT));

define('NOW', 		time());
define('YEAR',		365 * 24 * 60 * 60);

function bcurls_find_banned_glyphs($slug) {
	return FALSE;
}

// handle login
if (isset($_POST['username']))
{
	if (md5($_POST['username'].$_POST['password'].COOKIE_SALT) == COOKIE_VALUE)
	{
		setcookie(COOKIE_NAME, COOKIE_VALUE, NOW + YEAR, '/', COOKIE_DOMAIN);
		$_COOKIE[COOKIE_NAME] = COOKIE_VALUE;
	}
}
// API login
else if (isset($_GET['api']) && $_GET['api'] == API_KEY)
{
	$_COOKIE[COOKIE_NAME] = COOKIE_VALUE;
}
else if (isset($_GET['api'])) // spit out a nicer failure for API attempts
{
	exit('Invalid API key');
}

// handle logout
if (isset($_GET['logout']))
{
	setcookie(COOKIE_NAME, '', NOW - YEAR, '/', COOKIE_DOMAIN);
	unset($_COOKIE[COOKIE_NAME]);
	header('Location:./');
}

// require login
if (!isset($_COOKIE[COOKIE_NAME]) || $_COOKIE[COOKIE_NAME] != COOKIE_VALUE)
{
	include('pages/login.php');
	exit();
}
// prolong login for another year, unless this is an API request
else if (!isset($_GET['api']))
{
	setcookie(COOKIE_NAME, COOKIE_VALUE, NOW + YEAR, '/', COOKIE_DOMAIN);
}

// new shortcut
if (isset($_GET['url']) && !empty($_GET['url']))
{
	$url = $_GET['url'];
	$slug = NULL;
	if (!preg_match('#^[^:]+://#', $url))
	{
		$url = 'http://'.$url;
	}
	if(strpos($url, BCURLS_URL) === 0)
	{
		$error = 'You tried to shorten a URL on this domain which should already be short!';
		include('pages/error.php');
		exit;
	}
	// Is there already a row in the DB going to this same URL?
	$checksum 		= sprintf('%u', crc32($url));
	$result = $db->prepare('SELECT id, custom_url, redir_type FROM '.DB_PREFIX.'urls WHERE checksum=? AND url=? AND redir_type <> \'gone\' LIMIT 1');
	$result->bindValue(1, (int)$checksum);
	$result->bindValue(2, $url);
	if ($result->execute())
	{
		
		// exists
		if ($row = $result->fetch(PDO::FETCH_ASSOC))
		{
			$id = $row['id'];
		}
		// No redirection uses that URL yet
		else
		{
			$redir_type = 'auto';
			if(isset($_GET['custom_url']) && $_GET['custom_url'])
			{ // user wants to assign short URL
				$custom_url = $_GET['custom_url'];
				// check if the slug is already in use
				$stmt = $db->prepare('SELECT * FROM '.DB_PREFIX.'urls WHERE custom_url = ?');
				$stmt->bindValue(1, $custom_url);
				$stmt->execute();
				if($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$error = 'The custom short URL you attempted to use (/'.$row['custom_url'].') is already in use, and is pointing to '.$row['url'];
					include('pages/error.php');
					exit;
				}
				else {
					$redir_type = 'custom';
					$slug = $custom_url;
				}
			}
			else // auto-assign a slug
			{
				require_once 'library/BaseIntEncoder.php';
				
				$auto_assign_sql = 'SELECT base10 FROM '.DB_PREFIX.'autoslug '
					.'WHERE method = :method LIMIT 1';
				$auto = $db->prepare($auto_assign_sql);
				$auto->execute('method'=>'base36');
				$counter = $auto->fetch(PDO::FETCH_ASSOC);
				$attempts = 0;
				while ($slug === NULL) {
					switch(AUTO_SLUG_METHOD) {
						case 'base62':
							$glyphs = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
							$base = 62;
							break;
						case 'mixed-smart':
							$glyphs = '23456789abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
							// we excluded 6 characters: 0oO1Il
							$base = 56;
							break;
						case 'base36':
							$glyphs = '0123456789abcdefghijklmnopqrstuvwxyz';
							$base = 36;
							break;
						case 'smart':
							$glyphs = '23456789abcdefghijkmnpqrstuvwxyz';
							$base = 32; //exclude 0o1l
							break;
						default:
							throw new Exception ('Unsupported method to generate unique slugs!');
							break;
					}
					//Handles big bases AND big number conversions!
					$slug = BaseIntEncoder::encode($counter, $glyphs, $base);
					$banned_pos = 
					if()
					if(okay to insert)
					{
						//Begin transaction
						
						//end transaction
						break; // found a suitable URL
					}
					else
					{
						$slug = NULL;
						$counter += pow($attempts++, 1.5);
						continue;
					}
					
				}
			}
			$stmt = $db->prepare('INSERT INTO '.DB_PREFIX.'urls (url, checksum, custom_url, redir_type) VALUES(?, ?, ?, ?)');
			$stmt->bindValue(1, $url);
			$stmt->bindValue(2, $checksum);
			$stmt->bindValue(3, $slug);
			$stmt->bindValue(4, $redir_type);
			$stmt->execute();
		}
	}

	$new_url = BCURLS_URL.$slug;
	
	if (isset($_GET['tweet']))
	{
		$_GET['redirect'] = 'http://twitter.com/?status=%l';
	}
	if (isset($_GET['redirect']))
	{
		header('Location:'.str_replace('%l', urlencode($new_url), $_GET['redirect']));
		exit();
	}
	if (isset($_GET['api']))
	{
		echo $new_url;
		exit();
	}
	
	include('pages/done.php');
}
else if(isset($_GET['stats']))
{
	$top_urls = stats_top_urls($db);
	$top_referers = stats_top_referers($db);
	include('pages/stats.php');
}
else
{
	include('pages/add.php');
}