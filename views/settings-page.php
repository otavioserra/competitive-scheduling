<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <?php 
        $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'main';
    ?>
    <h2 class="nav-tab-wrapper">
        <a href="?page=competitive_scheduling_settings&tab=main" class="nav-tab <?php echo $active_tab == 'main' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Main', 'competitive-scheduling' ); ?></a>
        <a href="?page=competitive_scheduling_settings&tab=email" class="nav-tab <?php echo $active_tab == 'email' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Emails', 'competitive-scheduling' ); ?></a>
        <a href="?page=competitive_scheduling_settings&tab=message" class="nav-tab <?php echo $active_tab == 'message' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Messages', 'competitive-scheduling' ); ?></a>
        <a href="?page=competitive_scheduling_settings&tab=tools" class="nav-tab <?php echo $active_tab == 'tools' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Tools', 'competitive-scheduling' ); ?></a>
    </h2>
    <form action="options.php" method="post">
    <?php 

        switch($active_tab) {
            case 'main':
                settings_fields( 'competitive_scheduling_group_options' );
                do_settings_sections( 'competitive_scheduling_main' );
            break;
            case 'email':
                settings_fields( 'competitive_scheduling_group_html_options' );
                do_settings_sections( 'competitive_scheduling_email' );
            break;
            case 'message':
                settings_fields( 'competitive_scheduling_group_msg_options' );
                do_settings_sections( 'competitive_scheduling_messages' );
            break;
            case 'tools':
                settings_fields( 'competitive_scheduling_group_options' );
                do_settings_sections( 'competitive_scheduling_tools' );
            break;
            default:
                settings_fields( 'competitive_scheduling_group_options' );
                do_settings_sections( 'competitive_scheduling_main' );
        }

        submit_button( esc_html__( 'Save Settings', 'competitive-scheduling' ) );
    ?>
    </form>
</div>