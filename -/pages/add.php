<?php include('stubs/header.php'); ?>
<form method="get">
	<input type="text" id="url" name="url" placeholder="url" />
	<button>Shrink URL</button> <br />
	<input type="text" name="custom_url" value="" placeholder="custom short url" /> (optional)
	
	<p>Grab the <a 
		title="Shrink a link"
		href="javascript:location.href='<?php echo BCURLS_URL; ?>-/?url='+encodeURIComponent(location.href);" 
		onclick="alert('Drag this bookmarklet onto your browser bar.');return false;">
		Shrinkn
		</a> or <a 
		title="Shrink and tweet the shortened link"
		href="javascript:location.href='<?php echo BCURLS_URL; ?>-/?tweet&amp;url='+encodeURIComponent(location.href);" 
		onclick="alert('Drag this bookmarklet onto your browser bar.');return false;">
		Tweetn
		</a> bookmarklet.
		<span>API key: <code><?php echo API_KEY; ?></code></span>
	</p>
</form>

<p>
	<a href="?stats=1">URL Stats</a>
</p>
<script>
document.getElementById('url').focus();
</script>
<?php include('stubs/footer.php'); ?>