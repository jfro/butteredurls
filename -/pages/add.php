<?php include('stubs/header.php'); ?>
<?php if(isset($error) && strlen($error)) : ?>
<p class="error"><?php echo htmlentities($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>
<form method="get">
	<input type="text" id="url" name="url" placeholder="url" value="<?= htmlentities(@$_GET['url'], ENT_QUOTES, 'UTF-8')?>" />
	<button>Shrink URL</button> <br />
	<input type="text" name="custom_url" value="<?= htmlentities(@$_GET['custom_url'], ENT_QUOTES, 'UTF-8')?>" id="custom_url" placeholder="custom short url" /> 
	<label for="custom_url">(optional slug)</label> 
	
	<?php if(isset($error)): ?>
		<br />
		<input type="checkbox" name="overwrite" value="1" id="lm_overwrite" /> <label for="lm_overwrite">Overwrite existing slug <small><strong>(Bad for the Internet&trade;)</strong></small></label>
	<?php endif; ?>
	
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