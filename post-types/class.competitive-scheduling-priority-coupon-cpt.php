<?php 

if( !class_exists( 'Competitive_Scheduling_Priority_Coupon_Post_Type') ){
    class Competitive_Scheduling_Priority_Coupon_Post_Type{
        function __construct(){
            add_action( 'init', array( $this, 'create_post_type' ) );
            add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
            add_action( 'save_post_' . 'priority-coupon', array( $this, 'change_coupons' ), 10, 1 );
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

        public function change_coupons( $post_id ) {
            // Obter o valor do action
            $action = current_action();
            
            switch ($action) {
                case 'save_post_' . 'priority-coupon':
                case 'edit_post_' . 'priority-coupon':
                    $quantity = get_post_meta( $post_id, 'cs_quantity', true );
                        
                    break;
                case 'delete_post_' . 'priority-coupon':
                    
                    
                    break;
            }

            echo 'Ação: '.$action;

        }

    }
}