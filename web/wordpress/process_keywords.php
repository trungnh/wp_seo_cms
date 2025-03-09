<?php
require './wp-load.php'; // Đường dẫn đến wp-load.php

if (checkLockProcessKeywords()) {
	exit;
}

lockProcessKeywords();

if (checkProcessKeywordsFlag()) {
	global $wpdb;

	$keywords_table_name = $wpdb->prefix . 'search_keywords';  

	$keySqlStr = "SELECT id, category_id, user_id, keywords FROM {$keywords_table_name} WHERE status = %d LIMIT 1";
	$keySql = $wpdb->prepare($keySqlStr, 0);
	$rs = $wpdb->get_results($keySql, ARRAY_A);

	foreach ($rs as $item) {
		proceedKeyword($item);
		$wpdb->update($keywords_table_name, ['status' => 1], ['id' => $item['id']]);
	}

	$total_records = $wpdb->get_var("SELECT COUNT(*) FROM {$keywords_table_name} WHERE status = 0");
	if ($total_records == 0) {
		deleteProcessKeywordsFlag();
	} 
}

unlockProcessKeywords();