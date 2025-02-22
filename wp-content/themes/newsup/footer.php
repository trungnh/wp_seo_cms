<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after.
 *
 * @package Newsup
 */
?>
    <div class="container-fluid missed-section mg-posts-sec-inner">
        <?php do_action('newsup_action_footer_missed'); ?>
    </div>
    <!--==================== FOOTER AREA ====================-->
    <?php $newsup_footer_widget_background = get_theme_mod('newsup_footer_widget_background');
    $newsup_footer_overlay_color = get_theme_mod('newsup_footer_overlay_color'); 
    $style = !empty($newsup_footer_widget_background) ? "background-image:url('".esc_url($newsup_footer_widget_background)."');" : ""; ?>
    <footer class="footer back-img" style="<?php echo $style; ?>">
        <div class="overlay" style="background-color: <?php echo esc_attr($newsup_footer_overlay_color);?>;">
        <?php do_action('newsup_action_footer_widget_area'); 
        do_action('newsup_action_footer_bottom_area'); ?>
            <div class="mg-footer-copyright">
                <?php do_action('newsup_action_footer_copyright'); ?>
            </div>
        <!--/overlay-->
        </div>
    </footer>
    <!--/footer-->
  </div>
    <!--/wrapper-->
    <!--Scroll To Top-->
        <a href="#" class="ta_upscr bounceInup animated"><i class="fas fa-angle-up"></i></a>
    <!-- /Scroll To Top -->
<?php wp_footer(); ?>
</body>
</html>