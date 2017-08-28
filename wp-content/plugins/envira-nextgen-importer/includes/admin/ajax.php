<?php
/**
 * Handles all admin ajax interactions for the Envira Albums plugin.
 *
 * @since 1.0.0
 *
 * @package Envira_Nextgen_Importer
 * @author  Tim Carr
 */

/**
 * Imports a gallery from NextGEN into Envira
 *
 * @since 1.0.0
 */
function envira_nextgen_importer_import_gallery( $galleryID = '', $returnJSON = true ) {

	global $wpdb;

	// Set max execution time so we don't timeout
	ini_set( 'max_execution_time', 0 );
	set_time_limit( 0 );

	// If no gallery ID, get from AJAX request
	if ( empty( $galleryID ) ) {
	
	    // Run a security check first.
	    check_ajax_referer( 'envira-nextgen-importer', 'nonce' );
	    
	    // Define ABSPATH
	    if ( !defined( 'ABSPATH' ) ) {
	    	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
	    }
	    
	    // Check variables exist
	    if ( ! isset( $_POST['id']) ) {
	    	return_result( false, __( 'No gallery was selected', 'envira-nextgen-importer' ) );
	    }
	    
	    // Prepare variables.
	    $galleryID = absint( $_POST['id'] );
    
    }
    
    // Get Envira Common base for default config settings
    $instance = Envira_Gallery_Common::get_instance();
    
    // Get image path
    $sql = $wpdb->prepare( "SELECT path, title, galdesc, pageid 
    						FROM " . $wpdb->prefix . "ngg_gallery
    						WHERE gid = %d
    						LIMIT 1",
    						$galleryID );
    $gallery = $wpdb->get_row( $sql );
    
    // Get images, in order, from this NextGEN Gallery
    $sql = $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "ngg_pictures
    						WHERE galleryid = %d
    						ORDER BY sortorder ASC,
    						imagedate ASC",
    						$galleryID );
    $images = $wpdb->get_results( $sql );
    
    // Check gallery has images
    $attachments = array();
    if ( is_array( $images ) && count( $images ) > 0 ) {
    	// Add each image to Media Library
	    foreach ( $images as $image ) {
		    // Store image in WordPress Media Library
		    $attachment = envira_nextgen_importer_add_image_to_library( $gallery->path, $image->filename, $image->description, $image->alttext );
		    
		    if ( $attachment !== false ) {
		    	// Import tags
				$tags = wp_get_object_terms( $image->pid, 'ngg_tag', 'fields=names' );
				
				// Add to new attachment
				wp_set_object_terms( $attachment['ID'], $tags, 'envira-tag' );
				
				// Add to array of attachments
			    $attachments[] = $attachment;
		    }
	    }
    }
    
    // If image(s) were added to the Media Library, create a new Envira CPT containing these images
    if ( count( $attachments ) == 0 ) {
	 	return_result( false, __( 'No images found in gallery. Skipping...', 'envira-nextgen-importer' ) );   
    }
    
    // Build Envira Gallery metadata
    $meta = array();
    $meta['gallery'] = array();
    foreach ( $attachments as $attachment ) {
		$meta['gallery'][ $attachment['ID'] ] = array(
			'status' 	=> 'active',
			'src'		=> $attachment['src'],
			'title'		=> $attachment['title'],
			'link'		=> $attachment['src'],
			'alt'		=> $attachment['alt'],
			'caption'	=> $attachment['caption'],
			'thumb'		=> '',
		);		    
    }
    
    // Build Envira Config metadata
    $meta['config'] = array();
    $keys = array(
    	'type',
    	'columns',
    	'gallery_theme',
    	'lightbox_theme',
    	'gutter',
    	'margin',
    	'crop',
    	'crop_width',
    	'crop_height',
    	'mobile',
    	'mobile_width',
    	'mobile_height',
    	'title_display',
    	'arrows',
    	'keyboard',
    	'mousewheel',
    	'aspect',
    	'toolbar',
    	'toolbar_position',
    	'loop',
    	'effect',
    	'thumbnails',
    	'thumbnails_width',
    	'thumbnails_height',
    	'thumbnails_position',
    	'classes',
    	'rtl',
    );
    foreach ( $keys as $key ) {
	    $meta['config'][ $key ] = $instance->get_config_default( $key );
    }
    
    // Create Envira CPT
    $enviraGalleryID = wp_insert_post( array (
    	'post_type' 	=> 'envira',
    	'post_status' 	=> 'publish',
    	'post_title'	=> $gallery->title,
    ) );
    
    // Read Envira CPT so we can set title and slug metadata
    $enviraGallery = get_post( $enviraGalleryID );
    
    // Manually set post id, title and slug
    $meta['id'] = $enviraGalleryID;
    $meta['config']['title'] = trim( strip_tags( $enviraGallery->post_title ) );
    $meta['config']['slug']  = sanitize_text_field( $enviraGallery->post_name );
    
    // Attach meta to Envira CPT
    update_post_meta( $enviraGalleryID, '_eg_gallery_data', $meta );
    
    // Mark this NextGEN gallery as imported in option data
    $settings = get_option( 'envira_nextgen_importer' );
    if ( !isset( $settings['galleries'] ) ) {
	    $settings['galleries'] = array();
    }
    
    // Store NextGEN Gallery ID => Envira Gallery ID in options data, so we can show user in the Import UI
    // that this gallery has been imported
    $settings['galleries'][ $galleryID ] = $enviraGalleryID;
    update_option( 'envira_nextgen_importer', $settings );
    
    // NextGEN and Envira Shortcodes
    $nextGENShortcode = '[nggallery id=' . $galleryID . ']';
    $enviraShortcode = '[envira-gallery id="' . $enviraGalleryID . '"]';
    
    // Raw query to replace NextGEN shortcode with Envira Shortcode in Posts, Pages + CPTs
    $sql = $wpdb->prepare(	"UPDATE " . $wpdb->prefix . "posts SET post_content = REPLACE(post_content, '%s', '%s')",
    						$nextGENShortcode,
    						$enviraShortcode);
    $wpdb->query($sql);
   
    // Send back the response.
    if ( $returnJSON ) {
	    // Return JSON for AJAX request
    	return_result( true, __( 'Imported!', 'envira-nextgen-importer' ) );
    } else {
	    // Return Envira Gallery ID for envira_nextgen_importer_import_album()
	    return $enviraGalleryID;
    }
}
add_action( 'wp_ajax_envira_nextgen_importer_import_gallery', 'envira_nextgen_importer_import_gallery' );

