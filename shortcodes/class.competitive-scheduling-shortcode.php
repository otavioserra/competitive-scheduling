<?php 

if( ! class_exists('Competitive_Scheduling_Shortcode')){
    class Competitive_Scheduling_Shortcode{
        public function __construct(){
            add_shortcode( 'competitive_scheduling', array( $this, 'add_shortcode' ) );
        }

        public function add_shortcode( $atts = array(), $content = null, $tag = '' ){
            // Check if the user is logged in
            if ( ! is_user_logged_in() ) {
                // Checks if the Ultimate Member plugin is active
                if ( is_plugin_active( 'ultimate-member/ultimate-member.php' ) ) {
                    // The plugin is active
                    
                    // Redirects to the Ultimate Member login page
                    wp_redirect( home_url( '/login' ) );
                } else {
                    // The plugin is not active
                
                    // Redirects to the default WordPress login page
                    wp_redirect( wp_login_url() );
                }

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

            wp_enqueue_style( 'fomantic-ui', CS_URL . 'vendor/fomantic-UI@2.9.0/dist/semantic.min.css', array(  ), CS_VERSION );
            wp_enqueue_script( 'fomantic-ui', CS_URL . 'vendor/fomantic-UI@2.9.0/dist/semantic.min.js', array( 'jquery' ), CS_VERSION );
            
            wp_enqueue_style( 'competitive-scheduling', CS_URL . 'assets/css/shortecode.css', array(  ), ( CS_DEBUG ? filemtime( CS_PATH . 'assets/css/shortecode.css' ) : CS_VERSION ) );
            wp_enqueue_script( 'competitive-scheduling', CS_URL . 'assets/js/shortecode.js', array( 'jquery' ), ( CS_DEBUG ? filemtime( CS_PATH . 'assets/js/shortecode.js' ) : CS_VERSION ) );

            // Get page view and return processed page

            ob_start();
            require( CS_PATH . 'views/competitive-scheduling_shortecode.php' );

            return $this->shortcode_page(ob_get_clean());
        }

        private function shortcode_page( $page ){
            // Verify if page is defined
            if( empty($page) ){
                return '';
            }

            // Get the configuration data.
            $options = get_option( 'competitive_scheduling_options' );
            $msg_options = get_option( 'competitive_scheduling_msg_options' );

            $activation = (isset($options['activation']) ? true : false);
            $msgAgendamentoSuspenso = (isset($config['msg-agendamento-suspenso']) ? $config['msg-agendamento-suspenso'] : '');

            // Require templates class to manipulate page.
            require_once( CS_PATH . 'includes/class.templates.php' );

            $page = Templates::change_variable($page, '#teste-var#', '<h1>Hello World!</h1>');
        }

        private function nonce_verify( $nonce ){
            // Verifiying nonce
            if( isset( $_POST[$nonce] ) ){
                if( ! wp_verify_nonce( $_POST[$nonce], $nonce ) ){
                    $noNonce = true;
                }
            } else {
                $noNonce = true;
            }
            
            // If nonce is invalid, redirect to home
            if( isset( $noNonce ) ){
                wp_redirect( home_url( '/' ) );
            }
        }

        private function action_schedule( $params = false ){
            if( $params ) foreach( $params as $var => $val ) $$var = $val;
        }
    }
}