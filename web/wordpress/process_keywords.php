<?php
require './wp-load.php'; // Đường dẫn đến wp-load.php

if (checkProcessKeywordsFlag()) {
	global $wpdb;

	$keywords_table_name = $wpdb->prefix . 'search_keywords';
	$source_content_table_name = $wpdb->prefix . "crawled_source_content";

	$keySqlStr = "SELECT id, category_id, user_id, keywords FROM {$keywords_table_name} WHERE status = %d";
	$keySql = $wpdb->prepare($keySqlStr, 0);
	$rs = $wpdb->get_results($keySql, ARRAY_A);

	foreach ($rs as $item) {
		proceedKeyword($item);
		$wpdb->update($keywords_table_name, ['status' => 1], ['id' => $item['id']]);
	}
	deleteProcessKeywordsFlag();
}