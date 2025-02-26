<?php 
add_action( 'admin_menu', 'acg_register_menu_document', 9, 0);

function acg_register_menu_document() {  
    add_menu_page(
        'AI Content Generate',     // page title
        'AI Content Generate',     // menu title
        'manage_options',   // capability
        'acg-keywords',     // menu slug
        'acg_keywords_render', // callback function
        'dashicons-rss'
    );
    add_submenu_page('acg-keywords', 'Keywords List', 'Keywords List', 'manage_options', 'acg-keywords-list', 'acg_keywords_list_render');  
    //add_submenu_page('acg-keywords', 'Content List', 'Keywords List', 'manage_options', 'acg-keywords-list', 'acg_keywords_list_render');  
}  

function acg_keywords_render()
{
  require_once ACG_PLUGIN_DIR . '/pages/keywords.php';
}

function acg_keywords_list_render()
{
  require_once ACG_PLUGIN_DIR . '/pages/keywords_list.php';
}

function crawlSearchTopbyKeyword($keyword)
{
  $acg_options = get_option('acg_settings_option_name');
  $crawl_search_Endpoint = $acg_options['endpoint_crawl_search'] ?? 'https://google.serper.dev/search';
  $crawl_search_Token = $acg_options['token_crawl_search'] ?? '';
  $crawl_search_number = $acg_options['number_of_result_2'] ?? 10;

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