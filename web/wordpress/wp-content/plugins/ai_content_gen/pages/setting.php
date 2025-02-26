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
		$this->acg_settings_options = get_option( 'acg_settings_option_name' ); ?>

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
			'acg_settings_option_name', // option_name
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
			'number_of_result_2', // id
			'Number of result', // title
			array( $this, 'number_of_result_2_callback' ), // callback
			'acg-settings-admin', // page
			'acg_settings_setting_section' // section
		);

		add_settings_field(
			'endpoint_crawl_content_api_crawl4ai_3', // id
			'Endpoint crawl content API (Crawl4ai)', // title
			array( $this, 'endpoint_crawl_content_api_crawl4ai_3_callback' ), // callback
			'acg-settings-admin', // page
			'acg_settings_setting_section' // section
		);

		add_settings_field(
			'chatgpt_token_4', // id
			'ChatGPT token', // title
			array( $this, 'chatgpt_token_4_callback' ), // callback
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

		if ( isset( $input['number_of_result_2'] ) ) {
			$sanitary_values['number_of_result_2'] = sanitize_text_field( $input['number_of_result_2'] );
		}

		if ( isset( $input['endpoint_crawl_content_api_crawl4ai_3'] ) ) {
			$sanitary_values['endpoint_crawl_content_api_crawl4ai_3'] = sanitize_text_field( $input['endpoint_crawl_content_api_crawl4ai_3'] );
		}

		if ( isset( $input['chatgpt_token_4'] ) ) {
			$sanitary_values['chatgpt_token_4'] = sanitize_text_field( $input['chatgpt_token_4'] );
		}

		return $sanitary_values;
	}

	public function acg_settings_section_info() {
		
	}

	public function endpoint_crawl_search_callback() {
		printf(
			'<input class="regular-text" type="text" name="acg_settings_option_name[endpoint_crawl_search]" id="endpoint_crawl_search" value="%s">',
			isset( $this->acg_settings_options['endpoint_crawl_search'] ) ? esc_attr( $this->acg_settings_options['endpoint_crawl_search']) : ''
		);
	}

	public function token_crawl_search_callback() {
		printf(
			'<input class="regular-text" type="text" name="acg_settings_option_name[token_crawl_search]" id="token_crawl_search" value="%s">',
			isset( $this->acg_settings_options['token_crawl_search'] ) ? esc_attr( $this->acg_settings_options['token_crawl_search']) : ''
		);
	}

	public function number_of_result_2_callback() {
		printf(
			'<input class="regular-text" type="text" name="acg_settings_option_name[number_of_result_2]" id="number_of_result_2" value="%s">',
			isset( $this->acg_settings_options['number_of_result_2'] ) ? esc_attr( $this->acg_settings_options['number_of_result_2']) : ''
		);
	}

	public function endpoint_crawl_content_api_crawl4ai_3_callback() {
		printf(
			'<input class="regular-text" type="text" name="acg_settings_option_name[endpoint_crawl_content_api_crawl4ai_3]" id="endpoint_crawl_content_api_crawl4ai_3" value="%s">',
			isset( $this->acg_settings_options['endpoint_crawl_content_api_crawl4ai_3'] ) ? esc_attr( $this->acg_settings_options['endpoint_crawl_content_api_crawl4ai_3']) : ''
		);
	}

	public function chatgpt_token_4_callback() {
		printf(
			'<input class="regular-text" type="text" name="acg_settings_option_name[chatgpt_token_4]" id="chatgpt_token_4" value="%s">',
			isset( $this->acg_settings_options['chatgpt_token_4'] ) ? esc_attr( $this->acg_settings_options['chatgpt_token_4']) : ''
		);
	}

}
if ( is_admin() )
	$acg_settings = new ACGSettings();