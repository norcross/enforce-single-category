<?php
/**
 * The functionality tied to the WP-CLI stuff.
 *
 * @package EnforceSingleCategory
 */

// Call our namepsace.
namespace EnforceSingleCategory\Commands;

// Set our alias items.
use EnforceSingleCategory\Helpers as Helpers;

// Pull in the CLI items.
use WP_CLI;
use WP_CLI_Command;

/**
 * Extend the CLI command class with our own.
 */
class Commands extends WP_CLI_Command {

	/**
	* Get the array of post IDs to check.
	*
	* @return array
	*/
	protected function get_post_ids() {

		// Load the global DB.
		global $wpdb;

		// Set my table name to use.
		$table  = $wpdb->prefix . 'posts';

		// Set up our query.
		$setup  = $wpdb->prepare("
			SELECT   ID
			FROM     $table
			WHERE    post_status = '%s'
			AND      post_type = '%s'
			ORDER BY post_date DESC
		", esc_sql( 'publish' ), esc_sql( 'post' ) );

		// Process the query.
		$ids    = $wpdb->get_col( $setup );

		// Bail on empty or error.
		if ( empty( $ids ) || is_wp_error( $ids ) ) {
			WP_CLI::error( 'No post IDs could be retrieved.' );
		}

		// Set an empty data array.
		$build  = array();

		// Now loop my items.
		foreach ( $ids as $id ) {

			// Get the terms.
			$terms  = wp_get_post_categories( $id, array( 'orderby' => 'count', 'order' => 'DESC' ) );

			// Skip if empty or only 1.
			if ( empty( $terms ) || count( $terms ) < 2 ) {
				continue;
			}

			// Add our data array.
			$build[ $id ]   = $terms;
		}

		// Now return the IDs.
		return $build;
	}

	/**
	 * Run the check for multi-category posts.
	 *
	 * ## OPTIONS
	 *
	 * [--counts]
	 * : Whether to just show the counts.
	 * ---
	 * default: false
	 * options:
	 *   - true
	 *   - false
	 * ---
	 *
	 * [--single]
	 * : How to determine which category to use.
	 * ---
	 * default: random
	 * options:
	 *   - random
	 *   - first
	 *   - last
	 *   - popular
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp enforce-singlecat enforce
	 *
	 * @when after_wp_load
	 */
	function enforce( $args = array(), $assoc_args = array() ) {

		// Parse out the associatives.
		$parsed = wp_parse_args( $assoc_args, array(
			'counts'    => false,
			'single'    => 'random',
		));

		// Make sure that WooCommerce is installed and active.
		$this->verify_woocommerce();

		// Grab our list of releases, filtered.
		$releases   = Functions\get_filtered_release_list();

		// Throw an error and bail if we have no release data.
		if ( ! $releases ) {
			WP_CLI::error( 'No release data was found.' );
		}

		// Now loop the list of my releases, checking the versions.
		foreach ( $releases as $version => $download ) {

			// Prompt to continue the process if the autoloop wasn't set.
			if ( ! $parsed['autoloop'] ) {
				WP_CLI::confirm( sprintf( 'Do you want to install version %s?', esc_attr( $version ) ) );
				WP_CLI::log( '' );
			}

			// Run the database backup if prompted.
			if ( $parsed['backup'] ) {
				$this->run_backup( $download, $version );
			}

			// Run our function to update.
			$this->move_to_version( $download, $version );

			// Run the Woo updater.
			$this->run_woo_update();

			// And clear out the object cache.
			WP_CLI\Utils\wp_clear_object_cache();

			// Finished with the item inside the loop, so return the success.
			WP_CLI::success( sprintf( 'Success! You have updated WooCommerce to version %s', esc_attr( $version ) ) . "\n" );
		}
	}

	/**
	 * Run a quick numerical test.
	 *
	 * ## EXAMPLES
	 *
	 *     wp enforce-singlecat runtests
	 *
	 * @when after_wp_load
	 */
	function runtests() {

		// Set my args.
		$args   = array(
			'return'     => true,   // Return 'STDOUT'; use 'all' for full object.
			'parse'      => 'json', // Parse captured STDOUT to JSON array.
			'launch'     => false,  // Reuse the current process.
			'exit_error' => true,   // Halt script execution on error.
		);

		// Get my posts.
		$posts  = WP_CLI::runcommand( 'post list --post_type=post --format=json', $args );

		// This is blank, just here when I need it.
	}

	// End all custom CLI commands.
}
