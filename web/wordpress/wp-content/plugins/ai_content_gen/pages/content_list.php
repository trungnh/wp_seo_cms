<?php

global $wpdb;

$table_name = $wpdb->prefix . 'crawled_source_content';
$search_keywords_table_name = $wpdb->prefix . 'search_keywords';
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
$sql = "SELECT {$table_name}.*, {$search_keywords_table_name}.keywords FROM {$table_name} LEFT JOIN {$search_keywords_table_name} ON {$table_name}.keywords_id = {$search_keywords_table_name}.id";
if ($status_filter) {  
    $sql .= $wpdb->prepare(" WHERE status = %s", $status_filter);  
}  
$sql .= " LIMIT %d OFFSET %d";  
$data = $wpdb->get_results($wpdb->prepare($sql, $records_per_page, $offset), ARRAY_A);

// Xử lý bulk_action
if (isset($_POST['bulk_action']) && !empty($_POST['source_ids'])) {  
    $action = sanitize_text_field($_POST['bulk_action']);  
    $selected_ids = $_POST['source_ids'];  

    switch ($action) {  
        case 'delete':  
            // Thực hiện xóa bản ghi  
            break;  

        	// Chatgpt convert
            break;  
    }  
}  

function convertContent()
{
	global $wpdb;
    $source_content_table_name = $wpdb->prefix . "crawled_source_content";

    $sqlStr = "SELECT id, content FROM {$source_content_table_name} WHERE status = %d";
    $sql = $wpdb->prepare($sqlStr, 0);
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

function chatGPTConvert()
{
	$acg_options = get_option('acg_settings_option');
	$chatgpt_token = $acg_options['chatgpt_token'] ?? '';

	$api_key = "YOUR_OPENAI_API_KEY";

	$url = "https://api.openai.com/v1/chat/completions";

	$promt = "Bạn là một chuyên gia viết bài chuẩn SEO. Hãy viết lại bài viết dưới đây theo tiêu chuẩn tối ưu SEO, đảm bảo bài có cấu trúc rõ ràng (mở bài, thân bài, kết luận), có mục lục chi tiết, và sử dụng từ khóa chính một cách tự nhiên nhưng hiệu quả. Tuyệt đối không sao chép nguyên văn nội dung từ bất kỳ nguồn nào, hãy viết lại theo cách riêng để tránh vi phạm bản quyền";
	$message = "Hãy viết lại bài viết sau đây theo chuẩn SEO:\n\n[NỘI DUNG BÀI VIẾT GỐC]\n\nTừ khóa chính cần tập trung: [TỪ KHÓA CHÍNH], [TỪ KHÓA PHỤ 1], [TỪ KHÓA PHỤ 2]...\n\nYêu cầu:\n1. Bài viết có tiêu đề hấp dẫn, chứa từ khóa chính.\n2. Viết mở bài hấp dẫn, ngắn gọn, giới thiệu chủ đề bài viết.\n3. Tạo một mục lục (Table of Contents) liệt kê các phần chính của bài viết.\n4. Viết lại thân bài mạch lạc, có các tiêu đề phụ (H2, H3) và lồng ghép từ khóa tự nhiên.\n5. Kết luận súc tích, có lời kêu gọi hành động (nếu phù hợp).\n6. Độ dài bài viết tối thiểu [SỐ TỪ] từ.\n7. Giữ văn phong tự nhiên, không lặp lại câu chữ một cách vô nghĩa.\n\nHãy bắt đầu viết!";

	$data = [
	    "model" => "gpt-4",
	    "messages" => [
	        ["role" => "system", "content" => $promt],
	        ["role" => "user", "content" => $message]
	    ],
	    "temperature" => 0.7,
	    "max_tokens" => 200
	];

	$headers = [
	    "Content-Type: application/json",
	    "Authorization: Bearer " . $chatgpt_token
	];

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$response = curl_exec($ch);
	curl_close($ch);

	$result = json_decode($response, true);
	echo $result['choices'][0]['message']['content'];
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

		        /*jQuery(document).ready(function($) {
				    $('#do_action_dan_bai').on('click', function(e) {
				        var action = $('#bulk-action-selector-top').val();
				        if (action !== 'generate_gemini_outline') return;

				        e.preventDefault();

				        var keyword_ids = [];
				        $('tbody th.check-column input:checked').each(function() {
				            keyword_ids.push($(this).val());
				        });

				        if (keyword_ids.length === 0) {
				            alert("Vui lòng chọn ít nhất một bài viết!");
				            return;
				        }

				        $.ajax({
				            url: gemini_ajax.ajax_url,
				            type: 'POST',
				            data: {
				                action: 'generate_gemini_outline',
				                post_ids: keyword_ids,
				                nonce: gemini_ajax.nonce
				            },
				            beforeSend: function() {
				                alert("Đang gửi bài viết vào hàng đợi...");
				            },
				            success: function(response) {
				                alert(response.data);
				                location.reload();
				            }
				        });
				    });
				});*/
		    </script>  
		<form method="get">  
		    <input type="hidden" name="page" value="acg-content-list" />  
		    <label for="status">Status:</label>  
		    <select name="status" id="status">  
		        <option value="">Tất cả</option>  
		        <option value="1" <?php selected($_GET['status'], 'active'); ?>>Convertd</option>  
		        <option value="0" <?php selected($_GET['status'], 'inactive'); ?>>Not Convert</option>  
		    </select>  
		    <input type="submit" value="Filter" class="button" />  
		</form>  
		<!-- <br>
		<div>
			<label for="status">Hành động chính:</label>
			<button id="do_action_dan_bai" class="button">Tạo dàn bài</button>
			<button id="do_action_viet_bai" class="button">Tạo bài viết</button>
		</div>
		<br> -->
		<form method="post" action="">  
			<input type="hidden" name="page" value="my-admin-table" />  
			<label for="status">Hành động phụ:</label>
		    <select name="bulk_action">  
		        <option value="">Chọn hành động</option>  
		        <option value="delete">Xoá</option>  
		    </select>  
		    <input type="submit" value="Thực hiện" class="button" />  
			<table class="wp-list-table widefat fixed striped">  
			    <thead>  
			        <tr>  
			        	<td class="column-cb check-column"><input type="checkbox" id="check-all" /></td>  
			            <th scope="col">Keyword</th>  
			            <th scope="col">Title</th>  
			            <th scope="col">Description</th>  
			            <th scope="col">Link</th>  
			            <th scope="col">Chủ đề</th>  
			            <th scope="col">Thuộc tính chính</th> 
			            <th scope="col">Keyword chính</th> 
			            <th scope="col">Usert intent</th> 
			            <th scope="col">Tóm tắt</th> 
			            <th scope="col">Status</th>  
			        </tr>  
			    </thead>  
			    <tbody>  
			        <?php foreach ($data as $row) : ?>  
			            <tr>  
			            	<th scope="row" class="column-cb check-column"><input type="checkbox" name="source_ids[]" value="<?php echo esc_attr($row['id']); ?>" class="row-checkbox"/></th>  
			                <td><?php echo esc_html($row['keywords']); ?></td>  
			                <td><?php echo esc_html($row['title']); ?></td>  
			                <td><?php echo esc_html($row['description']); ?></td>  
			                <td><?php echo esc_html($row['link']); ?></td>  
			                <td><?php echo _truncate_string(esc_html($row['chu_de']), 100, '...'); ?></td>  
			                <td><?php echo _truncate_string(esc_html($row['thuoc_tinh_chinh']), 100, '...'); ?></td>  
			                <td><?php echo _truncate_string(esc_html($row['keyword_chinh']), 100, '...'); ?></td>  
			                <td><?php echo _truncate_string(esc_html($row['user_intent']), 100, '...'); ?></td>  
			                <td><?php echo _truncate_string(esc_html($row['tom_tat']), 100, '...'); ?></td>  
			                <td><?php echo $row['status'] == 0 ? '<span style="color:red; font-weight:bold">&#10005;</span>' : '<span style="color:green; font-weight:bold">&#10003;</span>'; ?></td>  
			            </tr>  
			        <?php endforeach; ?>  
			    </tbody>  
			</table> 
		</form>
</div>
<?php
echo '<div class="tablenav"><div class="pagination">' . paginate_links($pagination_args) . '</div></div>';  