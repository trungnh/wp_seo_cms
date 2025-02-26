<?php 
add_action( 'admin_menu', 'acg_register_menu_document', 9, 0);

function acg_register_menu_document() {  
    add_menu_page(
        'AI Content Generate',     // page title
        'AI Content Generate',     // menu title
        'manage_options',   // capability
        'acg-keywords',     // menu slug
        'acg_keywords_render', // callback function
        'dashicons-rss'
    );
    add_submenu_page('acg-keywords', 'Keywords List', 'Keywords List', 'manage_options', 'acg-keywords-list', 'acg_keywords_list_render');  
}  

function acg_keywords_render()
{
  require_once ACG_PLUGIN_DIR . '/pages/keywords.php';
}

function acg_keywords_list_render()
{
  require_once ACG_PLUGIN_DIR . '/pages/keywords_list.php';
}
