<?php 

if( ! class_exists( 'Competitive_Scheduling_Public' ) ){
    class Competitive_Scheduling_Public {
        public function __construct(){
            add_action( 'admin_post_schedule_cancellation', array( $this, 'redirect_to_public_page' ) );
            add_action( 'admin_post_schedule_confirmation', array( $this, 'redirect_to_public_page' ) );
        }

        public function redirect_to_public_page(){
            // Get all variables sent in the query string.
            if( ! empty( $_GET ) ) $query_string = http_build_query( $_GET ); else $query_string = '';

            // Get the ID of the public schedule page.
            $pages_options = get_option('competitive_scheduling_pages_options');

            if( ! empty( $pages_options['schedule-public-page-id'] ) ){
                $page_id = $pages_options['schedule-public-page-id'];
            } else {
                $page_id = 0;
            }

            // Redirect to public schedule page or to home page if the page does not exist.
            if( $page_id != 0 && $page_id != '0' ) { 
                $page_url = get_permalink( $page_id );
                wp_redirect( $page_url . '?'. $query_string );
            } else {
                wp_redirect( home_url() );
            }

            exit;
        }
    }
}