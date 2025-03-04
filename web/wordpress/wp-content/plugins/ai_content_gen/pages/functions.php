<?php 
add_action( 'admin_menu', 'acg_register_menu_document', 9, 0);

function acg_register_menu_document() {  
    add_menu_page(
        'AI Content Generate',    // page title
        'AI Content Generate',    // menu title
        'manage_options',         // capability
        'acg-keywords',           // menu slug
        'acg_keywords_render',    // callback function
        'dashicons-rss'
    );
    add_submenu_page('acg-keywords', 'Keywords List', 'Keywords List', 'manage_options', 'acg-keywords-list', 'acg_keywords_list_render');  
    add_submenu_page('acg-keywords', 'Content List', 'Content List', 'manage_options', 'acg-content-list', 'acg_content_list_render');  
}  

function acg_keywords_render()
{
  require_once ACG_PLUGIN_DIR . '/pages/keywords.php';
}

function acg_keywords_list_render()
{
  require_once ACG_PLUGIN_DIR . '/pages/keywords_list.php';
}

function acg_content_list_render()
{
  require_once ACG_PLUGIN_DIR . '/pages/content_list.php';
}

// Xử lý AJAX khi bấm "Tạo Dàn Bài"
function process_gemini_bulk_action() {
    if (!isset($_POST['keyword_ids']) || !is_array($_POST['keyword_ids'])) {
        wp_send_json_error("Không có bài viết nào được chọn!");
    }

    $keyword_ids = array_map('intval', $_POST['keyword_ids']);
    $process = new Crawled_Source_Content_Process();

    foreach ($keyword_ids as $id) {
        $process->push_to_queue($id);
    }

    $process->save()->dispatch();
    
    wp_send_json_success("Đã gửi các bài viết vào hàng đợi để xử lý!");
}
add_action('wp_ajax_generate_gemini_outline', 'process_gemini_bulk_action');

function callGemini($content)
{
  $acg_options = get_option('acg_settings_option');

  if (!isset($acg_options['gemini_token'])) {
        log_message("API Key chưa được thiết lập!");
        return false;
  }

  $api_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $acg_options['gemini_token'];
    
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $api_url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'contents' => [
        ['parts' => [['text' => $content]]]
    ]
  ]));

  $response = curl_exec($ch);
  $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  //log_message("Phản hồi từ API Gemini: " . $response);

  if ($http_code !== 200) {
    log_message("Lỗi API: Mã lỗi HTTP " . $http_code);
    return false;
  }

  $data = json_decode($response, true);
  if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
    log_message("API không trả về dữ liệu hợp lệ.");
    return false;
  }

  $result_text = $data['candidates'][0]['content']['parts'][0]['text'];
  //log_message("Nội dung phân tích: " . $result_text);

  return trim($result_text);    

}

function crawlSearchTopByKeywordsIds($ids)
{
  global $wpdb;

  $keywords_table_name = $wpdb->prefix . 'search_keywords';
  $source_content_table_name = $wpdb->prefix . "crawled_source_content";

  $keywords_ids = implode(',', $ids);
  $parsePar = trim(str_repeat( '%d,', count($ids)), ',');
    $keySqlStr = "SELECT id, keywords FROM {$keywords_table_name} WHERE id IN ({$parsePar})";
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
          'content'   => '',
          'status'    => 0
        ];
        try {
          // insert kết quả vào DB
          $wpdb->insert($source_content_table_name, $source_content_data); 
          // update status keyword vừa crawl
          $wpdb->update($keywords_table_name, ['status' => 1], ['id' => $item['id']]);

        } catch(Exception $e) {}
      }
      }
    }
}

function crawlContentByIds($ids)
{
    global $wpdb;
    $source_content_table_name = $wpdb->prefix . "crawled_source_content";

    $parsePar = trim(str_repeat( '%d,', count($ids)), ',');
    $sqlStr = "SELECT id, link FROM {$source_content_table_name} WHERE id IN ({$parsePar})";
    $sql = $wpdb->prepare($sqlStr, $ids);
    $rs = $wpdb->get_results($sql, ARRAY_A);

    foreach ($rs as $item) {
      try {
        // Crawl content từ link
        $content = crawlContentByUrl($item['link']);
        // update vào DB
        $wpdb->update($source_content_table_name, ['content' => $content['content']], ['id' => $item['id']]);

      } catch(Exception $e) {
      }
    }
}

function crawlContent()
{
    global $wpdb;
    $source_content_table_name = $wpdb->prefix . "crawled_source_content";

    $sqlStr = "SELECT id, link FROM {$source_content_table_name} WHERE content = %s";
    $sql = $wpdb->prepare($sqlStr, '');
    $rs = $wpdb->get_results($sql, ARRAY_A);

    foreach ($rs as $item) {
      try {
        // Crawl content từ link
        $content = crawlContentByUrl($item['link']);
        // update vào DB
        $wpdb->update($source_content_table_name, ['content' => $content['content']], ['id' => $item['id']]);

      } catch(Exception $e) {}
    }
}

function crawlContentByUrl($url)
{
  $acg_options = get_option('acg_settings_option');
  $crawl4ai_Endpoint = $acg_options['endpoint_crawl_content_api_crawl4ai'] ?? 'http://localhost:11235/crawl';

  $body = array(
        'url'           => $url,
        'content_type'  => 'markdown',
        'depth'         => 0,
        'data'          => array(
            'query'     => 'AI technology'
        )
    );

  $response = wp_remote_post($crawl4ai_Endpoint, array(
        'method'    => 'POST',
        'body'      => json_encode($body),
        'headers'   => array(
            'Content-Type' => 'application/json',
        ),
        'timeout'   => 30, 
    ));

    $body = wp_remote_retrieve_body($response);

    return json_decode($body, true);

}

function crawlSearchTopbyKeyword($keyword)
{
  $acg_options = get_option('acg_settings_option');
  $crawl_search_Endpoint = $acg_options['endpoint_crawl_search'] ?? 'https://google.serper.dev/search';
  $crawl_search_Token = $acg_options['token_crawl_search'] ?? '';
  $crawl_search_number = $acg_options['number_of_result'] ?? 10;

  $params = [
        'q' => $keyword, 
        'num' => $crawl_search_number
      ];

  $header = [
        'X-API-KEY:' . $crawl_search_Token,
      'Content-Type: application/json'
        ];

  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => $crawl_search_Endpoint,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>json_encode($params), // '{"q":"apple inc","num":20}',
    CURLOPT_HTTPHEADER => $header,
  ));

  $response = curl_exec($curl);

  curl_close($curl);

  return json_decode($response);
}

function _truncate_string ($string, $maxlength, $extension) {
    
    // Set the replacement for the "string break" in the wordwrap function
    $cutmarker = " ";

    // Checking if the given string is longer than $maxlength
    if (strlen($string) > $maxlength) {

      // Using wordwrap() to set the cutmarker
      // NOTE: wordwrap (PHP 4 >= 4.0.2, PHP 5)
      $string = wordwrap($string, $maxlength, $cutmarker);

      // Exploding the string at the cutmarker, set by wordwrap()
      $string = explode($cutmarker, $string);

      // Adding $extension to the first value of the array $string, returned by explode()
      $string = $string[0] . $extension;
    }

    // returning $string
    return $string;

}