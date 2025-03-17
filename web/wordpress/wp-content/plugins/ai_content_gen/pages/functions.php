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

function lockProcessKeywords()
{
  $file_path = WP_CONTENT_DIR . '/process_keywords.lock';
  $file_content = "processing";

  file_put_contents($file_path, $file_content);
}

function checkLockProcessKeywords()
{
  $file_path = WP_CONTENT_DIR . '/process_keywords.lock';

  if (file_exists($file_path)) {
      return true;
  }

  return false;
}

function unlockProcessKeywords()
{
  $file_path = WP_CONTENT_DIR . '/process_keywords.lock';
  if (file_exists($file_path)) {
      unlink($file_path);
  }
}


function createProcessKeywordsFlag()
{
  $file_path = WP_CONTENT_DIR . '/process_keywords.flag';
  $file_content = "processing";

  file_put_contents($file_path, $file_content);
}

function checkProcessKeywordsFlag()
{
  $file_path = WP_CONTENT_DIR . '/process_keywords.flag';

  if (file_exists($file_path)) {
      return true;
  }

  return false;
}

function deleteProcessKeywordsFlag()
{
  $file_path = WP_CONTENT_DIR . '/process_keywords.flag';
  if (file_exists($file_path)) {
      unlink($file_path);
  }
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

function crawlSearchTopByKeywordsIds($ids)
{
  global $wpdb;

  $keywords_table_name = $wpdb->prefix . 'search_keywords';
  $source_content_table_name = $wpdb->prefix . "crawled_source_content";

  $keywords_ids = implode(',', $ids);
  $parsePar = trim(str_repeat( '%d,', count($ids)), ',');
    $keySqlStr = "SELECT id, category_id, user_id, keywords FROM {$keywords_table_name} WHERE id IN ({$parsePar})";
    $keySql = $wpdb->prepare($keySqlStr, $ids);
    $rs = $wpdb->get_results($keySql, ARRAY_A);

    foreach ($rs as $item) {
      proceedKeyword($item);
      // update status keyword vừa crawl
      $wpdb->update($keywords_table_name, ['status' => 1], ['id' => $item['id']]);
    }
}

function approveKeywords ($ids) 
{
  global $wpdb;

  $keywords_table_name = $wpdb->prefix . 'search_keywords';
  $keywords_ids = implode(',', $ids);
  foreach ($keywords_ids as $id) {
    // update status keyword
    $wpdb->update($keywords_table_name, ['status' => 0], ['id' => $id]);
  }
}

function deleteKeywords ($ids) 
{
  global $wpdb;

  $keywords_table_name = $wpdb->prefix . 'search_keywords';
  $keywords_ids = implode(',', $ids);
  foreach ($keywords_ids as $id) {
    // delete keyword
    $wpdb->update($keywords_table_name, ['id' => $id]);
  }
}

function proceedKeyword($keyword_data)
{
  global $wpdb;
  $keywords_table_name = $wpdb->prefix . "search_keywords";
  $source_content_table_name = $wpdb->prefix . "crawled_source_content";
  $dan_bai_table_name = $wpdb->prefix . "dan_bai";

  $acg_options = get_option('acg_settings_option');
  $crawl_search_number = $acg_options['number_of_result'] ?? 10;

  print_to_screen("Bat dau xu ly keyword: " . $keyword_data['keywords']);
  $response = crawlSearchTopbyKeyword($keyword_data['keywords']);
  if (property_exists($response, 'organic')) {
    $chu_de_text = "";
    $thuoc_tinh_chinh_text = "";
    $keyword_chinh_text = "";
    $user_intent_text = "";
    $noi_dung_rut_gon = "";

    $title = '';
    $description = '';
    $acg_prompt_options = get_option('acg_settings_option');
    $exclude_domains = explode(PHP_EOL, $acg_prompt_options['exclude_crawl_search']);
    $trim_exclude_domains = array_map('trim', $exclude_domains);

    $organic = $response->organic;
    $rs_count = 1;

    // Save relatedSearches

    foreach ($response->relatedSearches as $relatedSearches) {
      if (trim($relatedSearches->query) == '') continue;

      try {
        $related_keyword_data = [
          'category_id' => $keyword_data['category_id'],
          'user_id'     => $keyword_data['user_id'],
          'keywords'    => $relatedSearches->query,
          'search'      => 0,
          'status'      => 2
        ];

        $wpdb->insert($keywords_table_name, $related_keyword_data);

      } catch(Exception $e) {}
    }

    foreach ($response->organic as $object_item) {
      if ($object_item->link != '') {
        // Loại trừ domain
        $host = parse_url($object_item->link, PHP_URL_HOST);
        // Loại bỏ "www." nếu có
        $host = preg_replace('/^www\./', '', $host);
        $host = preg_replace('/^m\./', '', $host);
        if (in_array($host, $trim_exclude_domains)) {
          continue;
        }

        // Crawl content từ link
        print_to_screen("Crawl link: " . $object_item->link);
        $content = crawlContentByUrl($object_item->link);
        if (isset($content['content']) & $content['content'] != '') {
          $source_content_data = [
            'keywords_id'   => $keyword_data['id'],
            'link'          => $object_item->link,
            'title'         => $object_item->title,
            'description'   => $object_item->snippet,
            'status'        => 0
          ];

          if ($title = '') {
            $title = $object_item->title;
          }
          $title .= '-' . $object_item->title . PHP_EOL;
          $description .= '-' . $object_item->snippet . PHP_EOL;

          // Lấy data dàn bài
          print_to_screen("Lay chu de");
          $prompt_chu_de = str_replace("{{keyword}}", $keyword_data['keywords'], $acg_prompt_options['prompt_chu_de']);
          $prompt_chu_de = str_replace("{{content}}", $content['content'], $prompt_chu_de);
          $chu_de = callGemini($prompt_chu_de);
          $source_content_data ['chu_de'] = $chu_de;
          $chu_de_text .= trim($chu_de);

          print_to_screen("Lay thuoc tinh chinh");
          $prompt_thuoc_tinh_chinh = str_replace("{{keyword}}", $keyword_data['keywords'], $acg_prompt_options['prompt_thuoc_tinh_chinh']);
          $prompt_thuoc_tinh_chinh = str_replace("{{content}}", $content['content'], $prompt_thuoc_tinh_chinh);
          $thuoc_tinh_chinh = callGemini($prompt_thuoc_tinh_chinh);
          $source_content_data ['thuoc_tinh_chinh'] = $thuoc_tinh_chinh;
          $thuoc_tinh_chinh_text .= trim($thuoc_tinh_chinh);

          print_to_screen("Lay keyword chinh");
          $prompt_keyword_chinh = str_replace("{{keyword}}", $keyword_data['keywords'], $acg_prompt_options['prompt_keyword_chinh']);
          $prompt_keyword_chinh = str_replace("{{content}}", $content['content'], $prompt_keyword_chinh);
          $keyword_chinh = callGemini($prompt_keyword_chinh);
          $source_content_data ['keyword_chinh'] = $keyword_chinh;
          $keyword_chinh_text .= trim($keyword_chinh);

          print_to_screen("Lay user intent");
          $prompt_user_intent = str_replace("{{keyword}}", $keyword_data['keywords'], $acg_prompt_options['prompt_user_intent']);
          $prompt_user_intent = str_replace("{{content}}", $content['content'], $prompt_user_intent);
          $user_intent = callGemini($prompt_user_intent);
          $source_content_data ['user_intent'] = $user_intent;
          $user_intent_text .= trim($user_intent);

          print_to_screen("Lay tom tat");
          $prompt_tom_tat = str_replace("{{keyword}}", $keyword_data['keywords'], $acg_prompt_options['prompt_tom_tat']);
          $prompt_tom_tat = str_replace("{{content}}", $content['content'], $prompt_tom_tat);
          $tom_tat = callGemini($prompt_tom_tat);
          $source_content_data ['tom_tat'] = $tom_tat;
          $noi_dung_rut_gon .= trim($tom_tat);

          try {
            $crawled_content_id = $wpdb->insert($source_content_table_name, $source_content_data); 
          } catch (Exception $e) {
            acg_log("Lỗi save DB: " . $source_content_table_name . ": " . curl_error($ch));
          }
        }
      }

      if ($rs_count++ >= $crawl_search_number) {
        break;
      }
    }

    print_to_screen("Lay dan bai");
    $prompt_dan_bai = str_replace("{{chu_de}}", $chu_de_text, $acg_prompt_options['prompt_dan_bai']);
    $prompt_dan_bai = str_replace("{{thuoc_tinh_chinh}}", $thuoc_tinh_chinh_text, $prompt_dan_bai);
    $prompt_dan_bai = str_replace("{{keyword_chinh}}", $keyword_chinh_text, $prompt_dan_bai);
    $prompt_dan_bai = str_replace("{{user_intent}}", $user_intent_text, $prompt_dan_bai);
    $dan_bai = callGemini($prompt_dan_bai);
    
    if ($dan_bai != '') {
      try {
            $dan_bai_data = [
              'keywords_id'   => $keyword_data['id'],
              'dan_bai'       => $dan_bai
            ];
            $wpdb->insert($dan_bai_table_name, $dan_bai_data); 
          } catch (Exception $e) {
            acg_log("Lỗi save DB: " . $source_content_table_name . ": " . curl_error($ch));
          }
      generateArticle($keyword_data, $title, $description, $dan_bai, $noi_dung_rut_gon);
    }
  }
}

function generateArticle($keyword_data, $title, $dan_bai, $noi_dung_rut_gon) 
{
  global $wpdb;
  $keywords_table_name = $wpdb->prefix . 'search_keywords';
  $source_content_table_name = $wpdb->prefix . "crawled_source_content";

  // Tạo bài viết
  print_to_screen("Viet bai");
  $acg_options = get_option('acg_settings_option');
  $deepseek_prompt = $acg_options['deepseek_prompt_viet_bai'];
  $deepseek_prompt = str_replace('{{keyword}}', $keyword_data['keywords'], $deepseek_prompt);
  $deepseek_prompt = str_replace('{{dan_bai}}', $dan_bai, $deepseek_prompt);
  $deepseek_prompt = str_replace('{{tom_tat}}', $noi_dung_rut_gon, $deepseek_prompt);

  $deepseek_prompt_title = $acg_options['deepseek_prompt_title'];
  $deepseek_prompt_title = str_replace('{{keyword}}', $keyword_data['keywords'], $deepseek_prompt_title);
  $deepseek_prompt_title = str_replace('{{user_intent}}', $title, $deepseek_prompt_title);
  $deepseek_prompt_title = str_replace('{{title}}', $title, $deepseek_prompt_title);

  $deepseek_prompt_description = $acg_options['deepseek_prompt_description'];
  $deepseek_prompt_description = str_replace('{{keyword}}', $keyword_data['keywords'], $deepseek_prompt_description);
  $deepseek_prompt_description = str_replace('{{user_intent}}', $title, $deepseek_prompt_description);
  $deepseek_prompt_description = str_replace('{{title}}', $title, $deepseek_prompt_description);

  $article_title = callDeepseek($deepseek_prompt_title);
  $article_excerpt = callDeepseek($deepseek_prompt_description);
  $article_content = callDeepseek($deepseek_prompt);

  // Đăng bài viết lên WordPress
  print_to_screen("Dang bai viet");
  $post_id = wp_insert_post([
      'post_title'   => ucfirst($article_title),
      'post_content' => $article_content,
      'post_excerpt' => $article_excerpt,
      'post_status'  => 'publish',
      'post_author'  => $keyword_data['user_id'],
      'post_category' => [$keyword_data['category_id']] // ID danh mục
  ]);
  if ($post_id) {
    $wpdb->update($source_content_table_name, ['status' => 1], ['keywords_id' => $keyword_data['id']]);
  }
}

function callDeepseek($prompt, $system_prompt = '') 
{
    $acg_options = get_option('acg_settings_option');
    $api_url = 'https://api.deepseek.com/v1/chat/completions';
    
    $data = [
        'model' => 'deepseek-chat',
        'messages' => [
            ['role' => 'user', 'content' => $prompt]
          ]
        ];

    if (!empty($system_prompt)) {
      $data = [
        'model' => 'deepseek-chat',
        'messages' => [
            ['role' => 'system', 'content' => $system_prompt],
            ['role' => 'user', 'content' => $prompt]
          ]
        ];
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $acg_options['deepseek_token']
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        acg_log("Lỗi khi gọi API: " . curl_error($ch));
        curl_close($ch);

        return '';
    }
    curl_close($ch);
    
    $decoded_response = json_decode($response, true);
    
    if (isset($decoded_response['choices'][0]['message']['content'])) {
        return $decoded_response['choices'][0]['message']['content'];
    } else {
        acg_log("API không trả về nội dung hợp lệ: " . $response);

        return '';
    }
}

function callGemini($content)
{
  $acg_options = get_option('acg_settings_option');

  if (!isset($acg_options['gemini_token'])) {
        acg_log("API Key chưa được thiết lập!");
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

  //acg_log("Phản hồi từ API Gemini: " . $response);

  if ($http_code !== 200) {
    acg_log("Lỗi API: Mã lỗi HTTP " . $http_code);
    return false;
  }

  $data = json_decode($response, true);
  if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
    acg_log("API không trả về dữ liệu hợp lệ.");
    return false;
  }

  $result_text = $data['candidates'][0]['content']['parts'][0]['text'];
  //acg_log("Nội dung phân tích: " . $result_text);

  return trim($result_text);    

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

  $params = [
        'q' => $keyword, 
        'num' => 50
      ];
  $params['hl'] = $acg_options['lang_crawl_search'] ?? 'vi';
  $params['gl'] = $acg_options['location_crawl_search'] ?? 'vn';

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

function acg_log($message) {
    $log_file = WP_CONTENT_DIR . '/acg_log.txt';
    $log_entry = date('Y-m-d H:i:s') . " - " . $message . "\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
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

function print_to_screen($mess)
{
  echo $mess . PHP_EOL;
}