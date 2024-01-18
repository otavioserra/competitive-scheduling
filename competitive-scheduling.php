<?php

/**
 * Plugin Name: Competitive Scheduling
 * Plugin URI: https://www.wordpress.org/competitive-scheduling
 * Description: Competitive Scheduling plugin allows you to schedule events competitively against other users. This is good to places that have more people interested in the same time slots than available slots. In cases that have less slots than people interested, this plugin will help you schedule events fairly based on random draws.
 * Version: 1.0
 * Requires at least: 5.6
 * Author: Otávio Campos de Abreu Serra
 * Author URI: https://www.ageone.com.br/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: competitive-scheduling
 * Domain Path: /languages
 */

 /*
Competitive Scheduling is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
Competitive Scheduling is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with Competitive Scheduling. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

if( ! defined( 'ABSPATH') ){
    exit;
}

if( ! class_exists( 'Competitive_Scheduling' ) ){
    class Competitive_Scheduling{
        public $objects = array();

        function __construct(){
            $this->define_constants();

            add_action( 'admin_menu', array( $this, 'add_menu' ) );

            require_once( CS_PATH . 'post-types/class.competitive-scheduling-priority-coupon-cpt.php' );
            $Competitive_Scheduling_Priority_Coupon_Post_Type = new Competitive_Scheduling_Priority_Coupon_Post_Type();

            require_once( CS_PATH . 'shortcodes/class.competitive-scheduling-shortcode.php' );
            $Competitive_Scheduling_Shortcode = new Competitive_Scheduling_Shortcode();

            require_once( CS_PATH . 'settings/class.competitive-scheduling-settings.php' );
            $this->objects['Competitive_Scheduling_Settings'] = new Competitive_Scheduling_Settings();
        }

        private function define_constants(){
            define( 'CS_ID', 'Competitive_Scheduling' );
            define( 'CS_PATH', plugin_dir_path( __FILE__ ) );
            define( 'CS_URL', plugin_dir_url( __FILE__ ) );
            define( 'CS_VERSION', '1.0.0' );
            define( 'CS_DEBUG', true );
            define( 'CS_EMAIL_ACTIVE', false );
            define( 'CS_FORCE_DATE_TODAY', true );
            define( 'CS_DATE_TODAY_FORCED_VALUE', '2024-01-25' );
            define( 'CS_NOUNCE_SCHEDULES', 'cs-nouce-schedules' );
            define( 'CS_NOUNCE_SCHEDULES_EXPIRES', 86400*45 );
            define( 'CS_NUM_RECORDS_PER_PAGE', 20 );
            define( 'CS_MAX_EMAILS_PER_CYCLE', 50 ); // Maximum number of emails sent per scheduled task execution cycle
            define( 'CS_TIME_NEXT_CYCLE_AFTER_EMAIL_PER_CYCLE_REACH', 15 ); // Number of minutes to run a new cycle of sending confirmation emails
            define( 'CS_MAX_RERUN_CYCLES', 20 ); // Maximum number of executions allowed to send all emails before stopping
        }

        public static function activate(){
            require_once( CS_PATH . 'includes/class.database.php' );
            require_once( CS_PATH . 'includes/class.authentication.php' );
            require_once( CS_PATH . 'includes/class.cron.php' );
            
            update_option( 'rewrite_rules', '' );

            Competitive_Scheduling_Settings::register_settings();
            Database::update_database();
            Authentication::install_keys();
            Cron::activate();
        }

        public static function desactivate(){
            require_once( CS_PATH . 'includes/class.cron.php' );

            flush_rewrite_rules();
            unregister_post_type( 'competitive-scheduling' );
            Cron::desactivate();
        }

        public static function uninstall(){
            require_once( CS_PATH . 'includes/class.authentication.php' );
            
            Competitive_Scheduling_Settings::unregister_settings();
            Authentication::uninstall_keys();
        }

        public function add_menu(){
            require_once( CS_PATH . 'pages/class.admin-page.php' );
            $this->objects['Competitive_Scheduling_Admin_Page'] = new Competitive_Scheduling_Admin_Page();

            add_menu_page(
                esc_html__( 'Competitive Scheduling Management', 'competitive-scheduling' ),
                esc_html__( 'Competitive Scheduling', 'competitive-scheduling' ),
                'manage_options',
                'competitive_scheduling_admin',
                array( $this->objects['Competitive_Scheduling_Admin_Page'], 'page' ),
                'dashicons-calendar-alt'
            );

            add_submenu_page(
                'competitive_scheduling_admin',
                esc_html__( 'Priority Coupons', 'competitive-scheduling' ),
                esc_html__( 'Priority Coupons', 'competitive-scheduling' ),
                'manage_options',
                'edit.php?post_type=priority-coupon',
                null,
                null
            );

            add_submenu_page(
                'competitive_scheduling_admin',
                esc_html__( 'Add Priority Coupon', 'competitive-scheduling' ),
                esc_html__( 'Add Priority Coupon', 'competitive-scheduling' ),
                'manage_options',
                'post-new.php?post_type=priority-coupon',
                null,
                null
            );

            add_submenu_page(
                'competitive_scheduling_admin',
                esc_html__( 'Competitive Scheduling Options', 'competitive-scheduling' ),
                esc_html__( 'Options', 'competitive-scheduling' ),
                'manage_options',
                'competitive_scheduling_settings',
                array( $this->objects['Competitive_Scheduling_Settings'], 'page' ),
                null,
                null
            );
        }
    }
}

if( class_exists( 'Competitive_Scheduling' ) ){
    register_activation_hook( __FILE__, array( 'Competitive_Scheduling', 'activate' ) );
    register_deactivation_hook( __FILE__, array( 'Competitive_Scheduling', 'desactivate' ) );
    register_uninstall_hook( __FILE__, array( 'Competitive_Scheduling', 'uninstall' ) );

    $Competitive_Scheduling = new Competitive_Scheduling();
} 