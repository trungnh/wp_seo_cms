<?php
/*
 * Plugin Name: AI Content Gen
 * Description: Ai content generate
 * Author: Trung
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Version: 1.2.0
 * Requires PHP: 8.3
 */


define( 'ACG_TEXT_DOMAIN', 'acg' );

define( 'ACG_PLUGIN', __FILE__ );

define( 'ACG_PLUGIN_BASENAME', plugin_basename( ACG_PLUGIN ) );

define( 'ACG_PLUGIN_NAME', trim( dirname( ACG_PLUGIN_BASENAME ), '/' ) );

define( 'ACG_PLUGIN_DIR', untrailingslashit( dirname( ACG_PLUGIN ) ) );

register_activation_hook ( __FILE__, 'on_activate' );

function on_activate() {
    global $wpdb;
    $create_table_query = "
            CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}search_keywords` (
	            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT , 
	            `keywords` VARCHAR(255) NOT NULL , 
	            `search` INT NOT NULL , 
	            `status` TINYINT NOT NULL DEFAULT 0 , 
	            PRIMARY KEY (`id`),
	            UNIQUE(`keywords`)
            ) ENGINE = InnoDB;	

            CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}crawled_source_content` (
	            `id` BIGINT NOT NULL AUTO_INCREMENT , 
	            `keywords_id` BIGINT NOT NULL , 
	            `link` VARCHAR(255) NOT NULL , 
	            `title` TEXT NOT NULL , 
	            `description` TEXT NOT NULL , 
	            `content` TEXT NOT NULL DEFAULT '', 
	            `status` TINYINT NOT NULL DEFAULT 0 , 
	            PRIMARY KEY (`id`), 
	            UNIQUE `link` (`link`)
            ) ENGINE = InnoDB;

    ";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $create_table_query );
}


require_once ACG_PLUGIN_DIR . '/pages/functions.php';
require_once ACG_PLUGIN_DIR . '/pages/setting.php';