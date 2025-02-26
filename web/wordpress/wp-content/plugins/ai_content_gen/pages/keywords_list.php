<?php

global $wpdb;

$table_name = $wpdb->prefix . 'search_keywords';
$records_per_page = 10; 
$total_records = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");  
$total_pages = ceil($total_records / $records_per_page);  
// Lấy trang hiện tại  
$current_page = isset($_GET['paged']) ? (int) $_GET['paged'] : 1;  
$current_page = max(1, $current_page); // Đảm bảo không nhỏ hơn 1  
// Tính toán OFFSET  
$offset = ($current_page - 1) * $records_per_page;  
$pagination_args = [  
        'total' => $total_pages,  
        'current' => $current_page,  
        'format' => '?paged=%#%',  
        'show_all' => false,  
        'prev_next' => true,  
        'prev_text' => __('&laquo; Previous'),  
        'next_text' => __('Next &raquo;'),  
        'end_size' => 1,  
        'mid_size' => 2,  
    ]; 

// Truy vấn dữ liệu với LIMIT và OFFSET  
$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';  
$sql = "SELECT * FROM " . $table_name;  
if ($status_filter) {  
    $sql .= $wpdb->prepare(" WHERE status = %s", $status_filter);  
}  
$sql .= " LIMIT %d OFFSET %d";  
$data = $wpdb->get_results($wpdb->prepare($sql, $records_per_page, $offset), ARRAY_A);

// Xử lý bulk_action
if (isset($_POST['bulk_action']) && !empty($_POST['keywords_ids'])) {  
    $action = sanitize_text_field($_POST['bulk_action']);  
    $selected_ids = $_POST['keywords_ids'];  

    switch ($action) {  
        case 'delete':  
            // Thực hiện xóa bản ghi  
            break;  
        case 'crawl':  
        	crawlSearchTopByKeywordsIds($_POST['keywords_ids']);

            break;  
    }  
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

				$source_content_data = [
					'keywords_id' 	=> $item['id'],
					'link' 			=> $object_item->link,
					'title' 		=> $object_item->title,
					'description' 	=> $object_item->snippet,
					'content'		=> '',
					'status'		=> 0
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

function crawlSearchTopbyKeyword($keyword)
{
	$acg_options = get_option('acg_settings_option_name');
    $crawl_search_Endpoint = $acg_options['endpoint_crawl_search'] ?? 'https://google.serper.dev/search';
    $crawl_search_Token = $acg_options['token_crawl_search'] ?? '';
    $crawl_search_number = $acg_options['number_of_result_2'] ?? 5;

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

?>  
<div class="wrap">
	<h2>Keywords List</h2>
		<script type="text/javascript">  
		        document.addEventListener('DOMContentLoaded', function() {  
		            const checkAll = document.getElementById('check-all');  
		            const rowCheckboxes = document.querySelectorAll('.row-checkbox');  

		            checkAll.addEventListener('change', function() {  
		                rowCheckboxes.forEach((checkbox) => {  
		                    checkbox.checked = checkAll.checked;  
		                });  
		            });  
		        });  
		    </script>  
		<form method="get">  
		    <input type="hidden" name="page" value="acg-keywords-list" />  
		    <label for="status">Status:</label>  
		    <select name="status" id="status">  
		        <option value="">Tất cả</option>  
		        <option value="1" <?php selected($_GET['status'], 'active'); ?>>Crawled</option>  
		        <option value="0" <?php selected($_GET['status'], 'inactive'); ?>>Not Crawl</option>  
		    </select>  
		    <input type="submit" value="Filter" class="button" />  
		</form>  
		<form method="post" action="">  
			<input type="hidden" name="page" value="my-admin-table" />  
			<label for="status">Hành động:</label>
		    <select name="bulk_action">  
		        <option value="">Chọn hành động</option>  
		        <option value="crawl">Crawl</option>  
		        <option value="delete">Xoá</option>  
		    </select>  
		    <input type="submit" value="Thực hiện" class="button" />  
			<table class="wp-list-table widefat fixed striped">  
			    <thead>  
			        <tr>  
			        	<th scope="col"><input type="checkbox" id="check-all" /></th>  
			            <th scope="col">Keyword</th>  
			            <th scope="col">Search</th>  
			            <th scope="col">Status</th>  
			        </tr>  
			    </thead>  
			    <tbody>  
			        <?php foreach ($data as $row) : ?>  
			            <tr>  
			            	<td><input type="checkbox" name="keywords_ids[]" value="<?php echo esc_attr($row['id']); ?>" class="row-checkbox"/></td>  
			                <td><?php echo esc_html($row['keywords']); ?></td>  
			                <td><?php echo esc_html($row['search']); ?></td>  
			                <td><?php echo $row['status'] == 0 ? '<span style="color:red; font-weight:bold">&#10005;</span>' : '<span style="color:green; font-weight:bold">&#10003;</span>'; ?></td>  
			            </tr>  
			        <?php endforeach; ?>  
			    </tbody>  
			</table> 
		</form>
</div>
<?php
echo '<div class="tablenav"><div class="pagination">' . paginate_links($pagination_args) . '</div></div>';  