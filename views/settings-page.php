<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <form action="options.php" method="post">
    <?php 
        settings_fields( 'competitive_scheduling_group' );
        do_settings_sections( 'competitive_scheduling_page1' );
        submit_button( esc_html__( 'Save Settings', 'competitive-scheduling' ) );
    ?>
    </form>
</div>