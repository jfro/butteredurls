<?php
// If install.php was deleted, don't allow access!
defined('OKAY_TO_MIGRATE') OR die('No direct access allowed.'); 


class OnlyExplicitSlugs extends Migration
{
	function up()
	{
	}
	function up2()
	{
	
		// Postgres:
		// CREATE TYPE bu_redir_type AS ENUM ('auto', 'custom', 'alias', 'gone');
		// ALTER TABLE ${prefix}urls ADD COLUMN redir_type bu_redir_type DEFAULT 'auto';
		// UPDATE ${prefix}urls SET redir_type = 'custom' WHERE custom_url IS NOT NULL;
		// 
		// MySQL:
		// ALTER TABLE ${prefix}urls ADD COLUMN redir_type ENUM  DEFAULT 'auto';
		// UPDATE ${prefix}urls SET redir_type = 'custom' WHERE custom_url IS NOT NULL;

		Migrator::message('inform',
		 	'This migration will attempt to assign each non-custom/legacy redirection an '
			.'explicit slug (short URL path) equal '
			.'to its id in base 36 (as in the original Lessn).'
		);

		$this->addColumn(
			DB_PREFIX.'urls', 
			'error_occurred', 
			'string', 
			array('default' => 'auto', 'size' => 6, 'null' => false)
		);
		throw new Exception('Not Implemented');

		$this->addColumn(
			DB_PREFIX.'urls', 
			'redir_type', 
			'string', 
			array('default' => 'auto', 'size' => 6, 'null' => false)
		);
		// We'd like to use an enum, but this is not supported by SQLite. Will use a varchar(6).
		//Set up
		$batch = 10;
		$prefix = DB_PREFIX;
		$prelim_sql = "UPDATE ${prefix}urls SET redir_type = 'custom' WHERE custom_url IS NOT NULL";
		$select_sql = "SELECT * FROM ${prefix}urls "
			.'WHERE custom_url IS NULL '
			.'AND id > :custom_url'
			.'LIMIT :limit';
		$check_sql = 'SELECT * FROM '.DB_PREFIX.'urls WHERE custom_url = :custom_url LIMIT 1';
		$update_sql = 'UPDATE '.DB_PREFIX.' SET custom_url=:custom_url WHERE id=:id LIMIT 1';

		// TODO: EXECUTE PRELIM

		// Avoid doing failed migration rows over and over
		$min_id = 0;
		
		while(TRUE)
		{
			set_time_limit(60);
			//TODO: START TRANSACTION
			$stmt = $db->query($select);
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
			$returned = 'TODO'; //TODO
			$errors = 0;
			
			// Migrate each of these
			foreach($rows as $row){
				if ($id > $min_id) $min_id = $id;
				
				$slug = base_convert($id, 10, 36);
				
				$chk  = $db->query(sprintf($check_sql,$slug));
				$conflict = $chk->fetch(PDO::FETCH_ASSOC);
				if(!$conflict) {
					
					
				}
				else {
					Migrator::message('failure', 'Could not give redirect an explicit slug (already in use!). '
						.'ID: '
						.$id
						.'. Attempted URL: '.$slug);
					$errors++;
				}
				$update = sprintf($update_sql, $slug, $id);

			}
			
			//TODO: COMMIT
			echo '<p>Updated '.($returned-$errors).' rows.'; //</p> later
			if ($returned < $batch) {
				// Complete!
				break;
			} else {
				Migrator::message('inform', 'Continuing...');
			}
		}

	}
	
	function down()
	{
		$this->removeColumn(DB_PREFIX.'urls', 'redir_type');
	}
}
