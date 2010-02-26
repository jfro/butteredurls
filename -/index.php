<?php

/**
*	
*	This file allows inserting redirections and displaying stats.
*	
*	For redirections added, there are several possibilities:
*	
*	1. Adding a new URL, no custom slug: Inserted, and new slug auto-generated.
*	
*	2. Adding an existing URL, no custom slug: The old slug is returned.
*	
*	3. Adding a new URL, with a custom slug:
*	
*	4. Adding an existing URL, with a custom slug: The new slug is inserted anyway.
*		The old redirection(s) become a "aliases" of the new one,
*		so in case 2, the newest slug is returned (it’s "preferred".)
*	
*	
*/
include('config.php');
include('db.php');
include('stats.php');

define('BCURLS_VERSION',	'2.0.0');

define('BCURLS_DOMAIN', 	preg_replace('#^www\.#', '', $_SERVER['SERVER_NAME']));
define('BCURLS_URL', 	str_replace('-/index.php', '', 'http://'.BCURLS_DOMAIN.$_SERVER['PHP_SELF']));

//don't reveal db prefix over HTTP. 16 chars is more than enough to avoid collisions
define('COOKIE_NAME', 	substr(md5(DB_PREFIX.COOKIE_SALT), 4, 16).'auth'); 
define('COOKIE_VALUE',	md5(USERNAME.PASSWORD.COOKIE_SALT));
define('COOKIE_DOMAIN', '.'.BCURLS_DOMAIN);

if (!defined('API_SALT')) define('API_SALT', 'B75jk4K25M5U7hTAP1'); // added in lessn 1.0.5
define('API_KEY', md5(USERNAME.PASSWORD.API_SALT));

define('NOW', 		time());
define('YEAR',		365 * 24 * 60 * 60);

/**
* @Returns int Characters from the right, 0-(n-1) where n = strlen($slug),
* where a banned word ends. e.g.:
* slug = 'dLpooz'
* 'poo' is a banned word
* returns 1
*/
function bcurls_find_banned_word($slug) {
	REQUIRE_ONCE 'banned_words.php';
	global $bcurls_banned_words;
	foreach ($bcurls_banned_words as $banned){
		$strpos = stripos($slug, $banned); 
		if ($strpos !== false) {
			// $slug = xBANNEDxx (length 9)
			// $banned = BANNED (length 6)
			// strpos = 1
			// we want to return 2 (BANNED is two characters from the right)
			return strlen($slug) - strlen($banned) - $strpos;
		}
	}
	return FALSE; 
}

/**
* @Returns int Characters from the right, 0-(n-1) where n = strlen($slug),
* where a banned glyph ends. e.g.:
*/
function bcurls_find_banned_glyph($slug) {
	if (ADDITIONAL_HOMOGLYPHS_TO_AVOID === false)
		return false;
	static $glyphs = str_split(ADDITIONAL_HOMOGLYPHS_TO_AVOID);	
	foreach ($glyphs as $banned){
		$strpos = strpos($slug, $banned); 
		if ($strpos !== FALSE) {
			// $slug = xIxx (length 4)
			// $banned = I (length I)
			// strpos = 1
			// we want to return 2 (I is two characters from the right)
			return strlen($slug) - 1 - $strpos;
		}
	}
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
		$existing_slug = false;
		// exists
		if ($row = $result->fetch(PDO::FETCH_ASSOC))
		{
			$existing_slug = $row['custom_url'];
		}
		
		if(isset($_GET['custom_url']) && strlen(trim($_GET['custom_url'])))
		{ // user wants to assign short URL
			$custom_url = trim($_GET['custom_url']);
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

			{
				require_once 'library/BaseIntEncoder.php';
				
				$auto_assign_sql = 'SELECT base10 FROM '.DB_PREFIX.'autoslug '
					.'WHERE method = :method LIMIT 1';
				$auto = $db->prepare($auto_assign_sql);
				$auto->execute('method'=>'base36');
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
				$attempts = 0; //Keep track of when there is a collision
				$counter = $auto->fetch(PDO::FETCH_ASSOC);
				while ($slug === NULL) {
					//Handles big bases AND big number conversions!
					$slug = BaseIntEncoder::encode($counter, $glyphs, $base);
					// Check for banned words showing up
					if(USE_BANNED_WORD_LIST){
						$banned_pos = bcurls_find_banned_word($slug);
						if($banned_pos !== FALSE) {
							// If slug is e.g. BANNEDa
							// we want to increment counter by (10 - a) in base $base
							// to make the slug BANNEE0
							// So we convert 1, 10, 100, etc. "in base $base" to base 10…
							$rollover = BaseIntEncoder::decode((string)bcpow(10,$banned_pos), 
								$glyphs, $base);
							$already_in = BaseIntEncoder::decode(substr($slug, 0-$banned_pos-1), 
								$glyphs, $base);
							
							$counter += ($rollover-$already_in);
							$slug = NULL;
							continue;
						}	
					}
					if(okay to insert)
					{
						//Begin transaction
						
						//end transaction
						break; // found a suitable URL
					}
					else
					{
						$slug = NULL;
						$counter += BaseIntEncoder::bcCeil(bcpow($attempts++, 1.5));
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
		elseif($existing_slug !== false)
		{
			$slug = $existing_slug;
		}
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
				$attempts = 0; //Keep track of when there is a collision
				$counter = $auto->fetch(PDO::FETCH_ASSOC);
				while ($slug === NULL) {
					//Handles big bases AND big number conversions!
					$slug = BaseIntEncoder::encode($counter, $glyphs, $base);
					// Check for banned words showing up
					if(USE_BANNED_WORD_LIST){
						$banned_pos = bcurls_find_banned_word($slug);
						if($banned_pos !== FALSE) {
							// If slug is e.g. BANNEDa
							// we want to increment counter by (10 - a) in base $base
							// to make the slug BANNEE0
							// So we convert 1, 10, 100, etc. "in base $base" to base 10…
							$rollover = BaseIntEncoder::decode((string)bcpow(10,$banned_pos), 
								$glyphs, $base);
							$already_in = BaseIntEncoder::decode(substr($slug, 0-$banned_pos-1), 
								$glyphs, $base);
							
							$counter += ($rollover-$already_in);
							$slug = NULL;
							continue;
						}	
					}
					if(okay to insert)
					{
						//Begin transaction
						
						//end transaction
						break; // found a suitable URL
					}
					else
					{
						$slug = NULL;
						$counter += BaseIntEncoder::bcCeil(bcpow($attempts++, 1.5));
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
		
	} else {
		throw new Exception('Problem executing query to check if the URL is already in the DB!');
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
else if(isset($_GET['stats']))  // Display stats
{
	$top_urls = stats_top_urls($db);
	$top_referers = stats_top_referers($db);
	include('pages/stats.php');
}
else
{
	include('pages/add.php');
}