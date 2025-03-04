<?php
/**
 * WP-Background Processing
 *
 * @package WP-Background-Processing
 */

/**
 * Plugin Name: WP Background Processing
 * Plugin URI: https://github.com/deliciousbrains/wp-background-processing
 * Description: Asynchronous requests and background processing in WordPress.
 * Author: Delicious Brains Inc.
 * Version: 1.0
 * Author URI: https://deliciousbrains.com/
 * GitHub Plugin URI: https://github.com/deliciousbrains/wp-background-processing
 * GitHub Branch: master
 */

if ( ! class_exists( 'WP_Async_Request' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'classes/wp-async-request.php';
}
if ( ! class_exists( 'WP_Background_Process' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'classes/wp-background-process.php';
}


class Crawled_Source_Content_Process extends WP_Background_Process {
    protected $action = 'Crawled_Source_Content';

    // Hàm xử lý từng item
    protected function task($item) 
    {
    	global $wpdb;
        $keyword_id = intval($item);
        if ($keyword_id) {
        	$keySqlStr = "SELECT id, keywords FROM {$keywords_table_name} WHERE id = {$keyword_id}";
		    $keySql = $wpdb->prepare($keySqlStr, $ids);
		    $rs = $wpdb->get_results($keySql, ARRAY_A);

		    foreach ($rs as $item) {
		      $response = crawlSearchTopbyKeyword($item['keywords']);
		      if (property_exists($response, 'organic')) {
		      $organic = $response->organic;
		      foreach ($response->organic as $object_item) {
		        // Loại trừ domain
		        if (strpos($object_item->link, 'youtube.com') !== false) continue;
		        if (strpos($object_item->link, 'facebook.com') !== false) continue;
		        if (strpos($object_item->link, 'tiktok.com') !== false) continue;
		        if (strpos($object_item->link, 'fbsbx.com') !== false) continue;
		        if (strpos($object_item->link, 'wikipedia.org') !== false) continue;

	        	$source_content_data = [
		          'keywords_id'   => $item['id'],
		          'link'      => $object_item->link,
		          'title'     => $object_item->title,
		          'description'   => $object_item->snippet,
		          'status'    => 0
		        ];

		        if ($object_item->link != '') {
		        	// Crawl content từ link
        			$content = crawlContentByUrl($object_item->link);
        			if ($content['content'] != '') {
    					// Lấy data dàn bài
    					$acg_prompt_options = get_option('acg_settings_prompt_option');
    					$prompt_chu_de = str_replace("{{keyword}}", $keyword, $acg_prompt_options['prompt_chu_de']);
    					$prompt_chu_de = str_replace("{{content}}", $content, $prompt_chu_de);
    					$chu_de = callGemini($prompt_chu_de);
    					$source_content_data ['chu_de'] = $chu_de;

    					$prompt_thuoc_tinh_chinh = str_replace("{{keyword}}", $keyword, $acg_prompt_options['prompt_thuoc_tinh_chinh']);
    					$prompt_thuoc_tinh_chinh = str_replace("{{content}}", $content, $prompt_thuoc_tinh_chinh);
    					$thuoc_tinh_chinh = callGemini($prompt_thuoc_tinh_chinh);
    					$source_content_data ['thuoc_tinh_chinh'] = $thuoc_tinh_chinh;

    					$prompt_keyword_chinh = str_replace("{{keyword}}", $keyword, $acg_prompt_options['prompt_keyword_chinh']);
    					$prompt_keyword_chinh = str_replace("{{content}}", $content, $prompt_keyword_chinh);
    					$keyword_chinh = callGemini($prompt_keyword_chinh);
    					$source_content_data ['keyword_chinh'] = $keyword_chinh;

    					$prompt_user_intent = str_replace("{{keyword}}", $keyword, $acg_prompt_options['prompt_user_intent']);
    					$prompt_user_intent = str_replace("{{content}}", $content, $prompt_user_intent);
    					$user_intent = callGemini($prompt_user_intent);
    					$source_content_data ['user_intent'] = $user_intent;

    					$prompt_tom_tat = str_replace("{{keyword}}", $keyword, $acg_prompt_options['prompt_tom_tat']);
    					$prompt_tom_tat = str_replace("{{content}}", $content, $prompt_tom_tat);
    					$tom_tat = callGemini($prompt_tom_tat);
    					$source_content_data ['tom_tat'] = $tom_tat;

    					$prompt_dan_bai = str_replace("{{chu_de}}", $chu_de, $acg_prompt_options['prompt_dan_bai']);
    					$prompt_dan_bai = str_replace("{{thuoc_tinh_chinh}}", $thuoc_tinh_chinh, $prompt_dan_bai);
    					$prompt_dan_bai = str_replace("{{keyword_chinh}}", $keyword_chinh, $prompt_dan_bai);
    					$prompt_dan_bai = str_replace("{{user_intent}}", $user_intent, $prompt_dan_bai);
				        $dan_bai = callGemini($prompt_dan_bai);
    					$source_content_data ['dan_bai'] = $dan_bai;
        			}
		        }
		        try {
		          // insert kết quả vào DB
		          $wpdb->insert($source_content_table_name, $source_content_data); 
		          // update status keyword vừa crawl
		          $wpdb->update($keywords_table_name, ['status' => 1], ['id' => $item['id']]);

		        } catch(Exception $e) {}
		      }
		      }
		    }

            error_log("Updated content by keyword_id: " . $keyword_id);
        }

        return false; // Trả về false để xóa item khỏi queue
    }
}