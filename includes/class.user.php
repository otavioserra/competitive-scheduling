<?php

if( ! class_exists( 'User' ) ){
    class User {

        /**
         * Get user's first and last name, else just their first name, else their
         * display name. Defaults to the current user if $user_id is not provided.
         *
         * @param mixed $user_id The user ID or object. Default is current user.
         * 
         * @return string the user's name.
         */

        public static function get_name( $user_id = null ) {
            $user_info = $user_id ? new WP_User( $user_id ) : wp_get_current_user();
        
            if ( $user_info->first_name ) {
                if ( $user_info->last_name ) {
                    return $user_info->first_name . ' ' . $user_info->last_name;
                }
            
                return $user_info->first_name;
            }
        
            return $user_info->display_name;
        }

        /**
         * Get user's email.
         *
         * @param mixed $user_id The user ID or object. Default is current user.
         * 
         * @return string the user's email.
         */

        public static function get_email( $user_id = null ) {
            $user_info = $user_id ? new WP_User( $user_id ) : wp_get_current_user();
        
            if ( $user_info->user_email ) {
                           
                return $user_info->user_email;
            }
        
            return '';
        }
    }
}