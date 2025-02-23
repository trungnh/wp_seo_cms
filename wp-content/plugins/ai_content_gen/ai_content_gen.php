<?php
/*
 * Plugin Name: AI Content Gen
 * Plugin URI: https://zzz.com/
 * Description: Ai content generate
 * Author: Trung
 * Author URI: https://trungnh.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Version: 1.0.0
 * Requires PHP: 8.3
 */

define('WP_DEBUG', true);

define( 'ACG_TEXT_DOMAIN', 'acg' );

define( 'ACG_PLUGIN', __FILE__ );

define( 'ACG_PLUGIN_BASENAME', plugin_basename( ACG_PLUGIN ) );

define( 'ACG_PLUGIN_NAME', trim( dirname( ACG_PLUGIN_BASENAME ), '/' ) );

define( 'ACG_PLUGIN_DIR', untrailingslashit( dirname( ACG_PLUGIN ) ) );

require_once ACG_PLUGIN_DIR . '/pages/setting.php';
