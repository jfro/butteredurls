<?php include('stubs/header.php'); ?>
<p>
	<!-- Using shortlink class. See http://ajh.us/k -->
	<input type="text" id="url" class="shortlink" value="<?php echo htmlspecialchars($new_url); ?>" onclick="this.focus();this.select();" readonly="readonly" />
	→ <strong><?php echo htmlspecialchars($url); ?></strong>
</p>
<p>
	<a href="http://twitter.com/?status=<?php echo urlencode($new_url); ?>">Tweet</a>
</p>
<script>
var input = document.getElementById('url');
input.focus();
input.select();
</script>
<?php include('stubs/footer.php'); ?>