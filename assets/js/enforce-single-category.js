(function($) {

	// Create a copy of the WP inline edit post function.
	var $wp_inline_edit = inlineEditPost.edit;

	// Then we overwrite the function with our own code.
	inlineEditPost.edit = function( id ) {

		// We "call" the original WP edit function so
		// we don't want to leave WordPress hanging
		$wp_inline_edit.apply( this, arguments );

		// Set a blank post ID.
		var $post_id = 0;

		// Now dig in and get out the ID.
		if ( typeof( id ) == 'object' ) {
			$post_id = parseInt( this.getId( id ) );
		}

		// We have a post ID, so set our value in the dropdown.
		if ( $post_id > 0 ) {

			// Define our post and edit rows.
			var $edit_row = $( '#edit-' + $post_id );
			var $post_row = $( '#post-' + $post_id );

			// Get the term value.
			var $term_id  = $( '.column-enforce-plc input.enforce-single-category-id', $post_row ).val();

			// Now set the dropdown.
			$( '.column-enforce-plc select.inline-dropdown', $edit_row ).val( $term_id );
		}
	};

})(jQuery);
