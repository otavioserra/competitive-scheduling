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
                self::reset_settings();
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
                    'title-establishment' => __( 'My Establishment', 'competitive-scheduling' ),
                    'calendar-years' => 3,
                    'calendar-holidays-start' => "20 December",
                    'calendar-holidays-end' => "20 January",
                    'calendar-limit-month-ahead' => 2,
                    'days-week' => "tue,thu",
                    'days-week-maximum-vacancies' => 70,
                    'free-choice-phase' => 7,
                    'residual-phase' => 5,
                    'draw-phase' => "7,5",
                    'max-companions' => 3,
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
                    'coupon-priority-description' => __( 'When making a new appointment, fill in the code below in the <b>Priority Coupon</b> field', 'competitive-scheduling' ),
                    'msg-scheduling-cancelled' => self::template_html( 'msg-scheduling-cancelled' ),
                    'msg-scheduling-confirmed' => self::template_html( 'msg-scheduling-confirmed' ),
                    'msg-scheduling-date-not-allowed' => self::template_html( 'msg-scheduling-date-not-allowed' ),
                    'msg-schedule-expired' => self::template_html( 'msg-schedule-expired' ),
                    'msg-scheduling-already-confirmed' => self::template_html( 'msg-scheduling-already-confirmed' ),
                    'msg-scheduling-already-exists' => self::template_html( 'msg-scheduling-already-exists' ),
                    'msg-scheduling-not-found' => self::template_html( 'msg-scheduling-not-found' ),
                    'msg-scheduling-without-vacancies' => self::template_html( 'msg-scheduling-without-vacancies' ),
                    'msg-scheduling-suspended' => self::template_html( 'msg-scheduling-suspended' ),
                    'msg-conclusion-scheduling' => self::template_html( 'msg-conclusion-scheduling' ),
                    'msg-conclusion-pre-scheduling' => self::template_html( 'msg-conclusion-pre-scheduling' ),
                    'msg-confirmation-status-not-permitted' => self::template_html( 'msg-confirmation-status-not-permitted' ),
                    'msg-coupon-priority-inactive' => self::template_html( 'msg-coupon-priority-inactive' ),
                    'msg-priority-coupon-already-used' => self::template_html( 'msg-priority-coupon-already-used' ),
                    'msg-coupon-priority-not-found' => self::template_html( 'msg-coupon-priority-not-found' ),
                    'msg-expired-priority-coupon' => self::template_html( 'msg-expired-priority-coupon' ),
                    'msg-residual-vacancies-unavailable' => self::template_html( 'msg-residual-vacancies-unavailable' ),
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
                case 'msg-scheduling-cancelled':
                    $change_variables = array(
                        'title' => __( 'Cancellation Success', 'competitive-scheduling' ),
                        'description' => __( 'Appointment <b>CANCELLED</b> successfully!', 'competitive-scheduling' ),
                    );
                    
                break;
                case 'msg-scheduling-confirmed':
                    $change_variables = array(
                        'title' => __( 'Confirmation Success', 'competitive-scheduling' ),
                        'description' => __( 'Scheduling <b>CONFIRMED</b> Successfully!', 'competitive-scheduling' ),
                        'scheduled-people' => __( 'Scheduled People', 'competitive-scheduling' ),
                        'your-name' => __( 'Your name', 'competitive-scheduling' ),
                        'companion' => __( 'Companion', 'competitive-scheduling' ),
                    );
                    
                break;
                case 'msg-scheduling-date-not-allowed':
                    $change_variables = array(
                        'title' => __( 'Date Not Allowed', 'competitive-scheduling' ),
                        'description' => __( 'This date is not valid. allowed, choose another.', 'competitive-scheduling' ),
                    );
                    
                break;
                case 'msg-schedule-expired':
                    $change_variables = array(
                        'title' => __( 'Confirmation Period Expired', 'competitive-scheduling' ),
                        'description1' => __( 'It is not It is possible to confirm your appointment once it is ready. outside the confirmation period!', 'competitive-scheduling' ),
                        'description2' => __( 'The confirmation period comprises the day <b>#date_confirmation_1#</b> until the end of the month. the day <b>#date_confirmation_2#</b> .', 'competitive-scheduling' ),
                    );
                    
                break;
                case 'msg-scheduling-already-confirmed':
                    $change_variables = array(
                        'title' => __( 'This appointment is already available. was confirmed in another confirmation attempt and therefore is not confirmed. You can confirm the same again.', 'competitive-scheduling' ),
                    );
                    
                break;
                case 'msg-scheduling-already-exists':
                    $change_variables = array(
                        'title' => __( 'Schedule Now Exist', 'competitive-scheduling' ),
                        'description' => __( 'This date is already over. You have an appointment registered in your name. It is not Allowed to schedule twice on the same date. If you want to modify a schedule, just click here. is This is possible by removing the schedule for the same date in <a class="_ajax_not" href="#url-schedules-previous#">Schedules</a> and creating a new schedule for the same date again, as long as the date is free for new appointments.', 'competitive-scheduling' ),
                    );
                    
                break;
                case 'msg-scheduling-not-found':
                    $change_variables = array(
                        'title' => __( 'Appointment Not Found', 'competitive-scheduling' ),
                        'description1' => __( 'We were unable to find your appointment or the verification code provided has expired.', 'competitive-scheduling' ),
                        'description2' => __( 'It is possible that your appointment has already been completed. has been confirmed or canceled and the appointment verification code has been removed from the system after use. Therefore, access the system <a class="_ajax_not" href="#url-schedules-previous#">Schedules</a> and make the changes there.', 'competitive-scheduling' ),
                    );
                    
                break;
                case 'msg-scheduling-without-vacancies':
                    $change_variables = array(
                        'title' => __( 'No Vacancy Scheduling', 'competitive-scheduling' ),
                        'description' => __( 'It is not It is possible to schedule you and/or their companions on the day <b>#date#</b> as it exceeds the capacity of appointment slots on the day in question!', 'competitive-scheduling' ),
                        'vacancies' => __( 'Total number of available places: <b>#vacancies#</b>.', 'competitive-scheduling' ),
                        'observations' => __( 'Observations:', 'competitive-scheduling' ),
                        'info1' => __( 'Choose another day and try again.', 'competitive-scheduling' ),
                        'info2' => __( 'Or, reduce the number of companions and try again.', 'competitive-scheduling' ),
                    );
                    
                break;
                case 'msg-scheduling-suspended':
                    $change_variables = array(
                        'title' => __( 'Scheduling is suspended!', 'competitive-scheduling' ),
                    );
                    
                break;
                case 'msg-conclusion-scheduling':
                    $change_variables = array(
                        'title' => __( 'Scheduling Completed Successfully!', 'competitive-scheduling' ),
                        'day' => __( 'Day', 'competitive-scheduling' ),
                        'password' => __( 'Password', 'competitive-scheduling' ),
                        'scheduled-people' => __( 'Scheduled People', 'competitive-scheduling' ),
                        'your-name' => __( 'Your Name', 'competitive-scheduling' ),
                        'companion' => __( 'Companion', 'competitive-scheduling' ),
                    );
                    
                break;
                case 'msg-conclusion-pre-scheduling':
                    $change_variables = array(
                        'title' => __( 'Pre-Schedule Completed Successfully!', 'competitive-scheduling' ),
                        'day' => __( 'Day', 'competitive-scheduling' ),
                        'important_1' => __( '<span class="txt-1"><b>IMPORTANT 1</b>:</span> Pre-appointments ARE NOT confirmed appointments. They will go through a draw using the <span class="txt-1">#draw_date#</span> system days before the day of service. If your pre-scheduling is drawn, you must confirm your appointment via an email that will be sent <span class="txt-1">#draw_date#</span> days before the day of the appointment. Or by directly accessing our system after this date and choosing the CONFIRM APPOINTMENT option for the day of your appointment. This confirmation must be made between <span class="txt-1">#date_confirmation_1#</span> and <span class="txt-1">#date_confirmation_2#</span> days before the day of service. If you do not confirm your appointment within this period, the places guaranteed in your pre-booking draw will no longer be effective and the places will be released to be chosen by other people via the system again.', 'competitive-scheduling' ),
                        'important_2' => __( '<br /><span class="txt-1"><b>IMPORTANT 2</b>:</span> If there are more pre-bookings than there are service spaces, the system will automatically carry out a draw and send a confirmation email to those selected, otherwise it will send a confirmation email to everyone. Therefore, if you do not receive a confirmation email, it is because you were not selected to participate in the service.', 'competitive-scheduling' ),
                        'important_3' => __( '<br /><span class="txt-1"><b>IMPORTANT 3</b>:</span> After day <span class="txt-1">#date_confirmation_2#</span>, the scheduling system will release the residual vacancies to be chosen again and if you have not confirmed, or have not been drawn, you will be able to choose the same date for an appointment. At this stage, places are not guaranteed and can be chosen by anyone who accesses the system.', 'competitive-scheduling' ),
                    );
                    
                break;
                case 'msg-confirmation-status-not-permitted':
                    $change_variables = array(
                        'title' => __( 'It is not possible to confirm this appointment as the system has not allowed it to do so.', 'competitive-scheduling' ),
                    );
                    
                break;
                case 'msg-coupon-priority-inactive':
                    $change_variables = array(
                        'title' => __( 'The priority coupon number <b>#coupon#</b> is inactive in the system and cannot be used.', 'competitive-scheduling' ),
                    );
                    
                break;
                case 'msg-priority-coupon-already-used':
                    $change_variables = array(
                        'title' => __( 'Priority coupon number <b>#coupon#</b> HAS ALREADY BEEN USED and cannot be used again.', 'competitive-scheduling' ),
                    );
                    
                break;
                case 'msg-coupon-priority-not-found':
                    $change_variables = array(
                        'title' => __( 'Priority coupon number <b>#coupon#</b> was not found!', 'competitive-scheduling' ),
                    );
                    
                break;
                case 'msg-coupon-priority-not-found':
                    $change_variables = array(
                        'title' => __( 'Priority coupon number <b>#coupon#</b> is out of validity period! It is only possible to use it between the days <b>#valid_from#</b> to <b>#valid_until#</b>.', 'competitive-scheduling' ),
                    );
                    
                break;
                case 'msg-residual-vacancies-unavailable':
                    $change_variables = array(
                        'title' => __( 'It was not possible to confirm due to residual vacancies. Either because it is beyond the deadline for confirmation or because the remaining vacancies have been exhausted.', 'competitive-scheduling' ),
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
                'title-establishment',
                esc_html__( 'Title Establishment', 'competitive-scheduling' ),
                array( $this, 'field_title_establishment_callback' ),
                'competitive_scheduling_main',
                'competitive_scheduling_main_section'
            );

            add_settings_field(
                'unavailable-dates',
                esc_html__( 'Unavailable Dates', 'competitive-scheduling' ),
                array( $this, 'field_unavailable_dates_callback' ),
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

            add_settings_field(
                'calendar-years',
                esc_html__( 'Calendar Years', 'competitive-scheduling' ),
                array( $this, 'field_calendar_years_values_callback' ),
                'competitive_scheduling_main',
                'competitive_scheduling_main_section'
            );

            add_settings_field(
                'calendar-holidays-start',
                esc_html__( 'Calendar Holidays Start', 'competitive-scheduling' ),
                array( $this, 'field_calendar_holidays_start_callback' ),
                'competitive_scheduling_main',
                'competitive_scheduling_main_section'
            );

            add_settings_field(
                'calendar-holidays-end',
                esc_html__( 'Calendar Holidays End', 'competitive-scheduling' ),
                array( $this, 'field_calendar_holidays_end_callback' ),
                'competitive_scheduling_main',
                'competitive_scheduling_main_section'
            );

            add_settings_field(
                'calendar-limit-month-ahead',
                esc_html__( 'Calendar Limit Month Ahead', 'competitive-scheduling' ),
                array( $this, 'field_calendar_limit_month_ahead_callback' ),
                'competitive_scheduling_main',
                'competitive_scheduling_main_section'
            );

            add_settings_field(
                'days-week',
                esc_html__( 'Days Week', 'competitive-scheduling' ),
                array( $this, 'field_days_week_callback' ),
                'competitive_scheduling_main',
                'competitive_scheduling_main_section'
            );

            add_settings_field(
                'days-week-maximum-vacancies',
                esc_html__( 'Days Week Maximum Vacancies', 'competitive-scheduling' ),
                array( $this, 'field_days_week_maximum_vacancies_callback' ),
                'competitive_scheduling_main',
                'competitive_scheduling_main_section'
            );

            add_settings_field(
                'free-choice-phase',
                esc_html__( 'Free Choice Phase', 'competitive-scheduling' ),
                array( $this, 'field_free_choice_phase_callback' ),
                'competitive_scheduling_main',
                'competitive_scheduling_main_section'
            );

            add_settings_field(
                'residual-phase',
                esc_html__( 'Residual Phase', 'competitive-scheduling' ),
                array( $this, 'field_residual_phase_callback' ),
                'competitive_scheduling_main',
                'competitive_scheduling_main_section'
            );

            add_settings_field(
                'draw-phase',
                esc_html__( 'Draw Phase', 'competitive-scheduling' ),
                array( $this, 'field_draw_phase_callback' ),
                'competitive_scheduling_main',
                'competitive_scheduling_main_section'
            );

            add_settings_field(
                'max-companions',
                esc_html__( 'Max Companions', 'competitive-scheduling' ),
                array( $this, 'field_max_companions_callback' ),
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
            wp_enqueue_style('codemirror-css', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/codemirror.min.css', array(), '5.65.15');
            wp_enqueue_style('codemirror-fullscreen', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/addon/display/fullscreen.min.css', array(), '5.65.15');

            wp_enqueue_script('codemirror', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/codemirror.min.js', array('jquery'), '5.65.15', false);
            wp_enqueue_script('codemirror-mode', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/mode/xml/xml.min.js', array('jquery'), '5.65.15', false);
            
            add_settings_field(
                'print-schedules',
                esc_html__( 'Print Schedules', 'competitive-scheduling' ),
                array( $this, 'field_print_schedules_callback' ),
                'competitive_scheduling_messages',
                'competitive_scheduling_messages_section',
            );

            add_settings_field(
                'coupon-priority-description',
                esc_html__( 'Coupon Priority Description', 'competitive-scheduling' ),
                array( $this, 'field_coupon_priority_description_callback' ),
                'competitive_scheduling_messages',
                'competitive_scheduling_messages_section'
            );

            add_settings_field(
                'msg-scheduling-cancelled',
                esc_html__( 'Scheduling Cancelled', 'competitive-scheduling' ),
                array( $this, 'field_msg_scheduling_cancelled_callback' ),
                'competitive_scheduling_messages',
                'competitive_scheduling_messages_section'
            );

            add_settings_field(
                'msg-scheduling-confirmed',
                esc_html__( 'Scheduling Confirmed', 'competitive-scheduling' ),
                array( $this, 'field_msg_scheduling_confirmed_callback' ),
                'competitive_scheduling_messages',
                'competitive_scheduling_messages_section'
            );

            add_settings_field(
                'msg-scheduling-date-not-allowed',
                esc_html__( 'Scheduling Date Not Allowed', 'competitive-scheduling' ),
                array( $this, 'field_msg_scheduling_date_not_allowed_callback' ),
                'competitive_scheduling_messages',
                'competitive_scheduling_messages_section'
            );

            add_settings_field(
                'msg-schedule-expired',
                esc_html__( 'Schedule Expired', 'competitive-scheduling' ),
                array( $this, 'field_msg_schedule_expired_callback' ),
                'competitive_scheduling_messages',
                'competitive_scheduling_messages_section'
            );

            add_settings_field(
                'msg-scheduling-already-confirmed',
                esc_html__( 'Scheduling Already Confirmed', 'competitive-scheduling' ),
                array( $this, 'field_msg_scheduling_already_confirmed_callback' ),
                'competitive_scheduling_messages',
                'competitive_scheduling_messages_section'
            );

            add_settings_field(
                'msg-scheduling-already-exists',
                esc_html__( 'Scheduling Already Exists', 'competitive-scheduling' ),
                array( $this, 'field_msg_scheduling_already_exists_callback' ),
                'competitive_scheduling_messages',
                'competitive_scheduling_messages_section'
            );

            add_settings_field(
                'msg-scheduling-not-found',
                esc_html__( 'Scheduling Not Found', 'competitive-scheduling' ),
                array( $this, 'field_msg_scheduling_not_found_callback' ),
                'competitive_scheduling_messages',
                'competitive_scheduling_messages_section'
            );

            add_settings_field(
                'msg-scheduling-without-vacancies',
                esc_html__( 'Scheduling Without Vacancies', 'competitive-scheduling' ),
                array( $this, 'field_msg_scheduling_without_vacancies_callback' ),
                'competitive_scheduling_messages',
                'competitive_scheduling_messages_section'
            );

            add_settings_field(
                'msg-scheduling-suspended',
                esc_html__( 'Scheduling Suspended', 'competitive-scheduling' ),
                array( $this, 'field_msg_scheduling_suspended_callback' ),
                'competitive_scheduling_messages',
                'competitive_scheduling_messages_section'
            );

            add_settings_field(
                'msg-conclusion-scheduling',
                esc_html__( 'Conclusion Scheduling', 'competitive-scheduling' ),
                array( $this, 'field_msg_conclusion_scheduling_callback' ),
                'competitive_scheduling_messages',
                'competitive_scheduling_messages_section'
            );

            add_settings_field(
                'msg-conclusion-pre-scheduling',
                esc_html__( 'Conclusion Pre-Scheduling', 'competitive-scheduling' ),
                array( $this, 'field_msg_conclusion_pre_scheduling_callback' ),
                'competitive_scheduling_messages',
                'competitive_scheduling_messages_section'
            );

            add_settings_field(
                'msg-confirmation-status-not-permitted',
                esc_html__( 'Confirmation Status Not Permitted', 'competitive-scheduling' ),
                array( $this, 'field_msg_confirmation_status_not_permitted_callback' ),
                'competitive_scheduling_messages',
                'competitive_scheduling_messages_section'
            );

            add_settings_field(
                'msg-coupon-priority-inactive',
                esc_html__( 'Coupon Priority Inactive', 'competitive-scheduling' ),
                array( $this, 'field_msg_coupon_priority_inactive_callback' ),
                'competitive_scheduling_messages',
                'competitive_scheduling_messages_section'
            );

            add_settings_field(
                'msg-priority-coupon-already-used',
                esc_html__( 'Priority Coupon Already Used', 'competitive-scheduling' ),
                array( $this, 'field_msg_priority_coupon_already_used_callback' ),
                'competitive_scheduling_messages',
                'competitive_scheduling_messages_section'
            );

            add_settings_field(
                'msg-coupon-priority-not-found',
                esc_html__( 'Coupon Priority Not Found', 'competitive-scheduling' ),
                array( $this, 'field_msg_coupon_priority_not_found_callback' ),
                'competitive_scheduling_messages',
                'competitive_scheduling_messages_section'
            );

            add_settings_field(
                'msg-expired-priority-coupon',
                esc_html__( 'Expired Priority Coupon', 'competitive-scheduling' ),
                array( $this, 'field_msg_expired_priority_coupon_callback' ),
                'competitive_scheduling_messages',
                'competitive_scheduling_messages_section'
            );

            add_settings_field(
                'msg-residual-vacancies-unavailable',
                esc_html__( 'Residual Vacancies Unavailable', 'competitive-scheduling' ),
                array( $this, 'field_msg_residual_vacancies_unavailable_callback' ),
                'competitive_scheduling_messages',
                'competitive_scheduling_messages_section'
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

        public function field_title_establishment_callback(){
            ?>
                <input 
                type="text" 
                name="competitive_scheduling_options[title-establishment]" 
                id="title-establishment"
                class="input-titles"
                value="<?php echo isset( self::$options['title-establishment'] ) ? esc_attr( self::$options['title-establishment'] ) : ''; ?>"
                >
                <p><?php echo esc_html__( 'Title of your establishment. Example: my company, my institution, etc.', 'competitive-scheduling' ); ?></p> 
            <?php
        }

        public function field_unavailable_dates_callback(){
            ?>

                <input 
                    type="checkbox"
                    name="competitive_scheduling_options[unavailable-dates]"
                    id="unavailable-dates"
                    value="1"
                    <?php 
                        if( isset( self::$options['unavailable-dates'] ) ){
                            checked( "1", self::$options['unavailable-dates'], true );
                        }    
                    ?>
                />
                <label for="unavailable-dates"><?php echo esc_html__( 'Activate/Deactivate definition of unavailable dates for scheduling.', 'competitive-scheduling' ); ?></label>
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

        public function field_calendar_years_values_callback(){
            ?>
                <input 
                type="number" 
                name="competitive_scheduling_options[calendar-years]" 
                id="calendar-years"
                class="input-numbers"
                min="1"
                max="10"
                value="<?php echo isset( self::$options['calendar-years'] ) ? esc_attr( self::$options['calendar-years'] ) : ''; ?>"
                >
                <p><?php echo esc_html__( 'Maximum number of years that appears on the calendar.', 'competitive-scheduling' ); ?></p> 
            <?php
        }

        public function field_calendar_holidays_start_callback(){
            ?>
                <input 
                type="text" 
                name="competitive_scheduling_options[calendar-holidays-start]" 
                id="calendar-holidays-start"
                class="input-small-text"
                value="<?php echo isset( self::$options['calendar-holidays-start'] ) ? esc_attr( self::$options['calendar-holidays-start'] ) : ''; ?>"
                >
                <p><?php echo esc_html__( 'Start date of the vacation period with the number of the day followed by the month in English. For example, the beginning of the period on December 20th must be filled in: 20 December.', 'competitive-scheduling' ); ?></p> 
            <?php
        }

        public function field_calendar_holidays_end_callback(){
            ?>
                <input 
                type="text" 
                name="competitive_scheduling_options[calendar-holidays-end]" 
                id="calendar-holidays-end"
                class="input-small-text"
                value="<?php echo isset( self::$options['calendar-holidays-end'] ) ? esc_attr( self::$options['calendar-holidays-end'] ) : ''; ?>"
                >
                <p><?php echo esc_html__( 'End date of the vacation period with the number of the day followed by the month in English. For example, the end of the period on January 20th must be filled in: 20 January.', 'competitive-scheduling' ); ?></p> 
            <?php
        }

        public function field_calendar_limit_month_ahead_callback(){
            ?>
                <input 
                type="number" 
                name="competitive_scheduling_options[calendar-limit-month-ahead]" 
                id="calendar-limit-month-ahead"
                class="input-numbers"
                min="1"
                max="24"
                value="<?php echo isset( self::$options['calendar-limit-month-ahead'] ) ? esc_attr( self::$options['calendar-limit-month-ahead'] ) : ''; ?>"
                >
                <p><?php echo esc_html__( 'Limit the number of months ahead of the current day to show dates available for scheduling. For example, if you put 2 in this field, it means that only the current month and the next month will appear to schedule in the calendar.', 'competitive-scheduling' ); ?></p> 
            <?php
        }

        public function field_days_week_callback(){
            ?>
                <input 
                type="text" 
                name="competitive_scheduling_options[days-week]" 
                id="days-week"
                class="input-small-text"
                value="<?php echo isset( self::$options['days-week'] ) ? esc_attr( self::$options['days-week'] ) : ''; ?>"
                >
                <p><?php echo esc_html__( 'Define the days of the week that have a schedule separated by the comma character ',' . The days must be described with 3 characters in English. For example, monday should be written \'mon\'. Template for every day of the week: sun,mon,tue,wed,thu,fri,sat.', 'competitive-scheduling' ); ?></p> 
            <?php
        }

        public function field_days_week_maximum_vacancies_callback(){
            ?>
                <input 
                type="number" 
                name="competitive_scheduling_options[days-week-maximum-vacancies]" 
                id="days-week-maximum-vacancies"
                class="input-numbers"
                min="1"
                max="999"
                value="<?php echo isset( self::$options['days-week-maximum-vacancies'] ) ? esc_attr( self::$options['days-week-maximum-vacancies'] ) : ''; ?>"
                >
                <p><?php echo esc_html__( 'Maximum number of appointment slots for days of the week. If you want to define different amounts per day of the week, use ',' to separate. For example, 40.60.', 'competitive-scheduling' ); ?></p> 
            <?php
        }

        public function field_free_choice_phase_callback(){
            ?>
                <input 
                type="number" 
                name="competitive_scheduling_options[free-choice-phase]" 
                id="free-choice-phase"
                class="input-numbers"
                min="1"
                max="60"
                value="<?php echo isset( self::$options['free-choice-phase'] ) ? esc_attr( self::$options['free-choice-phase'] ) : ''; ?>"
                >
                <p><?php echo esc_html__( 'Quantity in days before the day of a service that the system allows the free choice of this service to pre-schedule.', 'competitive-scheduling' ); ?></p> 
            <?php
        }

        public function field_residual_phase_callback(){
            ?>
                <input 
                type="number" 
                name="competitive_scheduling_options[residual-phase]" 
                id="residual-phase"
                class="input-numbers"
                min="1"
                max="60"
                value="<?php echo isset( self::$options['residual-phase'] ) ? esc_attr( self::$options['residual-phase'] ) : ''; ?>"
                >
                <p><?php echo esc_html__( 'Quantity in days before the day of a service that the system allows the free choice of this service to schedule residual vacancies.', 'competitive-scheduling' ); ?></p> 
            <?php
        }

        public function field_draw_phase_callback(){
            ?>
                <input 
                type="text" 
                name="competitive_scheduling_options[draw-phase]" 
                id="draw-phase"
                class="input-small-text"
                value="<?php echo isset( self::$options['draw-phase'] ) ? esc_attr( self::$options['draw-phase'] ) : ''; ?>"
                >
                <p><?php echo esc_html__( 'Period in days before the day of a service in which the system blocks the free choice of service to allow time for appointments to be confirmed.', 'competitive-scheduling' ); ?></p> 
            <?php
        }

        public function field_max_companions_callback(){
            ?>
                <input 
                type="number" 
                name="competitive_scheduling_options[max-companions]" 
                id="max-companions"
                class="input-numbers"
                min="1"
                max="10"
                value="<?php echo isset( self::$options['max-companions'] ) ? esc_attr( self::$options['max-companions'] ) : ''; ?>"
                >
                <p><?php echo esc_html__( 'Maximum number of companions in one appointment', 'competitive-scheduling' ); ?></p> 
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

        public function field_coupon_priority_description_callback(){
            // Renders custom TinyMCE editor
            wp_editor(isset( self::$msg_options['coupon-priority-description'] ) ? self::$msg_options['coupon-priority-description'] : '', 'coupon-priority-description', [
                'textarea_name' => 'competitive_scheduling_msg_options[coupon-priority-description]',
                'mode' => 'text/html',
                'theme' => 'monokai',
                'plugins' => ['advlist', 'autolink', 'link', 'media', 'paste', 'table', 'textcolor'],
                'width' => 1250,
                'min_width' => 500,
            ]);

            ?>
                <p><?php echo esc_html__( 'Brief description that will appear on all priority coupons.', 'competitive-scheduling' ); ?></p> 
            <?php
        }
        
        public function field_msg_scheduling_cancelled_callback(){
            // Renders custom TinyMCE editor
            wp_editor(isset( self::$msg_options['msg-scheduling-cancelled'] ) ? self::$msg_options['msg-scheduling-cancelled'] : '', 'msg-scheduling-cancelled', [
                'textarea_name' => 'competitive_scheduling_msg_options[msg-scheduling-cancelled]',
                'mode' => 'text/html',
                'theme' => 'monokai',
                'plugins' => ['advlist', 'autolink', 'link', 'media', 'paste', 'table', 'textcolor'],
                'width' => 1250,
                'min_width' => 500,
            ]);

            ?>
                <p><?php echo esc_html__( 'Alert message shown to users when canceling a schedule has been completed successfully.', 'competitive-scheduling' ); ?></p> 
            <?php
        }
        
        public function field_msg_scheduling_confirmed_callback(){
            // Renders custom TinyMCE editor
            wp_editor(isset( self::$msg_options['msg-scheduling-confirmed'] ) ? self::$msg_options['msg-scheduling-confirmed'] : '', 'msg-scheduling-confirmed', [
                'textarea_name' => 'competitive_scheduling_msg_options[msg-scheduling-confirmed]',
                'mode' => 'text/html',
                'theme' => 'monokai',
                'plugins' => ['advlist', 'autolink', 'link', 'media', 'paste', 'table', 'textcolor'],
                'width' => 1250,
                'min_width' => 500,
            ]);

            ?>
                <p><?php echo esc_html__( 'Alert message shown to users when confirmation of a booking has been completed successfully.', 'competitive-scheduling' ); ?></p> 
            <?php
        }
        
        public function field_msg_scheduling_date_not_allowed_callback(){
            // Renders custom TinyMCE editor
            wp_editor(isset( self::$msg_options['msg-scheduling-date-not-allowed'] ) ? self::$msg_options['msg-scheduling-date-not-allowed'] : '', 'msg-scheduling-date-not-allowed', [
                'textarea_name' => 'competitive_scheduling_msg_options[msg-scheduling-date-not-allowed]',
                'mode' => 'text/html',
                'theme' => 'monokai',
                'plugins' => ['advlist', 'autolink', 'link', 'media', 'paste', 'table', 'textcolor'],
                'width' => 1250,
                'min_width' => 500,
            ]);

            ?>
                <p><?php echo esc_html__( 'Alert message shown to users when a date is not available.', 'competitive-scheduling' ); ?></p> 
            <?php
        }
        
        public function field_msg_schedule_expired_callback(){
            // Renders custom TinyMCE editor
            wp_editor(isset( self::$msg_options['msg-schedule-expired'] ) ? self::$msg_options['msg-schedule-expired'] : '', 'msg-schedule-expired', [
                'textarea_name' => 'competitive_scheduling_msg_options[msg-schedule-expired]',
                'mode' => 'text/html',
                'theme' => 'monokai',
                'plugins' => ['advlist', 'autolink', 'link', 'media', 'paste', 'table', 'textcolor'],
                'width' => 1250,
                'min_width' => 500,
            ]);

            ?>
                <p><?php echo esc_html__( 'Alert message shown to users when confirmation of a schedule was not possible because the confirmation period has passed.', 'competitive-scheduling' ); ?></p> 
            <?php
        }
        
        public function field_msg_scheduling_already_confirmed_callback(){
            // Renders custom TinyMCE editor
            wp_editor(isset( self::$msg_options['msg-scheduling-already-confirmed'] ) ? self::$msg_options['msg-scheduling-already-confirmed'] : '', 'msg-scheduling-already-confirmed', [
                'textarea_name' => 'competitive_scheduling_msg_options[msg-scheduling-already-confirmed]',
                'mode' => 'text/html',
                'theme' => 'monokai',
                'plugins' => ['advlist', 'autolink', 'link', 'media', 'paste', 'table', 'textcolor'],
                'width' => 1250,
                'min_width' => 500,
            ]);

            ?>
                <p><?php echo esc_html__( 'Message that will be shown to the user when trying to confirm an already confirmed appointment again.', 'competitive-scheduling' ); ?></p> 
            <?php
        }
        
        public function field_msg_scheduling_already_exists_callback(){
            // Renders custom TinyMCE editor
            wp_editor(isset( self::$msg_options['msg-scheduling-already-exists'] ) ? self::$msg_options['msg-scheduling-already-exists'] : '', 'msg-scheduling-already-exists', [
                'textarea_name' => 'competitive_scheduling_msg_options[msg-scheduling-already-exists]',
                'mode' => 'text/html',
                'theme' => 'monokai',
                'plugins' => ['advlist', 'autolink', 'link', 'media', 'paste', 'table', 'textcolor'],
                'width' => 1250,
                'min_width' => 500,
            ]);

            ?>
                <p><?php echo esc_html__( 'Alert message shown to users when they try to schedule twice on the same date.', 'competitive-scheduling' ); ?></p> 
            <?php
        }
        
        public function field_msg_scheduling_not_found_callback(){
            // Renders custom TinyMCE editor
            wp_editor(isset( self::$msg_options['msg-scheduling-not-found'] ) ? self::$msg_options['msg-scheduling-not-found'] : '', 'msg-scheduling-not-found', [
                'textarea_name' => 'competitive_scheduling_msg_options[msg-scheduling-not-found]',
                'mode' => 'text/html',
                'theme' => 'monokai',
                'plugins' => ['advlist', 'autolink', 'link', 'media', 'paste', 'table', 'textcolor'],
                'width' => 1250,
                'min_width' => 500,
            ]);

            ?>
                <p><?php echo esc_html__( 'Alert message shown to users when a schedule was not found.', 'competitive-scheduling' ); ?></p> 
            <?php
        }
        
        public function field_msg_scheduling_without_vacancies_callback(){
            // Renders custom TinyMCE editor
            wp_editor(isset( self::$msg_options['msg-scheduling-without-vacancies'] ) ? self::$msg_options['msg-scheduling-without-vacancies'] : '', 'msg-scheduling-without-vacancies', [
                'textarea_name' => 'competitive_scheduling_msg_options[msg-scheduling-without-vacancies]',
                'mode' => 'text/html',
                'theme' => 'monokai',
                'plugins' => ['advlist', 'autolink', 'link', 'media', 'paste', 'table', 'textcolor'],
                'width' => 1250,
                'min_width' => 500,
            ]);

            ?>
                <p><?php echo esc_html__( 'Alert message shown to users when there are no spaces for an appointment.', 'competitive-scheduling' ); ?></p> 
            <?php
        }
        
        public function field_msg_scheduling_suspended_callback(){
            // Renders custom TinyMCE editor
            wp_editor(isset( self::$msg_options['msg-scheduling-suspended'] ) ? self::$msg_options['msg-scheduling-suspended'] : '', 'msg-scheduling-suspended', [
                'textarea_name' => 'competitive_scheduling_msg_options[msg-scheduling-suspended]',
                'mode' => 'text/html',
                'theme' => 'monokai',
                'plugins' => ['advlist', 'autolink', 'link', 'media', 'paste', 'table', 'textcolor'],
                'width' => 1250,
                'min_width' => 500,
            ]);

            ?>
                <p><?php echo esc_html__( 'Message shown to users when the system is suspended.', 'competitive-scheduling' ); ?></p> 
            <?php
        }
        
        public function field_msg_conclusion_scheduling_callback(){
            // Renders custom TinyMCE editor
            wp_editor(isset( self::$msg_options['msg-conclusion-scheduling'] ) ? self::$msg_options['msg-conclusion-scheduling'] : '', 'msg-conclusion-scheduling', [
                'textarea_name' => 'competitive_scheduling_msg_options[msg-conclusion-scheduling]',
                'mode' => 'text/html',
                'theme' => 'monokai',
                'plugins' => ['advlist', 'autolink', 'link', 'media', 'paste', 'table', 'textcolor'],
                'width' => 1250,
                'min_width' => 500,
            ]);

            ?>
                <p><?php echo esc_html__( 'Alert message shown to users when completing a schedule.', 'competitive-scheduling' ); ?></p> 
            <?php
        }
        
        public function field_msg_conclusion_pre_scheduling_callback(){
            // Renders custom TinyMCE editor
            wp_editor(isset( self::$msg_options['msg-conclusion-pre-scheduling'] ) ? self::$msg_options['msg-conclusion-pre-scheduling'] : '', 'msg-conclusion-pre-scheduling', [
                'textarea_name' => 'competitive_scheduling_msg_options[msg-conclusion-pre-scheduling]',
                'mode' => 'text/html',
                'theme' => 'monokai',
                'plugins' => ['advlist', 'autolink', 'link', 'media', 'paste', 'table', 'textcolor'],
                'width' => 1250,
                'min_width' => 500,
            ]);

            ?>
                <p><?php echo esc_html__( 'Alert message shown to users when completing a pre-booking.', 'competitive-scheduling' ); ?></p> 
            <?php
        }
        
        public function field_msg_confirmation_status_not_permitted_callback(){
            // Renders custom TinyMCE editor
            wp_editor(isset( self::$msg_options['msg-confirmation-status-not-permitted'] ) ? self::$msg_options['msg-confirmation-status-not-permitted'] : '', 'msg-confirmation-status-not-permitted', [
                'textarea_name' => 'competitive_scheduling_msg_options[msg-confirmation-status-not-permitted]',
                'mode' => 'text/html',
                'theme' => 'monokai',
                'plugins' => ['advlist', 'autolink', 'link', 'media', 'paste', 'table', 'textcolor'],
                'width' => 1250,
                'min_width' => 500,
            ]);

            ?>
                <p><?php echo esc_html__( 'Message that will be shown to the user when trying to confirm an appointment with a not allowed status.', 'competitive-scheduling' ); ?></p> 
            <?php
        }
        
        public function field_msg_coupon_priority_inactive_callback(){
            // Renders custom TinyMCE editor
            wp_editor(isset( self::$msg_options['msg-coupon-priority-inactive'] ) ? self::$msg_options['msg-coupon-priority-inactive'] : '', 'msg-coupon-priority-inactive', [
                'textarea_name' => 'competitive_scheduling_msg_options[msg-coupon-priority-inactive]',
                'mode' => 'text/html',
                'theme' => 'monokai',
                'plugins' => ['advlist', 'autolink', 'link', 'media', 'paste', 'table', 'textcolor'],
                'width' => 1250,
                'min_width' => 500,
            ]);

            ?>
                <p><?php echo esc_html__( 'Priority Coupon Inactivity Alert Message.', 'competitive-scheduling' ); ?></p> 
            <?php
        }
        
        public function field_msg_priority_coupon_already_used_callback(){
            // Renders custom TinyMCE editor
            wp_editor(isset( self::$msg_options['msg-priority-coupon-already-used'] ) ? self::$msg_options['msg-priority-coupon-already-used'] : '', 'msg-priority-coupon-already-used', [
                'textarea_name' => 'competitive_scheduling_msg_options[msg-priority-coupon-already-used]',
                'mode' => 'text/html',
                'theme' => 'monokai',
                'plugins' => ['advlist', 'autolink', 'link', 'media', 'paste', 'table', 'textcolor'],
                'width' => 1250,
                'min_width' => 500,
            ]);

            ?>
                <p><?php echo esc_html__( 'Priority coupon alert message already used.', 'competitive-scheduling' ); ?></p> 
            <?php
        }
        
        public function field_msg_coupon_priority_not_found_callback(){
            // Renders custom TinyMCE editor
            wp_editor(isset( self::$msg_options['msg-coupon-priority-not-found'] ) ? self::$msg_options['msg-coupon-priority-not-found'] : '', 'msg-coupon-priority-not-found', [
                'textarea_name' => 'competitive_scheduling_msg_options[msg-coupon-priority-not-found]',
                'mode' => 'text/html',
                'theme' => 'monokai',
                'plugins' => ['advlist', 'autolink', 'link', 'media', 'paste', 'table', 'textcolor'],
                'width' => 1250,
                'min_width' => 500,
            ]);

            ?>
                <p><?php echo esc_html__( 'Priority Coupon Not Found Alert Message.', 'competitive-scheduling' ); ?></p> 
            <?php
        }
        
        public function field_msg_expired_priority_coupon_callback(){
            // Renders custom TinyMCE editor
            wp_editor(isset( self::$msg_options['msg-expired-priority-coupon'] ) ? self::$msg_options['msg-expired-priority-coupon'] : '', 'msg-expired-priority-coupon', [
                'textarea_name' => 'competitive_scheduling_msg_options[msg-expired-priority-coupon]',
                'mode' => 'text/html',
                'theme' => 'monokai',
                'plugins' => ['advlist', 'autolink', 'link', 'media', 'paste', 'table', 'textcolor'],
                'width' => 1250,
                'min_width' => 500,
            ]);

            ?>
                <p><?php echo esc_html__( 'Expired priority coupon alert message.', 'competitive-scheduling' ); ?></p> 
            <?php
        }
        
        public function field_msg_residual_vacancies_unavailable_callback(){
            // Renders custom TinyMCE editor
            wp_editor(isset( self::$msg_options['msg-residual-vacancies-unavailable'] ) ? self::$msg_options['msg-residual-vacancies-unavailable'] : '', 'msg-residual-vacancies-unavailable', [
                'textarea_name' => 'competitive_scheduling_msg_options[msg-residual-vacancies-unavailable]',
                'mode' => 'text/html',
                'theme' => 'monokai',
                'plugins' => ['advlist', 'autolink', 'link', 'media', 'paste', 'table', 'textcolor'],
                'width' => 1250,
                'min_width' => 500,
            ]);

            ?>
                <p><?php echo esc_html__( 'When it is not possible to confirm residual vacancies due to the deadline or because there are no vacancies.', 'competitive-scheduling' ); ?></p> 
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