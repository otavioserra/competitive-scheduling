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
        function __construct(){
            $this->define_constants();

            add_action( 'admin_menu', array( $this, 'add_menu' ) );

            require_once( COMP_SCHEDULE_PATH . 'post-types/class.competitive-scheduling-priority-coupon-cpt.php' );
            $Competitive_Scheduling_Priority_Coupon_Post_Type = new Competitive_Scheduling_Priority_Coupon_Post_Type();
        }

        public function define_constants(){
            define( 'COMP_SCHEDULE_PATH', plugin_dir_path( __FILE__ ) );
            define( 'COMP_SCHEDULE_URL', plugin_dir_url( __FILE__ ) );
            define( 'COMP_SCHEDULE_VERSION', '1.0.0' );
            define( 'COMP_SCHEDULE_DEBUG', true );
        }

        public static function activate(){
            update_option( 'rewrite_rules', '' );
        }

        public static function deactivate(){
            flush_rewrite_rules();
            unregister_post_type( 'competitive-scheduling' );
        }

        public static function uninstall(){

        }

        public function add_menu(){
            add_menu_page(
                esc_html__( 'Competitive Scheduling', 'competitive-scheduling' ),
                esc_html__( 'Competitive Scheduling', 'competitive-scheduling' ),
                'manage_options',
                'competitive_scheduling_admin',
                array( $this, 'competitive_scheduling_page' ),
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

            /*add_submenu_page(
                'competitive_scheduling_admin',
                esc_html__( 'Competitive Scheduling Options', 'competitive-scheduling' ),
                esc_html__( 'Competitive Scheduling Options', 'competitive-scheduling' ),
                'manage_options',
                array( $this, 'competitive_scheduling_settings_page' ),
                null,
                null
            );*/

        }

        public function competitive_scheduling_page(){
            if( ! current_user_can( 'manage_options' ) ){
                return;
            }

            wp_enqueue_style( 'fomantic-ui', COMP_SCHEDULE_URL . 'vendor/fomantic-UI@2.9.0/dist/semantic.min.css', array(  ), COMP_SCHEDULE_VERSION );
            wp_enqueue_script( 'fomantic-ui', COMP_SCHEDULE_URL . 'vendor/fomantic-UI@2.9.0/dist/semantic.min.js', array( 'jquery' ), COMP_SCHEDULE_VERSION );
            
            wp_enqueue_style( 'competitive-scheduling-admin', COMP_SCHEDULE_URL . 'assets/css/admin.css', array(  ), ( COMP_SCHEDULE_DEBUG ? filemtime( COMP_SCHEDULE_PATH . 'assets/css/admin.css' ) : COMP_SCHEDULE_VERSION ) );
            wp_enqueue_script( 'competitive-scheduling-admin', COMP_SCHEDULE_URL . 'assets/js/admin.js', array( 'jquery' ), ( COMP_SCHEDULE_DEBUG ? filemtime( COMP_SCHEDULE_PATH . 'assets/js/admin.js' ) : COMP_SCHEDULE_VERSION ) );

            require( COMP_SCHEDULE_PATH . 'views/competitive-scheduling-page.php' );
        }

        public function competitive_scheduling_settings_page(){
            if( ! current_user_can( 'manage_options' ) ){
                return;
            }

            if( isset( $_GET['settings-updated'] ) ){
                add_settings_error( 'competitive_scheduling_options', 'competitive_scheduling_message', esc_html__( 'Settings Saved', 'competitive-scheduling' ), 'success' );
            }
            
            settings_errors( 'competitive_scheduling_options' );

            require( COMP_SCHEDULE_PATH . 'views/settings-page.php' );
        }
    }
}

if( class_exists( 'Competitive_Scheduling' ) ){
    register_activation_hook( __FILE__, array( 'Competitive_Scheduling', 'activate' ) );
    register_deactivation_hook( __FILE__, array( 'Competitive_Scheduling', 'deactivate' ) );
    register_uninstall_hook( __FILE__, array( 'Competitive_Scheduling', 'uninstall' ) );

    $competitive_scheduling = new Competitive_Scheduling();
} 