/**
 * Imports an album from NextGEN into Envira
 *
 * @since 1.0.0
 */
function envira_nextgen_importer_import_album() {

	global $wpdb;

	// Set max execution time so we don't timeout
	ini_set( 'max_execution_time', 0 );
	set_time_limit( 0 );
	
    // Run a security check first.
    check_ajax_referer( 'envira-nextgen-importer', 'nonce' );
    
    // Define ABSPATH
    if ( ! defined( 'ABSPATH' ) ) {
    	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
    }
    
    // Get Envira Common base for default config settings
    $instance = Envira_Albums_Common::get_instance();
    
    // Check variables exist
    if ( ! isset( $_POST['id']) ) {
    	return_result( false, __( 'No album was selected', 'envira-nextgen-importer' ) );
    }
    
    // Prepare variables.
    $albumID = absint( $_POST['id'] );
    
    // Get album name
    $sql = $wpdb->prepare( "SELECT name, sortorder, albumdesc
    						FROM " . $wpdb->prefix . "ngg_album
    						WHERE id = %d
    						LIMIT 1",
    						$albumID );
    $album = $wpdb->get_row( $sql );
    $album->galleries = nextgen_unserialize($album->sortorder);
   
	// Check album has galleries
	if ( !is_array( $album->galleries) ) {
		return_result( false, __( 'No galleries found in album. Skipping...', 'envira-nextgen-importer' ) ); 
	}
	
	// Build Envira Album metadata
    $meta = array();
    $meta['galleryIDs'] = array();
    $meta['galleries'] = array();
    
	// Get settings
	$settings = get_option( 'envira_nextgen_importer' );
	
	// Iterate through galleries
	foreach ( $album->galleries as $galleryID ) {
		// Check if this NextGEN gallery has already been imported into Envira and still exists as a published Envira CPT
		// If so, we don't need to import it again
		
		if ( isset( $settings['galleries'] ) && array_key_exists( $galleryID, $settings['galleries'] ) ) {
			// Gallery imported
			// Get existing Envira Gallery
			$enviraGalleries = new WP_Query( array(
				'post_type' => 'envira',
				'post_status' => 'publish',
				'p' => $settings['galleries'][ $galleryID ], // value of array key = Envira Gallery ID
				'posts_per_page' => 1,
			) );
		} else {
			// Gallery not imported
			// Import
			$result = envira_nextgen_importer_import_gallery( $galleryID, false );
	
			// Get existing Envira Gallery
			$enviraGalleries = new WP_Query( array(
				'post_type' => 'envira',
				'post_status' => 'publish',
				'p' => $result,
				'posts_per_page' => 1,
			) );
		}
			
		// Check Envira Gallery Exists
		if ( $enviraGalleries->have_posts() ) {
			// Get Envira Gallery and Meta
			$enviraGallery = $enviraGalleries->posts[0];
			$enviraGalleryMeta = get_post_meta( $enviraGallery->ID, '_eg_gallery_data', true );
			
			// Get cover image from first gallery image
			reset($enviraGalleryMeta['gallery']);
			$imageID = key($enviraGalleryMeta['gallery']);
			
			// Gallery exists in Envira - no need to import
			// Just add to metadata
			$meta['galleryIDs'][] = $enviraGallery->ID;
			$meta['galleries'][ $enviraGallery->ID ] = array(
				'title' 		=> $enviraGallery->post_title,
				'alt'			=> $enviraGallery->post_title,
				'cover_image_id'=> $imageID,	
			);
		}
	}
	
	// All galleries imported
	// Build Envira Album Config metadata
    $meta['config'] = array();
    $keys = array(
    	'columns',
    	'album_theme',
    	'gutter',
    	'margin',
    	'classes',
    	'rtl',
    );
    foreach ( $keys as $key ) {
	    $meta['config'][ $key ] = $instance->get_config_default( $key );
    }
    
    // Set description
    $meta['config']['description'] = $album->albumdesc;

    // Create Envira Album CPT
    $enviraAlbumID = wp_insert_post( array (
    	'post_type' 	=> 'envira_album',
    	'post_status' 	=> 'publish',
    	'post_title'	=> $album->name,
    ) );
    
    // Read Envira Album CPT so we can set title and slug metadata
    $enviraAlbum = get_post( $enviraAlbumID );
    
    // Manually set post id, title and slug
    $meta['id'] = $enviraAlbumID;
    $meta['config']['title'] = trim( strip_tags( $enviraAlbum->post_title ) );
    $meta['config']['slug']  = sanitize_text_field( $enviraAlbum->post_name );
    
    // Attach meta to Envira Album CPT
    update_post_meta( $enviraAlbumID, '_eg_album_data', $meta );
    
    // Mark this NextGEN album as imported in option data
    $settings = get_option( 'envira_nextgen_importer' );
    if ( !isset( $settings['albums'] ) ) {
	    $settings['albums'] = array();
    }
    
    // Store NextGEN Gallery ID => Envira Gallery ID in options data, so we can show user in the Import UI
    // that this gallery has been imported
    $settings['albums'][ $albumID ] = $enviraAlbumID;
    update_option( 'envira_nextgen_importer', $settings );
    
    // Send back the response.
    return_result( true, __( 'Imported!', 'envira-nextgen-importer' ) );
    
}
add_action( 'wp_ajax_envira_nextgen_importer_import_album', 'envira_nextgen_importer_import_album' );

