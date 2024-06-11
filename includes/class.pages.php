<?php

if( ! class_exists( 'Pages' ) ){
    class Pages {
        /**
         * To activate default pages and install pages if necessary.
         *
         * @return void
         */

        public static function activate(){
            // Get default page settings.
            $pages_options = get_option('competitive_scheduling_pages_options');

            // Checks whether the default pages already exist. If not, include the page data for each of the default pages.
            if( ! empty( $pages_options ) ){
                if( ! empty( $pages_options['schedule-page-id'] ) ){
                    if( $pages_options['schedule-page-id'] == '0' ){
                        $pages_data['schedules'] = array(
                            'post_title' => __( 'Schedules', 'competitive-scheduling' ),
                            'post_content' => "<!-- wp:shortcode -->
[competitive_scheduling]
<!-- /wp:shortcode -->",
                            'post_status' => 'publish',
                            'post_type' => 'page',
                            'post_name' => __( 'schedules', 'competitive-scheduling' ),
                        );
                    }
                }

                if( ! empty( $pages_options['schedule-public-page-id'] ) ){
                    if( $pages_options['schedule-public-page-id'] == '0' ){
                        $pages_data['schedules-public'] = array(
                            'post_title' => __( 'Schedules Public', 'competitive-scheduling' ),
                            'post_content' => "<!-- wp:shortcode -->
[competitive_scheduling_public]
<!-- /wp:shortcode -->",
                            'post_status' => 'publish',
                            'post_type' => 'page',
                            'post_name' => __( 'schedules-public', 'competitive-scheduling' ),
                        );
                    }
                }
            }

            // Insert default pages.
            if( ! empty( $pages_data ) ){
                foreach( $pages_data as $key => $page_data ){
                    $page_id = wp_insert_post( $page_data );

                    if( $page_id ){
                        update_option( 'competitive_scheduling_pages_options['. $key. ']', $page_id );
                    }
                }
            }
        }

        /**
         * To uninstall default pages.
         *
         * @return void
         */

        public static function uninstall(){
            // Get default page settings.
            $pages_options = get_option('competitive_scheduling_pages_options');

            // Checks whether the default pages already exist. If so, delete them.
            if( ! empty( $pages_options ) ){
                if( ! empty( $pages_options['schedule-page-id'] ) ){
                    if( $pages_options['schedule-page-id'] != '0' ){
                        wp_delete_post( absint( $pages_options['schedule-page-id'] ), true );
                    }
                }

                if( ! empty( $pages_options['schedule-public-page-id'] ) ){
                    if( $pages_options['schedule-public-page-id'] != '0' ){
                        // Delete the post permanently
                        wp_delete_post( absint( $pages_options['schedule-public-page-id'] ), true );
                    }
                }
            }
        }
    }
}