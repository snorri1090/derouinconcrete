<?php
/**
 * Admin Screen and Importer
 *
 * @since 1.0.0
 *
 * @package Envira_Nextgen_Importer
 * @author  Tim Carr
 */
class Envira_Nextgen_Importer_Settings {

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

    	// Load the base class object.
        $this->base = Envira_Nextgen_Importer::get_instance();

        // Load the NextGEN wrapper
        $this->nextgen = Envira_Nextgen_Wrapper::get_instance();

        // Add custom settings submenu.
        add_action( 'admin_menu', array( $this, 'admin_menu' ), 10 );

        // Add settings item to Plugins screen
        add_filter( 'plugin_action_links_' . plugin_basename( plugin_dir_path( dirname( dirname( __FILE__ ) ) ) . 'envira-nextgen-importer.php' ), array( $this, 'settings_link' ) );

		// Add callbacks for settings tabs.
	    add_action( 'envira_nextgen_importer_tab_settings_galleries', array( $this, 'settings_galleries_tab' ) );

    }

    /**
     * Register the Settings submenu item for Envira.
     *
     * @since 1.0.0
     */
    public function admin_menu() {

        // Register the submenu.
        $this->hook = add_submenu_page(
            'edit.php?post_type=envira',
            __( 'Envira NextGEN Import', 'envira-gallery' ),
            __( 'NextGEN Import', 'envira-gallery' ),
            apply_filters( 'envira_gallery_menu_cap', 'manage_options' ),
            $this->base->plugin_slug,
            array( $this, 'settings_page' )
        );

        // If successful, load admin assets only on that page and check for addons refresh.
        if ( $this->hook ) {
            add_action( 'load-' . $this->hook, array( $this, 'settings_page_assets' ) );
        }

    }

    /**
     * Loads assets for the settings page.
     *
     * @since 1.0.0
     */
    public function settings_page_assets() {

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

    }

    /**
     * Register and enqueue settings page specific CSS.
     *
     * @since 1.0.0
     */
    public function enqueue_admin_styles() {

        // Load Envira Gallery Instance
        $instance = Envira_Gallery::get_instance();

        // Load Envira Gallery Settings styles
        wp_register_style( $instance->plugin_slug . '-settings-style', plugins_url( 'assets/css/settings.css', $instance->file ), array(), $instance->version );
        wp_enqueue_style( $instance->plugin_slug . '-settings-style' );

        // Load Addon-specific styles
        wp_register_style( $this->base->plugin_slug . '-settings-style', plugins_url( 'assets/css/settings.css', $instance->file ), array(), $instance->version );
        wp_enqueue_style( $this->base->plugin_slug . '-settings-style' );

        // Run a hook to load in custom styles.
        do_action( 'envira_nextgen_importer_settings_styles' );

    }

    /**
     * Register and enqueue settings page specific JS.
     *
     * @since 1.0.0
     */
    public function enqueue_admin_scripts() {

        // Load Envira Gallery Instance
        $instance = Envira_Gallery::get_instance();

        // Load Envira Gallery Tabs
        wp_register_script( $instance->plugin_slug . '-tabs-script', plugins_url( 'assets/js/tabs.js', $instance->file ), array( 'jquery' ), $instance->version, true );
        wp_enqueue_script( $instance->plugin_slug . '-tabs-script' );

		// Load jQuery UI Progressbar
		wp_enqueue_script( 'jquery-ui-progressbar' );

		// Load debug or live script depending on WP_DEBUG setting
        $settingsJS = ( ( WP_DEBUG === true ) ? 'settings.js' : 'min/settings-min.js' );
        wp_register_script( $this->base->plugin_slug . '-settings-script', plugins_url( 'assets/js/' . $settingsJS, $this->base->file ), array( 'jquery', 'jquery-ui-tabs' ), $this->base->version, true );
        wp_enqueue_script( $this->base->plugin_slug . '-settings-script' );
        wp_localize_script(
            $this->base->plugin_slug . '-settings-script',
            'envira_nextgen_importer_settings',
            array(
            	// AJAX endpoint + Nonce
                'ajax'					=> admin_url( 'admin-ajax.php' ),
                'nonce'					=> wp_create_nonce( 'envira-nextgen-importer' ),

                // Generic Messages
                'importing'				=> __( 'Importing', 'envira-nextgen-importer' ),

                // Gallery Messages
                'no_galleries_selected' => __( 'Please choose at least one NextGEN Gallery to import.', 'envira-nextgen-importer' ),

                // Album Messages
                'no_albums_selected' 	=> __( 'Please choose at least one NextGEN Album to import.', 'envira-nextgen-importer' ),

            )
        );

        // Run a hook to load in custom scripts.
        do_action( 'envira_nextgen_importer_settings_scripts' );

    }

    /**
     * Callback to output the Envira NextGEN Importer Settings page.
     *
     * @since 1.0.0
     */
    public function settings_page() {

        ?>
        <!-- Tabs -->
        <h2 id="envira-tabs-nav" class="envira-tabs-nav" data-container="#envira-gallery-settings" data-update-hashbang="1">
            <?php 
            $i = 0; 
            foreach ( (array) $this->get_settings_tab_nav() as $id => $title ) {
                $class = ( 0 === $i ? 'envira-active' : '' ); 
                ?>
                <a class="nav-tab <?php echo $class; ?>" href="#envira-tab-<?php echo $id; ?>" title="<?php echo $title; ?>"><?php echo $title; ?></a>
                <?php 
                $i++;
            }
            ?>
        </h2>

        <div id="envira-gallery-settings" class="wrap">
	        <h1 class="envira-hideme"></h1>
            <div class="envira-gallery envira-clear">
                <div id="envira-tabs" class="envira-clear" data-navigation="#envira-tabs-nav">
                    <?php 
                    $i = 0; 
                    foreach ( (array) $this->get_settings_tab_nav() as $id => $title ) {
                        $class = ( 0 === $i ? 'envira-active' : '' ); 
                        ?>
                        <div id="envira-tab-<?php echo $id; ?>" class="envira-tab envira-clear <?php echo $class; ?>">
                            <?php do_action( 'envira_nextgen_importer_tab_settings_' . $id ); ?>
                        </div>
                        <?php
                        $i++;
                    }
                    ?>
                </div>
            </div>
        </div>

        <?php

    }

    /**
     * Callback for getting all of the settings tabs for Envira.
     *
     * @since 1.0.0
     *
     * @return array Array of tab information.
     */
    public function get_settings_tab_nav() {

        $tabs = array(
            'galleries' => __( 'Galleries', 'envira-nextgen-importer' ),
        );

        // Apply filter for any other tabs to be added/removed
        $tabs = apply_filters( 'envira_nextgen_importer_settings_tab_nav', $tabs );

        return $tabs;

    }

    /**
     * Callback for displaying the UI for galleries settings tab.
     *
     * @since 1.0.0
     */
    public function settings_galleries_tab() {

        // Check and see if NextGEN is installed... if not, do not attempt to display settings and instead report an error
        if ( !is_plugin_active( 'nextgen-gallery/nggallery.php' ) ) { ?>
            <div id="envira-nextgen-importer-settings-galleries">
                <p>Please install and activate the <a href="https://wordpress.org/plugins/nextgen-gallery/" target="_blank">NextGEN Gallery plugin</a> before using this addon.</p>
            </div>
        <?php return;
        }

    	// Get NextGEN Galleries
		$galleries = $this->nextgen->get_galleries();

		// Get settings (contains imported galleries)
		$settings = get_option( 'envira_nextgen_importer' );

        ?>
        <div id="envira-nextgen-importer-settings-galleries">
        	<!-- Progress Bar -->
        	<div id="gallery-progress"><div id="gallery-progress-label"></div></div>

        	<!-- Form -->
            <form id="envira-nextgen-importer-galleries" method="post">
	            <table class="form-table">
	                <tbody>
	                    <tr id="envira-settings-key-box">
	                        <th scope="row">
	                            <label for="envira-settings-key"><?php _e( 'Galleries to Import', 'envira-nextgen-importer' ); ?></label>
	                        </th>
	                        <td>
	                        	<?php
	                        	if ( $galleries !== false ) {
	                            	foreach ( $galleries as $gallery ) {
	                            		// Check if gallery imported from NextGEN previously
	                            		$imported = ( ( isset( $settings['galleries'] ) && isset( $settings['galleries'][ $gallery->gid ] ) ) ? true : false );
	                            		?>
	                            		<label for="galleries-<?php echo $gallery->gid; ?>" data-id="<?php echo $gallery->gid; ?>"<?php echo ( $imported ? ' class="imported"' : '' ); ?>>
	                            			<input type="checkbox" name="galleries" id="galleries-<?php echo $gallery->gid; ?>" value="<?php echo $gallery->gid; ?>" />
	                            			<?php echo $gallery->title; ?>
	                            			<span>
		                            			<?php
		                            			if ( $imported ) {
			                            			// Already imported
			                            			_e('Imported', 'envira-nextgen-importer');
		                            			}
		                            			?>
	                            			</span>
	                            		</label>
	                            		<?php
	                            	}
	                        	}
	                        	?>
	                        </td>
	                    </tr>
	                    <tr>
	                    	<th scope="row">
	                    		&nbsp;
	                    	</th>
	                    	<td>
	                    		<?php
	                    		submit_button( __( 'Import Galleries', 'envira-nextgen-importer' ), 'primary', 'envira-gallery-verify-submit', false );
								?>
	                    	</td>
	                    </tr>
	                    <?php do_action( 'envira_nextgen_importer_settings_galleries_box' ); ?>
	                </tbody>
	            </table>
            </form>
        </div>
        <?php

    }

    /**
     * Add Settings page to plugin action links in the Plugins table.
     *
     * @since 1.0.0
     *
     * @param array $links  Default plugin action links.
     * @return array $links Amended plugin action links.
     */
    public function settings_link( $links ) {

        $settings_link = sprintf( '<a href="%s">%s</a>', add_query_arg( array( 'post_type' => 'envira', 'page' => $this->base->plugin_slug ), admin_url( 'edit.php' ) ), __( 'Settings', 'envira-nextgen-importer' ) );
        array_unshift( $links, $settings_link );

        return $links;

    }

	/**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Albums_Metaboxes object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Nextgen_Importer_Settings ) ) {
            self::$instance = new Envira_Nextgen_Importer_Settings();
        }

        return self::$instance;

    }

}

// Load the class.
$envira_nextgen_importer_settings = Envira_Nextgen_Importer_Settings::get_instance();