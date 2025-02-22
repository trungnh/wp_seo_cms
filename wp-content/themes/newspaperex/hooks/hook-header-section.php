<?php
if (!function_exists('newspaperex_header_section')) :
/**
 *  Slider
 *
 * @since Newspaperex
 *
 */
function newspaperex_header_section()
{
?>
<div class="mg-head-detail hidden-xs">
    <div class="container-fluid">
        <div class="row">
            <?php
            $header_social_icon_enable = esc_attr(get_theme_mod('header_social_icon_enable','true'));
            $newsup_header_fb_link = get_theme_mod('newsup_header_fb_link');
            $newsup_header_fb_target = esc_attr(get_theme_mod('newsup_header_fb_target','true'));
            $newsup_header_twt_link = get_theme_mod('newsup_header_twt_link');
            $newsup_header_twt_target = esc_attr(get_theme_mod('newsup_header_twt_target','true'));
            $newsup_header_lnkd_link = get_theme_mod('newsup_header_lnkd_link');
            $newsup_header_lnkd_target = esc_attr(get_theme_mod('newsup_header_lnkd_target','true'));
            $newsup_header_insta_link = get_theme_mod('newsup_header_insta_link');
            $newsup_insta_insta_target = esc_attr(get_theme_mod('newsup_insta_insta_target','true'));
            $newsup_header_youtube_link = get_theme_mod('newsup_header_youtube_link');
            $newsup_header_youtube_target = esc_attr(get_theme_mod('newsup_header_youtube_target','true'));
            $newsup_header_pintrest_link = get_theme_mod('newsup_header_pintrest_link');
            $newsup_header_pintrest_target = esc_attr(get_theme_mod('newsup_header_pintrest_target','true'));
            $newsup_header_telegram_link = get_theme_mod('newsup_header_tele_link');
            $newsup_header_telegram_target = esc_attr(get_theme_mod('newsup_header_tele_target','true'));
              ?>
            <div class="col-md-6 col-xs-12">
                <ul class="info-left">
                    <?php newsup_date_display_type(); ?>
                </ul>

               <?php
               if ( has_nav_menu( 'top_right' ) ) { 
                wp_nav_menu( array(
                        'theme_location' => 'top_right',
                        'menu_class' => 'info-left',
                      ) ); 
                } ?>
            </div>


            <?php 
            if($header_social_icon_enable == true)
            {
            ?>
            <div class="col-md-6 col-xs-12">
                <ul class="mg-social info-right">
                    
                      <?php if($newsup_header_fb_link !=''){ ?>
                      <li><a <?php if($newsup_header_fb_target) { ?> target="_blank" <?php } ?>href="<?php echo esc_url($newsup_header_fb_link); ?>">
                      <span class="icon-soci facebook"><i class="fa fa-facebook"></i></span> </a></li>
                      <?php } ?>
                      <?php if($newsup_header_twt_link !=''){ ?>
                      <li><a <?php if($newsup_header_twt_target) { ?>target="_blank" <?php } ?>href="<?php echo esc_url($newsup_header_twt_link);?>">
                      <span class="icon-soci x-twitter"><i class="fa-brands fa-x-twitter"></i></span></a></li>
                      <?php } ?>
                      <?php if($newsup_header_lnkd_link !=''){ ?>
                      <li><a <?php if($newsup_header_lnkd_target) { ?>target="_blank" <?php } ?> href="<?php echo esc_url($newsup_header_lnkd_link); ?>">
                      <span class="icon-soci linkedin"><i class="fa fa-linkedin"></i></span></a></li>
                      <?php } ?>
                      <?php if($newsup_header_insta_link !=''){ ?>
                      <li><a <?php if($newsup_insta_insta_target) { ?>target="_blank" <?php } ?> href="<?php echo esc_url($newsup_header_insta_link); ?>">
                      <span class="icon-soci instagram"><i class="fa fa-instagram"></i></span></a></li>
                      <?php } ?>
                      <?php if($newsup_header_youtube_link !=''){ ?>
                      <li><a <?php if($newsup_header_youtube_target) { ?>target="_blank" <?php } ?> href="<?php echo esc_url($newsup_header_youtube_link); ?>">
                      <span class="icon-soci youtube"><i class="fa fa-youtube"></i></span></a></li>
                      <?php } ?>
                       <?php if($newsup_header_pintrest_link !=''){ ?>
                      <li><a <?php if($newsup_header_pintrest_target) { ?>target="_blank" <?php } ?> href="<?php echo esc_url($newsup_header_pintrest_link); ?>">
                      <span class="icon-soci pinterest"><i class="fa fa-pinterest-p"></i></span></a></li>
                      <?php } ?> 
                      <?php if($newsup_header_telegram_link !=''){ ?>
                      <li><a <?php if($newsup_header_telegram_target) { ?>target="_blank" <?php } ?> href="<?php echo esc_url($newsup_header_telegram_link); ?>">
                      <span class="icon-soci telegram"><i class="fa fa-telegram"></i></span></a></li>
                      <?php } ?>
                </ul>
            </div>
            <?php }?>
        </div>
    </div>
</div>
<?php 
}
endif;
add_action('newspaperex_action_header_section', 'newspaperex_header_section', 5);