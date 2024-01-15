<?php

if( ! class_exists( 'Cron' ) ){
    class Cron {
        /**
         * Activate scheduled task.
         *
         * @return void
         */

        public static function activate(){
            if( ! wp_next_scheduled( 'competitive_scheduling_cron_hook' ) ){
                add_action( 'competitive_scheduling_cron_hook', 'Cron::run' );
                add_action( 'competitive_scheduling_cron_hook_after', 'Cron::run_after' );

                // Get the current timestamp
                $current_timestamp = time();

                // Add 24 hours to the date
                $tomorrow_date = date( "Y-m-d", $current_timestamp + 86400 );

                // Convert the tomorrow date to a timestamp and defines the task execution time
                $tomorrow_timestamp = strtotime( $tomorrow_date . " 00:01" );
                
                // Schedule the event for the tomorrow timestamp
                wp_schedule_event( $tomorrow_timestamp, 'daily', 'competitive_scheduling_cron_hook' );
            }
        }

        /**
         * Desactivate scheduled task.
         *
         * @return void
         */

        public static function desactivate(){
            remove_action( 'competitive_scheduling_cron_hook', 'Cron::run' );
            wp_clear_scheduled_hook( 'competitive_scheduling_cron_hook' );
            wp_clear_scheduled_hook( 'competitive_scheduling_cron_hook_after' );
        }

        /**
         * Run scheduled task.
         *
         * @return void
         */

        public static function run(){
            Self::cleaning();
            Self::draw();
        }

        /**
         * Run after run scheduled task.
         *
         * @return void
         */

        public static function run_after(){
            Self::draw();
        }

        /**
         * Perform cleaning procedure.
         *
         * @return void
         */

        public static function cleaning(){
            // Control variables initial values.
            $today = date( 'Y-m-d' );

            // Check the scheduled dates in the database.
            global $wpdb;
            $query = $wpdb->prepare(
                "SELECT id_schedules_dates  
                FROM {$wpdb->prefix}schedules_dates 
                WHERE date < '%s' 
                AND status='no-schedules'",
                $today
            );
            $schedules_dates = $wpdb->get_results( $query );

            // Delete the scheduled date.
            if( ! empty( $schedules_dates ) )
            foreach( $schedules_dates as $schedule_date ){
                global $wpdb;
                $wpdb->delete( $wpdb->prefix.'schedules_dates', ['id_schedules_dates' => $schedule_date->id_schedules_dates] );
            }
        }

        /**
         * Execute draw procedure.
         *
         * @return void
         */

        public static function draw(){
            error_log( CS_ID . ': ' . 'draw' );

            // Control variables initial values.
            $today_day_week = strtolower( date( 'D' ) );
            $today = date( 'Y-m-d' );
            
            // Get current configuration data.
            $options = get_option( 'competitive_scheduling_options' );

            $draw_phase = ( ! empty( $options['draw-phase'] ) ? explode( ',', $options['draw-phase'] ) : Array( 7, 5 ) );
            $days_week_maximum_vacancies_arr = ( ! empty( $options['days-week-maximum-vacancies'] ) ? explode( ',', $options['days-week-maximum-vacancies'] ) : Array() );
            $days_week = ( ! empty( $options['days-week'] ) ? explode( ',', $options['days-week'] ) : Array() );
            
            // Draw date.
            $date = date( 'Y-m-d', strtotime( $today.' + '.( $draw_phase[0] ).' day' ) );
            
            // Check the scheduled dates in the database.
            global $wpdb;
            $query = $wpdb->prepare(
                "SELECT total, status 
                FROM {$wpdb->prefix}schedules_dates 
                WHERE date = '%s'",
                $date
            );
            $schedules_dates = $wpdb->get_results( $query );
            
            // Create date in scheduling_dates if it does not exist.
            if( empty( $schedules_dates ) ){
                global $wpdb;
                $wpdb->insert( $wpdb->prefix.'schedules_dates', array(
                    'date' => $date,
                    'total' => 0,
                    'status' => 'new',
                ) );
                
                $lastid = $wpdb->insert_id;
                
                $statusProcessDraw = 'new';
            } else {
                $statusProcessDraw = ( $schedules_dates->status ? $schedules_dates->status : 'new' );
            }
            
            // Counting and control variables.
            $totalSchedules = 0;
            $newQualification = false;
            $sendEmails = false;
            $noSchedules = false;
            
            // Check the status of the draw process. If it is 'new', make a new qualification attempt, otherwise continue looping and go to another host.
            switch( $statusProcessDraw ){
                case 'new':
                    // Get schedules from the database for the specific date, if any.
                    global $wpdb;
                    $query = $wpdb->prepare(
                        "SELECT user_id, id_schedules, companions  
                        FROM {$wpdb->prefix}schedules 
                        WHERE date = '%s' 
                        AND status='new'",
                        $date
                    );
                    $schedules = $wpdb->get_results( $query );

                    // Define the current status of the draw process if there is a 'new' unprocessed schedule.
                    
                    if( ! empty( $schedules ) ){
                        foreach( $schedules as $schedule ){
                            $totalSchedules += 1 + ( int ) $schedule->companions;
                        }
                        
                        $statusProcessDraw = 'qualify';
                        $newQualification = true;
                        $sendEmails = true;
                    } else {
                        $statusProcessDraw = 'no-schedules';
                        $noSchedules = true;
                    }
                    
                    // Update draw process.
                    global $wpdb;
                    $result = $wpdb->update( $wpdb->prefix.'schedules_dates', 
                        array( 
                            'status' => $statusProcessDraw,
                        ), 
                        array(
                            'date' => $date,
                        ) 
                    );
                break;
                case 'send-emails':
                    $sendEmails = true;
                break;
                case 'confirmations-sent':
                case 'no-schedules':
                    $noSchedules = true;
                break;
            }
            
            // If there is no schedule, return.
            if( $noSchedules ){
                return;
            }
            
            // Draw or qualify schedules for confirmation.
            if( $newQualification ){
                // Define the maximum number of vacancies for the day of the week in question.
                
                $max_vacancies = 0;
                $count = 0;
                if( ! empty( $days_week ) )
                foreach( $days_week as $day_week ){
                    if( strtolower( $day_week ) == $today_day_week ){
                        if( count( $days_week_maximum_vacancies_arr ) > 1 ){
                            $max_vacancies = (int)$days_week_maximum_vacancies_arr[$count];
                        } else {
                            $max_vacancies = (int)$days_week_maximum_vacancies_arr[0];
                        }
                        
                        if( $max_vacancies < 0 ) $max_vacancies = 0;
                        break;
                    }
                    
                    $count++;
                }
                
                // Check whether or not you need a draw based on the maximum number of service vacancies.
                if( $totalSchedules > $max_vacancies ){
                    $draw = true;
                }
                
                // Draw if the total number of schedules is greater than the maximum number of places. Otherwise qualify all schedules directly.
                if( isset( $draw ) ){
                    // Preparation of tickets with application of weights.
                    foreach( $schedules as $num => $schedule ){
                        $ticket = Array(
                            'id_schedules' => $schedule->id_schedules,
                            'user_id' => $schedule->user_id,
                            'companions' => (int)$schedule->companions,
                        );
                        
                        // Take the user's weight.
                        global $wpdb;
                        $query = $wpdb->prepare(
                            "SELECT weight 
                            FROM {$wpdb->prefix}schedules_weights 
                            WHERE user_id = '%s'",
                            $schedule->user_id
                        );
                        $schedules_weights = $wpdb->get_results( $query );

                        // Create the number of tickets a user has based on their weight.
                        if( ! empty( $schedules_weights ) ){
                            $weight = (int)$schedules_weights[0]->weight;
                            if( $weight > 0 ){
                                for( $i=0; $i<$weight+1; $i++ ){
                                    $tickets[] = $ticket;
                                }
                            } else {
                                $tickets[] = $ticket;
                            }
                            
                            // Mark the existence of the weight in the database.
                            $schedules[$num]['weight_database'] = true;
                        } else {
                            $weight = 0;
                            $tickets[] = $ticket;
                        }
                        
                        // Update schedule array and add weight.
                        $schedules[$num]['weight'] = $weight;
                    }
                    
                    // Draw the tickets.
                    $drawn = Array();
                    $tickets_aux = $tickets;
                    $drawn_vacancies = 0;
                    
                    while( $drawn_vacancies < $max_vacancies ){
                        $na = count( $tickets_aux ) - 1;
                        $index = rand( 0, $na );
                        
                        $id_schedules = $tickets_aux[$index]['id_schedules'];
                        $drawn[] = $tickets_aux[$index];
                        
                        $drawn_vacancies += 1 + $tickets_aux[$index]['companions'];
                        
                        $tickets_aux2 = Array();
                        foreach( $tickets_aux as $ticket ){
                            if( $ticket['id_schedules'] != $id_schedules ){
                                $remaining_vacancies = $max_vacancies - $drawn_vacancies;
                                
                                if( $remaining_vacancies >= 3 ){
                                    $tickets_aux2[] = $ticket;
                                } else if( $remaining_vacancies == 2 && $ticket['companions'] <= 1 ){
                                    $tickets_aux2[] = $ticket;
                                } else if( $remaining_vacancies == 1 && $ticket['companions'] == 0 ){
                                    $tickets_aux2[] = $ticket;
                                }
                            }
                        }
                        
                        $tickets_aux = $tickets_aux2;
                        
                        if( count( $tickets_aux ) == 0 ){
                            break;
                        }
                    }
                    
                    // Qualify drawn schedules for confirmation.
                    if( $drawn )
                    foreach( $drawn as $dr ){
                        global $wpdb;
                        $result = $wpdb->update( $wpdb->prefix.'schedules', 
                            array( 
                                'status' => 'qualified',
                            ), 
                            array(
                                'id_schedules' => $dr['id_schedules'],
                            ) 
                        );
                    }
                    
                    // Schedules NOT drawn update weights.
                    if( $drawn ){
                        unset( $computed );
                        
                        if( $schedules )
                        foreach( $schedules as $schedule ){
                            $user_id = $schedule->user_id;
                            
                            // Check whether the schedule was drawn or not.
                            $drawnFlag = false;
                            foreach( $drawn as $dr){
                                if( $schedule->id_schedules == $dr['id_schedules'] ){
                                    $drawnFlag = true;
                                    break;
                                }
                            }
                            
                            // Check whether the user's new weight has already been computed. If not, update weight in the database.
                            if( ! isset( $computed[$user_id] ) ){
                                // Check if the user already has weight registered with the bank.
                                if( isset( $schedule->weight_database ) ){
                                    $weight_database = true;
                                } else {
                                    $weight_database = false;
                                }
                                
                                // Increase the weight of users who were not drawn in order to increase the chance of being drawn the next time they will be drawn by 100%. For those selected, reset the weight to have a single chance in the next draw.
                                if( ! $drawnFlag ){
                                    $weight = (int)$schedule->weight + 1;
                                } else {
                                    $weight = '0';
                                }
                                
                                // Update or create a new record in the database with the user's updated weight.
                                if( $weight_database ){
                                    global $wpdb;
                                    $result = $wpdb->update( $wpdb->prefix.'schedules_weights', 
                                        array( 
                                            'weight' => $weight,
                                        ), 
                                        array(
                                            'user_id' => $user_id,
                                        ) 
                                    );
                                } else {
                                    global $wpdb;
                                    $wpdb->insert( $wpdb->prefix.'schedules_weights', array(
                                        'user_id' => $user_id,
                                        'weight' => $weight,
                                    ) );
                                }
                                
                                // Mark the user as computed because the same user can have more than one draw ticket.
                                $computed[$user_id] = true;
                            }
                        }
                    }
                } else {
                    // Schedules qualify for confirmation.
                    if( $schedules )
                    foreach( $schedules as $schedule ){
                        global $wpdb;
                        $result = $wpdb->update( $wpdb->prefix.'schedules', 
                            array( 
                                'status' => 'qualified',
                            ), 
                            array(
                                'id_schedules' => $schedule['id_schedules'],
                            ) 
                        );
                    }
                }
                
                // Update process to send confirmation emails.
                global $wpdb;
                $result = $wpdb->update( $wpdb->prefix.'schedules_dates', 
                    array( 
                        'status' => 'send-emails',
                    ), 
                    array(
                        'date' => $date,
                    ) 
                );
            }
            
            // Send schedule confirmation email to each user for each schedule.
            if( $sendEmails ){
                // Get data from qualified schedules in the database.
                global $wpdb;
                $query = $wpdb->prepare(
                    "SELECT user_id, id_schedules, pubID 
                    FROM {$wpdb->prefix}schedules 
                    WHERE date = '%s' AND status='qualified'",
                    $date
                );
                $schedules = $wpdb->get_results( $query );
                
                // If there is, send emails to each user with the option to confirm or cancel.
                if( $schedules ){
                    // Get the message and subject of the emails, as well as the title of the establishment.
                    $html_options = get_option('competitive_scheduling_html_options');
                    $options = get_option('competitive_scheduling_options');

                    $confirmationSubject = ( ! empty( $html_options['confirmation-subject'] ) ? $html_options['confirmation-subject'] : '');
                    $confirmationMessage = ( ! empty( $html_options['confirmation-message'] ) ? $html_options['confirmation-message'] : '');
                    $titleEstablishment = ( ! empty( $options['title-establishment'] ) ? $options['title-establishment'] : '');
                    
                    // Format the date in question into Brazilian form, as well as include the necessary libraries.
                    require_once( CS_PATH . 'includes/class.authentication.php' );
                    require_once( CS_PATH . 'includes/class.formats.php' );
                    require_once( CS_PATH . 'includes/class.templates.php' );
                    require_once( CS_PATH . 'includes/class.custom-mailer.php' );

                    $date_str = Formats::data_format_to( 'date-to-text', $date );

                    // Require user class to get user's data.
                    require_once( CS_PATH . 'includes/class.user.php' );

                    // Scan all schedules.
                    $emails_sent = 0;
                    foreach( $schedules as $schedule ){
                        $user_id = $schedule['user_id'];
                        $id_schedules = $schedule['id_schedules'];
                        $pubID = $schedule['pubID'];

                        // Generate the validation token.
                        $auth = Authentication::generate_token_validation( array( 
                            'pubID' => $pubID,
                        ) );

                        $token = $auth['token'];
                        
                        // Generate the url to be able to cancel or confirm
                        $urlCancellation = esc_url( add_query_arg(
                            array(
                                'action' => 'schedule_cancellation',
                                'pubID' => $pubID,
                                'token' => $token,
                            ),
                            admin_url('admin-post.php')
                        ) );
                        $urlConfirmation = esc_url( add_query_arg(
                            array(
                                'action' => 'schedule_confirmation',
                                'pubID' => $pubID,
                                'token' => $token,
                            ),
                            admin_url('admin-post.php')
                        ) );
                        
                        // Get the user's name and email
                        $name = User::get_name();
                        $email = User::get_email();
                        
                        $code = date( 'dmY' ).Formats::format_zero_to_the_left( $id_schedules, 6 );
                        
                        // Format email message.
                        
                        $emailConfirmationSubjectAux = $confirmationSubject;
                        $emailConfirmationMessageAux = $confirmationMessage;

                        $emailConfirmationSubjectAux = Templates::change_variable( $emailConfirmationSubjectAux, '#code#', $code );

                        $emailConfirmationMessageAux = Templates::change_variable( $emailConfirmationMessageAux, '#code#', $code );
                        $emailConfirmationMessageAux = Templates::change_variable( $emailConfirmationMessageAux, '#title#', $titleEstablishment );
                        $emailConfirmationMessageAux = Templates::change_variable( $emailConfirmationMessageAux, '#date#', $date_str );
                        $emailConfirmationMessageAux = Templates::change_variable( $emailConfirmationMessageAux, '#url-cancelation#', '<a target="schedule" href="'.$urlCancellation.'" style="overflow-wrap: break-word;">'.$urlCancellation.'</a>' );
                        $emailConfirmationMessageAux = Templates::change_variable( $emailConfirmationMessageAux, '#url-confirmation#', '<a target="schedule" href="'.$urlConfirmation.'" style="overflow-wrap: break-word;">'.$urlConfirmation.'</a>' );
                        
                        // Prepare email fields.
                        $to = $name . ' <'.$email.'>';
                        $subject = $emailConfirmationSubjectAux;
                        $body = $emailConfirmationMessageAux;
                
                        // Send an email to the user requesting confirmation or cancellation of the schedule.
                        $custom_mailer = new Custom_Mailer();
                        
                        if( $custom_mailer->send( $to, $subject, $body ) ){
                            $scheduling_status = 'email-sent';
                        } else {
                            $scheduling_status = 'email-not-sent';
                        }
                        
                        // Update the schedule in the database.
                        global $wpdb;
                        $sql = $wpdb->prepare(
                            "UPDATE {$wpdb->prefix}schedules      
                            SET 
                                status = '%s',  
                                version = version + 1,  
                                modification_date = '%s'  
                            WHERE 
                                id_schedules = '%s' AND 
                                user_id = '%s'",
                            array( $scheduling_status, current_time('mysql', false), $id_schedules, $user_id )
                        );
                        $wpdb->query($sql);
                        
                        // Email sending limit control per cron request. If it reaches the limit, return the function and finish.
                        $emails_sent++;
                        
                        if( $emails_sent >= CS_MAX_EMAILS_PER_CYCLE ){
                            $cron = get_option('competitive_scheduling_cron');

                            $maxReRuns = CS_MAX_RERUN_CYCLES;
                            $reRuns = 0;
                            if( ! emtpy( $cron ) ){
                                $reRuns = $cron['reRuns'];
                            } else {
                                $cron['reRuns'] = $reRuns;
                            }

                            $cron['reRuns']++;

                            if( $reRuns < $maxReRuns ){
                                $task_time = strtotime( '+' . CS_TIME_NEXT_CYCLE_AFTER_EMAIL_PER_CYCLE_REACH . ' minutes' );
                                wp_schedule_single_event( $task_time, 'competitive_scheduling_cron_hook_after', array( 'reRuns' => $cron['reRuns'] ) );
                            }

                            update_option( 'competitive_scheduling_cron', $cron );

                            return;
                        }
                    }
                }
            }

            // Reset control parameters
            $cron = get_option('competitive_scheduling_cron');
            $cron['reRuns'] = 0;
            update_option( 'competitive_scheduling_cron', $cron );

            // Change the status of the schedule dates to 'confirmations-sent'.
            global $wpdb;
            $result = $wpdb->update( $wpdb->prefix.'schedules_dates', 
                array( 
                    'status' => 'confirmations-sent',
                ), 
                array(
                    'date' => $date,
                ) 
            );
        }
    }
}