<?php 

if( ! class_exists('Competitive_Scheduling_Shortcode')){
    class Competitive_Scheduling_Shortcode{
        public function __construct(){
            add_shortcode( 'competitive_scheduling', array( $this, 'add_shortcode' ) );
        }

        public function add_shortcode( $atts = array(), $content = null, $tag = '' ){

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

            ob_start();
            require( COMP_SCHEDULE_PATH . 'views/competitive-scheduling_shortecode.php' );
            return ob_get_clean();
        }
    }
}