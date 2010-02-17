<?php
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
