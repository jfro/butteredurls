<?php include('stubs/header.php'); ?>
<p>
	<a href="/-/">Back</a>
</p>
<table border="0" cellspacing="0" cellpadding="10">
	<tr>
		<th>URL</th>
		<th>Lessn'd</th>
		<th>Hits</th>
	</tr>
	
<? foreach($top_urls as $url) { ?>
<tr>
	<td><?=$url['url']?></td>
	<td><?=LESSN_URL.base_convert($url['id'], 10, 36);?></td>
	<td><?=$url['hits']?></td>
</tr>
<? } ?>

</table>

<table border="0" cellspacing="0" cellpadding="10">
	<tr>
		<th>Referer</th>
		<th>Hits</th>
	</tr>
	
<? foreach($top_referers as $url) { ?>
<tr>
	<td><?=$url['referer']?></td>
	<td><?=$url['hits']?></td>
</tr>
<? } ?>

</table>

<?php include('stubs/footer.php'); ?>
