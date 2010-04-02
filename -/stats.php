<?php

function record_stats($db, $url_id) {
	$stmt = $db->prepare('INSERT INTO '.DB_PREFIX.'url_stats (url_id, ip_address, referer, created_on) VALUES(?,?,?,?)');
	$stmt->bindValue(1, $url_id);
	$stmt->bindValue(2, $_SERVER['REMOTE_ADDR']);
	$stmt->bindValue(3, isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null);
	$stmt->bindValue(4, date('Y-m-d H:i:s'));
	$stmt->execute();
}

function stats_top_urls($db, $count=10) {
	$stmt = $db->query('SELECT u.id,u.url,u.custom_url,COUNT(s.url_id) as hits FROM '.DB_PREFIX.'urls u LEFT JOIN '.DB_PREFIX.'url_stats s ON u.id = s.url_id GROUP BY u.id,u.url ORDER BY hits desc LIMIT '.$count);
	return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function stats_top_referers($db, $count=10) {
	$query = 'SELECT s.referer,COUNT(s.referer) as hits FROM '.DB_PREFIX.'url_stats s WHERE s.referer IS NOT NULL GROUP BY s.referer ORDER BY hits DESC LIMIT '.$count;
	$stmt = $db->query($query);
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	return $rows;
}