<?php 

if( ! class_exists('Competitive_Scheduling_Shortcode')){
    class Competitive_Scheduling_Shortcode{
        public function __construct(){
            add_shortcode( 'competitive_scheduling', array( $this, 'add_shortcode' ) );
        }

        public function add_shortcode( $atts = array(), $content = null, $tag = '' ){
            // Check if the user is logged in
            if ( ! is_user_logged_in() ) {
                // Redirect to the login page
                wp_redirect( wp_login_url() );
                exit;
            }

            $atts = array_change_key_case( (array) $atts, CASE_LOWER );

            extract( shortcode_atts(
                array(
                    'id' => '',
                    'orderby' => 'date'
                ),
                $atts,
                $tag
            ));

            if( !empty( $id ) ){
                $id = array_map( 'absint', explode( ',', $id ) );
            }

            wp_enqueue_style( 'fomantic-ui', COMP_SCHEDULE_URL . 'vendor/fomantic-UI@2.9.0/dist/semantic.min.css', array(  ), COMP_SCHEDULE_VERSION );
            wp_enqueue_script( 'fomantic-ui', COMP_SCHEDULE_URL . 'vendor/fomantic-UI@2.9.0/dist/semantic.min.js', array( 'jquery' ), COMP_SCHEDULE_VERSION );
            
            wp_enqueue_style( 'competitive-scheduling', COMP_SCHEDULE_URL . 'assets/css/shortecode.css', array(  ), ( COMP_SCHEDULE_DEBUG ? filemtime( COMP_SCHEDULE_PATH . 'assets/css/shortecode.css' ) : COMP_SCHEDULE_VERSION ) );
            wp_enqueue_script( 'competitive-scheduling', COMP_SCHEDULE_URL . 'assets/js/shortecode.js', array( 'jquery' ), ( COMP_SCHEDULE_DEBUG ? filemtime( COMP_SCHEDULE_PATH . 'assets/js/shortecode.js' ) : COMP_SCHEDULE_VERSION ) );

            ob_start();
            require( COMP_SCHEDULE_PATH . 'views/competitive-scheduling_shortecode.php' );
            return ob_get_clean();
        }
    }
}