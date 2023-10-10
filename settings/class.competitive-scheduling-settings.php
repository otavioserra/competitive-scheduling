<?php

if( !class_exists( 'Competitive_Scheduling_Settings' ) ){
    class Competitive_Scheduling_Settings {
        
        public static $options;
        
        public function __construct(){
            self::$options = get_option('competitive_scheduling_options');

            add_action( 'admin_init', array( $this, 'admin_init' ) );
        }

        public static function register_settings(){
            add_option(
                'competitive_scheduling_options',
                array(
                    'activation' => "1",
                    'subject' => esc_html__( 'Scheduling made - number #code#', 'competitive-scheduling' ),
                )
            );
        }

        public static function unregister_settings(){
            delete_option('competitive_scheduling_options');
        }

        public function admin_init(){
            wp_enqueue_style( 'cs-settings', COMP_SCHEDULE_URL . 'assets/css/settings.css', array(  ), ( COMP_SCHEDULE_DEBUG ? filemtime( COMP_SCHEDULE_PATH . 'assets/css/settings.css' ) : COMP_SCHEDULE_VERSION ) );
            wp_enqueue_script( 'cs-settings', COMP_SCHEDULE_URL . 'assets/js/settings.js', array( 'jquery' ), ( COMP_SCHEDULE_DEBUG ? filemtime( COMP_SCHEDULE_PATH . 'assets/js/settings.js' ) : COMP_SCHEDULE_VERSION ) );

            register_setting( 
                'competitive_scheduling_group', 
                'competitive_scheduling_options'
            );

            add_settings_section(
                'competitive_scheduling_main_section',
                esc_html__( 'Main', 'competitive-scheduling' ),
                array( $this, 'section_callback_main' ),
                'competitive_scheduling_main'
            );

            $this->section_fields_main();

            add_settings_section(
                'competitive_scheduling_email_section',
                esc_html__( 'Email', 'competitive-scheduling' ),
                array( $this, 'section_callback_email' ),
                'competitive_scheduling_email'
            );

            $this->section_fields_email();
        }

        function section_callback_main() {
            esc_html_e( 'Here you can find all the main options to personalise your plugin\'s instance.', 'competitive-scheduling' );
        }

        function section_fields_main(){
            add_settings_field(
                'activation',
                esc_html__( 'Activation', 'competitive-scheduling' ),
                array( $this, 'field_activation_callback' ),
                'competitive_scheduling_main',
                'competitive_scheduling_main_section'
            );

            add_settings_field(
                'shortcode',
                esc_html__( 'Shortcode', 'competitive-scheduling' ),
                array( $this, 'field_shortcode_callback' ),
                'competitive_scheduling_main',
                'competitive_scheduling_main_section'
            );

            add_settings_field(
                'subject',
                esc_html__( 'Subject', 'competitive-scheduling' ),
                array( $this, 'field_subject_callback' ),
                'competitive_scheduling_main',
                'competitive_scheduling_main_section',
                array(
                    'label_for' => 'subject'
                )
            );
        }

        function section_callback_email() {
            esc_html_e( 'Here you can find all the email options to personalise your plugin\'s instance.', 'competitive-scheduling' );
        }

        function section_fields_email(){
            add_settings_field(
                'message',
                esc_html__( 'Activation', 'competitive-scheduling' ),
                array( $this, 'field_message_callback' ),
                'competitive_scheduling_email',
                'competitive_scheduling_email_section'
            );
        }

        public function field_activation_callback(){
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

        public function field_shortcode_callback(){
            ?>
            <span><?php echo esc_html__( 'Use the shortcode [competitive_scheduling] to display the controler in any page/post/widget. IMPORTANT: is necessary to be logged-in to see it.', 'competitive-scheduling' ); ?></span>
            <?php
        }

        public function field_subject_callback(){
            ?>
                <input 
                type="text" 
                name="competitive_scheduling_options[subject]" 
                id="subject"
                class="input-titles" 
                value="<?php echo isset( self::$options['subject'] ) ? esc_attr( self::$options['subject'] ) : ''; ?>"
                >
                <p><?php echo esc_html__( 'Subject of emails that will be sent to users\' appointments made on your website.', 'competitive-scheduling' ); ?></p> 
            <?php
        }

        public function field_message_callback(){
            ?>
                <input 
                type="text" 
                name="competitive_scheduling_options[subject]" 
                id="subject"
                class="input-titles" 
                value="<?php echo isset( self::$options['subject'] ) ? esc_attr( self::$options['subject'] ) : ''; ?>"
                >
                <p><?php echo esc_html__( 'Subject of emails that will be sent to users\' appointments made on your website.', 'competitive-scheduling' ); ?></p> 
            <?php
        }
    }
}