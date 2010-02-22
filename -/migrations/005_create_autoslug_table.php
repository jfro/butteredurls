<?php
// If install.php was deleted, don't allow access!
defined('OKAY_TO_MIGRATE') OR die('No direct access allowed.'); 



class Create_Autoslug_Table extends Migration
{
	function up()
	{
		$t = $this->createTable(DB_PREFIX.'autoslug');
		$t->column('id', 'serial', array('primary_key' => true, 'null' => false));
		$t->column('method', 'string', array('null' => false, 'size' =>'31'));
		$t->column('base10', 'bigint', array('default'=>0));
		$t->save();

		$this->createIndex(DB_PREFIX.'autoslug', 'method', 'method_index', 'unique');

	}
	
	function down()
	{
		// drop autoslug table
	}
}