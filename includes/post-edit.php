<?php
/**
 * The functionality tied to the post editor page.
 *
 * @package EnforceSingleCategory
 */

// Call our namepsace.
namespace EnforceSingleCategory\PostEdit;

// Set our alias items.
use EnforceSingleCategory as Core;
use EnforceSingleCategory\Helpers as Helpers;

/**
 * Start our engines.
 */
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\load_admin_assets' );
add_action( 'add_meta_boxes', __NAMESPACE__ . '\remove_default_metabox', 11 );

/**
 * Load our admin side JS and CSS.
 *
 * @param $hook  Admin page hook we are current on.
 *
 * @return void
 */
function load_admin_assets( $hook ) {

	/*
	// Set my handle.
	$handle = 'enforce-single-category';

	// Set a file suffix structure based on whether or not we want a minified version.
	$file   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? $handle . '-admin' : $handle . '-admin.min';

	// Set a version for whether or not we're debugging.
	$vers   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : Core\VERS;

	// Load our CSS file.
	wp_enqueue_style( $handle, Core\ASSETS_URL . '/css/' . $file . '.css', false, $vers, 'all' );

	// And our JS.
	wp_enqueue_script( $handle, Core\ASSETS_URL . '/js/' . $file . '.js', array( 'jquery' ), $vers, true );
	*/
}

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
		'show_option_all'    => '',
		'show_option_none'   => '',
		'option_none_value'  => '-1',
		'hide_empty'         => 0,
		'echo'               => 0,
		'selected'           => absint( $value ),
		'hierarchical'       => 1,
		'hide_if_empty'      => false,
	);

	/*
	// fetch the first term on the item
	$value  = DPP2015_Helper::get_first_term( $post->ID, 'showcase-theme' );

	// build the dropdown
	echo '<select class="" name="showcase-theme-drop" id="showcase-theme-drop">';

		echo '<option value="">(Select)</option>';

		// loop the terms
		foreach ( $terms as $term ) {
			echo '<option value="' . $term['term_id'] . '" ' . selected( $value['term_id'], $term['term_id'], false ) . '>' . esc_html( $term['name'] ) . '</option>';
		}

	// close the dropdown
	echo '</select>';
	*/
}
