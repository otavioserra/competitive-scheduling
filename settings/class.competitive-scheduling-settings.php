<?php

if( !class_exists( 'Competitive_Scheduling_Settings' ) ){
    class Competitive_Scheduling_Settings {
        
        public static $options;
        public static $html_options;
        public static $msg_options;
        public static $tools_options;
        
        public function __construct(){
            self::$options = get_option('competitive_scheduling_options');
            self::$html_options = get_option('competitive_scheduling_html_options');
            self::$msg_options = get_option('competitive_scheduling_msg_options');
            self::$tools_options = get_option('competitive_scheduling_tools_options');

            if(isset(self::$tools_options['reset-to-defaults'])){
                self::reset_options();
                unset(self::$tools_options['reset-to-defaults']);
                update_option('competitive_scheduling_tools_options', self::$tools_options);
            }

            add_action( 'admin_init', array( $this, 'sections_init' ) );
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
                    'schedule-subject' => esc_html__( 'Scheduling made - nº #code#', 'competitive-scheduling' ),
                    'schedule-message' => self::template_html( 'schedule-message' ),
                    'unschedule-subject' => esc_html__( 'Schedule Cancellation - nº #code#', 'competitive-scheduling' ),
                    'unschedule-message' => self::template_html( 'unschedule-message' ),
                    'confirmation-subject' => esc_html__( 'Schedule Confirmation - nº #code#', 'competitive-scheduling' ),
                    'confirmation-message' => self::template_html( 'confirmation-message' ),
                    'pre-scheduling-subject' => esc_html__( 'Pre-Scheduling Made - nº #code#', 'competitive-scheduling' ),
                    'pre-scheduling-message' => self::template_html( 'pre-scheduling-message' ),
                )
            );

            add_option(
                'competitive_scheduling_msg_options',
                array(
                    'print-schedules' => self::template_html( 'print-schedules' ),
                )
            );

            add_option(
                'competitive_scheduling_tools_options',
                array(
                    'print-schedules' => self::template_html( 'print-schedules' ),
                )
            );
        }

        public static function unregister_settings(){
            delete_option('competitive_scheduling_options');
            delete_option('competitive_scheduling_html_options');
            delete_option('competitive_scheduling_msg_options');
            delete_option('competitive_scheduling_tools_options');
        }

        public static function reset_settings(){
            self::unregister_settings();
            self::register_settings();
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
                        'title' => __( 'Your appointment was successful!', 'competitive-scheduling' ),
                        'protocol' => __( 'Protocol nº #code#', 'competitive-scheduling' ),
                        'description' => __( 'You have just made an appointment within the #title# booking system:', 'competitive-scheduling' ),
                        'day' => __( 'Day', 'competitive-scheduling' ),
                        'password' => __( 'Password', 'competitive-scheduling' ),
                        'scheduled-people' => __( 'Scheduled People', 'competitive-scheduling' ),
                        'your-name' => __( 'Your name', 'competitive-scheduling' ),
                        'escort' => __( 'Escort', 'competitive-scheduling' ),
                        'cancel-appointment' => __( 'If you wish to <b>CANCEL</b> your appointment, go to', 'competitive-scheduling' ),
                    );
                    
                break;
                case 'unschedule-message':
                    $change_variables = array(
                        'title' => __( 'Your appointment has been successfully cancelled!', 'competitive-scheduling' ),
                        'protocol' => __( 'Protocol nº #code#', 'competitive-scheduling' ),
                        'description' => __( 'You just deleted a schedule within the #title# scheduling system:', 'competitive-scheduling' ),
                        'day' => __( 'Day', 'competitive-scheduling' ),
                    );
                    
                break;
                case 'confirmation-message':
                    $change_variables = array(
                        'title' => __( 'Confirm or cancel your appointment!', 'competitive-scheduling' ),
                        'protocol' => __( 'Protocol nº #code#', 'competitive-scheduling' ),
                        'description' => __( 'The system qualified your pre-scheduling for scheduling of #title#:', 'competitive-scheduling' ),
                        'day' => __( 'Day', 'competitive-scheduling' ),
                        'confirm' => __( 'To <b>CONFIRM</b> your appointment and receive your service password, go to', 'competitive-scheduling' ),
                        'cancel' => __( 'To <b>CANCEL</b> your appointment, go to', 'competitive-scheduling' ),
                    );
                    
                break;
                case 'pre-scheduling-message':
                    $change_variables = array(
                        'title' => __( 'Your pre-scheduling was successful!', 'competitive-scheduling' ),
                        'protocol' => __( 'Protocol nº #code#', 'competitive-scheduling' ),
                        'description' => __( 'You have just made a pre-booking within the #title# booking system:', 'competitive-scheduling' ),
                        'day' => __( 'Day', 'competitive-scheduling' ),
                        'info' => __( 'IMPORTANT INFORMATION', 'competitive-scheduling' ),
                        'important_1' => __( '<span class="txt-1"><b>IMPORTANT 1</b>:</span> Pre-appointments ARE NOT confirmed appointments. They will go through a draw using the <span class="txt-1">#draw_date#</span> system days before the day of service. If your pre-scheduling is drawn, you must confirm your appointment via an email that will be sent <span class="txt-1">#draw_date#</span> days before the day of the appointment. Or by directly accessing our system after this date and choosing the CONFIRM APPOINTMENT option for the day of your appointment. This confirmation must be made between <span class="txt-1">#date_confirmation_1#</span> and <span class="txt-1">#date_confirmation_2#</span> days before the day of service. If you do not confirm your appointment within this period, the places guaranteed in your pre-booking draw will no longer be effective and the places will be released to be chosen by other people via the system again.', 'competitive-scheduling' ),
                        'important_2' => __( '<br /><span class="txt-1"><b>IMPORTANT 2</b>:</span> If there are more pre-bookings than there are service spaces, the system will automatically carry out a draw and send a confirmation email to those selected, otherwise it will send a confirmation email to everyone. Therefore, if you do not receive a confirmation email, it is because you were not selected to participate in the service.', 'competitive-scheduling' ),
                        'important_3' => __( '<br /><span class="txt-1"><b>IMPORTANT 3</b>:</span> After day <span class="txt-1">#date_confirmation_2#</span>, the scheduling system will release the residual vacancies to be chosen again and if you have not confirmed, or have not been drawn, you will be able to choose the same date for an appointment. At this stage, places are not guaranteed and can be chosen by anyone who accesses the system.', 'competitive-scheduling' ),
                        'cancel' => __( 'If you wish to <b>CANCEL</b> your appointment, go to', 'competitive-scheduling' ),
                    );
                    
                break;
                case 'print-schedules':
                    $change_variables = array(
                        'name' => __( 'Name', 'competitive-scheduling' ),
                        'password' => __( 'Password', 'competitive-scheduling' ),
                        'viewed' => __( 'Viewed', 'competitive-scheduling' ),
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

        public function sections_init(){
            
            wp_enqueue_style( 'cs-settings', COMP_SCHEDULE_URL . 'assets/css/settings.css', array(  ), ( COMP_SCHEDULE_DEBUG ? filemtime( COMP_SCHEDULE_PATH . 'assets/css/settings.css' ) : COMP_SCHEDULE_VERSION ) );
            wp_enqueue_script( 'cs-settings', COMP_SCHEDULE_URL . 'assets/js/settings.js', array( 'jquery' ), ( COMP_SCHEDULE_DEBUG ? filemtime( COMP_SCHEDULE_PATH . 'assets/js/settings.js' ) : COMP_SCHEDULE_VERSION ) );

            wp_enqueue_style( 'fomantic-ui', COMP_SCHEDULE_URL . 'vendor/fomantic-UI@2.9.0/dist/semantic.min.css', array(  ), COMP_SCHEDULE_VERSION );
            wp_enqueue_script( 'fomantic-ui', COMP_SCHEDULE_URL . 'vendor/fomantic-UI@2.9.0/dist/semantic.min.js', array( 'jquery' ), COMP_SCHEDULE_VERSION );

            register_setting( 
                'competitive_scheduling_group_options', 
                'competitive_scheduling_options'
            );

            register_setting( 
                'competitive_scheduling_group_html_options', 
                'competitive_scheduling_html_options'
            );

            register_setting( 
                'competitive_scheduling_group_msg_options', 
                'competitive_scheduling_msg_options'
            );

            register_setting( 
                'competitive_scheduling_group_tools', 
                'competitive_scheduling_tools_options'
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
                esc_html__( 'Emails', 'competitive-scheduling' ),
                array( $this, 'section_callback_email' ),
                'competitive_scheduling_email'
            );

            $this->section_fields_email();

            add_settings_section(
                'competitive_scheduling_messages_section',
                esc_html__( 'Messages', 'competitive-scheduling' ),
                array( $this, 'section_callback_messages' ),
                'competitive_scheduling_messages'
            );

            $this->section_fields_messages();

            add_settings_section(
                'competitive_scheduling_tools_section',
                esc_html__( 'Tools', 'competitive-scheduling' ),
                array( $this, 'section_callback_tools' ),
                'competitive_scheduling_tools'
            );

            $this->section_fields_tools();
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
                'unavailable-dates-values',
                esc_html__( 'Unavailable Dates Values', 'competitive-scheduling' ),
                array( $this, 'field_unavailable_dates_values_callback' ),
                'competitive_scheduling_main',
                'competitive_scheduling_main_section'
            );
        }

        function section_callback_email() {
            esc_html_e( 'Here you can find all the email options to personalise your plugin\'s instance.', 'competitive-scheduling' );
        }

        function section_fields_email(){
            add_settings_field(
                'pre-scheduling-subject',
                esc_html__( 'Pre-scheduling Subject', 'competitive-scheduling' ),
                array( $this, 'field_preschedule_subject_callback' ),
                'competitive_scheduling_email',
                'competitive_scheduling_email_section',
                array(
                    'label_for' => 'pre-scheduling-subject'
                )
            );

            add_settings_field(
                'pre-scheduling-message',
                esc_html__( 'Pre-scheduling Message', 'competitive-scheduling' ),
                array( $this, 'field_preschedule_message_callback' ),
                'competitive_scheduling_email',
                'competitive_scheduling_email_section'
            );

            add_settings_field(
                'schedule-subject',
                esc_html__( 'Schedule Subject', 'competitive-scheduling' ),
                array( $this, 'field_schedule_subject_callback' ),
                'competitive_scheduling_email',
                'competitive_scheduling_email_section',
                array(
                    'label_for' => 'schedule-subject'
                )
            );

            add_settings_field(
                'schedule-message',
                esc_html__( 'Schedule Message', 'competitive-scheduling' ),
                array( $this, 'field_schedule_message_callback' ),
                'competitive_scheduling_email',
                'competitive_scheduling_email_section'
            );

            add_settings_field(
                'unschedule-subject',
                esc_html__( 'Unschedule Subject', 'competitive-scheduling' ),
                array( $this, 'field_unschedule_subject_callback' ),
                'competitive_scheduling_email',
                'competitive_scheduling_email_section',
                array(
                    'label_for' => 'unschedule-subject'
                )
            );

            add_settings_field(
                'unschedule-message',
                esc_html__( 'Unschedule Message', 'competitive-scheduling' ),
                array( $this, 'field_unschedule_message_callback' ),
                'competitive_scheduling_email',
                'competitive_scheduling_email_section'
            );

            add_settings_field(
                'confirmation-subject',
                esc_html__( 'Confirmation Subject', 'competitive-scheduling' ),
                array( $this, 'field_confirmation_subject_callback' ),
                'competitive_scheduling_email',
                'competitive_scheduling_email_section',
                array(
                    'label_for' => 'confirmation-subject'
                )
            );

            add_settings_field(
                'confirmation-message',
                esc_html__( 'Confirmation Message', 'competitive-scheduling' ),
                array( $this, 'field_confirmation_message_callback' ),
                'competitive_scheduling_email',
                'competitive_scheduling_email_section'
            );
        }

        function section_callback_messages() {
            esc_html_e( 'Here you can find all the messages options to personalise your plugin\'s instance.', 'competitive-scheduling' );
        }

        function section_fields_messages(){
            wp_enqueue_script('codemirror', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/codemirror.min.js', array('jquery'), '6.65.7', false);
            wp_enqueue_style('codemirror-css', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/codemirror.min.css', array(), '6.65.7');
            wp_enqueue_script('codemirror-mode', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/mode/htmlmixed/htmlmixed.min.js', array('codemirror'), '6.65.7', false);
            
            add_settings_field(
                'print-schedules',
                esc_html__( 'Print Schedules', 'competitive-scheduling' ),
                array( $this, 'field_print_schedules_callback' ),
                'competitive_scheduling_messages',
                'competitive_scheduling_messages_section',
            );
        }

        function section_callback_tools() {
            esc_html_e( 'Here you can find all the tools options to personalise your plugin\'s instance.', 'competitive-scheduling' );
        }

        function section_fields_tools(){
            add_settings_field(
                'shortcode',
                esc_html__( 'Shortcode', 'competitive-scheduling' ),
                array( $this, 'field_shortcode_callback' ),
                'competitive_scheduling_tools',
                'competitive_scheduling_tools_section'
            );

            add_settings_field(
                'reset-to-defaults',
                esc_html__( 'Reset To Defaults', 'competitive-scheduling' ), 
                array( $this, 'field_reset_to_defaults_callback' ),
                'competitive_scheduling_tools',
                'competitive_scheduling_tools_section'
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

        public function field_unavailable_dates_values_callback(){
            ?>

            <div class="contDates">
                <div class="ui existing segment calendar-multiple campo datas-multiplas" data-locale="<?php echo get_locale(); ?>">
                    <div class="ui calendar multiplo"></div>
                    <div class="ui calendar-dates"></div>
                    <input type="hidden" name="competitive_scheduling_options[unavailable-dates-values]"  value="<?php echo isset( self::$options['unavailable-dates-values'] ) ? esc_attr( self::$options['unavailable-dates-values'] ) : ''; ?>" class="calendar-dates-input">
                </div>
            </div>

                <p><?php echo esc_html__( 'Specific dates unavailable to choose when scheduling.', 'competitive-scheduling' ); ?></p>
            <?php
        }

        public function field_preschedule_subject_callback(){
            ?>
                <input 
                type="text" 
                name="competitive_scheduling_html_options[pre-scheduling-subject]" 
                id="subject"
                class="input-titles" 
                value="<?php echo isset( self::$html_options['pre-scheduling-subject'] ) ? esc_attr( self::$html_options['pre-scheduling-subject'] ) : ''; ?>"
                >
                <p><?php echo esc_html__( 'Subject of emails that will be sent to users pre-bookings made on your website.', 'competitive-scheduling' ); ?></p> 
            <?php
        }

        public function field_preschedule_message_callback(){
            // Renders custom TinyMCE editor
            wp_editor(isset( self::$html_options['pre-scheduling-message'] ) ? self::$html_options['pre-scheduling-message'] : '', 'pre-scheduling-message', [
                'textarea_name' => 'competitive_scheduling_html_options[pre-scheduling-message]',
                'mode' => 'text/html',
                'theme' => 'monokai',
                'plugins' => ['advlist', 'autolink', 'link', 'media', 'paste', 'table', 'textcolor'],
                'width' => 1250,
                'min_width' => 500,
            ]);

            ?>
                <p><?php echo esc_html__( 'Email message that will be sent to users\' pre-bookings made on your website.', 'competitive-scheduling' ); ?></p> 
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
            // Renders custom TinyMCE editor
            wp_editor(isset( self::$html_options['schedule-message'] ) ? self::$html_options['schedule-message'] : '', 'schedule-message', [
                'textarea_name' => 'competitive_scheduling_html_options[schedule-message]',
                'mode' => 'text/html',
                'theme' => 'monokai',
                'plugins' => ['advlist', 'autolink', 'link', 'media', 'paste', 'table', 'textcolor'],
                'width' => 1250,
                'min_width' => 500,
            ]);

            ?>
                <p><?php echo esc_html__( 'Message of emails that will be sent to users\' appointments made on your website.', 'competitive-scheduling' ); ?></p> 
            <?php
        }

        public function field_unschedule_subject_callback(){
            ?>
                <input 
                type="text" 
                name="competitive_scheduling_html_options[unschedule-subject]" 
                id="subject"
                class="input-titles" 
                value="<?php echo isset( self::$html_options['unschedule-subject'] ) ? esc_attr( self::$html_options['unschedule-subject'] ) : ''; ?>"
                >
                <p><?php echo esc_html__( 'Subject of the emails that will be sent regarding the exclusions of user appointments made on your website.', 'competitive-scheduling' ); ?></p> 
            <?php
        }

        public function field_unschedule_message_callback(){
            // Renders custom TinyMCE editor
            wp_editor(isset( self::$html_options['unschedule-message'] ) ? self::$html_options['unschedule-message'] : '', 'unschedule-message', [
                'textarea_name' => 'competitive_scheduling_html_options[unschedule-message]',
                'mode' => 'text/html',
                'theme' => 'monokai',
                'plugins' => ['advlist', 'autolink', 'link', 'media', 'paste', 'table', 'textcolor'],
                'width' => 1250,
                'min_width' => 500,
            ]);

            ?>
                <p><?php echo esc_html__( 'Message of the emails that will be sent to exclude users from bookings made on your website.', 'competitive-scheduling' ); ?></p> 
            <?php
        }

        public function field_confirmation_subject_callback(){
            ?>
                <input 
                type="text" 
                name="competitive_scheduling_html_options[confirmation-subject]" 
                id="subject"
                class="input-titles" 
                value="<?php echo isset( self::$html_options['confirmation-subject'] ) ? esc_attr( self::$html_options['confirmation-subject'] ) : ''; ?>"
                >
                <p><?php echo esc_html__( 'Subject of emails that will be sent to confirm user appointments made on your website.', 'competitive-scheduling' ); ?></p> 
            <?php
        }

        public function field_confirmation_message_callback(){
            // Renders custom TinyMCE editor
            wp_editor(isset( self::$html_options['confirmation-message'] ) ? self::$html_options['confirmation-message'] : '', 'confirmation-message', [
                'textarea_name' => 'competitive_scheduling_html_options[confirmation-message]',
                'mode' => 'text/html',
                'theme' => 'monokai',
                'plugins' => ['advlist', 'autolink', 'link', 'media', 'paste', 'table', 'textcolor'],
                'width' => 1250,
                'min_width' => 500,
            ]);

            ?>
                <p><?php echo esc_html__( 'Email message that will be sent to confirm user appointments made on your website.', 'competitive-scheduling' ); ?></p> 
            <?php
        }
        
        public function field_print_schedules_callback(){
            ?>
                <textarea id="codemirror_editor" name="competitive_scheduling_msg_options[print-schedules]" rows="10" cols="50"><?php echo isset( self::$msg_options['print-schedules'] ) ? self::$msg_options['print-schedules'] : '';  ?></textarea>
                <p><?php echo esc_html__( 'HTML layout for printing confirmed appointments.', 'competitive-scheduling' ); ?></p> 
            <?php
        }
        
        public function field_shortcode_callback(){
            ?>
            <span><?php echo esc_html__( 'Use the shortcode [competitive_scheduling] to display the controler in any page/post/widget. IMPORTANT: is necessary to be logged-in to see it.', 'competitive-scheduling' ); ?></span>
            <?php
        }

        public function field_reset_to_defaults_callback(){
            ?>

                <input 
                    type="checkbox"
                    name="competitive_scheduling_tools_options[reset-to-defaults]"
                    id="reset-to-defaults"
                    value="1"
                />
                <label for="reset-to-defaults"><?php echo esc_html__( 'Select this option and then click <b>Save Settings</b> below so that all settings are reset to default values. Important: all previous values will be deleted.', 'competitive-scheduling' ); ?></label>
            <?php
        }
    }
}