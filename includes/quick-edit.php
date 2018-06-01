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
add_action( 'admin_head', __NAMESPACE__ . '\add_table_css' );
add_filter( 'manage_post_posts_columns', __NAMESPACE__ . '\add_dummy_column', 10, 2 );
add_filter( 'hidden_columns', __NAMESPACE__ . '\set_hidden_column', 10, 3 );
add_filter( 'quick_edit_show_taxonomy', __NAMESPACE__ . '\remove_category_inline', 10, 3 );
add_action( 'quick_edit_custom_box', __NAMESPACE__ . '\display_category_quickedit', 10, 2 );

/**
 * Add some CSS to the head.
 *
 * @return string
 */
function add_table_css() {

	// Get our screen item.
	$screen = Helpers\check_admin_screen();

	// Make sure we're only on the post table.
	if ( 'edit' !== sanitize_text_field( $screen['base'] ) || 'edit-post' !== sanitize_text_field( $screen['id'] ) ) {
		return;
	}

	// Now echo out the CSS.
	echo '<style type="text/css">' . "\n";
		echo '#wpbody-content .inline-edit-row fieldset#inline-edit-col-single-category { margin-top:10px; }' . "\n";
		echo '.inline-edit-spacer { width:50%; float:left; }' . "\n";
		echo '.column-enforce-plc { float:left; }' . "\n";
	echo '</style>' . "\n";
}

/**
 * Add our dummy column (which will get removed).
 *
 * @param  array  $columns    All the columns.
 *
 * @return array  $columns    The modified array of columns.
 */
function add_dummy_column( $columns ) {

	// Rename the categories to singular.
	$columns['categories']  = __( 'Category', 'enforce-single-category' );

	// Add the dummy placeholder column.
	$columns['enforce-plc'] = __return_empty_string();

	// Return the resulting columns.
	return $columns;
}

/**
 * Add our dummy column to the hidden items.
 *
 * @param  array   $hidden        An array of hidden columns.
 * @param  object  $screen        WP_Screen object of the current screen.
 * @param  boolean $use_defaults  Whether to show the default columns.
 *
 * @return array   $hidden        The modified array of columns.
 */
function set_hidden_column( $hidden, $screen, $use_defaults ) {

	// Check the screen ID before we set.
	if ( empty( $screen->id ) || 'edit-post' !== $screen->id ) {
		return $hidden;
	}

	// Return our updated array.
	return wp_parse_args( (array) 'enforce-plc', $hidden );
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
	if ( empty( $column ) || 'enforce-plc' !== $column ) {
		return;
	}

	// Set my empty.
	$field  = '';

	// Open up the fieldset.
	$field .= '<fieldset id="inline-edit-col-single-category" class="inline-edit-col-full">';

		// Our spacer to make it line up.
		$field .= '<div class="inline-edit-spacer">&nbsp;</div>';

		// Wrap it in a div.
		$field .= '<div class="inline-edit-col column-' . esc_attr( $column ) . '">';

			// Add the clearfix group.
			$field .= '<div class="inline-edit-group wp-clearfix">';

				// Wrap the whole thing in a label.
				$field .= '<label>';

					// Load the label portion.
					$field .= '<span class="title inline-edit-category-label">' . esc_html__( 'Category', 'enforce-single-category' ) . '</span>';

					// Call our dropdown function.
					$field .= Helpers\get_dropdown_field( 0, array( 'class' => 'inline-dropdown' ) );

				// Close the label.
				$field .= '</label>';

			// Close up the div.
			$field .= '</div>';

		// Close up the div.
		$field .= '</div>';

	// Close the fieldset.
	$field .= '</fieldset>';

    // And echo it out.
    echo $field;
}

