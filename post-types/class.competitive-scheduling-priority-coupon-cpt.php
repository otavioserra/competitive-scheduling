<?php 

if( !class_exists( 'Competitive_Scheduling_Priority_Coupon_Post_Type') ){
    class Competitive_Scheduling_Priority_Coupon_Post_Type{
        function __construct(){
            add_action( 'init', array( $this, 'create_post_type' ) );
            add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
            add_action( 'save_post_' . 'priority-coupon', array( $this, 'change_coupons' ), 10, 2 );
        }

        public function create_post_type(){
            register_post_type(
                'priority-coupon',
                array(
                    'label' => esc_html__( 'Priority Coupon', 'competitive-scheduling' ),
                    'description'   => esc_html__( 'Priority Coupons', 'competitive-scheduling' ),
                    'labels' => array(
                        'name'  => esc_html__( 'Priority Coupons', 'competitive-scheduling' ),
                        'singular_name' => esc_html__( 'Priority Coupon', 'competitive-scheduling' ),
                    ),
                    'public'    => false,
                    'supports'  => array( 'title' ),
                    'hierarchical'  => false,
                    'show_ui'   => true,
                    'show_in_menu'  => false,
                    'menu_position' => 5,
                    'show_in_admin_bar' => true,
                    'show_in_nav_menus' => true,
                    'can_export'    => true,
                    'has_archive'   => false,
                    'exclude_from_search'   => true,
                    'publicly_queryable'    => false,
                    'show_in_rest'  => true,
                    'menu_icon' => 'dashicons-calendar-alt',
                )
            );
        }

        public function add_meta_boxes(){
            add_meta_box(
                'work_schedule_meta_box',
                esc_html__( 'Priority Coupon Fields', 'competitive-scheduling' ),
                array( $this, 'add_inner_meta_boxes' ),
                'priority-coupon',
                'normal',
                'high'
            );
        }

        public function add_inner_meta_boxes( $post ){
            require_once( CS_PATH . 'views/competitive-scheduling_metabox.php' );
        }

        public function change_coupons( $post_id, $post ) {
            // Flag to identify whether this is a new post or an update or trash
            $action = '';

            // Find out which operation is being done by the save_post hook: add, update, delete.
            $is_new = $post->post_date === $post->post_modified;
            if ( $is_new && $post->post_status === 'publish' ) {
                $action = 'add';
            } else if ( $post->post_status === 'publish' ){
                $action = 'update';
            } else if ( $post->post_status === 'trash' ){
                $action = 'delete';
            }
            
            // Do coupon changes based on the action.
            switch($action){
                case 'add':

                    break;
                case 'update':

                    break;
                case 'delete':

                    break;
            }
        }

    }
}