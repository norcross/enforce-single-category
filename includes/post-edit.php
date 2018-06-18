<?php
/**
 * The functionality tied to the post editor page.
 *
 * @package EnforceSingleCategory
 */

// Call our namepsace.
namespace EnforceSingleCategory\PostEdit;

// Set our alias items.
use EnforceSingleCategory\Helpers as Helpers;

/**
 * Start our engines.
 */
add_action( 'add_meta_boxes', __NAMESPACE__ . '\remove_default_metabox', 11 );

/**
 * Remove the default metabox from the editor page.
 *
 * @return void
 */
function remove_default_metabox() {

	// Get our screen item.
	$screen = Helpers\check_admin_screen();

	// Make sure we're only on the post editor.
	if ( 'post' !== sanitize_text_field( $screen['base'] ) || 'post' !== sanitize_text_field( $screen['id'] ) ) {
		return;
	}

	// First remove the base category box.
	remove_meta_box( 'categorydiv', 'post', 'side' );

	// Now add our new box.
	add_meta_box( 'single-cat-box', __( 'Category', 'enforce-single-category' ), __NAMESPACE__ . '\single_category_box', 'post', 'side', 'core' );
}

/**
 * Set up the new select box for category.
 *
 * @param  object $post  The post object we are currently working with.
 *
 * @return HTML
 */
function single_category_box( $post ) {

	// Output div around the dropdown box.
	echo '<div id="taxonomy-category" class="categorydiv">';

		// Call our dropdown function.
		echo Helpers\get_dropdown_field( $post->ID ); // WPCS: XSS ok.

	// Close up the div.
	echo '</div>';
}

