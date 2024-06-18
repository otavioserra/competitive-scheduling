<?php 

if( ! class_exists( 'Calendar_Shortcode' ) ){
    class Calendar_Shortcode {
        public function __construct(){
            add_shortcode( 'competitive_scheduling_calendar', array( $this, 'add_shortcode' ) );

        }

        public function add_shortcode( $atts = array(), $content = null, $tag = '' ){
            // Prepare JSs and CSSs
            if( ! wp_style_is( 'fomantic-ui' ) ) wp_enqueue_style( 'fomantic-ui', CS_URL . 'vendor/fomantic-UI@2.9.0/dist/semantic.min.css', array(  ), CS_VERSION );
            if( ! wp_script_is( 'fomantic-ui' ) ) wp_enqueue_script( 'fomantic-ui', CS_URL . 'vendor/fomantic-UI@2.9.0/dist/semantic.min.js', array( 'jquery' ), CS_VERSION );
            
            wp_enqueue_style( 'competitive-scheduling-public', CS_URL . 'assets/css/public.css', array(  ), ( CS_DEBUG ? filemtime( CS_PATH . 'assets/css/public.css' ) : CS_VERSION ) );
            wp_enqueue_script( 'competitive-scheduling-public', CS_URL . 'assets/js/public.js', array( 'jquery' ), ( CS_DEBUG ? filemtime( CS_PATH . 'assets/js/public.js' ) : CS_VERSION ) );

            // Require interfaces class to manipulate page.
            require_once( CS_PATH . 'includes/class.interfaces.php' );

            // Get page view and return processed page
            ob_start();
            require( CS_PATH . 'views/calendar-shortcode.php' );

            return $this->add_shortcode_page( ob_get_clean() );
        }
        
        private function add_shortcode_page( $page){
            global $_MANAGER;

            // Calendar assembly.
            $this->calendar();

            // Enable the calendar interface in JS.
            $_MANAGER['javascript-vars']['calendarShortcode'] = true;

            Interfaces::finish( CS_JS_MANAGER_VAR, 'competitive-scheduling-public' );

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
                
                /* if( isset( $draw_phase ) ){
                    if(
                        $day >= strtotime( $today.' + '.( $draw_phase[1]+1).' day') &&
                        $day < strtotime( $today.' + '.( $draw_phase[0]+1).' day')
                    ){
                        $flag = true;
                    }
                } */
                
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
    }
}