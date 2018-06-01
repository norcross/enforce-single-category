<?php
/**
 * The functionality tied to the quick editor.
 *
 * @package EnforceSingleCategory
 */

// Call our namepsace.
namespace EnforceSingleCategory\QuickEdit;

// Set our alias items.
use EnforceSingleCategory\Helpers as Helpers;

/**
 * Start our engines.
 */
//add_action( 'admin_head', __NAMESPACE__ . '\admin_css' );

//add_filter( 'manage_post_posts_columns', __NAMESPACE__ . '\add_dummy_column', 10, 2 );
//add_action( 'manage_posts_custom_column', __NAMESPACE__ . '\add_column_data', 10, 2 );

//add_filter( 'manage_edit-post_columns', __NAMESPACE__ . '\remove_dummy_column', 20 );
add_filter( 'quick_edit_show_taxonomy', __NAMESPACE__ . '\remove_category_inline', 10, 3 );
//add_action( 'quick_edit_custom_box', __NAMESPACE__ . '\display_category_quickedit', 10, 2 );


add_action( 'quick_edit_columns_left', __NAMESPACE__ . '\display_inline_quickedit', 10, 2 );

function display_inline_quickedit( $post_type, $bulk ) {

	// Bail if we aren't on the post type we want.
	if ( empty( $post_type ) || 'post' !== $post_type ) {
		return;
	}

	// Set my empty.
	$field  = '';

	// Wrap it in a div.
	$field .= '<div class="inline-edit-group wp-clearfix"><label>';

		// Load the label portion.
		$field .= '<span class="title inline-edit-category-label">' . esc_html__( 'Category', 'enforce-single-category' ) . '</span>';

		// Call our dropdown function.
		$field .= '<span class="input-text-wrap dropdown">';
			$field .= Helpers\get_dropdown_field( 0, array( 'class' => 'inline-dropdown' ) );
		$field .= '</span>';

	// Close up the div.
	$field .= '</label></div>';

    // And echo it out.
    echo $field;
}

/**
 * Add our dummy column (which will get removed).
 *
 * @param  array  $columns    All the columns.
 *
 * @return array  $columns    The modified array of columns.
 */
function add_dummy_column( $columns ) {

	// Rename the categories
	$columns['categories'] = __( 'Category', 'enforce-single-category' );

	// Add the dummy column.
	$columns['enforce-dummy'] = ''; // __( 'Dummy', 'enforce-single-category' );

	// Return the resulting columns.
	return $columns;
}

/**
 * Add the category data to our column.
 *
 * @param  string  $column   The name of the column.
 * @param  integer $post_id  The post ID we're on.
 *
 * @return mixed
 */
function add_column_data( $column, $post_id ) {

	// Run through our columns.
	switch ( $column ) {

		case 'enforce-dummy' :
 			echo '<span class="enforce-dummy-value">' . Helpers\get_first_term( $post_id, 'term_id' ) , '</span>';
			break;
    }
}

/**
 * Remember that dummy column we added? We're removing it.
 *
 * @param  array $columns  The existing array of columns.
 *
 * @return array $columns  The modified array of columns.
 */
function remove_dummy_column( $columns ) {

	// If we have the dummy, remove it.
	if ( isset( $columns['enforce-dummy'] ) ) {
		unset( $columns['enforce-dummy'] );
	}

	// And return them.
	return $columns;
}

/**
 * Remove the category selector from the inline quick edit.
 *
 * @param  integer $show       Whether to show the current taxonomy in Quick Edit.
 * @param  string  $taxonomy   Taxonomy name.
 * @param  string  $post_type  Post type of current Quick Edit post.
 *
 * @return boolean
 */
function remove_category_inline( $show, $taxonomy, $post_type ) {
	return 'category' === $taxonomy && 'post' === $post_type ? 0 : 1;
}

/**
 * Display our custom dropdown for a category.
 *
 * @param  string $column     The name of the column we're on.
 * @param  string $post_type  The post type.
 *
 * @return HTML
 */
function display_category_quickedit( $column, $post_type ) {

	// Bail if we aren't on the column we want.
	if ( empty( $column ) || 'enforce-dummy' !== $column ) {
	//	return;
	}

	// Set my empty.
	$field  = '';

	// Open up the fieldset.
	$field .= '<fieldset class="inline-edit-col-end inline-edit-col-category-dropdown">';

		// Wrap it in a div.
		$field .= '<div class="inline-edit-col column-' . esc_attr( $column ) . '">';

			// Load the label portion.
			$field .= '<span class="title inline-edit-category-label">' . esc_html__( 'Category', 'enforce-single-category' ) . '</span>';

			// Call our dropdown function.
			$field .= Helpers\get_dropdown_field( 0, array( 'class' => 'inline-dropdown' ) );

		// Close up the div.
		$field .= '</div>';

	// Close the fieldset.
	$field .= '</fieldset>';

    // And echo it out.
    echo $field;
}

