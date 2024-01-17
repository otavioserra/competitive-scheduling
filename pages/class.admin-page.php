<?php 

if( ! class_exists( 'Competitive_Scheduling_Admin_Page' ) ){
    class Competitive_Scheduling_Admin_Page {

        public function __construct(){
            add_action( 'rest_api_init', function () {
                register_rest_route( 'competitive-scheduling/v1', '/admin-page/', array(
                        'methods' => WP_REST_Server::READABLE,
                        'callback' => array( $this, 'ajax' )
                ) );
            } );
        }

        public function page(){
            if( ! current_user_can( 'manage_options' ) ){
                return;
            }

            wp_enqueue_style( 'fomantic-ui', CS_URL . 'vendor/fomantic-UI@2.9.0/dist/semantic.min.css', array(  ), CS_VERSION );
            wp_enqueue_script( 'fomantic-ui', CS_URL . 'vendor/fomantic-UI@2.9.0/dist/semantic.min.js', array( 'jquery' ), CS_VERSION );
            
            wp_enqueue_style( 'competitive-scheduling-admin', CS_URL . 'assets/css/admin.css', array(  ), ( CS_DEBUG ? filemtime( CS_PATH . 'assets/css/admin.css' ) : CS_VERSION ) );
            wp_enqueue_script( 'competitive-scheduling-admin', CS_URL . 'assets/js/admin.js', array( 'jquery' ), ( CS_DEBUG ? filemtime( CS_PATH . 'assets/js/admin.js' ) : CS_VERSION ) );

            require( CS_PATH . 'views/competitive-scheduling-admin-page.php' );
        }

        public function ajax( $request ) {
            // Get all sent parameters
            $params = $request->get_params();
            
            if( is_user_logged_in() ){
                // Verify nonce
                $nonce = $params['nonce'];
                if( ! wp_verify_nonce( $nonce, 'companions-nonce' ) ){
                    return new WP_Error( 'rest_api_nonce_invalid', esc_html__( 'The system did not validate the nonce sent. Please try again or seek help from support.', 'competitive-scheduling' ), array( 'status' => 403 ) );
                }

                // Require templates class to manipulate page.
                require_once( CS_PATH . 'includes/class.templates.php' );

                // Get schedule data.
                $schedule_id = $params['schedule_id'];

                // Sanitize all fields
                $schedule_id = sanitize_text_field( $schedule_id );

                // Get user ID
                $user_id = get_current_user_id();

                // Get cells from the data.
                $page = file_get_contents( CS_PATH . 'views/competitive-scheduling_shortecode.php' );

                $cell_name = 'cell-data'; $cell[$cell_name] = Templates::tag_value( $page, '<!-- '.$cell_name.' < -->','<!-- '.$cell_name.' > -->' ); $page = Templates::tag_in( $page,'<!-- '.$cell_name.' < -->', '<!-- '.$cell_name.' > -->', '<!-- '.$cell_name.' -->' );
                $cell_name = 'schedule-data'; $cell[$cell_name] = Templates::tag_value( $page, '<!-- '.$cell_name.' < -->','<!-- '.$cell_name.' > -->' ); $page = Templates::tag_in( $page,'<!-- '.$cell_name.' < -->', '<!-- '.$cell_name.' > -->', '<!-- '.$cell_name.' -->' );
                
                $dataSchedules = $cell['schedule-data'];
                
                // Get the user's full name.
                $first_name = get_user_meta( $user_id, 'first_name', true );
                $last_name = get_user_meta( $user_id, 'last_name', true );
                
                if( ! empty($first_name) && ! empty($last_name) ) {
                    $user_name = $first_name . ' ' . $last_name;
                } else {
                    $user_data = get_userdata( $user_id );
                    $user_name = $user_data->display_name;
                }

                $dataSchedules = Templates::change_variable( $dataSchedules, '[[header-name]]', __( 'Scheduled People', 'competitive-scheduling' ) );
                $dataSchedules = Templates::change_variable( $dataSchedules, '[[your-name-title]]', __( 'Your name', 'competitive-scheduling' ) );
                $dataSchedules = Templates::change_variable( $dataSchedules, '[[your-name]]', $user_name );
                
                // Companion details.
                global $wpdb;
                $query = $wpdb->prepare(
                    "SELECT name 
                    FROM {$wpdb->prefix}schedules_companions 
                    WHERE id_schedules = '%s' AND user_id = '%s'",
                    array( $schedule_id, $user_id )
                );
                $schedules_companions = $wpdb->get_results( $query );
                
                // Set up the companions' cell.
                $num = 0;
                if( $schedules_companions ){
                    foreach( $schedules_companions as $companion ){
                        $num++;

                        $cell_aux = $cell['cell-data'];

                        $cell_aux = Templates::change_variable( $cell_aux, '[[companion-title]]', __( 'Companion', 'competitive-scheduling' ) . ' ' . $num );
                        $cell_aux = Templates::change_variable( $cell_aux, '[[companion]]', $companion->name );

                        $dataSchedules = Templates::variable_in( $dataSchedules, '<!-- cell-data -->', $cell_aux );
                    }
                }

                // Response data
                $response = array(
                    'status' => 'OK',
                    'dataSchedules' => $dataSchedules,
                    'nonce' => wp_create_nonce( 'companions-nonce' ),
                );
            } else {
                // Response data
                $response = array(
                    'status' => 'ERROR',
                    'alert' => __( 'User is not logged in', 'competitive-scheduling' ),
                );
            }

            return rest_ensure_response( $response );
        }
    }
}