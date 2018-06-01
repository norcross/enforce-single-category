<?php
/**
 * Our helper functions to use across the plugin.
 *
 * @package EnforceSingleCategory
 */

// Call our namepsace.
namespace EnforceSingleCategory\Helpers;

/**
 * Check where we are on the current admin.
 *
 * @param  string $key  A single key from the data array.
 *
 * @return mixed
 */
function check_admin_screen( $key = '' ) {

	// If we aren't on the admin, or don't have the function, fail right away.
	if ( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
		return false;
	}

	// Get the screen object.
	$screen = get_current_screen();
	//preprint( $screen, true );
	// If we didn't get our screen object, bail.
	if ( ! is_object( $screen ) ) {
		return false;
	}

	// Switch through and return the item.
	switch ( sanitize_key( $key ) ) {

		case 'object' :
			return $screen;
			break;

		case 'action' :
			return $screen->action;
			break;

		case 'base' :
			return $screen->base;
			break;

		case 'id' :
			return $screen->id;
			break;

		default :
			return array(
				'action' => $screen->action,
				'base'   => $screen->base,
				'id'     => $screen->id
			);

		// End all case breaks.
	}
}

/**
 * Get the first category associated with a post.
 *
 * @param  integer $post_id  The post ID we are checking.
 * @param  string  $key      A single key from the data array.
 *
 * @return mixed
 */
function get_first_term( $post_id = 0, $key = '' ) {

	// First check for terms.
	$terms  = get_the_terms( $post_id, 'category' );

	// If we have no terms for the post, use the default.
	if ( empty( $terms ) || is_wp_error( $terms ) ) {

		// Get the default category.
		$defcat = get_option( 'default_category', '1' );

		// Set the term data.
		$deftrm = get_term( $defcat, 'category', ARRAY_A );

		// Return the entire thing if we didn't include a key
		if ( empty( $key ) ) {
			return $deftrm;
		}

		// Return a single portion of the data.
		return isset( $deftrm[ $key ] ) ? $deftrm[ $key ] : false;
	}

	// Reset array so we can pull easily.
	$terms  = array_values( $terms );

	// Pull out the first one.
	$term   = (array) $terms[0];

	// Bail without a term.
	if ( empty( $term ) ) {
		return false;
	}

	// Return the entire thing if we didn't include a key
	if ( empty( $key ) ) {
		return $term;
	}

	// Return a single portion of the data.
	return isset( $term[ $key ] ) ? $term[ $key ] : false;
}

/**
 * Get the actual markup for the dropdown.
 *
 * @param  integer $post_id  The post ID we are displaying on.
 * @param  array   $args     Any args to modify the setup.
 * @param  boolean $hidden   Whether to show the hidden field with zero.
 * @param  boolean $echo     Whether to echo it out or just return.
 *
 * @return HTML
 */
function get_dropdown_field( $post_id = 0, $args = array(), $hidden = true, $echo = false ) {

	// Fetch our actual value.
	$value  = get_first_term( $post_id, 'term_id' );

	// Set the base args for the dropdown.
	$base   = array(
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

	// Parse in anything we passed.
	$setup  = wp_parse_args( $args, $base );

	// Set the empty.
	$field  = '';

	// Include a hidden field to mimic the original metabox.
	$field .= ! $hidden ? '' : '<input type="hidden" name="post_category[]" value="0">';

	// Call the actual dropdown.
	$field .= wp_dropdown_categories( $setup );

	// Echo if requested.
	if ( ! empty( $echo ) ) {
		echo $field; // WPCS: XSS ok.
	}

	// Return it.
	return $field;
}
