<?php
/**
 * The functionality tied to actually saving the post.
 *
 * @package EnforceSingleCategory
 */

// Call our namepsace.
namespace EnforceSingleCategory\SavePost;

// Set our alias items.
use EnforceSingleCategory\Helpers as Helpers;

/**
 * Start our engines.
 */
add_action( 'save_post', __NAMESPACE__ . '\enforce_on_save', 12, 2 );

/**
 * Check the number of categories being passed.
 *
 * @param  integer $post_id  The individual post ID.
 * @param  object  $post     The entire post object.
 *
 * @return void
 */
function enforce_on_save( $post_id, $post ) {

	// Bail if it isn't an actual post.
	if ( 'post' !== $post->post_type ) {
		return;
	}

	// Make sure the current user has the ability to save.
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

	// Bail if we don't have categories at all (not sure why but ¯\_(ツ)_/¯ )
	if ( empty( $_POST['post_category'] ) ) {
		return;
	}

	// Check the number of items passed.
	$count  = count( $_POST['post_category'] );

	// If we have two array items, we're gold.
	if ( 2 === absint( $count ) ) {
		return;
	}

	// Set the terms passed.
	$terms  = array_map( 'absint', $_POST['post_category'] );

	// Cut down to two items.
	$setup  = array_slice( $terms, 0, 2 );

	// Unhook, else ye will recieve the ENDLESS LOOP OF DEATH.
	remove_action( 'save_post', __NAMESPACE__ . '\enforce_on_save', 12, 2 );

	// Set the post categories correctly.
	wp_set_post_categories( $post_id, $setup, false );

	// Add it back so it'll run again.
	add_action( 'save_post', __NAMESPACE__ . '\enforce_on_save', 12, 2 );
}
