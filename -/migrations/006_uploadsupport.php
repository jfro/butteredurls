<?php
// If install.php was deleted, don't allow access!
defined('OKAY_TO_MIGRATE') OR die('No direct access allowed.'); 



class UploadSupport extends Migration
{
	function up()
	{
		Migrator::message('inform',
		 	'This migration enables support for uploading files as short URLs.'
		);
		$prefix = DB_PREFIX;
		
		$t = $this->createTable(DB_PREFIX.'files');
		$t->column('id', 'serial', array('primary_key' => true, 'null' => false));
		$t->column('title', 'string', array('null' => false));
		$t->column('filepath', 'string', array('null' => false));
		$t->column('type', 'string', array('null' => false, 'default' => 'other'));
		$t->save();
		$this->createIndex(DB_PREFIX.'files', 'type', 'index_'.DB_PREFIX.'files_type');
		
		$this->addColumn(DB_PREFIX.'urls', 'file_id', 'integer', array('default' => null));
		$this->createIndex(DB_PREFIX.'urls', 'file_id', 'index_'.DB_PREFIX.'urls_file_id');
	}
	
	function down()
	{
		// drop autoslug table
	}
}