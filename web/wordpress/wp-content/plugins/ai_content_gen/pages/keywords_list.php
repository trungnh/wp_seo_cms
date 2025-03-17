<?php
// Xử lý bulk_action
if (isset($_POST['bulk_action']) && !empty($_POST['keywords_ids'])) {  
    $action = sanitize_text_field($_POST['bulk_action']);  
    $selected_ids = $_POST['keywords_ids'];  

    switch ($action) {  
        case 'delete':  
            // Thực hiện xóa bản ghi  
        	deleteKeywords($selected_ids);
            break;  
        case 'approve':  
        	approveKeywords($selected_ids);
            break;  
    }  
}  

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

if (isset($_POST['process'])) {
	createProcessKeywordsFlag();
}

?>  
<div class="wrap">
	<h2>Keywords List</h2>
	<?php if (checkProcessKeywordsFlag()):?>
		<div id="setting-error-tgmpa" class="notice notice-success settings-error is-dismissible"> 
			<p>
				<strong>
					<span style="display: block; margin: 0.5em 0.5em 0 0; clear: both;">Có tiến trình crawler đang chạy</span>
				</strong>
			</p>
		</div>
	<?php endif;?>
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
		<form method="post" action="">
			<label for="procees">Crawler:</label>
			<input type="submit" name="process" value="Xử lý crawl tất cả keywords" class="button" />  
		</form>
		<br>
		<form method="get">  
		    <input type="hidden" name="page" value="acg-keywords-list" />  
		    <label for="status">Status:</label>  
		    <select name="status" id="status">  
		        <option value="">Tất cả</option>  
		        <option value="1" <?php selected($_GET['status'], 1); ?>>Crawled</option>  
		        <option value="0" <?php selected($_GET['status'], 0); ?>>Not Crawl</option>  
		        <option value="2" <?php selected($_GET['status'], 2); ?>>Related Keywords</option>  
		    </select>  
		    <input type="submit" value="Filter" class="button" />  
		</form>  
		<form method="post" action="">  
			<input type="hidden" name="page" value="my-admin-table" />  
			<label for="bulk_action">Hành động:</label>
		    <select name="bulk_action">  
		        <option value="">Chọn hành động</option>  
		        <option value="approve">Duyệt từ khoá để crawl</option>  
		        <option value="delete">Xoá</option>  
		    </select>  
		    <input type="submit" value="Thực hiện" class="button" />  
			<table class="wp-list-table widefat fixed striped">  
			    <thead>  
			        <tr>  
			        	<td class="column-cb check-column"><input type="checkbox" id="check-all" /></th>  
			            <th scope="col">Keyword</th>  
			            <th scope="col">Category ID</th> 
			            <th scope="col">User ID</th> 
			            <th scope="col">Search</th>  
			            <th scope="col">Status</th>  
			        </tr>  
			    </thead>  
			    <tbody>  
			        <?php foreach ($data as $row) : ?>  
			            <tr>  
			            	<th scope="row" class="column-cb check-column"><input type="checkbox" name="keywords_ids[]" value="<?php echo esc_attr($row['id']); ?>" class="row-checkbox"/></td>  
			                <td><?php echo esc_html($row['keywords']); ?></td>  
			                <td><?php echo esc_html($row['category_id']); ?></td>  
			                <td><?php echo esc_html($row['user_id']); ?></td>  
			                <td><?php echo esc_html($row['search']); ?></td>  
			                <td>
		                		<?php 
		                			if ($row['status'] == 0) {
		                				echo '<span style="color:red; font-weight:bold">&#10005;</span>';
		                			}
		                			else if ($row['status'] == 1) {
	                					echo '<span style="color:green; font-weight:bold">&#10003;</span>';
		                			}
		                			else {
                						echo '<span style="font-weight:bold">Từ khoá liên quan (Cần duyệt để crawl)</span>';
		                			}

		                		?>			                		
			                </td>  
			            </tr>  
			        <?php endforeach; ?>  
			    </tbody>  
			</table> 
		</form>
</div>
<?php
echo '<div class="tablenav"><div class="pagination">' . paginate_links($pagination_args) . '</div></div>';  