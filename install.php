<?php
include('-/config.php');
include('-/db.php');
$prefix = DB_PREFIX;
$sql = array();

//$db_prefix = DB_PREFIX;
$sql['pgsql'] = array();
$sql['pgsql'][] = <<<EOT
CREATE TABLE ${prefix}urls
(
	id serial NOT NULL, 
	url text NOT NULL, 
	checksum bigint NOT NULL,
	CONSTRAINT lessn_urls_primary_key PRIMARY KEY (id)
);
EOT;

$sql['pgsql'][] = <<<EOT
CREATE TABLE ${prefix}url_stats
(
	id serial NOT NULL, 
	url_id integer NOT NULL, 
	ip_address inet, 
	referer text, 
	created_on timestamp without time zone NOT NULL,
	CONSTRAINT ${prefix}url_stats_primary_key PRIMARY KEY (id),
	CONSTRAINT url_id_fk FOREIGN KEY (url_id) REFERENCES ${prefix}urls (id) ON UPDATE NO ACTION ON DELETE CASCADE
);
EOT;

$sql['pgsql'][] = <<<EOT
ALTER TABLE ${prefix}urls ADD COLUMN custom_url varchar(255) DEFAULT NULL;
EOT;

// MySQL
$sql['mysql'] = array();
$sql['mysql'][] = <<<EOT
CREATE TABLE ${prefix}urls(
id int(11) unsigned NOT NULL auto_increment,
url text character set utf8 collate utf8_unicode_ci NOT NULL,
checksum int(10) unsigned NOT NULL,
PRIMARY KEY (id),
KEY checksum (checksum)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
EOT;

$sql['mysql'][] = <<<EOT
CREATE TABLE `${prefix}url_stats` (
`id` int(11) unsigned NOT NULL auto_increment, 
`url_id` int(11) NOT NULL,
`ip_address` varchar(255),
`referer` varchar(255),
`created_on` datetime,
PRIMARY KEY  (`id`),
INDEX `url_id` (`url_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
EOT;

$sql['mysql'][] = <<<EOT
ALTER TABLE ${prefix}urls ADD COLUMN custom_url varchar(255) DEFAULT NULL;
EOT;

if(!array_key_exists(DB_DRIVER, $sql))
	die('Unknown database driver, no installation SQL found');
	
$queries = $sql[DB_DRIVER];
if(isset($_GET['start']))
{
	$queries = array_slice($queries, $_GET['start']);
	print 'Starting from query #'.$_GET['start'].'<br />';
}

foreach($queries as $q) {
	//$q = str_replace("\n", "", $q);
	// $stmt = $db->prepare($q);
	// $stmt->execute();
	$db->exec($q);
}
print 'Done, delete install.php';
