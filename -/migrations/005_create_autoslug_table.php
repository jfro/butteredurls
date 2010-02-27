<?php
// If install.php was deleted, don't allow access!
defined('OKAY_TO_MIGRATE') OR die('No direct access allowed.'); 



class Create_Autoslug_Table extends Migration
{
	function up()
	{
		Migrator::message('inform',
		 	'This migration enables an efficient method of generating automatic slugs.'
		);
		$prefix = DB_PREFIX;
		
		$t = $this->createTable(DB_PREFIX.'autoslug');
		$t->column('id', 'serial', array('primary_key' => true, 'null' => false));
		$t->column('method', 'string', array('null' => false, 'size' =>'31'));
		$t->column('base10', 'bigint', array('default'=>1));
		$t->save();

		$this->createIndex(DB_PREFIX.'autoslug', 'method', 'method_index', 'unique');
		
		// Populate
		$add_methods_sql = <<<EOSQL
		INSERT INTO  ${prefix}autoslug (id ,method ,base10)
		VALUES (NULL ,  'base36',  1), 
		(NULL ,  'base62',  1), 
		(NULL ,  'mixed-smart',  1), 
		(NULL ,  'lower-smart',  1);		
EOSQL;
		
		$ins = $this->db->prepare($add_methods_sql);
		if( ! $ins->execute()){
			throw new Exception('Failed to add rows for autoslug methods.');
		}

	}
	
	function down()
	{
		// drop autoslug table
	}
}