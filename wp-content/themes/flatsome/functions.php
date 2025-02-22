<?php

/**
 * Flatsome functions and definitions
 *
 * @package flatsome
 */
$site_url = get_site_url();

$domain_name = wp_parse_url($site_url, PHP_URL_HOST);

$update_option_data = array(
    'id'           => 'new_id_123456',
    'type'         => 'PUBLIC',
    'domain'       => $domain_name, // Set the domain to the current domain name
    'registeredAt' => '2021-07-18T12:51:10.826Z',
    'purchaseCode' => '29e4ecab-b48c-490d-996b-e061ed7813b9',
    'licenseType'  => 'Regular License',
    'errors'       => array(),
    'show_notice'  => false
);

update_option('flatsome_registration', $update_option_data, 'yes');

require get_template_directory() . '/inc/init.php';

flatsome()->init();

/**
 * It's not recommended to add any custom code here. Please use a child theme
 * so that your customizations aren't lost during updates.
 *
 * Learn more here: https://developer.wordpress.org/themes/advanced-topics/child-themes/
 */
