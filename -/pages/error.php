<?php is_defined('OKAY_TO_SHOW_PAGES') OR DIE('Unauthenticated!');

if(isset($_GET['api']))
	exit($error);

include('stubs/header.php'); ?>
<h1>Error</h1>
<p class="error">
	<?php echo htmlentities($error) ?>
</p>
<p>
	<a href="/-/">Back</a>
</p>
<?php include('stubs/footer.php'); ?>
