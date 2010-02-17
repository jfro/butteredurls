<?php
include('-/config.php');
include('-/db.php');
$prefix = DB_PREFIX;

echo "<!DOCTYPE HTML>
<html>
<head>
<title>".APP_NAME." Migration</title>
<link type=\"text/css\" rel=\"stylesheet\" href=\"http://pan.alanhogan.com/css/reset.css\"/>
<link type=\"text/css\" rel=\"stylesheet\" href=\"http://pan.alanhogan.com/css/standalonepage.css\"/>
</head>
<body>
	<div class=\"bigWrap\">
		<div class=\"huge\">&#8635;</div>
		<h2 class=\"bigTitle\">".APP_NAME." Migration</h2>
	</div>
	<div class=\"everythingElse\">
";

$migration = (isset($_GET['migration']) ? $_GET['migration'] : '');
switch($migration) {
	case 'explicit-slugs':
		echo '<p>This migration will attempt</p>';
			try{
				//Set up
				$batch = 10;
				$select_template = 'SELECT * FROM '.DB_PREFIX.'urls '
					.'WHERE custom_url IS NULL '
					.'AND id > %s'
					.'LIMIT '.$batch;
				$check_template = 'SELECT * FROM '.DB_PREFIX.'urls WHERE custom_url=\''.$_GET['token']
					.'\' LIMIT 1';
				$update_template = 'UPDATE '.DB_PREFIX.' SET custom_url=\'%1\' WHERE id=\'%2\' LIMIT 1';

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
						
						$chk  = $db->query(sprintf($check_template,$slug));
						$conflicts = $chk->fetch(PDO::FETCH_ASSOC);
						if(!$conflicts) {
							
							
						}
						else {
							echo '<p><strong>Failed</strong> migrating a redirect. ID: '.$id
								.'. Attempted URL: '.$slug.'</p>';
							$errors++;
						}
						$update = sprintf($update_template, $slug, $id);

					}
					
					//TODO: COMMIT
					echo '<p>Updated '.($returned-$errors).' rows.'; //</p> later
					if ($returned < $batch) {
						echo '</p><p><strong>Complete.</strong><p>';
						break;
					} else {
						echo 'Continuing...</p>';
					}
				}
			}
			catch(Exception $e)
			{
				echo '<p>Migration failed. Exception occurred. '
					.nl2br(htmlentities($e->getMessage())).'</p>';
				// TODO: ROLLBACK
			}
			
		break;
		
	default:
		echo '<p>No migration selected. See the README.</p>';
		break;
}

print '</body></html>';
