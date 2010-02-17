<?php
// If install.php was deleted, don't allow access!
defined('OKAY_TO_MIGRATE') OR die('No direct access allowed.'); 


class AddCustomURL extends Migration
{
	function up()
	{
		$this->addColumn(DB_PREFIX.'urls', 'custom_url', 'string', array('default' => null, 'size' => 255));
	}
	
	function down()
	{
		
	}
}
