<?php
add_action( 'admin_menu', 'acg_keywords_register' );

function acg_keywords_register()
{
    add_menu_page(
        'AI Content Generate',     // page title
        'AI Content Generate',     // menu title
        'manage_options',   // capability
        'acg-keywords-import',     // menu slug
        'acg_keywords_render', // callback function
        'dashicons-rss'
    );
}
function acg_keywords_render()
{
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

			$data = ['keywords' => $keywordTmp, 'search' => $searchTmp, 'status' => 0];
			try {
				$wpdb->insert($tableName, $data ); 	
			} catch(Exception $e) {}
			
		}
		echo "Done";
	}

    ?>


    <div class="wrap">
		<h2>Keywords</h2>
		<p></p>

		<form method="post" action="">
			<div>
				<label for="acg_keywords"> Import Keywords (Mỗi dòng 1 key, theo định dạng: key|search)</label>
				<br>
				<br>
				<textarea style="width:50%; height:500px" required id="acg_keywords" type="text" name="acg_keywords"></textarea>
			</div>                
			<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Submit"></p>
		</form>
	</div>

    <?php
}