<?php

if( !class_exists( 'Competitive_Scheduling_Settings' ) ){
    class Competitive_Scheduling_Settings {
        
        public static $options;
        public static $html_options;
        
        public function __construct(){
            self::$options = get_option('competitive_scheduling_options');
            self::$html_options = get_option('competitive_scheduling_html_options');

            add_action( 'admin_init', array( $this, 'admin_init' ) );
        } 

        public static function register_settings(){
            add_option(
                'competitive_scheduling_options',
                array(
                    'activation' => "1",
                )
            );

            add_option(
                'competitive_scheduling_html_options',
                array(
                    'schedule-subject' => esc_html__( 'Scheduling made - number #code#', 'competitive-scheduling' ),
                    'schedule-message' => self::template_html( 'schedule-message' ),
                )
            );
        }

        public static function unregister_settings(){
            delete_option('competitive_scheduling_options');
            delete_option('competitive_scheduling_html_options');
        }

        private static function template_html( $id_template ){
            // Require templates class.
            require_once( COMP_SCHEDULE_PATH . 'includes/class.templates.php' );

            // Read template content.
            $template = file_get_contents( COMP_SCHEDULE_PATH . 'settings/templates/template-' . $id_template . '.html' );

            // Check if template exists
            if ( $template === null ) {
                return '';
            }

            // Change template variables
            switch($id_template) {
                case 'schedule-message':
                    $change_variables = array(
                        'title' => esc_html__( 'Your appointment was successful!', 'competitive-scheduling' ),
                        'protocol' => esc_html__( 'Protocol n&ordm; #code#', 'competitive-scheduling' ),
                        'description' => esc_html__( 'You have just made an appointment within the #title# booking system:', 'competitive-scheduling' ),
                        'day' => esc_html__( 'Day', 'competitive-scheduling' ),
                        'password' => esc_html__( 'Password', 'competitive-scheduling' ),
                        'scheduled-people' => esc_html__( 'Scheduled People', 'competitive-scheduling' ),
                        'your-name' => esc_html__( 'Your name', 'competitive-scheduling' ),
                        'escort' => esc_html__( 'Escort', 'competitive-scheduling' ),
                        'cancel-appointment' => esc_html__( 'If you wish to <b>CANCEL</b> your appointment, go to', 'competitive-scheduling' ),
                    );
                    
                break;
            }

            // Change all occurrences of changes_variables on template
            if( isset($change_variables) ){
                foreach($change_variables as $key => $value){
                    $template = Templates::change_variable($template, '[['.$key.']]', $value);
                }
            }

            return $template;
        }

        private static function template_html_variables( $id_template, $variables ) {
            return preg_replace('/'.preg_quote($var).'/i',$valor,$modelo);
        }

        private function admin_init(){
            wp_enqueue_style( 'cs-settings', COMP_SCHEDULE_URL . 'assets/css/settings.css', array(  ), ( COMP_SCHEDULE_DEBUG ? filemtime( COMP_SCHEDULE_PATH . 'assets/css/settings.css' ) : COMP_SCHEDULE_VERSION ) );
            wp_enqueue_script( 'cs-settings', COMP_SCHEDULE_URL . 'assets/js/settings.js', array( 'jquery' ), ( COMP_SCHEDULE_DEBUG ? filemtime( COMP_SCHEDULE_PATH . 'assets/js/settings.js' ) : COMP_SCHEDULE_VERSION ) );

            register_setting( 
                'competitive_scheduling_group', 
                'competitive_scheduling_options'
            );

            register_setting( 
                'competitive_scheduling_group', 
                'competitive_scheduling_html_options'
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
        }

        function section_callback_email() {
            esc_html_e( 'Here you can find all the email options to personalise your plugin\'s instance.', 'competitive-scheduling' );
        }

        function section_fields_email(){
            add_settings_field(
                'schedule-subject',
                esc_html__( 'Schedule Subject', 'competitive-scheduling' ),
                array( $this, 'field_schedule_subject_callback' ),
                'competitive_scheduling_email',
                'competitive_scheduling_email_section',
                array(
                    'label_for' => 'subject'
                )
            );

            add_settings_field(
                'schedule-message',
                esc_html__( 'Schedule Message', 'competitive-scheduling' ),
                array( $this, 'field_schedule_message_callback' ),
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

        public function field_schedule_subject_callback(){
            ?>
                <input 
                type="text" 
                name="competitive_scheduling_html_options[schedule-subject]" 
                id="subject"
                class="input-titles" 
                value="<?php echo isset( self::$html_options['schedule-subject'] ) ? esc_attr( self::$html_options['schedule-subject'] ) : ''; ?>"
                >
                <p><?php echo esc_html__( 'Subject of emails that will be sent to users\' appointments made on your website.', 'competitive-scheduling' ); ?></p> 
            <?php
        }

        public function field_schedule_message_callback(){
            ?>
                <input 
                type="text" 
                name="competitive_scheduling_html_options[schedule-message]" 
                id="subject"
                class="input-titles" 
                value="<?php echo isset( self::$html_options['schedule-message'] ) ? esc_attr( self::$html_options['schedule-message'] ) : ''; ?>"
                >
                <p><?php echo esc_html__( 'Subject of emails that will be sent to users\' appointments made on your website.', 'competitive-scheduling' ); ?></p> 
            <?php
        }
    }
}