/**
* Unserialize NextGEN data
*
* @param string $value 	Serialized Data
* @return array 		Unserialized Data
*/
function nextgen_unserialize( $value ) {
	
	$retval = NULL;

	if ( is_string( $value ) ){
		$retval = stripcslashes( $value );

		if ( strlen( $value ) > 1 ) {
            // We can't always rely on base64_decode() or json_decode() to return FALSE as their documentation
            // claims so check if $retval begins with a: as that indicates we have a serialized PHP object.
            if ( strpos( $retval, 'a:' ) === 0 ) {
                $er = error_reporting( 0 );
                $retval = unserialize( $value );
                error_reporting( $er );
            } else {
                // We use json_decode() here because PHP's unserialize() is not Unicode safe.
                $retval = json_decode( base64_decode( $retval ), TRUE );
            }
		}
	}

	return $retval;

}

/**
 * Adds a server side image to the WordPress Media Library
 *
 * @param string $sourcePath Source Path (e.g. /wp-content/gallery/gallery-name) - no trailing slash
 * @param string $sourceImage Source Image (e.g. my-image.jpg)
 * @param string $description Image Description
 * @param string $alt Image Alt Text
 * @return mixed Image ID | false
 *
 * @since 1.0.0
*/
function envira_nextgen_importer_add_image_to_library( $sourcePath, $sourceFile, $description, $alt ) {
	
	// Get full path and filename
	$sourceFileAndPath = ABSPATH . $sourcePath . '/' . $sourceFile;
	
	// Get WP upload dir
	$uploadDir = wp_upload_dir();
	
	// Generate a unique filename so we don't overwrite an existing WordPress Media Library image
	// Create our destination file paths and URLs
	$destinationFile = wp_unique_filename( $uploadDir['path'], $sourceFile );
	$destinationFileAndPath = $uploadDir['path'] . '/' . $destinationFile;
	$destinationURL = $uploadDir['url'] . '/' . $destinationFile;
	
	// Check file is valid
	$wp_filetype = wp_check_filetype( $sourceFile, null );
	extract( $wp_filetype );
	if ( ( !$type || !$ext ) && !current_user_can( 'unfiltered_upload' ) ) {
		// Invalid file type - skip
		return false;
	}
	
	// Copy the file to the WordPress uploads dir
	$result = copy( $sourceFileAndPath, $destinationFileAndPath );
	if (!$result) {
		// Could not copy image
		return false;
	}
	
	// Set correct file permissions, as NextGEN can set these wrong
	$stat = stat( $destinationFileAndPath );
	$perms = $stat['mode'] & 0000666;
	chmod( $destinationFileAndPath, $perms );
	
	// Apply upload filters
	$return = apply_filters( 'wp_handle_upload', array( 
		'file'	=> $destinationFileAndPath, 
		'url' 	=> $destinationURL, 
		'type' 	=> $type, 
	) );
	
	// Construct the attachment array
	$attachment = array(
		'post_mime_type' 	=> $type,
		'guid' 				=> $destinationURL,
		'post_title' 		=> $alt,
		'post_name'			=> $alt,
		'post_content' 		=> $description,
	);

	// Save as attachment
	$attachmentID = wp_insert_attachment($attachment, $destinationFileAndPath);
	
	// Update attachment metadata
	if ( !is_wp_error( $attachmentID ) ) {
		$metadata = wp_generate_attachment_metadata( $attachmentID, $destinationFileAndPath );
		wp_update_attachment_metadata( $attachmentID, wp_generate_attachment_metadata( $attachmentID, $destinationFileAndPath ) );
	}
	
	// Force alt and caption
	update_post_meta( $attachmentID, '_wp_attachment_image_alt', $alt );
	$attachment = get_post( $attachmentID );
	$attachment->post_excerpt = $description;
	wp_update_post( $attachment );
	
	// Return attachment data
	return array(
		'ID' 		=> $attachmentID,
		'src' 		=> $destinationURL,
		'title' 	=> $alt,
		'alt'		=> $alt,
		'caption'	=> $description,
	);
	
}


/**
 * Returns a JSON encoded success or error flag with message string
 *
 * @param bool $success Success (true|false)
 * @param string $message Message
 *
 * @since 1.0.0
*/
function return_result( $success, $message ) {
	echo json_encode( array (
    	'success' 	=> (bool) $success,
    	'message'	=> (string) $message,
    ) );
    die;
}