<?php

if( !class_exists( 'Competitive_Scheduling_Settings' ) ){
    class Competitive_Scheduling_Settings {
        
        public static $options;
        
        public function __construct(){
            self::$options = get_option('competitive_scheduling_options');

            add_action( 'admin_init', array( $this, 'admin_init' ) );
        }

        public static function register_settings(){
            register_setting( 'competitive_scheduling_group', 'competitive_scheduling_options', array(
                'default' => array(
                    'activation' => "1",
                    'subject' => "2",
                ),
            ) );
        }

        public static function unregister_settings(){
            delete_option('competitive_scheduling_options');
        }

        public function admin_init(){
            add_settings_section(
                'competitive_scheduling_main_section',
                esc_html__( 'How does it work?', 'competitive-scheduling' ),
                array( $this, 'main_section_callback' ),
                'competitive_scheduling_page1'
            );

            add_settings_field(
                'activation',
                esc_html__( 'Activation', 'competitive-scheduling' ),
                array( $this, 'activation_callback' ),
                'competitive_scheduling_page1',
                'competitive_scheduling_main_section'
            );

            add_settings_field(
                'shortcode',
                esc_html__( 'Shortcode', 'competitive-scheduling' ),
                array( $this, 'shortcode_callback' ),
                'competitive_scheduling_page1',
                'competitive_scheduling_main_section'
            );

            add_settings_field(
                'subject',
                esc_html__( 'Subject', 'competitive-scheduling' ),
                array( $this, 'subject_callback' ),
                'competitive_scheduling_page1',
                'competitive_scheduling_main_section',
                array(
                    'label_for' => 'subject'
                )
            );
        }

        function main_section_callback() {
            esc_html_e('Here you can find all the main options to personalise your plugin\'s instance.','competitive-scheduling');
        }

        public function activation_callback(){
            ?>

                <input 
                    type="checkbox"
                    name="competitive_scheduling_options[activation]"
                    id="activation"
                    value="1"
                    <?php 
                        if( isset( self::$options['activation'] ) ){
                            checked( "1", self::$options['activation'], true );
                        }    
                    ?>
                />
                <label for="activation"><?php echo esc_html__( 'Activate/Deactivate the scheduling system.', 'competitive-scheduling' ); ?></label>
            <?php
        }

        public function shortcode_callback(){
            ?>
            <span><?php echo esc_html__( 'Use the shortcode [competitive_scheduling] to display the controler in any page/post/widget. IMPORTANT: is necessary to be logged-in to see it.', 'competitive-scheduling' ); ?></span>
            <?php
        }

        public function subject_callback(){
            ?>
                <input 
                type="text" 
                name="competitive_scheduling_options[subject]" 
                id="subject"
                value="<?php echo isset( self::$options['subject'] ) ? esc_attr( self::$options['subject'] ) : ''; ?>"
                >
                <p><?php echo esc_html__( 'Subject of emails that will be sent to users\' appointments made on your website.', 'competitive-scheduling' ); ?></p> 
            <?php
        }
    }
}