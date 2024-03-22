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
                add_action( 'competitive_scheduling_cron_hook', array( __CLASS__, 'run' ) );
                add_action( 'competitive_scheduling_cron_hook_after', array( __CLASS__, 'run_after' ) );

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
            remove_action( 'competitive_scheduling_cron_hook', array( __CLASS__, 'run' ) );
            remove_action( 'competitive_scheduling_cron_hook_after', array( __CLASS__, 'run_after' ) );
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
            if( CS_FORCE_DATE_TODAY ){ $today = CS_DATE_TODAY_FORCED_VALUE; } else { $today = date('Y-m-d'); }

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
            // Set the day today, either automatically or by forcing for testing.
            if( CS_FORCE_DATE_TODAY ){ $today = CS_DATE_TODAY_FORCED_VALUE; } else { $today = date('Y-m-d'); }

            // Control variables initial values.
            $today_day_week = strtolower( date( 'D' ) );
            
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
                $statusProcessDraw = ( $schedules_dates[0]->status ? $schedules_dates[0]->status : 'new' );
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
                            $schedules[$num]->weight_database = true;
                        } else {
                            $weight = 0;
                            $tickets[] = $ticket;
                        }
                        
                        // Update schedule array and add weight.
                        $schedules[$num]->weight = $weight;
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
                                'id_schedules' => $schedule->id_schedules,
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
                        $user_id = $schedule->user_id;
                        $id_schedules = $schedule->id_schedules;
                        $pubID = $schedule->pubID;

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
                        $name = User::get_name( $user_id );
                        $email = User::get_email( $user_id );
                        
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

        /**
         * Randomly create schedules to test the robot.
         *
         * @return void
         */

        public static function tests(){
            echo 'Start [tests]... ' . '<br>';

            // Include wordpress library to delete users.
            require_once(ABSPATH.'wp-admin/includes/user.php');
            
            // Initial Options
            $controls = array(
                'delete_users' => true,
                'reset' => true,
                'create_schedules' => true,
                'num_users' => 60,
                'date' => '2024-03-28',
                'status' => 'confirmed',
                'first_names' => ['João', 'José', 'Maria', 'Ana', 'Adriana', 'Aline', 'Antônio', 'Carlos', 'Paulo', 'Pedro', 'Henrique', 'Bruna', 'Amanda', 'Fernanda', 'Luana', 'Luiza', 'Laura', 'Lucas', 'Matheus', 'Gabriel'],
                'last_names' => ['Silva', 'Santos', 'Oliveira', 'Rodrigues', 'Ferreira', 'Almeida', 'Pereira', 'Lima', 'Ribeiro', 'Gomes', 'Martins', 'Souza', 'Mendes', 'Teixeira', 'Marques', 'Azevedo', 'Costa', 'Barros', 'Fernandes', 'Alves'],
            );

            echo 'Get stats... ' . '<br>';
            // Tests data
            $tests = get_option( 'competiive_scheduling_tests');

            // If it is necessary to reset or delete all users, remove all previously created users.
            if( ( $controls['reset'] || $controls['delete_users'] ) && ! empty( $tests['user_ids'] ) ){
                echo 'Delete users if exists any...' . '<br>';
                // Array containing previously generated user IDs
                $user_ids = $tests['user_ids']; 

                // Loop through array and delete each user
                foreach( $user_ids as $user_id ) {
                    // Removes all schedules created for the user.
                    global $wpdb;
                    $query = $wpdb->prepare(
                        "SELECT id_schedules  
                        FROM {$wpdb->prefix}schedules 
                        WHERE user_id = '%s'",
                        array( $user_id )
                    );
                    $schedules = $wpdb->get_results( $query );

                    foreach( $schedules as $schedule ) {
                        $wpdb->delete(
                            $wpdb->prefix.'schedules_companions',
                            array( 
                                'id_schedules' => $schedule->id_schedules,
                                'user_id' => $user_id
                            )
                        );
                        $wpdb->delete(
                            $wpdb->prefix.'schedules',
                            array( 
                                'id_schedules' => $schedule->id_schedules,
                                'user_id' => $user_id
                            )
                        );
                    }

                    // Delete user by ID
                    wp_delete_user( $user_id ); 
                }
            }

            // If the options are empty, create the initial test data.
            if( empty( $tests ) || $controls['reset'] ){
                echo 'Reset or create initial users...' . '<br>';
                // Start variable.
                if( empty( $tests ) ){
                    $tests = array();
                }

                // Array to store user IDs
                $user_ids = array(); 

                // Number of users to create 
                $num_users = $controls['num_users'];

                // Loop to create random users
                for( $i = 0; $i < $num_users; $i++ ){

                    // Generate random user data
                    $random_username = 'test_' . rand(100,999);
                    $random_email = $random_username . '@test.com';
                    $random_password = wp_generate_password(12);

                    // Create the user
                    $user_id = wp_create_user( $random_username, $random_password, $random_email );

                    // Add user ID to array
                    $user_ids[] = $user_id;

                    // Set role as subscriber
                    $user = new WP_User( $user_id );
                    $user->set_role('subscriber');

                }

                // $user_ids array now contains the IDs of generated users
                $tests['user_ids'] = $user_ids;
            }

            // Randomly create schedules for all users.
            if( $controls['create_schedules'] ) {
                echo 'Create schedules...' . '<br>';
                foreach( $tests['user_ids'] as $user_id ) {
                    // Date required to schedule all users.
                    $date = $controls['date'];

                    // Generate random schedule data
                    $num_companions = rand(0,3);
                    $pubID = md5( uniqid( rand(), true ) );

                    // Insert schedule
                    global $wpdb;
                    $wpdb->insert(
                        $wpdb->prefix.'schedules',
                        array(
                            'user_id' => $user_id,
                            'date' => $date,
                            'companions' => $num_companions,
                            'pubID' => $pubID,
                            'status' => $controls['status'],
                            'version' => 1,
                            'date_creation' => current_time( 'mysql', false ),
                            'modification_date' => current_time( 'mysql', false ),
                        )
                    );

                    $lastid = $wpdb->insert_id;

                    // Create companions if any.
                    if( $num_companions > 0 ) {
                        for( $j = 0; $j < $num_companions; $j++ ) {
                            // Generate random first and last name
                            $rand_first_name = $controls['first_names'][ array_rand( $controls['first_names'] ) ];

                            $rand_last_name = '';
                            for( $k = 0; $k < rand(1,5); $k++ ) {
                                $rand_last_name .= ( empty( $rand_last_name ) ? '' : ' ' ) . $controls['last_names'][ array_rand( $controls['last_names'] ) ];
                            }

                            // Combine into full name
                            $rand_full_name = $rand_first_name . ' ' . $rand_last_name;
                            
                            // Insert companion
                            $wpdb->insert(
                                $wpdb->prefix.'schedules_companions',
                                array(
                                    'id_schedules' => $lastid,
                                    'user_id' => $user_id,
                                    'name' => $rand_full_name
                                )
                            );
                        }
                    }
                }
            }

            echo 'Update stats... ' . '<br>';
            // Update test data in the database.
            update_option( 'competiive_scheduling_tests', $tests );

            echo 'Done!' . '<br>';
        }
    }
}