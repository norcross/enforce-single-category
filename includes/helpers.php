<?php
/**
 * Our helper functions to use across the plugin.
 *
 * @package EnforceSingleCategory
 */

// Call our namepsace.
namespace EnforceSingleCategory\Helpers;

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
