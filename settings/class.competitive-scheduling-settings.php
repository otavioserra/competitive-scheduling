<?php

if( !class_exists( 'Competitive_Scheduling_Settings' ) ){
    class Competitive_Scheduling_Settings {
        
        public static $options;
        
        public function __construct(){
            self::get_option('competitive_scheduling_options');

            add_action( 'admin_init', array( $this, 'admin_init' ) );
        }

        public function admin_init(){
            
            register_setting( 'competitive_scheduling_group', 'competitive_scheduling_options' );

            add_settings_section(
                'competitive_scheduling_main_section',
                esc_html__( 'How does it work?', 'competitive-scheduling' ),
                array( $this, 'competitive_scheduling_main_section_callback' ),
                'competitive_scheduling_page1'
            );

            add_settings_field(
                'competitive_scheduling_shortcode',
                esc_html__( 'Shortcode', 'competitive-scheduling' ),
                array( $this, 'competitive_scheduling_shortcode_callback' ),
                'competitive_scheduling_page1',
                'competitive_scheduling_main_section'
            );
        }

        function competitive_scheduling_main_section_callback() {
            esc_html_e('Here you can find all the main options to personalise your plugin\'s instance.','competitive-scheduling');
        }

        public function competitive_scheduling_shortcode_callback(){
            ?>
            <span><?php echo esc_html__( 'Use the shortcode [competitive_scheduling] to display the controler in any page/post/widget. IMPORTANT: is necessary to be logged-in to see it.', 'competitive-scheduling' ); ?></span>
            <?php
        }
    }
}