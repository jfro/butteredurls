<?php include('stubs/header.php'); ?>
<form method="get">
	<input type="text" id="url" name="url" placeholder="url" />
	<button>Shrink URL</button> <br />
	<input type="text" name="custom_url" value="" placeholder="custom short url" /> (optional slug)
	
	<p>Grab the <a 
		title="Shrink a link"
		href="javascript:var%20my_slug=window.prompt('Shrinking%20this%20URL.%20Enter%20a%20custom%20short%20URL,%20or%20leave%20blank%20to%20automatically%20assign%20one.');location.href='<?php echo BCURLS_URL; ?>-/?url='+encodeURIComponent(location.href)+'&amp;custom_url='+encodeURIComponent(my_slug);" 
		onclick="alert('Drag this bookmarklet onto your browser bar.');return false;">
		Lessn
		</a> or <a 
		title="Shrink and tweet the shortened link"
		href="javascript:var%20my_slug=window.prompt('Shrinking%20this%20URL.%20Enter%20a%20custom%20short%20URL,%20or%20leave%20blank%20to%20automatically%20assign%20one.');location.href='<?php echo BCURLS_URL; ?>-/?tweet&amp;url='+encodeURIComponent(location.href)+'&amp;custom_url='+encodeURIComponent(my_slug);"
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