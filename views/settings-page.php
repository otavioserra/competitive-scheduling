<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <?php 
        $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'main';
    ?>
    <h2 class="nav-tab-wrapper">
        <a href="?page=competitive_scheduling_settings&tab=main" class="nav-tab <?php echo $active_tab == 'main' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Main', 'competitive-scheduling' ); ?></a>
        <a href="?page=competitive_scheduling_settings&tab=email" class="nav-tab <?php echo $active_tab == 'email' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Email', 'competitive-scheduling' ); ?></a>
    </h2>
    <form action="options.php" method="post">
    <?php 
        settings_fields( 'competitive_scheduling_group' );

        switch($active_tab) {
            case 'main':
                do_settings_sections( 'competitive_scheduling_main' );
            break;
            case 'email':
                do_settings_sections( 'competitive_scheduling_email' );
            break;
            default:
                do_settings_sections( 'competitive_scheduling_main' );
        }

        submit_button( esc_html__( 'Save Settings', 'competitive-scheduling' ) );
    ?>
    </form>
</div>