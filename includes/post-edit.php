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
add_action( 'save_post', __NAMESPACE__ . '\enforce_on_save', 12, 2 );

/**
 * Remove the default metabox from the editor page.
 *
 * @return void
 */
function remove_default_metabox() {

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

	// Fetch our actual value.
	$value  = Helpers\get_first_term( $post->ID, 'term_id' );

	// Set the args for the dropdown.
	$args   = array(
		'show_option_all'   => '',
		'show_option_none'  => '',
		'option_none_value' => '',
		'hide_empty'        => 0,
		'echo'              => 0,
		'selected'          => absint( $value ),
		'hierarchical'      => 1,
		'hide_if_empty'     => false,
		'name'              => 'post_category[]',
		'id'                => 'single-cat-box-dropdown',
		'class'             => 'widefat',
	);

	// Now output div around the dropdown box.
	echo '<div id="taxonomy-category" class="categorydiv">';

		// Include a hidden field to mimic the original metabox.
		echo '<input type="hidden" name="post_category[]" value="0">';

		// Echo the actual dropdown.
		echo wp_dropdown_categories( $args );

	// Close up the div.
	echo '</div>';
}

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
