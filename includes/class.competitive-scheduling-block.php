<?php

if ( ! class_exists( 'Competitive_Scheduling_Block' ) ) {
    class Competitive_Scheduling_Block {

        public function __construct() {
            // Register REST API Routes
            add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

            // Register the Gutenberg block
            add_action( 'init', array( $this, 'register_block' ) );
        }

        public function register_rest_routes() {
            
        }

        public function register_block() {
            
        }

        private function schedule( $params = false ){
            if( $params ) foreach( $params as $var => $val ) $$var = $val;
            
            // Get user ID
            $user_id = get_current_user_id();

            // Check if the mandatory fields were sent: user_id and scheduleDate.
            if( isset( $user_id ) && isset( $scheduleDate ) ){
                // Get the configuration data.
                $options = get_option( 'competitive_scheduling_options' );
                $msg_options = get_option( 'competitive_scheduling_msg_options' );
                
                $activation = (isset( $options['activation'] ) ? true : false);
                $msgScheduleSuspended = (isset( $msg_options['msg-scheduling-suspended'] ) ? $msg_options['msg-scheduling-suspended'] : '');
                
                // If the schedule is inactive, return an inactivity message.
                if( !$activation ){
                    return Array(
                        'status' => 'INACTIVE_SCHEDULING',
                        'error-msg' => $msgScheduleSuspended,
                    );
                }
                
                // Process the data sent.
                $companions = (int)$companions;
                
                for( $i=0;$i<(int)$companions;$i++){
                    $companionsNames[$i] = trim(ucwords(strtolower( $companionsNames[$i] )));
                }
                
                // Check if the date sent is allowed. Otherwise, an error message will be returned.
                if( ! $this->allowed_date( $scheduleDate)){
                    $msgSchedulingDateNotAllowed = ( ! empty( $msg_options['msg-scheduling-date-not-allowed'] ) ? $msg_options['msg-scheduling-date-not-allowed'] : '');
                    
                    return Array(
                        'status' => 'SCHEDULE_DATE_NOT_ALLOWED',
                        'error-msg' => $msgSchedulingDateNotAllowed,
                    );
                }
                
                // Create date in scheduling_dates if it does not exist.
                global $wpdb;
                $query = $wpdb->prepare(
                    "SELECT id_schedules_dates 
                    FROM {$wpdb->prefix}schedules_dates 
                    WHERE date = '%s'",
                    $scheduleDate
                );
                $schedules_dates = $wpdb->get_results( $query );

                if( ! $schedules_dates ){
                    $wpdb->insert( $wpdb->prefix.'schedules_dates', array(
                        'date' => $scheduleDate,
                        'total' => 0,
                        'status' => 'new',
                    ) );
                }
                
                // Generate the validation token.
                require_once( CS_PATH . 'includes/class.authentication.php' );
            
                $auth = Authentication::generate_token_validation();

                $token = $auth['token'];
                $pubID = $auth['pubID'];
                
                // Check the user's schedule for the sent date.
                $query = $wpdb->prepare(
                    "SELECT id_schedules,status 
                    FROM {$wpdb->prefix}schedules 
                    WHERE date = '%s' AND user_id = '%s'",
                    array( $scheduleDate, $user_id )
                );
                $schedules = $wpdb->get_results( $query );
                if( $schedules ) $schedules = $schedules[0];
                
                // Force date to today for debuging or set today's date
                if( CS_FORCE_DATE_TODAY ){ $today = CS_DATE_TODAY_FORCED_VALUE; } else { $today = date('Y-m-d'); }

                // Require templates class to manipulate data.
                require_once( CS_PATH . 'includes/class.templates.php' );

                // Require formats class to manipulate data.
                require_once( CS_PATH . 'includes/class.formats.php' );
                
                // Check priority coupon.
                if( isset( $coupon ) )
                if( ! empty( $coupon ) ){
                    $query = $wpdb->prepare(
                        "SELECT id_schedules_coupons_priority,post_id,id_schedules 
                        FROM {$wpdb->prefix}schedules_coupons_priority 
                        WHERE coupon = '%s'",
                        $coupon
                    );
                    $coupons_priority = $wpdb->get_results( $query );
                    if( $coupons_priority ) $coupons_priority = $coupons_priority[0];
                    
                    // Check if the coupon was found. Otherwise return a not found error.
                    if( $coupons_priority ){
                        $post_id = $coupons_priority->post_id;
                        $id_schedules_coupons_priority = $coupons_priority->id_schedules_coupons_priority;
                        $id_schedules_coupon_used = $coupons_priority->id_schedules;
                        
                        $post = get_post($post_id);
                        
                        if( $post ){
                            // Check if the coupon is active. Otherwise, an inactive coupon error will be returned.
                            if( $post->post_status != 'publish'){
                                $msgCouponPriorityInactive = ( ! empty( $msg_options['msg-coupon-priority-inactive'] ) ? $msg_options['msg-coupon-priority-inactive'] : '' );
                                
                                $msgCouponPriorityInactive = Templates::change_variable( $msgCouponPriorityInactive, '#coupon#', $coupon );

                                return Array(
                                    'status' => 'COUPON_PRIORITY_INACTIVE',
                                    'error-msg' => $msgCouponPriorityInactive,
                                );
                            }
                            
                            // Check if the coupon is within its expiration date. Otherwise return expiration error.
                            $cs_valid_from = get_post_meta( $post_id, 'cs_valid_from', true );
                            $cs_valid_until = get_post_meta( $post_id, 'cs_valid_until', true );

                            $valid_from = Formats::data_format_to( 'text-to-date', $cs_valid_from );
                            $valid_until = Formats::data_format_to( 'text-to-date', $cs_valid_until );

                            if( 
                                strtotime( $valid_from ) <= strtotime( $today) && 
                                strtotime( $valid_until ) >= strtotime( $today)
                            ){
                                
                            } else {
                                $msgExpiredPriorityCoupon = ( ! empty( $msg_options['msg-expired-priority-coupon'] ) ? $msg_options['msg-expired-priority-coupon'] : '' );
                                
                                $msgExpiredPriorityCoupon = Templates::change_variable( $msgExpiredPriorityCoupon, '#coupon#', $coupon );
                                $msgExpiredPriorityCoupon = Templates::change_variable( $msgExpiredPriorityCoupon, '#valid_from#', $cs_valid_from );
                                $msgExpiredPriorityCoupon = Templates::change_variable( $msgExpiredPriorityCoupon, '#valid_until#', $cs_valid_until );

                                return Array(
                                    'status' => 'COUPON_PRIORITY_EXPIRED',
                                    'error-msg' => $msgExpiredPriorityCoupon,
                                );
                            }
                            
                            // Check if the coupon has already been used on another schedule. If so, return coupon error already used.
                            if( ! empty( $id_schedules_coupon_used ) ){
                                $msgPriorityCouponAlreadyUsed = ( ! empty( $msg_options['msg-priority-coupon-already-used'] ) ? $msg_options['msg-priority-coupon-already-used'] : '' );
                                
                                $msgPriorityCouponAlreadyUsed = Templates::change_variable( $msgPriorityCouponAlreadyUsed, '#coupon#', $coupon );
                                
                                return Array(
                                    'status' => 'COUPON_PRIORITY_ALREADY_USED',
                                    'error-msg' => $msgPriorityCouponAlreadyUsed,
                                );
                            }
                            
                            // Valid coupon, select to include the coupon.
                            $couponValid = $id_schedules_coupons_priority;
                            $scheduleConfirm = true;
                        } else {
                            $couponNotFound = true;
                        }
                    } else {
                        $couponNotFound = true;
                    }
                }
                
                if( isset( $couponNotFound ) ){
                    $msgCouponPriorityNotFound = ( ! empty( $msg_options['msg-coupon-priority-not-found'] ) ? $msg_options['msg-coupon-priority-not-found'] : '' );
                    
                    $msgCouponPriorityNotFound = Templates::change_variable( $msgCouponPriorityNotFound, '#coupon#', $coupon );
                    
                    return Array(
                        'status' => 'COUPON_PRIORITY_NOT_FOUND',
                        'error-msg' => $msgCouponPriorityNotFound,
                    );
                }
                
                // Check if it is in the residual or pre-scheduling phase (draw phase is handled in the previous function 'permitted_date'). Treat each case differently.
                $residualPhase = ( ! empty( $options['residual-phase'] ) ? (int)$options['residual-phase'] : 5 );
                
                if( strtotime( $scheduleDate ) <= strtotime( $today.' + '.$residualPhase.' day' ) ){
                    $scheduleConfirm = true;
                }

                // Generate schedule password.
                $password = Formats::format_put_char_half_number( Formats::format_zero_to_the_left( rand( 1, 99999 ), 6 ) );

                // Confirm schedule or create pre-schedule.
                if( isset( $scheduleConfirm ) ){
                    
                    // Check if you already have a confirmed schedule for this date. If so, return error and permission message for only one schedule per date.
                    if( $schedules ){
                        if( $schedules->status == 'confirmed' ){
                            $msgSchedulingAlreadyExists = ( ! empty( $msg_options['msg-scheduling-already-exists'] ) ? $msg_options['msg-scheduling-already-exists'] : '' );

                            $msgSchedulingAlreadyExists = Templates::change_variable( $msgSchedulingAlreadyExists, '#url-schedules-previous#', '.?window=previous-schedules' );
                            
                            return Array(
                                'status' => 'MULTIPLE_SCHEDULING_NOT_ALLOWED',
                                'error-msg' => $msgSchedulingAlreadyExists,
                            );
                        } else {
                            $updateSchedule = true;
                        }
                    }
                    
                    // Take the maximum number of places.
                    $days_week = ( ! empty( $options['days-week'] ) ? explode( ',', $options['days-week'] ) : Array() );
                    $daysWeekMaximumVacanciesArr = ( ! empty( $options['days-week-maximum-vacancies'] ) ? explode( ',', $options['days-week-maximum-vacancies'] ) : Array() );
                    
                    $count_days = 0;
                    if( $days_week )
                    foreach( $days_week as $day_week ){
                        if( $day_week == strtolower( date( 'D', strtotime( $scheduleDate ) ) ) ){
                            break;
                        }
                        $count_days++;
                    }
                    
                    if( count( $daysWeekMaximumVacanciesArr ) > 1 ){
                        $maximum_number_days_week = $daysWeekMaximumVacanciesArr[$count_days];
                    } else {
                        $maximum_number_days_week = $daysWeekMaximumVacanciesArr[0];
                    }
                    
                    // Check if there are enough vacancies for the required date. If not, return an error message.
                    global $wpdb;
                    $query = $wpdb->prepare(
                        "SELECT id_schedules_dates,total 
                        FROM {$wpdb->prefix}schedules_dates 
                        WHERE date = '%s' AND total + %d <= %d",
                        array( $scheduleDate, ( $companions + 1 ), $maximum_number_days_week )
                    );
                    $schedules_dates = $wpdb->get_results( $query );
                    if( $schedules_dates ) $schedules_dates = $schedules_dates[0];

                    if( ! $schedules_dates ){
                        // Available vacancies.
                        $query = $wpdb->prepare(
                            "SELECT total 
                            FROM {$wpdb->prefix}schedules_dates 
                            WHERE date = '%s'",
                            $scheduleDate
                        );
                        $schedules_dates = $wpdb->get_results( $query );
                        if( $schedules_dates ) $schedules_dates = $schedules_dates[0];

                        $vacancies = (int)$maximum_number_days_week - (int)$schedules_dates->total;
                        if( $vacancies < 0 ) $vacancies = 0;
                        
                        // Alert.
                        $msgSchedulingWithoutVacancies = ( ! empty(  $msg_options['msg-scheduling-without-vacancies'] ) ? $msg_options['msg-scheduling-without-vacancies'] : '' );

                        $msgSchedulingWithoutVacancies = Templates::change_variable( $msgSchedulingWithoutVacancies, '#date#', Formats::data_format_to( 'date-to-text', $scheduleDate ) );
                        $msgSchedulingWithoutVacancies = Templates::change_variable( $msgSchedulingWithoutVacancies, '#vacancies#', $vacancies );
                        
                        return Array(
                            'status' => 'SCHEDULE_WITHOUT_VACANCIES',
                            'error-msg' => $msgSchedulingWithoutVacancies,
                        );
                    }
                    
                    // Update the total number of spaces used in schedules for the date in question.
                    
                    global $wpdb;
                    $sql = $wpdb->prepare(
                        "UPDATE {$wpdb->prefix}schedules_dates      
                        SET total = total + %d 
                        WHERE id_schedules_dates = '%s'",
                        array( ( $companions + 1 ), $schedules_dates->id_schedules_dates )
                    );
                    $wpdb->query($sql);

                    // Generate a schedule or update an existing one.
                    if( isset( $updateSchedule ) ){
                        $id_schedules = $schedules->id_schedules;
                        
                        // Replace companions.
                        global $wpdb;
                        $wpdb->delete( $wpdb->prefix.'schedules_companions', ['id_schedules' => $id_schedules] );

                        for( $i=0;$i<(int)$companions;$i++){
                            $wpdb->insert( $wpdb->prefix.'schedules_companions', array(
                                'id_schedules' => $id_schedules,
                                'user_id' => $user_id,
                                'name' => $companionsNames[$i],
                            ) );
                        }
                        
                        // Update schedule.
                        $sql = $wpdb->prepare(
                            "UPDATE {$wpdb->prefix}schedules      
                            SET 
                                companions = '%s',
                                password = '%s',
                                status = 'confirmed',
                                pubID = '%s',
                                version = version + 1,
                                modification_date = '%s' 
                            WHERE 
                                id_schedules = '%s' AND 
                                user_id = '%s'",
                            array( $companions, $password, $pubID, current_time('mysql', false), $id_schedules, $user_id )
                        );
                        $wpdb->query($sql);
                    } else {
                        // Create new schedule.
                        global $wpdb;
                        $wpdb->insert( $wpdb->prefix.'schedules', array(
                            'user_id' => $user_id,
                            'date' => $scheduleDate,
                            'companions' => $companions,
                            'password' => $password,
                            'status' => 'confirmed',
                            'pubID' => $pubID,
                            'version' => '1',
                            'date_creation' => current_time('mysql', false),
                            'modification_date' => current_time('mysql', false),
                        ) );

                        $id_schedules = $wpdb->insert_id;

                        // Create schedule companions if applicable.
                        if( (int)$companions > 0 ){
                            for( $i=0; $i<(int)$companions; $i++ ){
                                $wpdb->insert( $wpdb->prefix.'schedules_companions', array(
                                    'id_schedules' => $id_schedules,
                                    'user_id' => $user_id,
                                    'name' => $companionsNames[$i],
                                ) );
                            }
                        }
                    }
                    
                    // Check whether a coupon has been used. If yes, mark the coupon with the schedule identifier.
                    if( isset( $couponValid ) ){
                        $id_schedules_coupons_priority = $couponValid;

                        $sql = $wpdb->prepare(
                            "UPDATE {$wpdb->prefix}schedules_coupons_priority      
                            SET id_schedules = '%s' 
                            WHERE id_schedules_coupons_priority = '%s'",
                            array( $id_schedules, $id_schedules_coupons_priority )
                        );
                        $wpdb->query($sql);
                    }
                    
                    // Get the html data.
                    $html_options = get_option( 'competitive_scheduling_html_options' );
                    
                    // Format email data.
                    $scheduleSubject = ( ! empty( $html_options['schedule-subject'] ) ? $html_options['schedule-subject'] : '' );
                    $scheduleMessage = ( ! empty( $html_options['schedule-message'] ) ? $html_options['schedule-message'] : '' );
                    $msgConclusionScheduling = ( ! empty( $msg_options['msg-conclusion-scheduling'] ) ? $msg_options['msg-conclusion-scheduling'] : '' );
                    
                    $titleEstablishment = ( ! empty( $options['title-establishment'] ) ? $options['title-establishment'] : '' );
                    
                    $code = date('dmY').Formats::format_zero_to_the_left( $id_schedules, 6 );

                    // Get the ID of the public schedule page.
                    $pages_options = get_option('competitive_scheduling_pages_options');

                    if( ! empty( $pages_options['schedule-public-page-id'] ) ){
                        $page_id = $pages_options['schedule-public-page-id'];
                    } else {
                        $page_id = 0;
                    }

                    // Format URL to public schedule page or to home page if the page does not exist.
                    if( $page_id != 0 && $page_id != '0' ) { 
                        $page_url = get_permalink( $page_id );
                    } else {
                        $page_url = home_url();
                    }
                    
                    // Generate the url to be able to cancel
                    $urlCancellation = esc_url( add_query_arg(
                        array(
                            'action' => 'schedule_cancellation',
                            'pubID' => $pubID,
                            'token' => $token,
                        ),
                        $page_url
                    ) );

                    // Require user class to get user's data.
                    require_once( CS_PATH . 'includes/class.user.php' );

                    // Get the user's name and email
                    $name = User::get_name();
                    $email = User::get_email();
                    
                    // Format email message.
                    $scheduleSubject = Templates::change_variable( $scheduleSubject, '#code#', $code );

                    $scheduleMessage = Templates::change_variable( $scheduleMessage, '#code#', $code );
                    $scheduleMessage = Templates::change_variable( $scheduleMessage, '#title#', $titleEstablishment );
                    $scheduleMessage = Templates::change_variable( $scheduleMessage, '#date#', Formats::data_format_to( 'date-to-text', $scheduleDate ) );
                    $scheduleMessage = Templates::change_variable( $scheduleMessage, '#password#', $password );
                    $scheduleMessage = Templates::change_variable( $scheduleMessage, '#url-cancellation#', '<a target="schedule" href="'.$urlCancellation.'" style="overflow-wrap: break-word;">'.$urlCancellation.'</a>' );
                    
                    $cell_name = 'cell'; $cell[$cell_name] = Templates::tag_value( $scheduleMessage, '<!-- '.$cell_name.' < -->','<!-- '.$cell_name.' > -->' ); $scheduleMessage = Templates::tag_in( $scheduleMessage,'<!-- '.$cell_name.' < -->', '<!-- '.$cell_name.' > -->', '<!-- '.$cell_name.' -->' );
                    
                    $scheduleMessage = Templates::change_variable( $scheduleMessage, '#your-name#', $name );
                    
                    for( $i=0; $i<(int)$companions; $i++ ){
                        $cell_aux = $cell[$cell_name];
                        
                        $cell_aux = Templates::change_variable( $cell_aux, '#num#', ( $i+1 ) );
                        $cell_aux = Templates::change_variable( $cell_aux, '#companion#', $companionsNames[$i] );
                        
                        $scheduleMessage = Templates::variable_in( $scheduleMessage, '<!-- '.$cell_name.' -->', $cell_aux );
                    }
                    $scheduleMessage = Templates::change_variable( $scheduleMessage, '<!-- '.$cell_name.' -->', '' );

                    // Format alert message.
                    $msgConclusionScheduling = Templates::change_variable( $msgConclusionScheduling, '#date#', Formats::data_format_to( 'date-to-text', $scheduleDate ) );
                    $msgConclusionScheduling = Templates::change_variable( $msgConclusionScheduling, '#password#', $password );
                    
                    $cell_name = 'cell'; $cell[$cell_name] = Templates::tag_value( $msgConclusionScheduling, '<!-- '.$cell_name.' < -->','<!-- '.$cell_name.' > -->' ); $msgConclusionScheduling = Templates::tag_in( $msgConclusionScheduling,'<!-- '.$cell_name.' < -->', '<!-- '.$cell_name.' > -->', '<!-- '.$cell_name.' -->' );
                    
                    $msgConclusionScheduling = Templates::change_variable( $msgConclusionScheduling, '#your-name#', $name );
                    
                    for( $i=0; $i<(int)$companions; $i++ ){
                        $cell_aux = $cell[$cell_name];
                        
                        $cell_aux = Templates::change_variable( $cell_aux, '#num#', ( $i+1 ) );
                        $cell_aux = Templates::change_variable( $cell_aux, '#companion#', $companionsNames[$i] );
                        
                        $msgConclusionScheduling = Templates::variable_in( $msgConclusionScheduling, '<!-- '.$cell_name.' -->', $cell_aux );
                    }
                    $msgConclusionScheduling = Templates::change_variable( $msgConclusionScheduling, '<!-- '.$cell_name.' -->', '' );

                    $msgAlert = $msgConclusionScheduling;
                    
                    // Prepare email fields.
                    $to = $name . ' <'.$email.'>';
                    $subject = $scheduleSubject;
                    $body = $scheduleMessage;
            
                    // Require custom-mailer class to send emails.
                    require_once( CS_PATH . 'includes/class.custom-mailer.php' );

                    // Send email with scheduling information.
                    $custom_mailer = new Custom_Mailer();
                    $custom_mailer->send($to, $subject, $body);
                } else {
                    // Check if you already have an schedule for this date. If so, return error and permission message for only one schedule per date.
                    if( $schedules ){
                        if( $schedules->status != 'finished' && $schedules->status != 'canceled' ){
                            $msgSchedulingAlreadyExists = ( ! empty( $msg_options['msg-scheduling-already-exists'] ) ? $msg_options['msg-scheduling-already-exists'] : '' );

                            $msgSchedulingAlreadyExists = Templates::change_variable( $msgSchedulingAlreadyExists, '#url-schedules-previous#', '.?window=previous-schedules' );
                            
                            return Array(
                                'status' => 'MULTIPLE_SCHEDULING_NOT_ALLOWED',
                                'error-msg' => $msgSchedulingAlreadyExists,
                            );
                        } else {
                            $updateSchedule = true;
                        }
                    }
                    
                    // Generate a schedule or update an existing one.
                    if( isset( $updateSchedule ) ){
                        $id_schedules = $schedules->id_schedules;
                        
                        // Replace companions.
                        global $wpdb;
                        $wpdb->delete( $wpdb->prefix.'schedules_companions', ['id_schedules' => $id_schedules] );

                        for( $i=0;$i<(int)$companions;$i++){
                            $wpdb->insert( $wpdb->prefix.'schedules_companions', array(
                                'id_schedules' => $id_schedules,
                                'user_id' => $user_id,
                                'name' => $companionsNames[$i],
                            ) );
                        }
                        
                        // Update schedule.
                        $sql = $wpdb->prepare(
                            "UPDATE {$wpdb->prefix}schedules      
                            SET 
                                companions = '%s',
                                password = '%s',
                                status = 'new',
                                pubID = '%s',
                                version = version + 1,
                                modification_date = '%s' 
                            WHERE 
                                id_schedules = '%s' AND 
                                user_id = '%s'",
                            array( $companions, $password, $pubID, current_time('mysql', false), $id_schedules, $user_id )
                        );
                        $wpdb->query($sql);
                    } else {
                        // Create new schedule.
                        global $wpdb;
                        $wpdb->insert( $wpdb->prefix.'schedules', array(
                            'user_id' => $user_id,
                            'date' => $scheduleDate,
                            'companions' => $companions,
                            'password' => $password,
                            'status' => 'new',
                            'pubID' => $pubID,
                            'version' => '1',
                            'date_creation' => current_time('mysql', false),
                            'modification_date' => current_time('mysql', false),
                        ) );
                        
                        $id_schedules = $wpdb->insert_id;

                        // Create schedule companions if applicable.
                        if( (int)$companions > 0 ){
                            for( $i=0; $i<(int)$companions; $i++ ){
                                $wpdb->insert( $wpdb->prefix.'schedules_companions', array(
                                    'id_schedules' => $id_schedules,
                                    'user_id' => $user_id,
                                    'name' => $companionsNames[$i],
                                ) );
                            }
                        }
                    }
                    
                    // Format dates.
                    $free_choice_phase = ( ! empty( $options['free-choice-phase'] ) ? (int)$options['free-choice-phase'] : 7 );
                    $draw_phase = ( ! empty( $options['draw-phase'] ) ? explode( ',', $options['draw-phase'] ) : Array(7,5) );
                    
                    $draw_date = Formats::data_format_to( 'date-to-text', date( 'Y-m-d', strtotime( $scheduleDate.' - '.$free_choice_phase.' day' ) ) );
                    $date_confirmation_1 = Formats::data_format_to( 'date-to-text', date( 'Y-m-d', strtotime( $scheduleDate.' - '.$draw_phase[0].' day' ) ) );
                    $date_confirmation_2 = Formats::data_format_to( 'date-to-text', date( 'Y-m-d', strtotime( $scheduleDate.' - '.$draw_phase[1].' day' ) ) );
                    
                    // Get the html data.
                    $html_options = get_option( 'competitive_scheduling_html_options' );

                    // Format email data.
                    $preSchedulingSubject = ( ! empty( $html_options['pre-scheduling-subject'] ) ? $html_options['pre-scheduling-subject'] : '');
                    $preSchedulingMessage = ( ! empty( $html_options['pre-scheduling-message'] ) ? $html_options['pre-scheduling-message'] : '');
                    $msgConclusionPreScheduling = ( ! empty( $msg_options['msg-conclusion-pre-scheduling'] ) ? $msg_options['msg-conclusion-pre-scheduling'] : '');
                    
                    $titleEstablishment = ( ! empty( $options['title-establishment'] ) ? $options['title-establishment'] : '');
                    
                    $code = date( 'dmY' ).Formats::format_zero_to_the_left( $id_schedules, 6 );

                    // Get the ID of the public schedule page.
                    $pages_options = get_option('competitive_scheduling_pages_options');

                    if( ! empty( $pages_options['schedule-public-page-id'] ) ){
                        $page_id = $pages_options['schedule-public-page-id'];
                    } else {
                        $page_id = 0;
                    }

                    // Format URL to public schedule page or to home page if the page does not exist.
                    if( $page_id != 0 && $page_id != '0' ) { 
                        $page_url = get_permalink( $page_id );
                    } else {
                        $page_url = home_url();
                    }
                    
                    // Generate the url to be able to cancel
                    $urlCancellation = esc_url( add_query_arg(
                        array(
                            'action' => 'schedule_cancellation',
                            'pubID' => $pubID,
                            'token' => $token,
                        ),
                        $page_url
                    ) );

                    // Format email message.
                    $preSchedulingSubject = Templates::change_variable( $preSchedulingSubject, '#code#', $code );
                    
                    $preSchedulingMessage = Templates::change_variable( $preSchedulingMessage, '#code#', $code );
                    $preSchedulingMessage = Templates::change_variable( $preSchedulingMessage, '#title#', $titleEstablishment );
                    $preSchedulingMessage = Templates::change_variable( $preSchedulingMessage, '#date#', Formats::data_format_to( 'date-to-text', $scheduleDate ) );
                    $preSchedulingMessage = Templates::change_variable_all( $preSchedulingMessage, '#draw_date#', $draw_date );
                    $preSchedulingMessage = Templates::change_variable( $preSchedulingMessage, '#date_confirmation_1#', $date_confirmation_1 );
                    $preSchedulingMessage = Templates::change_variable_all( $preSchedulingMessage, '#date_confirmation_2#', $date_confirmation_2 );
                    $preSchedulingMessage = Templates::change_variable( $preSchedulingMessage, '#url-cancellation#', '<a target="schedule" href="'.$urlCancellation.'" style="overflow-wrap: break-word;">'.$urlCancellation.'</a>' );
                    
                    // Format alert message.
                    $msgConclusionPreScheduling = Templates::change_variable( $msgConclusionPreScheduling, '#date#', Formats::data_format_to( 'date-to-text', $scheduleDate ) );
                    $msgConclusionPreScheduling = Templates::change_variable_all( $msgConclusionPreScheduling, '#draw_date#', $draw_date );
                    $msgConclusionPreScheduling = Templates::change_variable( $msgConclusionPreScheduling, '#date_confirmation_1#', $date_confirmation_1 );
                    $msgConclusionPreScheduling = Templates::change_variable_all( $msgConclusionPreScheduling, '#date_confirmation_2#', $date_confirmation_2 );

                    $msgAlert = $msgConclusionPreScheduling;
                    
                    // Require user class to get user's data.
                    require_once( CS_PATH . 'includes/class.user.php' );

                    // Get the user's name and email
                    $name = User::get_name();
                    $email = User::get_email();
                    
                    // Prepare email fields.
                    $to = $name . ' <'.$email.'>';
                    $subject = $preSchedulingSubject;
                    $body = $preSchedulingMessage;
            
                    // Require custom-mailer class to send emails.
                    require_once( CS_PATH . 'includes/class.custom-mailer.php' );

                    // Send email with pre-scheduling information.
                    $custom_mailer = new Custom_Mailer();
                    $custom_mailer->send( $to, $subject, $body );
                }
                
                // Handle return data.
                $returnData = Array(
                    'alert' => $msgAlert,
                );
                
                // Return data.
                return Array(
                    'status' => 'OK',
                    'data' => $returnData,
                );
            } else {
                return Array(
                    'status' => 'MANDATORY_FIELDS_NOT_INFORMED',
                );
            }
        }

        private function confirmation( $page ){
            global $_MANAGER;

            // Require formats class to manipulate data.
            require_once( CS_PATH . 'includes/class.formats.php' );

            // Require templates class to manipulate page.
            require_once( CS_PATH . 'includes/class.templates.php' );

            // Require interfaces class to manipulate page.
            require_once( CS_PATH . 'includes/class.interfaces.php' );

            // Get current user id.
            $user_id = get_current_user_id();
            
            // Get the configuration data.
            $options = get_option( 'competitive_scheduling_options' );
            $msg_options = get_option( 'competitive_scheduling_msg_options' );
            
            // Validate the sent schedule_id.
            $id_schedules = ( isset( $_REQUEST['schedule_id'] ) ? sanitize_text_field( $_REQUEST['schedule_id'] ) : '' );
            
            global $wpdb;
            $query = $wpdb->prepare(
                "SELECT date, status 
                FROM {$wpdb->prefix}schedules 
                WHERE id_schedules = '%s' 
                AND user_id = '%s'",
                array( $id_schedules, $user_id )
            );
            $schedules = $wpdb->get_results( $query );
            if( $schedules ) $schedules = $schedules[0];

            if( ! $schedules ){
                // Activation of expiredOrNotFound.
                $_MANAGER['javascript-vars']['expiredOrNotFound'] = true;

                // Activation of confirmation.
                $_MANAGER['javascript-vars']['confirm'] = true;
            } else {
                // Force date to today for debuging or set today's date
                if( CS_FORCE_DATE_TODAY ){ $today = CS_DATE_TODAY_FORCED_VALUE; } else { $today = date('Y-m-d'); }

                // Scheduling data.
                $date = $schedules->date;
                $status = $schedules->status;
                
                // Get the configuration data.
                $draw_phase = ( isset( $options['draw-phase'] ) ? explode(',',$options['draw-phase'] ) : Array(7,5) );
                $residual_phase = ( isset( $options['residual-phase'] ) ? (int)$options['residual-phase'] : 5 );

                // Discovering the current phase of the scheduling process.
                $phase = 'undetermined';
                if(
                    strtotime( $date ) >= strtotime( $today.' + '.($draw_phase[1]+1).' day' ) &&
                    strtotime( $date ) < strtotime( $today.' + '.($draw_phase[0]+1).' day' )
                ){
                    $phase = 'confirmation';
                } else if(
                    strtotime( $today ) >= strtotime( $date.' - '.$residual_phase.' day' ) &&
                    strtotime( $today ) <= strtotime( $date.' - 1 day' )
                ){
                    $phase = 'residual';
                }
           
                // Check whether the current status of the schedule allows confirmation.
                if(
                    $status == 'confirmed' ||
                    $status == 'qualified' ||
                    $status == 'email-sent' ||
                    $status == 'email-not-sent'
                ){
                    // Check if you are in the confirmation phase or residual.
                    if( $phase == 'confirmation' || $phase == 'residual' ){
                        
                    } else {
                        // Confirmation period dates.
                        $date_confirmation_1 = Formats::data_format_to( 'date-to-text', date( 'Y-m-d', strtotime( $date.' - '.($draw_phase[0]).' day' ) ) );
                        $date_confirmation_2 = Formats::data_format_to( 'date-to-text', date( 'Y-m-d', strtotime( $date.' - '.($draw_phase[1]).' day' ) - 1 ) );
                    
                        // Return the expired schedule message.
                        $msgScheduleExpired = ( ! empty( $msg_options['msg-schedule-expired'] ) ? $msg_options['msg-schedule-expired'] : '' );
                        
                        $msgScheduleExpired = Templates::change_variable( $msgScheduleExpired, '#date_confirmation_1#', $date_confirmation_1 );
                        $msgScheduleExpired = Templates::change_variable( $msgScheduleExpired, '#date_confirmation_2#', $date_confirmation_2 );
                        
                        Interfaces::alert( array(
                            'redirect' => true,
                            'msg' => $msgScheduleExpired
                        ));

                        // Redirects the page to previous schedules.
                        wp_redirect( get_permalink() . '?window=previous-schedules', 301 ); exit;
                    }
                } else {
                    if( $phase == 'residual' ){
                        
                    } else {
                        Interfaces::alert( array(
                            'redirect' => true,
                            'msg' => 'SCHEDULING_STATUS_NOT_ALLOWED_CONFIRMATION'
                        ));

                        // Redirects the page to previous schedules.
                        wp_redirect( get_permalink() . '?window=previous-schedules', 301 ); exit;
                    }
                }
                
                // Pick up the change choice.
                $choice = ( $_REQUEST['action'] == 'confirm' ? 'confirm' : 'cancel' );

                // Treat each choice: 'confirm' or 'cancel'.
                switch( $choice ){
                    case 'confirm':
                        // If it has not been confirmed previously, confirm the schedule.
                        $return = $this->schedule_confirm( array(
                            'id_schedules' => $id_schedules,
                            'user_id' => $user_id,
                            'date' => $date,
                        ) );
                    break;
                    default:
                        // Make the cancellation.
                        $return = $this->schedule_cancel( array(
                            'id_schedules' => $id_schedules,
                            'user_id' => $user_id,
                            'date' => $date,
                        ) );
                }
                
                if( ! $return['completed'] ){
                    switch( $return['status'] ){
                        case 'SCHEDULE_WITHOUT_VACANCIES':
                            $msgAlert = ( ! empty( $return['error-msg'] ) ? $return['error-msg'] : $return['status'] );
                    break;
                    default:
                        $msgAlert = ( ! empty( $msg_options['msg-alert'] ) ? $msg_options['msg-alert'] : '' );
                        
                        $msgAlert = Templates::change_variable( $msgAlert, '#error-msg#', ( ! empty( $return['error-msg'] ) ? $return['error-msg'] : $return['status'] ) );
                    }
                    
                    // Alert the user if a problem occurs with the problem description message.
                    Interfaces::alert( array(
                        'redirect' => true,
                        'msg' => $msgAlert
                    ));
                } else {
                    // Alert the user of change success.
                    Interfaces::alert( array(
                        'redirect' => true,
                        'msg' => $return['alert']
                    ));
                }
                
                // Redirects the page to previous schedules.
                wp_redirect( get_permalink() . '?window=previous-schedules', 301 ); exit;
            }

            // Remove the active cell and changes.
            $cell_name = 'active'; $cell[$cell_name] = Templates::tag_value( $page, '<!-- '.$cell_name.' < -->','<!-- '.$cell_name.' > -->' ); $page = Templates::tag_in( $page,'<!-- '.$cell_name.' < -->', '<!-- '.$cell_name.' > -->', '<!-- '.$cell_name.' -->' );
            $cell_name = 'changes'; $cell[$cell_name] = Templates::tag_value( $page, '<!-- '.$cell_name.' < -->','<!-- '.$cell_name.' > -->' ); $page = Templates::tag_in( $page,'<!-- '.$cell_name.' < -->', '<!-- '.$cell_name.' > -->', '<!-- '.$cell_name.' -->' );
            
            // Change the scheduling date and id.
            $page = Templates::change_variable( $page, '[[confirmation-date]]', ( $schedules ? Formats::data_format_to( 'date-to-text', $schedules->date ) : '' ) );
            $page = Templates::change_variable( $page, '[[confirmation-scheduling-id]]', $id_schedules );

            // Finalize interface.
            Interfaces::components_include( array(
                'component' => Array(
                    'modal-loading',
                    'modal-alert',
                )
            ) );
            
            Interfaces::finish( CS_JS_MANAGER_VAR );

            return $page;
        }

        private function cancellation( $page ){
            global $_MANAGER;
            
            // Require formats class to manipulate data.
            require_once( CS_PATH . 'includes/class.formats.php' );

            // Require templates class to manipulate page.
            require_once( CS_PATH . 'includes/class.templates.php' );

            // Require interfaces class to manipulate page.
            require_once( CS_PATH . 'includes/class.interfaces.php' );

            // Get current user id.
            $user_id = get_current_user_id();
            
            // Get the configuration data.
            $options = get_option( 'competitive_scheduling_options' );
            $msg_options = get_option( 'competitive_scheduling_msg_options' );

            // Validate the sent schedule_id.
            $id_schedules = ( isset( $_REQUEST['schedule_id'] ) ? sanitize_text_field( $_REQUEST['schedule_id'] ) : '' );

            global $wpdb;
            $query = $wpdb->prepare(
                "SELECT date 
                FROM {$wpdb->prefix}schedules 
                WHERE id_schedules = '%s' 
                AND user_id = '%s'",
                array( $id_schedules, $user_id )
            );
            $schedules = $wpdb->get_results( $query );
            if( $schedules ) $schedules = $schedules[0];

            if( ! $schedules ){
                // Activation of expiredOrNotFound.
                $_MANAGER['javascript-vars']['expiredOrNotFound'] = true;

                // Cancellation activation.
                $_MANAGER['javascript-vars']['cancel'] = true;
            } else {
                // Scheduling data.
                $date = $schedules->date;
                
                // Make the cancellation.
                $return = $this->schedule_cancel( array(
                    'id_schedules' => $id_schedules,
                    'user_id' => $user_id,
                    'date' => $date,
                ) );
            
                // Alert the user of change success.
                Interfaces::alert( array(
                    'redirect' => true,
                    'msg' => $return['alert']
                ));
            
                // Redirects the page to previous schedules.
                wp_redirect( get_permalink() . '?window=previous-schedules', 301 ); exit;
            }

            // Remove the active cell and changes.
            $cell_name = 'active'; $cell[$cell_name] = Templates::tag_value( $page, '<!-- '.$cell_name.' < -->','<!-- '.$cell_name.' > -->' ); $page = Templates::tag_in( $page,'<!-- '.$cell_name.' < -->', '<!-- '.$cell_name.' > -->', '<!-- '.$cell_name.' -->' );
            $cell_name = 'changes'; $cell[$cell_name] = Templates::tag_value( $page, '<!-- '.$cell_name.' < -->','<!-- '.$cell_name.' > -->' ); $page = Templates::tag_in( $page,'<!-- '.$cell_name.' < -->', '<!-- '.$cell_name.' > -->', '<!-- '.$cell_name.' -->' );
            
            // Change the scheduling date and id.
            $page = Templates::change_variable( $page, '[[cancellation-date]]', ( $schedules ? Formats::data_format_to( 'date-to-text', $schedules->date ) : '' ) );
            $page = Templates::change_variable( $page, '[[cancellation-scheduling-id]]', $id_schedules );

            // Finalize interface.
            Interfaces::components_include( array(
                'component' => Array(
                    'modal-loading',
                    'modal-alert',
                )
            ) );
            
            Interfaces::finish( CS_JS_MANAGER_VAR );

            return $page;
        }

        private function calendar( $params = false ){
            global $_MANAGER;

            if( $params ) foreach( $params as $var => $val ) $$var = $val;
            
            // Force date to today for debuging or set today's date
            if( CS_FORCE_DATE_TODAY ){ 
                $today = CS_DATE_TODAY_FORCED_VALUE;
                $timeNow = strtotime( $today );
            } else { 
                $today = date('Y-m-d'); 
                $timeNow = time();
            }
            
            // Get the configuration data.
            $options = get_option( 'competitive_scheduling_options' );
            
            $days_week = ( isset( $options['days-week'] ) ? explode(',',$options['days-week'] ) : Array() );
            $maxCompanions = ( ! empty( $options['max-companions'] ) ? $options['max-companions'] : 0 );
            $years = ( isset( $options['calendar-years'] ) ? (int)$options['calendar-years'] : 2 );
            $days_week_maximum_vacancies = ( isset( $options['days-week-maximum-vacancies'] ) ? explode(',',$options['days-week-maximum-vacancies'] ) : Array() );
            if( isset( $options['unavailable-dates'] )) $unavailable_dates = ( isset( $options['unavailable-dates-values'] ) ? explode('|',$options['unavailable-dates-values'] ) : Array() );
            $calendar_limit_month_ahead = ( isset( $options['calendar-limit-month-ahead'] ) ? (int)$options['calendar-limit-month-ahead'] : false );
            $draw_phase = ( isset( $options['draw-phase'] ) ? explode(',',$options['draw-phase'] ) : Array(7,5) );
            $residual_phase = ( isset( $options['residual-phase'] ) ? (int)$options['residual-phase'] : 5 );
            $calendar_holidays_start = ( isset( $options['calendar-holidays-start'] ) ? trim( $options['calendar-holidays-start'] ) : '15 December' );
            $calendar_holidays_end = ( isset( $options['calendar-holidays-end'] ) ? trim( $options['calendar-holidays-end'] ) : '20 January' );
            
            $start_year = date('Y', $timeNow);
            $year_end = (int)$start_year + $years;

            global $wpdb;
            $query = $wpdb->prepare(
                "SELECT date,total 
                FROM {$wpdb->prefix}schedules_dates 
                WHERE date >= '%s'",
                $today
            );
            $schedules_dates = $wpdb->get_results( $query );

            for( $i=-1; $i<$years+1; $i++ ){
                $period_holidays[] = Array(
                    'start' => strtotime( $calendar_holidays_start." ".( $start_year+$i ) ),
                    'end' => strtotime( $calendar_holidays_end." ".( $start_year+$i+1 ) ),
                );
            }
            
            $first_day = strtotime( date( "Y-m-d", $timeNow ) . " + 1 day" );
            $last_day = strtotime( date( "Y-m-d", $timeNow ) . " + ".$years." year" );
            
            if( $calendar_limit_month_ahead ){
                $limit_calendar = strtotime( date( "Y-m", strtotime( $today . " + ".$calendar_limit_month_ahead." month") ).'-01' );
            }

            $day = $first_day;
            do {
                if( isset( $limit_calendar ) ){
                    if( $day >= $limit_calendar ){
                        break;
                    }
                }
                
                $dateFormatted = date( 'd/m/Y', $day );
                $flag = false;
                
                if( isset( $period_holidays ) ){
                    foreach( $period_holidays as $period ){
                        if(
                            $day > $period['start'] &&
                            $day < $period['end']
                        ){
                            $flag = true;
                            break;
                        }
                    }
                }
                
                if( isset( $unavailable_dates ) ){
                    foreach( $unavailable_dates as $ud){
                        if( $dateFormatted == $ud ){
                            $flag = true;
                            break;
                        }
                    }
                }
                
                if( isset( $draw_phase ) ){
                    if(
                        $day >= strtotime( $today.' + '.( $draw_phase[1]+1).' day') &&
                        $day < strtotime( $today.' + '.( $draw_phase[0]+1).' day')
                    ){
                        $flag = true;
                    }
                }
                
                if( ! $flag ){
                    $flag2 = false;
                    $count_days = 0;

                    if( isset( $days_week ) )
                    foreach( $days_week as $day_week ){
                        if( $day_week == strtolower( date( 'D', $day ) ) ){
                            $flag2 = true;
                            break;
                        }
                        $count_days++;
                    }

                    if( $flag2 ){
                        $date = date('Y-m-d', $day);
                        $flag3 = false;
                        
                        if( $day < strtotime($today.' + '.$residual_phase.' day' ) ){
                            if( $schedules_dates ){
                                foreach( $schedules_dates as $schedule_date ){
                                    if( $date == $schedule_date->date ){
                                        if( count( $days_week_maximum_vacancies ) > 1 ){
                                            $days_semana_maximo_vacancies = $days_week_maximum_vacancies[$count_days];
                                        } else {
                                            $days_semana_maximo_vacancies = $days_week_maximum_vacancies[0];
                                        }
                                        
                                        if( (int)$days_semana_maximo_vacancies <= (int)$schedule_date->total ){
                                            $flag3 = true;
                                        }
                                        
                                        break;
                                    }
                                }
                            }
                        }
                        
                        if( ! $flag3 ){
                            $dates[$date] = 1;
                        }
                    }
                }
                
                $day += 86400;
            } while ( $day < $last_day );
            
            $JScalendar['available_dates'] = ( ! empty( $dates ) ? $dates : Array() );
            $JScalendar['start_year'] = $start_year;
            $JScalendar['year_end'] = $year_end;
            $JScalendar['max_companions'] = $maxCompanions;
            
            // JS variables.
            $_MANAGER['javascript-vars']['calendar'] = $JScalendar;
        }

        private function allowed_date( $date ){
            // Require formats class to prepare data.
            require_once( CS_PATH . 'includes/class.formats.php' );
            
            // Force date to today for debuging or set today's date
            if( CS_FORCE_DATE_TODAY ){ $today = CS_DATE_TODAY_FORCED_VALUE; } else { $today = date('Y-m-d'); }
            
            // Get the configuration data.
            $options = get_option( 'competitive_scheduling_options' );
            
            $days_week = ( isset( $options['days-week'] ) ? explode(',',$options['days-week'] ) : Array());
            $years = ( isset( $options['calendar-years'] ) ? (int)$options['calendar-years'] : 2);
            if( isset( $options['unavailable-dates'] )) $unavailable_dates = ( isset( $options['unavailable-dates-values'] ) ? explode('|',$options['unavailable-dates-values'] ) : Array());
            $calendar_limit_month_ahead = ( isset( $options['calendar-limit-month-ahead'] ) ? (int)$options['calendar-limit-month-ahead'] : false);
            $draw_phase = ( isset( $options['draw-phase'] ) ? explode(',',$options['draw-phase'] ) : Array(7,5));
            $calendar_holidays_start = ( isset( $options['calendar-holidays-start'] ) ? trim( $options['calendar-holidays-start'] ) : '15 December');
            $calendar_holidays_end = ( isset( $options['calendar-holidays-end'] ) ? trim( $options['calendar-holidays-end'] ) : '20 January');
            
            $start_year = date('Y');
            $year_end = (int)$start_year + $years;
            
            $flag = false;
            if( $days_week )
            foreach( $days_week as $day_week ){
                if( ! $flag ){
                    $first_day_week = $day_week;
                    $flag = true;
                }
            }
            
            for( $i=-1; $i<$years+1; $i++ ){
                $period_holidays[] = Array(
                    'start' => strtotime( $calendar_holidays_start." ".( $start_year+$i ) ),
                    'end' => strtotime( $calendar_holidays_end." ".( $start_year+$i+1 ) ),
                );
            }
            
            $first_day = strtotime( date( "Y-m-d", time() ) . " + 1 day" );
            $last_day = strtotime( date( "Y-m-d", time() ) . " + ".$years." year" );
            
            if( $calendar_limit_month_ahead ){
                $limit_calendar = strtotime( date( "Y-m", strtotime( $today . " + ".$calendar_limit_month_ahead." month") ).'-01' );
            }
            
            $day = $first_day;
            do {
                if( isset( $limit_calendar ) ){
                    if( $day >= $limit_calendar ){
                        break;
                    }
                }
                
                $flag = false;
                
                if( isset( $period_holidays ) ){
                    foreach( $period_holidays as $period ){
                        if(
                            $day > $period['start'] &&
                            $day < $period['end']
                        ){
                            $flag = true;
                            break;
                        }
                    }
                }
                
                if( isset( $unavailable_dates ) ){
                    foreach( $unavailable_dates as $ud){
                        if(
                            $day > strtotime( Formats::data_format_to( 'text-to-date', $ud ).' 00:00:00' ) &&
                            $day < strtotime( Formats::data_format_to( 'text-to-date', $ud ).' 23:59:59' )
                        ){
                            $flag = true;
                            break;
                        }
                    }
                }
                
                if( isset( $draw_phase ) ){
                    if(
                        $day >= strtotime( $today.' + '.( $draw_phase[1]+1).' day') &&
                        $day < strtotime( $today.' + '.( $draw_phase[0]+1).' day')
                    ){
                        $flag = true;
                    }
                }
                
                if( ! $flag ){
                    $flag2 = false;
                    
                    if( isset( $days_week ) )
                    foreach( $days_week as $day_week ){
                        if( $day_week == strtolower( date( 'D', $day ) ) ){
                            $flag2 = true;
                            break;
                        }
                    }
                    
                    if( $flag2 ){
                        if( $date == date('Y-m-d', $day)){
                            return true;
                        }
                    }
                }
                
                $day += 86400;
            } while ( $day < $last_day);
            
            return false;
        }

        private function status_text( $status = '' ){
            $statusSchedulingTexts = Array(
                'status-confirmed' => __( '<span class="ui green label">Confirmed</span>', 'competitive-scheduling' ),
                'status-finished' => __( '<span class="ui grey label">Finished</span>', 'competitive-scheduling' ),
                'status-canceled' => __( '<span class="ui red label">Canceled</span>', 'competitive-scheduling' ),
                'status-unqualified' => __( '<span class="ui brown label">Not Drawn - Waiting for Residual Vacancies</span>', 'competitive-scheduling' ),
                'status-new' => __( '<span class="ui grey label">Waiting For Draw</span>', 'competitive-scheduling' ),
                'status-qualified' => __( '<span class="ui yellow label">Drawn - Awaiting Confirmation</span>', 'competitive-scheduling' ),
                'status-no-residual-vacancy' => __( '<span class="ui brown label">No Residual Vacancies</span>', 'competitive-scheduling' ),
                'status-residual-vacancies' => __( '<span class="ui teal label">Available Residual Vacancies</span>', 'competitive-scheduling' ),
            );

            return ( ! empty( $statusSchedulingTexts[$status] ) ? $statusSchedulingTexts[$status] : __( '<span class="ui grey label">Undefined Status</span>', 'competitive-scheduling' ) );
        }

        private function js_texts(){
            global $_MANAGER;

            $jsTexts = Array(
                'companion-label' => __( 'Companion', 'competitive-scheduling' ),
                'companion-placeholder' => __( 'Companion\'s full name', 'competitive-scheduling' ),
                'cancel-confirmation' => __( 'Are you sure you want to delete this schedule?', 'competitive-scheduling' ),
                'confirm-confirmation' => __( 'Are you sure you want to confirm this schedule?', 'competitive-scheduling' ),
                'date-not-selected' => __( 'Date not selected! It is mandatory to select a date to continue!', 'competitive-scheduling' ),
            );

            foreach( $jsTexts as $key => $text ){
                $_MANAGER['javascript-vars']['texts'][$key] = $text;
            }
        }

        private function nonce_verify( $nonce ){
            // Verifiying nonce
            if( isset( $_POST[$nonce] ) ){
                if( ! wp_verify_nonce( $_POST[$nonce], $nonce ) ){
                    $noNonce = true;
                }
            } else {
                $noNonce = true;
            }
            
            // If nonce is invalid, redirect to home
            if( isset( $noNonce ) ){
                wp_redirect( home_url( '/' ) ); exit;
            }
        }
    }
}