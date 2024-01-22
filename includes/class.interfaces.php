<?php

if( ! class_exists( 'Interfaces' ) ){
    class Interfaces {
        
        /**
         * Alert the user of any system message on the screen.
         *
         * @param string $msg Include a message to be alerted on the user's next screen.
         * @param string $redirect Only allow printing after redirect.
         * @param string $print Print the alert on the screen.
         *
         * @return void
         */

        public static function alert( $params = false ){
            if( $params ) foreach( $params as $var => $val ) $$var = $val;
            
            global $_MANAGER;

            if( isset( $msg ) ){
                if( isset( $redirect ) ){
                    add_option( 'cs_interface_alert', array(
                        'msg' => $msg,
                    ) );
                    
                    $_MANAGER['interface-alert-not-printing'] = true;
                } else {
                    $_MANAGER['alert-page'] = array(
                        'msg' => $msg,
                    );
                }
            }
            
            if( isset( $print ) ){
                if( ! isset( $_MANAGER['interface-alert-not-printing'] ) ){
                    if( empty( get_option( 'cs_interface_alert_redirect' ) ) ){
                        if( ! empty( get_option( 'cs_interface_alert' ) ) ){
                            $alert = get_option( 'cs_interface_alert' );
                            delete_option( 'cs_interface_alert' );
                        } else if( isset( $_MANAGER['alert-page'] ) ){
                            $alert = $_MANAGER['alert-page'];
                        }
                        
                        if( isset( $alert ) ){
                            if( ! isset( $_MANAGER['javascript-vars']['interface'] ) ){
                                $_MANAGER['javascript-vars']['interface'] = Array();
                            }
                            
                            $_MANAGER['javascript-vars']['interface']['alert'] = $alert;
                            
                            self::components_include(Array(
                                'component' => Array(
                                    'modal-alert',
                                )
                            ));
                        }
                    }
                } else {
                    unset( $_MANAGER['interface-alert-not-printing'] );
                }
            }
        }

        /**
         * Add components to the interface.
         *
         * @param string|array $component unique identifier of the component or an array of unique identifiers of all components that will be included.
         *
         * @return void
         */

        public static function components_include( $params = false ){
            if( $params ) foreach( $params as $var => $val ) $$var = $val;

            global $_MANAGER;
        
            if( isset( $component ) ){
                switch( gettype( $component ) ){
                    case 'array':
                        if( count( $component ) > 0 ){
                            foreach( $component as $com ){
                                $_MANAGER['interface']['components'][$com] = true;
                            }
                        }
                    break;
                    default:
                        $_MANAGER['interface']['components'][$component] = true;
                }
            }
        }

        /**
         * Get component template value.
         *
         * @param string $component unique identifier of the component template that will be returned.
         *
         * @return string
         */

        public static function get_component( $component = '' ){
            // Read component content.
            return file_get_contents( CS_PATH . 'includes/components/' . $component . '.html' );
        }

        /**
         * Includes the components on the page and defines the components' JS variables.
         *
         * @param string $page HTML code of the page where the components will be included.
         *
         * @return string
         */

        public static function components( $page = '' ){
            global $_MANAGER;

            if( isset( $_MANAGER['interface'] ) ){
                if( isset( $_MANAGER['interface']['components'] ) ){
                    // Load layout of all components.
                    $components = $_MANAGER['interface']['components'];
                    
                    if( count( $components ) > 0 ){
                        foreach( $components as $component => $val ){
                            $layouts[$component] = self::get_component( $component );
                        }
                    }
                    
                    if( isset( $layouts ) ){
                        // Require templates class to manipulate page.
                        require_once( CS_PATH . 'includes/class.templates.php' );

                        $variables_js = Array();
                        
                        foreach( $layouts as $id => $layout ){
                            $component_html = '';
                            
                            switch( $id ){
                                // Modal deletion.
                                case 'modal-deletion':
                                    $component_html = $layout;
                                    
                                    $component_html = Templates::change_variable( $component_html, '#title#', self::component_text( 'delete-confirm-title' ) );
                                    $component_html = Templates::change_variable( $component_html, '#message#', self::component_text( 'delete-confirm-message' ) );
                                    $component_html = Templates::change_variable( $component_html, '#button-cancel#', self::component_text( 'delete-confirm-button-cancel' ) );
                                    $component_html = Templates::change_variable( $component_html, '#button-confirm#', self::component_text( 'delete-confirm-button-confirm' ) );
                                    
                                    break;
                                    
                                // Modal alert.
                                case 'modal-alert':
                                    $component_html = $layout;
                                        
                                    $component_html = Templates::change_variable( $component_html, '#title#', self::component_text( 'alert-title' ) );
                                    $component_html = Templates::change_variable( $component_html, '#button-ok#', self::component_text( 'alert-button-ok' ) );

                                    $variables_js['ajaxTimeoutMessage'] = self::component_text( 'ajax-timeout-message' );
                                    
                                    break;

                                // Modal iframe.
                                case 'modal-iframe':
                                    $component_html = $layout;
                                    
                                    $component_html = Templates::change_variable( $component_html, '#title#', self::component_text( 'iframe-title' ) );
                                    $component_html = Templates::change_variable( $component_html, '#button-cancel#', self::component_text( 'iframe-button-cancel' ) );
                                    
                                    break;
                                // Modal info.
                                case 'modal-info':
                                    $component_html = $layout;
                                    
                                    $component_html = Templates::change_variable( $component_html, '#title#', self::component_text( 'info-title' ) );
                                    
                                    break;

                                // Modal loading.
                                case 'modal-loading':
                                    $component_html = $layout;
                                    
                                    $component_html = Templates::change_variable( $component_html, '#title#', self::component_text( 'loading-title' ) );
                                    
                                    break;
                                
                                default:
                                    $component_html = $layout;
                            }
                            
                            if( ! empty( $component_html ) ){
                                $page .= $component_html;
                            }
                        }
                        
                        $_MANAGER['javascript-vars']['components'] = $variables_js;
                    }
                }
            }

            return $page;
        }
        
        /**
         * Returns the default text for each field in the component
         *
         * @param string $id text identifier.
         * 
         * @return string
         */

        public static function component_text( $id ){
            $texts = [
                'delete-confirm-title' => __( 'Deletion Confirmation', 'competitive-scheduling' ),
                'delete-confirm-message' => __( 'Are you sure you want to delete this item?', 'competitive-scheduling' ),
                'delete-confirm-button-cancel' => __( 'Cancel', 'competitive-scheduling' ),
                'delete-confirm-button-confirm' => __( 'Confirm', 'competitive-scheduling' ),
                'alert-title' => __( 'Alert', 'competitive-scheduling' ),
                'alert-button-ok' => __( 'Ok', 'competitive-scheduling' ),
                'ajax-timeout-message' => __( 'The attempt to connect to the server has reached its time limit. There was some failure in your connection to it or there was a programming error that was not automatically detectable. Try again, if the problem persists, please contact technical support.', 'competitive-scheduling' ),
                'iframe-title' => __( 'Iframe', 'competitive-scheduling' ),
                'iframe-button-cancel' => __( 'Cancel', 'competitive-scheduling' ),
                'info-title' => __( 'Information', 'competitive-scheduling' ),
                'loading-title' => __( 'Loading', 'competitive-scheduling' ),
            ];

            return ( ! empty( $texts[$id] ) ? $texts[$id] : '' );
        }

        /**
         * Finish interface
         * 
         * @return string
         */

        public static function finish(){
            global $_MANAGER;

            // Print alert on user screen.
            Interfaces::alert( array( 'print' => true ) );
            
            // Interfaces javascript vars.
            if( ! isset( $_MANAGER['javascript-vars']['interface'] ) ){
                $_MANAGER['javascript-vars']['interface'] = Array();
            }

            wp_add_inline_script( 'competitive-scheduling', '
                var manager = '.json_encode( $_MANAGER['javascript-vars'] ).';
            ','before');

            // Action to include components at the beginning of the BODY tag
            //add_action( 'wp_body_open', array( __CLASS__, 'components_html' ) );
        }

        /**
         * Echo the components html
         * 
         * @return string
         */

        public static function components_html(){
            echo Interfaces::components();
        }
    }
}