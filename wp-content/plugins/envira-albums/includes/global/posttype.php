<?php
/**
 * Posttype class.
 *
 * @since 1.0.0
 *
 * @package Envira_Album
 * @author  Envira Team
 */
class Envira_Albums_Posttype {

    /**
     * Holds the class object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public static $instance;

    /**
     * Path to the file.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Holds the base class object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public $base;

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        $envira_whitelabel = apply_filters('envira_whitelabel', false );

        // Build the labels for the post type.
        $labels = apply_filters( 'envira_albums_post_type_labels',
            array(
                'name'               => $envira_whitelabel ? apply_filters('envira_album_whitelabel_name_plural', false ) : __( 'Envira Albums', 'envira-gallery' ),
                'name_admin_bar'     => $envira_whitelabel ? apply_filters('envira_album_whitelabel_name_plural', false ) : __( 'Envira Albums', 'envira-gallery' ),
                'singular_name'      => $envira_whitelabel ? apply_filters('envira_album_whitelabel_name', false ) : __( 'Envira Gallery', 'envira-gallery' ),
                'add_new'            => __( 'Add New', 'envira-albums' ),
                'add_new_item'       => $envira_whitelabel ? __( 'Add New Album', 'envira-gallery' ) :__( 'Add New Envira Album', 'envira-albums' ),
                'edit_item'          => $envira_whitelabel ? __( 'Edit Album', 'envira-gallery' ) :__( 'Edit Envira Album', 'envira-albums' ),
                'new_item'           => $envira_whitelabel ? __( 'New Album', 'envira-gallery' ) :__( 'New Envira Album', 'envira-albums' ),
                'view_item'          => $envira_whitelabel ? __( 'View Album', 'envira-gallery' ) :__( 'View Envira Album', 'envira-albums' ),
                'search_items'       => $envira_whitelabel ? __( 'Search Albums', 'envira-gallery' ) : __( 'Search Envira Albums', 'envira-gallery' ),
                'not_found'          => $envira_whitelabel ? __( 'No albums found.', 'envira-gallery' ) :__( 'No Envira albums found.', 'envira-albums' ),
                'not_found_in_trash' => $envira_whitelabel ? __( 'No albums found in trash.', 'envira-gallery' ) : __( 'No Envira albums found in trash.', 'envira-albums' ),
                'parent_item_colon'  => '',
                'menu_name'          => __( 'Albums', 'envira-albums' )
            )
        );

        // Build out the post type arguments.
        $args = apply_filters( 'envira_albums_post_type_args',
            array(
                'labels'              => $labels,
                'public'              => false,
                'exclude_from_search' => false,
                'show_ui'             => true,
                'show_in_admin_bar'   => true,
                'rewrite'             => false,
                'query_var'           => false,
                'show_in_menu'		  => 'edit.php?post_type=envira',
                'supports'            => array( 'title', 'author' ),
                'capabilities'        => array(
                    // Meta caps
                    'edit_post'             => 'edit_envira_album',
                    'read_post'             => 'read_envira_album',
                    'delete_post'           => 'delete_envira_album',

                    // Primitive caps outside map_meta_cap()
                    'edit_posts'            => 'edit_envira_albums',
                    'edit_others_posts'     => 'edit_other_envira_albums',
                    'publish_posts'         => 'publish_envira_albums',
                    'read_private_posts'    => 'read_private_envira_albums',

                    // Primitive caps used within map_meta_cap()
                    'read'                  => 'read',
                    'delete_posts'          => 'delete_envira_albums',
                    'delete_private_posts'  => 'delete_private_envira_albums',
                    'delete_published_posts'=> 'delete_published_envira_albums',
                    'delete_others_posts'   => 'delete_others_envira_albums',
                    'edit_private_posts'    => 'edit_private_envira_albums',
                    'edit_published_posts'  => 'edit_published_envira_albums',
                    'edit_posts'            => 'create_envira_albums',
                ),
                'map_meta_cap' 		 => true,

            )
        );

        if ( get_option( 'envira_gallery_standalone_enabled' ) ) {
            // Get the slug
            $slug = Envira_Gallery_Common::get_instance()->standalone_get_slug( 'albums' );

            // Change the default post type args so that it can be publicly accessible.
            $args['rewrite']   = array( 'with_front' => false, 'slug' => $slug );
            $args['query_var'] = true;
            $args['public']    = true;
            $args['supports'][] = 'slug';
        }

        // Register the post type with WordPress.
        register_post_type( 'envira_album', $args );

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Gallery_Posttype object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Albums_Posttype ) ) {
            self::$instance = new Envira_Albums_Posttype();
        }

        return self::$instance;

    }

}

// Load the posttype class.
$envira_albums_posttype = Envira_Albums_Posttype::get_instance();