<?php 

if( !class_exists( 'Competitive_Scheduling_Priority_Coupon_Post_Type') ){
    class Competitive_Scheduling_Priority_Coupon_Post_Type{
        
        var $cpt_id = 'priority-coupon';
        var $nounce = 'cs_nonce_coupon';

        function __construct(){
            add_action( 'init', array( $this, 'create_post_type' ) );
            add_filter( 'manage_' . self::$cpt_id . '_posts_columns', array( $this, 'posts_columns' ) );
            add_action( 'manage_' . self::$cpt_id . '_posts_custom_column', array( $this, 'posts_custom_column'), 10, 2 );
            add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
            add_action( 'save_post_' . self::$cpt_id, array( $this, 'save_post' ), 10, 2 );
        }

        public function create_post_type(){
            register_post_type(
                $this->$cpt_id,
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

        public function posts_columns( $columns ){
            $columns['cs_quantity'] = esc_html__( 'Quantity', 'competitive-scheduling' );
            $columns['cs_valid_from'] = esc_html__( 'Valid From', 'competitive-scheduling' );
            $columns['cs_valid_until'] = esc_html__( 'Valid Until', 'competitive-scheduling' );
            return $columns;
        }

        public function posts_custom_column( $column, $post_id ){
            switch( $column ){
                case 'cs_quantity':
                    echo esc_html( get_post_meta( $post_id, 'cs_quantity', true ) );
                break;
                case 'cs_valid_from':
                    echo esc_url( get_post_meta( $post_id, 'cs_valid_from', true ) );
                break; 
                case 'cs_valid_until':
                    echo esc_url( get_post_meta( $post_id, 'cs_valid_until', true ) );
                break;                
            }
        }

        public function add_meta_boxes(){
            add_meta_box(
                $this->$cpt_id . '-metabox',
                esc_html__( 'Priority Coupon Fields', 'competitive-scheduling' ),
                array( $this, 'add_inner_meta_boxes' ),
                $this->$cpt_id,
                'normal',
                'high'
            );
        }

        public function add_inner_meta_boxes( $post ){
            wp_enqueue_style( 'fomantic-ui', CS_URL . 'vendor/fomantic-UI@2.9.0/dist/semantic.min.css', array(  ), CS_VERSION );
            wp_enqueue_script( 'fomantic-ui', CS_URL . 'vendor/fomantic-UI@2.9.0/dist/semantic.min.js', array( 'jquery' ), CS_VERSION );

            wp_enqueue_style( 'coupon-css', CS_URL . 'assets/css/coupon.css', array(  ), ( CS_DEBUG ? filemtime( CS_PATH . 'assets/css/coupon.css' ) : CS_VERSION ) );

            require_once( CS_PATH . 'views/competitive-scheduling_metabox.php' );
        }

        public function save_post( $post_id, $post ) {
            // Check the nounce, if it is not auto-save and if it is a save_post of the required cpt.
            if( isset( $_POST[$this->$nounce] ) ){
                if( ! wp_verify_nonce( $_POST[$this->$nounce], $this->$nounce ) ){
                    return;
                }
            }

            if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
                return;
            }

            if( isset( $_POST['post_type'] ) && $_POST['post_type'] === $this->$cpt_id ){
                if( ! current_user_can( 'edit_page', $post_id ) ){
                    return;
                }elseif( ! current_user_can( 'edit_post', $post_id ) ){
                    return;
                }
            }

            // Check if it is an edit of a post.
            if( isset( $_POST['action'] ) && $_POST['action'] == 'editpost' ){
                // Save all metabox fields.
                $fields_ids = array(
                    'cs_quantity', 
                    'cs_valid_from',
                    'cs_valid_until',
                );

                foreach ($fields_ids as $id) {
                    $old[$id] = get_post_meta( $post_id, $id, true );
                    $new[$id] = $_POST[$id];
                }

                foreach ($new as $key => $value) {
                    if( empty( $value )){
                        update_post_meta( $post_id, $key, '' );
                    } else {
                        update_post_meta( $post_id, $key, sanitize_text_field( $value ), $old[$key] );
                    }
                }

                // Get the quantity value to create or update coupons. But only do this if the values are modified.
                if( $new['cs_quantity'] != $old['cs_quantity'] ){
                    $quantity = $new['cs_quantity'];
                
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
    }
}