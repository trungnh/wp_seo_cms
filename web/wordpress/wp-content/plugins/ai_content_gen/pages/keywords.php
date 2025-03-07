<?php
// Lấy danh sách categories
$categories = get_categories([
    'hide_empty' => false // Hiển thị cả category rỗng
]);

// Lấy danh sách tác giả, editor, admin
$args = [
    'role__in' => ['author', 'editor', 'administrator'], // Lọc theo vai trò
    'orderby'  => 'display_name',
    'order'    => 'ASC'
];
$users = get_users($args);

if (isset($_POST['acg_keywords'])) {
	global $wpdb;

	$keywordLines = explode(PHP_EOL, $_POST['acg_keywords']);

	//$sqlStr = "INSERT INTO `{$wpdb->prefix}search_keywords` (`keywords`, `search`, `status`) VALUES ";
	$tableName = $wpdb->prefix . "search_keywords";
	foreach ($keywordLines as $line) {
		$lineArr = explode('|', $line);
		$keywordTmp = trim($lineArr[0]);
		$searchTmp = isset($lineArr[1]) ? trim($lineArr[1]) : 0;
		$searchTmp = (int) $searchTmp;
		$searchTmp = min($searchTmp, 1);

		$data = ['keywords' => $keywordTmp, 'category_id' => $_POST['category_id'], 'user_id' => $_POST['user_id'], 'search' => $searchTmp, 'status' => 0];
		try {
			$wpdb->insert($tableName, $data); 	
			createProcessKeywordsFlag();

		} catch(Exception $e) {}
		
	}
	wp_redirect( admin_url( '/admin.php?page=acg-keywords-list' ) );
}

?>

<div class="wrap">
	<h2>Keywords</h2>
	<p></p>
	<form method="post" action="">
		<table>
			<tr>
				<td><label for="user_id">Chọn author: </label></td>
				<td>
					<select name="user_id" id="user_id" required>
		                <option value="">-- Chọn tác giả --</option>
		                <?php foreach ($users as $user) : ?>
		                    <option value="<?php echo $user->ID; ?>">
		                        <?php echo esc_html($user->display_name); ?>
		                    </option>
		                <?php endforeach; ?>
		            </select>
				</td>
			</tr>
			<tr>
				<td><label for="category">Chọn danh mục bài viết: </label></td>
				<td>
					<select name="category_id" id="category" required>
		                <option value="">-- Chọn danh mục --</option>
		                <?php foreach ($categories as $category) : ?>
		                    <option value="<?php echo $category->term_id; ?>" <?php selected($selected_cat, $category->term_id); ?>>
		                        <?php echo $category->name; ?>
		                    </option>
		                <?php endforeach; ?>
		            </select>
				</td>
			</tr>
		</table>
		
		<br>
		<div>
			<label for="acg_keywords"> Import Keywords (Mỗi dòng 1 key, theo định dạng: key|search)</label>
			<br>
			<textarea style="width:50%; height:500px" required id="acg_keywords" type="text" name="acg_keywords"></textarea>
		</div>                
		<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Submit"></p>
	</form>
</div>