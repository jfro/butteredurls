<?php
class CreateURLsTable extends Migration
{
	function up()
	{
		$t = $this->createTable(DB_PREFIX.'urls');
		$t->column('id', 'serial', array('primary_key' => true));
		$t->column('url', 'text', array('null' => false));
		$t->column('checksum', 'integer', array('null' => false));
		$t->save();
	}
	
	function down()
	{
		
	}
}
