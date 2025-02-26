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
            // Thực hiện kích hoạt bản ghi  
            break;  
    }  
}  

?>  
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
<?php
echo '<div class="tablenav"><div class="pagination">' . paginate_links($pagination_args) . '</div></div>';  