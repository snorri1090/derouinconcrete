<?php
/**
 * NextGEN Class Wrapper + Importer
 *
 * @since 1.0.0
 *
 * @package Envira_Nextgen_Wrapper
 * @author  Tim Carr
 */
class Envira_Nextgen_Wrapper {

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

    }

    /**
     * Get all NextGEN Galleries
     *
     * @since 1.0.0
     *
     * @return mixed false | array of NextGEN Galleries
    */
    public function get_galleries() {

	    global $wpdb;

	    // Attempt to get galleries
	    $galleries = $wpdb->get_results(" SELECT * FROM " . $wpdb->prefix . "ngg_gallery");
	    if ( count( $galleries ) == 0 ) {
		    return false;
	    }

	    return $galleries;
    }

    /**
     * Get all NextGEN Albums
     *
     * @since 1.0.0
     *
     * @return mixed false | array of NextGEN Galleries
    */
    public function get_albums() {

	    global $wpdb;

	    // Attempt to get albums
	    $albums = $wpdb->get_results(" SELECT * FROM " . $wpdb->prefix . "ngg_album");

	    if ( count( $albums ) == 0 ) {
		    return false;
	    }

	    return $albums;
    }

	/**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Albums_Metaboxes object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Nextgen_Wrapper ) ) {
            self::$instance = new Envira_Nextgen_Wrapper();
        }

        return self::$instance;

    }

}

// Load the class.
$envira_nextgen_wrapper = Envira_Nextgen_Wrapper::get_instance();