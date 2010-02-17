<?php

// LOGIN
define('USERNAME',	'admin');
define('PASSWORD',	'blarg');

// DATABASE
define('DB_NAME', 		'bcurls');
define('DB_USERNAME', 	'bcurls');
define('DB_PASSWORD', 	'pass');

// Enable statistics?
define('RECORD_URL_STATS', true);

// FINE AS IS (UNLESS YOU KNOW OTHERWISE)
define('DB_DRIVER',		'mysql'); // mysql or pgsql, sqlite could be done with maybe tweaks
define('DB_SERVER', 	'localhost');
define('DB_PREFIX', 	'bcurls_');
define('COOKIE_SALT', 	'B75sS4L7T0R3PEPp3R');
define('API_SALT',		'B75jk4K25M5U7hTAP1');


// How should short URL slugs be generated? 
// The default is 'base36', as in the original Lessn.
// For mixed-case alphanumerical strings, use 'base62'.
// For mixed-case alhpanumerical strings that by default do not 
// include homoglyphs (lookalikes, like l/1 and O/0), use 'mixed-smart'.
// Recommended: 'mixed-smart'
define('AUTO_SLUG_METHOD', 'base36');

// Any characters you would like to manually exclude from future 
// auto-generated URL slugs? NULL if not.
define('HOMOGLYPHS_TO_AVOID', '10lIoO');


// Are there any characters, words, or phrases you want banned?
// If so, set them in banned_words.php and set this to TRUE.
define('USE_BANNED_WORD_LIST', TRUE);

// Allow banned words in custom URLs, or just auto-generated?
define('ALLOW_BANNED_WORDS_IN_CUSTOM_URLS', TRUE);
define('ALLOW_HOMOGLYPHS_IN_CUSTOM_URLS', TRUE);


// URL to hit if someone visits your site without a short url, set to null for just a blank page
define('HOMEPAGE_URL', NULL); //e.g. 'http://example.com'
// If an slug is not found occurns, e.g. http://doma.in/this-slug-doesn't-exist
define('ERROR_404_URL', NULL); //e.g. 'http://example.com/404'
// If an slug was deleted (marked 'gone')
define('GONE_410_URL', NULL); //e.g. 'http://example.com/gone'

define('APP_NAME', 'Lessn More');