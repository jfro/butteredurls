<?php
// If install.php was deleted, don't allow access!
defined('OKAY_TO_MIGRATE') OR die('No direct access allowed.'); 



class Create_Autoslug_Table extends Migration
{
	function up()
	{
		$prefix = DB_PREFIX;
		
		$t = $this->createTable(DB_PREFIX.'autoslug');
		$t->column('id', 'serial', array('primary_key' => true, 'null' => false));
		$t->column('method', 'string', array('null' => false, 'size' =>'31'));
		$t->column('base10', 'bigint', array('default'=>0));
		$t->save();

		$this->createIndex(DB_PREFIX.'autoslug', 'method', 'method_index', 'unique');
		
		// Populate
		$add_methods_sql = <<<EOSQL
		INSERT INTO  ${prefix}autoslug (id ,method ,base10)
		VALUES (NULL ,  'base36',  '0'), 
		(NULL ,  'base62',  '0'), 
		(NULL ,  'mixed-smart',  '0'), 
		(NULL ,  'lower-smart',  '0');		
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