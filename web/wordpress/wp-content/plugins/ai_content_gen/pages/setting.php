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
		$this->acg_settings_options = get_option( 'acg_settings_option' ); 
		$this->acg_settings_promt_options = get_option( 'acg_settings_promt_option' ); ?>

		<div class="wrap">
			<h2>ACG Settings</h2>
			<p></p>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'acg_settings_option_group' );
					settings_fields( 'acg_settings_option_promt_group' );
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

		register_setting(
			'acg_settings_option_promt_group', // option_group
			'acg_settings_promt_option', // option_name
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
			'number_of_result', // id
			'Number of result', // title
			array( $this, 'number_of_result_callback' ), // callback
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
			'chatgpt_token', // id
			'ChatGPT token', // title
			array( $this, 'chatgpt_token_callback' ), // callback
			'acg-settings-admin', // page
			'acg_settings_setting_section' // section
		);

		add_settings_field(
			'promt_chu_de', // id
			'Promt lấy Chủ Đề', // title
			array( $this, 'promt_chu_de_callback' ), // callback
			'acg-settings-admin', // page
			'acg_settings_setting_section' // section
		);

		add_settings_field(
			'promt_thuoc_tinh_chinh', // id
			'Promt lấy Thuộc Tính Chính', // title
			array( $this, 'promt_thuoc_tinh_chinh_callback' ), // callback
			'acg-settings-admin', // page
			'acg_settings_setting_section' // section
		);

		add_settings_field(
			'promt_keyword_chinh', // id
			'Promt lấy Keyword Chính', // title
			array( $this, 'promt_keyword_chinh_callback' ), // callback
			'acg-settings-admin', // page
			'acg_settings_setting_section' // section
		);

		add_settings_field(
			'promt_user_intent', // id
			'Promt lấy User Intent', // title
			array( $this, 'promt_user_intent_callback' ), // callback
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

		if ( isset( $input['number_of_result'] ) ) {
			$sanitary_values['number_of_result'] = sanitize_text_field( $input['number_of_result'] );
		}

		if ( isset( $input['endpoint_crawl_content_api_crawl4ai'] ) ) {
			$sanitary_values['endpoint_crawl_content_api_crawl4ai'] = sanitize_text_field( $input['endpoint_crawl_content_api_crawl4ai'] );
		}

		if ( isset( $input['chatgpt_token'] ) ) {
			$sanitary_values['chatgpt_token'] = sanitize_text_field( $input['chatgpt_token'] );
		}

		if ( isset( $input['promt_chu_de'] ) ) {
			$sanitary_values['promt_chu_de'] = sanitize_text_field( $input['promt_chu_de'] );
		}

		if ( isset( $input['promt_thuoc_tinh_chinh'] ) ) {
			$sanitary_values['promt_thuoc_tinh_chinh'] = sanitize_text_field( $input['promt_thuoc_tinh_chinh'] );
		}

		if ( isset( $input['promt_keyword_chinh'] ) ) {
			$sanitary_values['promt_keyword_chinh'] = sanitize_text_field( $input['promt_keyword_chinh'] );
		}

		if ( isset( $input['promt_user_intent'] ) ) {
			$sanitary_values['promt_user_intent'] = sanitize_text_field( $input['promt_user_intent'] );
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

	public function number_of_result_callback() {
		printf(
			'<input class="regular-text" type="text" name="acg_settings_option[number_of_result]" id="number_of_result" value="%s">',
			isset( $this->acg_settings_options['number_of_result'] ) ? esc_attr( $this->acg_settings_options['number_of_result']) : ''
		);
	}

	public function endpoint_crawl_content_api_crawl4ai_callback() {
		printf(
			'<input class="regular-text" type="text" name="acg_settings_option[endpoint_crawl_content_api_crawl4ai]" id="endpoint_crawl_content_api_crawl4ai" value="%s">',
			isset( $this->acg_settings_options['endpoint_crawl_content_api_crawl4ai'] ) ? esc_attr( $this->acg_settings_options['endpoint_crawl_content_api_crawl4ai']) : ''
		);
	}

	public function chatgpt_token_callback() {
		printf(
			'<input class="regular-text" type="text" name="acg_settings_option[chatgpt_token]" id="chatgpt_token" value="%s">',
			isset( $this->acg_settings_options['chatgpt_token'] ) ? esc_attr( $this->acg_settings_options['chatgpt_token']) : ''
		);
	}

	public function promt_chu_de_callback() {
		printf(
			'<textarea class="regular-text" type="text" name="acg_settings_promt_option[promt_chu_de]" id="promt_chu_de">%s</textarea>',
			isset( $this->acg_settings_promt_options['promt_chu_de'] ) ? esc_attr( $this->acg_settings_promt_options['promt_chu_de']) : ''
		);
	}

	public function promt_thuoc_tinh_chinh_callback() {
		printf(
			'<textarea class="regular-text" type="text" name="acg_settings_promt_option[promt_thuoc_tinh_chinh]" id="promt_thuoc_tinh_chinh">%s</textarea>',
			isset( $this->acg_settings_promt_options['promt_thuoc_tinh_chinh'] ) ? esc_attr( $this->acg_settings_promt_options['promt_thuoc_tinh_chinh']) : ''
		);
	}

	public function promt_keyword_chinh_callback() {
		printf(
			'<textarea class="regular-text" type="text" name="acg_settings_promt_option[promt_keyword_chinh]" id="promt_keyword_chinh">%s</textarea>',
			isset( $this->acg_settings_promt_options['promt_keyword_chinh'] ) ? esc_attr( $this->acg_settings_promt_options['promt_keyword_chinh']) : ''
		);
	}

	public function promt_user_intent_callback() {
		printf(
			'<textarea class="regular-text" type="text" name="acg_settings_promt_option[promt_user_intent]" id="promt_user_intent">%s</textarea>',
			isset( $this->acg_settings_promt_options['promt_user_intent'] ) ? esc_attr( $this->acg_settings_promt_options['promt_user_intent']) : ''
		);
	}

}
if ( is_admin() )
	$acg_settings = new ACGSettings();