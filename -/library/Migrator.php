<?php
require_once 'Migration.php';

class Migrator
{
	protected $db;
	protected $current_version;
	protected $migrations_dir;
	
	function __construct($db, $migrations_dir=null)
	{
		$this->db = $db;
		$this->current_version = 0;
		if($migrations_dir === null)
			$migrations_dir = './migrations'; // default to ./migrations
		$this->migrations_dir = $migrations_dir;
		$this->qt = new QueryTools($this->db);
		
		$this->check_schema_table();
	}
	
	function currentVersion()
	{
		return $this->current_version;
	}
	
	function check_schema_table()
	{
		try {
			$stmt = $this->db->prepare('SELECT * FROM '.DB_PREFIX.'schema_info');
			$stmt->execute();
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if(count($rows) == 0)
				$this->db->exec('INSERT INTO '.DB_PREFIX.'schema_info VALUES(1, 0)');
			else 
			{
				$this->current_version = $rows[0]['version'];
			}
		}
		catch(PDOException $e) {
			$t = $this->qt->createTable(DB_PREFIX.'schema_info');
			$t->column('id', 'serial', array('primary_key' => true, 'null' => false));
			$t->column('version', 'integer', array('default' => 0, 'null' => false));
			$t->save();
			$this->db->exec('INSERT INTO '.DB_PREFIX.'schema_info VALUES(1, 0)');
		}
	}
	
	function updateInfoToVersion($v)
	{
		$this->db->exec('UPDATE '.DB_PREFIX.'schema_info SET version='.$v.' WHERE id = 1');
	}
	
	function migrate($start=null)
	{
		if($start === null)
			$start = $this->current_version + 1;
		$files = glob($this->migrations_dir."/*.php");
		
		$this->db->beginTransaction();
		$last_version = 0;
		foreach($files as $file)
		{
			include $file;
			$file = basename($file, '.php');
			$class = substr($file, strpos($file,'_')+1);
			$v = intval(substr($file,0,strpos($file,'_')));
			if(!class_exists($class))
			{
				print 'Failed to find class: \''.$class.'\' in migration \''.$file.'\'<br />';
				exit;
			}
			if($v < $start)
			{
				continue;
			}
			$m = new $class($this->db);
			try {
				$m->up();
			}
			catch(Exception $e)
			{
				print 'Failed to migrate, rolling back<br />';
				$this->db->rollBack();
				throw $e;
			}
			$last_version = $v;
		}
		if($last_version > $start)
		{
			$this->updateInfoToVersion($last_version);
			$this->db->commit();
			print 'Successfully updated to '.$last_version;
		}
		else
			print 'Already up-to-date';
		
	}
	
}