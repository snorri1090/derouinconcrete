var progressbar;

jQuery( document ).ready( function( $ ) {

    // Import Galleries
    $('form#envira-nextgen-importer-galleries').submit(function(e) {
		e.preventDefault();
		
		// Check at least one gallery has been selected
		var galleries = $('input[name=galleries]:checked');
		if (galleries.length == 0) {
			alert(envira_nextgen_importer_settings.no_galleries_selected);
			return false;
		}
		
		// Disable form
		$('form#envira-nextgen-importer-galleries :input').prop('disabled', true);
		
		// Display Progress Bar
		progressbar = $('#gallery-progress').progressbar({
			value: 0
		});
		
		// Get array of IDs
		var currentIndex = -1;
		var ids = [];
		$(galleries).each(function(i) {
			ids[i] = $(this).val();
		});
		
		// Start first request
		importNext('gallery', ids, currentIndex);
    });
    
    // Import Albums
    $('form#envira-nextgen-importer-albums').submit(function(e) {
		e.preventDefault();
		
		// Check at least one album has been selected
		var albums = $('input[name=albums]:checked');
		if (albums.length == 0) {
			alert(envira_nextgen_importer_settings.no_albums_selected);
			return false;
		}
		
		// Disable form
		$('form#envira-nextgen-importer-albums :input').prop('disabled', true);
		
		// Display Progress Bar
		progressbar = $('#album-progress').progressbar({
			value: 0
		});
		
		// Get array of IDs
		var currentIndex = -1;
		var ids = [];
		$(albums).each(function(i) {
			ids[i] = $(this).val();
		});
		
		// Start first request
		importNext('album', ids, currentIndex);
    });
    
    /**
    * Imports the next album or gallery, if required
    *
    * @param string type album|gallery
    * @param array ids Album/Gallery IDs
    * @param int currentIndex Current index in array to import
    */
    var importNext = function(type, ids, currentIndex) {
    	// Increment index
    	currentIndex++;
    	
    	// Check if we have reached end of ID array
		if (ids.length == currentIndex) {
	    	// Enable form
	    	switch (type) {
		    	case 'album':
		    		$('form#envira-nextgen-importer-albums :input').prop('disabled', false);
		    		break;
		    	case 'gallery':
		    		$('form#envira-nextgen-importer-galleries :input').prop('disabled', false);
		    		break;
	    	}
		
	    	// Finish execution
	    	return;
		}
		
		// Import next item
		doAJAXRequest(type, ids, currentIndex);	
	}
    
    /**
	* Performs an AJAX request to import an album or gallery
	*
	* @param string type album|gallery
    * @param array ids Album/Gallery IDs
    * @param int currentIndex Current index in array to import
	*/
	var doAJAXRequest = function(type, ids, currentIndex) {
		// Get ID and status label on form
		var id = ids[currentIndex];
		switch (type) {
	    	case 'album':
	    		var statusLabel = $('form#envira-nextgen-importer-albums label[data-id='+id+']');
	    		break;
	    	case 'gallery':
	    		var statusLabel = $('form#envira-nextgen-importer-galleries label[data-id='+id+']');
	    		break;
    	}
		
		// Mark on form as importing
		$(statusLabel).removeClass().addClass('importing');
		$('span', $(statusLabel)).text(envira_nextgen_importer_settings.importing);
	
		// Do request
		$.ajax({
		    url:      envira_nextgen_importer_settings.ajax,
		    type:     'post',
		    async:    true,
		    cache:    false,
		    dataType: 'json',
		    data: {
		        action:  		'envira_nextgen_importer_import_' + type,
		        id:   			id,
		        post_id:		0, // Required so Envira_Gallery_Common::get_config_default doesn't throw a PHP notice
		        nonce:   		envira_nextgen_importer_settings.nonce
		    },
		    success: function(response) {
		    	updateUI(type, ids, currentIndex, response.success, response.message);
		    	importNext(type, ids, currentIndex);
		    	return;
		    },
		    error: function(xhr, textStatus, e) {
			    updateUI(type, ids, currentIndex, false, textStatus);
		    	importNext(type, ids, currentIndex);
		    	return;
		    }
		});	
	}
	
	/**
	* Update the UI when an import request has completed (whether successful or not)
	*
	* @param string type album|gallery
    * @param array ids Album/Gallery IDs
    * @param int currentIndex Current index in array to import
    * @param bool result AJAX Result (true|false)
    * @param string message Message to display
	*/
	var updateUI = function(type, ids, currentIndex, result, message) {
		// Get ID and status label on form
		var id = ids[currentIndex];
		switch (type) {
	    	case 'album':
	    		var statusLabel = $('form#envira-nextgen-importer-albums label[data-id='+id+']');
	    		break;
	    	case 'gallery':
	    		var statusLabel = $('form#envira-nextgen-importer-galleries label[data-id='+id+']');
	    		break;
    	}
		
		// Mark gallery on form as not imported
		$(statusLabel).removeClass().addClass((result ? 'imported' : 'error'));
		
		// Display result from AJAX call
		$('span', $(statusLabel)).text(message);
	
		// Update progress bar
		progressbar.progressbar('value', Number(((currentIndex+1) / ids.length) * 100));
	}
	
});