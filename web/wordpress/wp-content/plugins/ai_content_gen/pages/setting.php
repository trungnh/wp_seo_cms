<?php
class ACGSettings {
	private $acg_settings_options;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'acg_settings_add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'acg_settings_page_init' ) );
	}

	public function acg_settings_add_plugin_page() {
		add_options_page(
			'ACG Settings', // page_title
			'ACG Settings', // menu_title
			'manage_options', // capability
			'acg-settings', // menu_slug
			array( $this, 'acg_settings_create_admin_page' ) // function
		);
	}

	public function acg_settings_create_admin_page() {
		$this->acg_settings_options = get_option( 'acg_settings_option' ); ?>

		<div class="wrap">
			<h2>ACG Settings</h2>
			<p></p>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'acg_settings_option_group' );
					do_settings_sections( 'acg-settings-admin' );
					submit_button();
				?>
			</form>
		</div>
	<?php }

	public function acg_settings_page_init() {
		register_setting(
			'acg_settings_option_group', // option_group
			'acg_settings_option', // option_name
			array( $this, 'acg_settings_sanitize' ) // sanitize_callback
		);

		add_settings_section(
			'acg_settings_setting_section', // id
			'Settings', // title
			array( $this, 'acg_settings_section_info' ), // callback
			'acg-settings-admin' // page
		);

		add_settings_field(
			'endpoint_crawl_search', // id
			'Endpoint crawl search result API (serper.dev)', // title
			array( $this, 'endpoint_crawl_search_callback' ), // callback
			'acg-settings-admin', // page
			'acg_settings_setting_section' // section
		);

		add_settings_field(
			'token_crawl_search', // id
			'Token crawl search result API (serper.dev)', // title
			array( $this, 'token_crawl_search_callback' ), // callback
			'acg-settings-admin', // page
			'acg_settings_setting_section' // section
		);

		add_settings_field(
			'lang_crawl_search', // id
			'Language search result API (serper.dev). EG: vi', // title
			array( $this, 'lang_crawl_search_callback' ), // callback
			'acg-settings-admin', // page
			'acg_settings_setting_section' // section
		);

		add_settings_field(
			'location_crawl_search', // id
			'Location search result API (serper.dev). EG: vn', // title
			array( $this, 'location_crawl_search_callback' ), // callback
			'acg-settings-admin', // page
			'acg_settings_setting_section' // section
		);

		add_settings_field(
			'number_of_result', // id
			'Number of result', // title
			array( $this, 'number_of_result_callback' ), // callback
			'acg-settings-admin', // page
			'acg_settings_setting_section' // section
		);

		add_settings_field(
			'exclude_crawl_search', // id
			'Loại trừ domain khi crawl search (mỗi domain trên 1 dòng)', // title
			array( $this, 'exclude_crawl_search_callback' ), // callback
			'acg-settings-admin', // page
			'acg_settings_setting_section' // section
		);

		add_settings_field(
			'endpoint_crawl_content_api_crawl4ai', // id
			'Endpoint crawl content API (Crawl4ai)', // title
			array( $this, 'endpoint_crawl_content_api_crawl4ai_callback' ), // callback
			'acg-settings-admin', // page
			'acg_settings_setting_section' // section
		);

		add_settings_field(
			'gemini_token', // id
			'Gemini token', // title
			array( $this, 'gemini_token_callback' ), // callback
			'acg-settings-admin', // page
			'acg_settings_setting_section' // section
		);

		add_settings_field(
			'deepseek_token', // id
			'Deepseek token', // title
			array( $this, 'deepseek_token_callback' ), // callback
			'acg-settings-admin', // page
			'acg_settings_setting_section' // section
		);

		add_settings_field(
			'prompt_chu_de', // id
			'Prompt  lấy Chủ Đề', // title
			array( $this, 'prompt_chu_de_callback' ), // callback
			'acg-settings-admin', // page
			'acg_settings_setting_section' // section
		);

		add_settings_field(
			'prompt_thuoc_tinh_chinh', // id
			'Prompt  lấy Thuộc Tính Chính', // title
			array( $this, 'prompt_thuoc_tinh_chinh_callback' ), // callback
			'acg-settings-admin', // page
			'acg_settings_setting_section' // section
		);

		add_settings_field(
			'prompt_keyword_chinh', // id
			'Prompt  lấy Keyword Chính', // title
			array( $this, 'prompt_keyword_chinh_callback' ), // callback
			'acg-settings-admin', // page
			'acg_settings_setting_section' // section
		);

		add_settings_field(
			'prompt_user_intent', // id
			'Prompt  lấy User Intent', // title
			array( $this, 'prompt_user_intent_callback' ), // callback
			'acg-settings-admin', // page
			'acg_settings_setting_section' // section
		);

		add_settings_field(
			'prompt_tom_tat', // id
			'Prompt  lấy Tóm Tắt', // title
			array( $this, 'prompt_tom_tat_callback' ), // callback
			'acg-settings-admin', // page
			'acg_settings_setting_section' // section
		);

		add_settings_field(
			'prompt_dan_bai', // id
			'Prompt  lấy Dàn Bài', // title
			array( $this, 'prompt_dan_bai_callback' ), // callback
			'acg-settings-admin', // page
			'acg_settings_setting_section' // section
		);

		add_settings_field(
			'deepseek_prompt_title', // id
			'Deepseek Prompt Tiêu đề', // title
			array( $this, 'deepseek_prompt_title_callback' ), // callback
			'acg-settings-admin', // page
			'acg_settings_setting_section' // section
		);

		add_settings_field(
			'deepseek_prompt_description', // id
			'Deepseek Prompt Description bài viết', // title
			array( $this, 'deepseek_prompt_description_callback' ), // callback
			'acg-settings-admin', // page
			'acg_settings_setting_section' // section
		);

		add_settings_field(
			'deepseek_prompt_viet_bai', // id
			'Deepseek Prompt Viết bài', // title
			array( $this, 'deepseek_prompt_viet_bai_callback' ), // callback
			'acg-settings-admin', // page
			'acg_settings_setting_section' // section
		);
	}

	public function acg_settings_sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input['endpoint_crawl_search'] ) ) {
			$sanitary_values['endpoint_crawl_search'] = sanitize_text_field( $input['endpoint_crawl_search'] );
		}

		if ( isset( $input['token_crawl_search'] ) ) {
			$sanitary_values['token_crawl_search'] = sanitize_text_field( $input['token_crawl_search'] );
		}

		if ( isset( $input['lang_crawl_search'] ) ) {
			$sanitary_values['lang_crawl_search'] = sanitize_text_field( $input['lang_crawl_search'] );
		}

		if ( isset( $input['location_crawl_search'] ) ) {
			$sanitary_values['location_crawl_search'] = sanitize_text_field( $input['location_crawl_search'] );
		}

		if ( isset( $input['number_of_result'] ) ) {
			$sanitary_values['number_of_result'] = sanitize_text_field( $input['number_of_result'] );
		}

		if ( isset( $input['exclude_crawl_search'] ) ) {
			$sanitary_values['exclude_crawl_search'] = wp_unslash( $input['exclude_crawl_search'] );
		}

		if ( isset( $input['endpoint_crawl_content_api_crawl4ai'] ) ) {
			$sanitary_values['endpoint_crawl_content_api_crawl4ai'] = sanitize_text_field( $input['endpoint_crawl_content_api_crawl4ai'] );
		}

		if ( isset( $input['gemini_token'] ) ) {
			$sanitary_values['gemini_token'] = sanitize_text_field( $input['gemini_token'] );
		}

		if ( isset( $input['deepseek_token'] ) ) {
			$sanitary_values['deepseek_token'] = sanitize_text_field( $input['deepseek_token'] );
		}

		if ( isset( $input['prompt_chu_de'] ) ) {
			$sanitary_values['prompt_chu_de'] = wp_unslash( $input['prompt_chu_de'] );
		}

		if ( isset( $input['prompt_thuoc_tinh_chinh'] ) ) {
			$sanitary_values['prompt_thuoc_tinh_chinh'] = wp_unslash( $input['prompt_thuoc_tinh_chinh'] );
		}

		if ( isset( $input['prompt_keyword_chinh'] ) ) {
			$sanitary_values['prompt_keyword_chinh'] = wp_unslash( $input['prompt_keyword_chinh'] );
		}

		if ( isset( $input['prompt_user_intent'] ) ) {
			$sanitary_values['prompt_user_intent'] = wp_unslash( $input['prompt_user_intent'] );
		}

		if ( isset( $input['prompt_tom_tat'] ) ) {
			$sanitary_values['prompt_tom_tat'] = wp_unslash( $input['prompt_tom_tat'] );
		}

		if ( isset( $input['prompt_dan_bai'] ) ) {
			$sanitary_values['prompt_dan_bai'] = wp_unslash( $input['prompt_dan_bai'] );
		}

		if ( isset( $input['deepseek_prompt_title'] ) ) {
			$sanitary_values['deepseek_prompt_title'] = wp_unslash( $input['deepseek_prompt_title'] );
		}

		if ( isset( $input['deepseek_prompt_description'] ) ) {
			$sanitary_values['deepseek_prompt_description'] = wp_unslash( $input['deepseek_prompt_description'] );
		}

		if ( isset( $input['deepseek_prompt_viet_bai'] ) ) {
			$sanitary_values['deepseek_prompt_viet_bai'] = wp_unslash( $input['deepseek_prompt_viet_bai'] );
		}

		return $sanitary_values;
	}

	public function acg_settings_section_info() {
		
	}

	public function endpoint_crawl_search_callback() {
		printf(
			'<input class="regular-text" type="text" name="acg_settings_option[endpoint_crawl_search]" id="endpoint_crawl_search" value="%s">',
			isset( $this->acg_settings_options['endpoint_crawl_search'] ) ? esc_attr( $this->acg_settings_options['endpoint_crawl_search']) : ''
		);
	}

	public function token_crawl_search_callback() {
		printf(
			'<input class="regular-text" type="text" name="acg_settings_option[token_crawl_search]" id="token_crawl_search" value="%s">',
			isset( $this->acg_settings_options['token_crawl_search'] ) ? esc_attr( $this->acg_settings_options['token_crawl_search']) : ''
		);
	}

	public function lang_crawl_search_callback() {
		printf(
			'<input class="regular-text" type="text" name="acg_settings_option[lang_crawl_search]" id="lang_crawl_search" value="%s">',
			isset( $this->acg_settings_options['lang_crawl_search'] ) ? esc_attr( $this->acg_settings_options['lang_crawl_search']) : ''
		);
	}

	public function location_crawl_search_callback() {
		printf(
			'<input class="regular-text" type="text" name="acg_settings_option[location_crawl_search]" id="location_crawl_search" value="%s">',
			isset( $this->acg_settings_options['location_crawl_search'] ) ? esc_attr( $this->acg_settings_options['location_crawl_search']) : ''
		);
	}

	public function number_of_result_callback() {
		printf(
			'<input class="regular-text" type="text" name="acg_settings_option[number_of_result]" id="number_of_result" value="%s">',
			isset( $this->acg_settings_options['number_of_result'] ) ? esc_attr( $this->acg_settings_options['number_of_result']) : ''
		);
	}

	public function exclude_crawl_search_callback() {
		printf(
			'<textarea class="regular-text" type="text" name="acg_settings_option[exclude_crawl_search]" id="exclude_crawl_search">%s</textarea>',
			isset( $this->acg_settings_options['exclude_crawl_search'] ) ? esc_textarea( $this->acg_settings_options['exclude_crawl_search']) : ''
		);
	}

	public function endpoint_crawl_content_api_crawl4ai_callback() {
		printf(
			'<input class="regular-text" type="text" name="acg_settings_option[endpoint_crawl_content_api_crawl4ai]" id="endpoint_crawl_content_api_crawl4ai" value="%s">',
			isset( $this->acg_settings_options['endpoint_crawl_content_api_crawl4ai'] ) ? esc_attr( $this->acg_settings_options['endpoint_crawl_content_api_crawl4ai']) : ''
		);
	}

	public function gemini_token_callback() {
		printf(
			'<input class="regular-text" type="text" name="acg_settings_option[gemini_token]" id="gemini_token" value="%s">',
			isset( $this->acg_settings_options['gemini_token'] ) ? esc_attr( $this->acg_settings_options['gemini_token']) : ''
		);
	}

	public function deepseek_token_callback() {
		printf(
			'<input class="regular-text" type="text" name="acg_settings_option[deepseek_token]" id="deepseek_token" value="%s">',
			isset( $this->acg_settings_options['deepseek_token'] ) ? esc_attr( $this->acg_settings_options['deepseek_token']) : ''
		);
	}

	public function prompt_chu_de_callback() {
		printf(
			'<textarea class="regular-text" type="text" name="acg_settings_option[prompt_chu_de]" id="prompt_chu_de">%s</textarea>',
			isset( $this->acg_settings_options['prompt_chu_de'] ) ? esc_textarea( $this->acg_settings_options['prompt_chu_de']) : ''
		);
	}

	public function prompt_thuoc_tinh_chinh_callback() {
		printf(
			'<textarea class="regular-text" type="text" name="acg_settings_option[prompt_thuoc_tinh_chinh]" id="prompt_thuoc_tinh_chinh">%s</textarea>',
			isset( $this->acg_settings_options['prompt_thuoc_tinh_chinh'] ) ? esc_textarea( $this->acg_settings_options['prompt_thuoc_tinh_chinh']) : ''
		);
	}

	public function prompt_keyword_chinh_callback() {
		printf(
			'<textarea class="regular-text" type="text" name="acg_settings_option[prompt_keyword_chinh]" id="prompt_keyword_chinh">%s</textarea>',
			isset( $this->acg_settings_options['prompt_keyword_chinh'] ) ? esc_textarea( $this->acg_settings_options['prompt_keyword_chinh']) : ''
		);
	}

	public function prompt_user_intent_callback() {
		printf(
			'<textarea class="regular-text" type="text" name="acg_settings_option[prompt_user_intent]" id="prompt_user_intent">%s</textarea>',
			isset( $this->acg_settings_options['prompt_user_intent'] ) ? esc_textarea( $this->acg_settings_options['prompt_user_intent']) : ''
		);
	}

	public function prompt_tom_tat_callback() {
		printf(
			'<textarea class="regular-text" type="text" name="acg_settings_option[prompt_tom_tat]" id="prompt_tom_tat">%s</textarea>',
			isset( $this->acg_settings_options['prompt_tom_tat'] ) ? esc_textarea( $this->acg_settings_options['prompt_tom_tat']) : ''
		);
	}

	public function prompt_dan_bai_callback() {
		printf(
			'<textarea class="regular-text" type="text" name="acg_settings_option[prompt_dan_bai]" id="prompt_dan_bai">%s</textarea>',
			isset( $this->acg_settings_options['prompt_dan_bai'] ) ? esc_textarea( $this->acg_settings_options['prompt_dan_bai']) : ''
		);
	}

	public function deepseek_prompt_title_callback() {
		printf(
			'<textarea class="regular-text" type="text" name="acg_settings_option[deepseek_prompt_title]" id="deepseek_prompt_title">%s</textarea>',
			isset( $this->acg_settings_options['deepseek_prompt_title'] ) ? esc_textarea( $this->acg_settings_options['deepseek_prompt_title']) : ''
		);
	}

	public function deepseek_prompt_description_callback() {
		printf(
			'<textarea class="regular-text" type="text" name="acg_settings_option[deepseek_prompt_description]" id="deepseek_prompt_description">%s</textarea>',
			isset( $this->acg_settings_options['deepseek_prompt_description'] ) ? esc_textarea( $this->acg_settings_options['deepseek_prompt_description']) : ''
		);
	}

	public function deepseek_prompt_viet_bai_callback() {
		printf(
			'<textarea class="regular-text" type="text" name="acg_settings_option[deepseek_prompt_viet_bai]" id="deepseek_prompt_viet_bai">%s</textarea>',
			isset( $this->acg_settings_options['deepseek_prompt_viet_bai'] ) ? esc_textarea( $this->acg_settings_options['deepseek_prompt_viet_bai']) : ''
		);
	}

}
if ( is_admin() )
	$acg_settings = new ACGSettings();