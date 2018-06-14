<?php
/**
 * The functionality tied to the WP-CLI stuff.
 *
 * @package EnforceSingleCategory
 */

// Call our namepsace.
namespace EnforceSingleCategory;

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
	* Get the array of posts to check.
	*
	* @return array
	*/
	protected function get_multi_posts() {

		// Set my args.
		$args   = array(
			'return'     => true,   // Return 'STDOUT'; use 'all' for full object.
			'parse'      => 'json', // Parse captured STDOUT to JSON array.
			'launch'     => false,  // Reuse the current process.
			'exit_error' => true,   // Halt script execution on error.
		);

		// Get my posts.
		$ids    = WP_CLI::runcommand( 'post list --post_type=post --field=ID --format=json', $args );

		// Bail on empty or error.
		if ( empty( $ids ) || is_wp_error( $ids ) ) {
			WP_CLI::error( __( 'No post IDs could be retrieved.', 'enforce-single-category' ) );
		}

		// First run the recount function.
		WP_CLI::runcommand( 'term recount category --quiet=true' );

		// Set an empty data array.
		$build  = array();

		// Now loop the IDs.
		foreach ( $ids as $id ) {

			// Grab the terms.
			$terms  = WP_CLI::runcommand( 'post term list ' . absint( $id ) . ' category --field=term_id --format=json', $args );

			// Skip if empty or only 1.
			if ( empty( $terms ) || count( $terms ) < 2 ) {
				continue;
			}

			// Add our data array.
			$build[ $id ]   = $terms;
		}

		// Kill it if they have no multi.
		if ( empty( $build ) || is_wp_error( $build ) ) {
			WP_CLI::success( __( 'You have no posts with multiple categories assigned.', 'enforce-single-category' ) );
			WP_CLI::halt( 0 );
		}

		// Send it back.
		return $build;
	}

	/**
	 * Figure out the most popular category based on IDs.
	 *
	 * @param  array   $terms  The array of term IDs.
	 *
	 * @return integer
	 */
	protected function get_popular_category( $terms = array() ) {

		// Set my args.
		$args   = array(
			'return'     => true,   // Return 'STDOUT'; use 'all' for full object.
			'parse'      => 'json', // Parse captured STDOUT to JSON array.
			'launch'     => false,  // Reuse the current process.
			'exit_error' => true,   // Halt script execution on error.
		);

		// Set an empty.
		$setup  = array();

		// Loop my terms.
		foreach ( $terms as $term_id ) {

			// Get my count.
			$count  = WP_CLI::runcommand( 'term get category ' . absint( $term_id ) . ' --by=id --field=count --format=json', $args );

			// Add to the setup.
			$setup[ $term_id ] = $count;
		}

		// Sort by highest value, keeping our key association.
		arsort( $setup, SORT_NUMERIC );

		// Reset our pointer to the first element.
		reset( $setup );

		// Return the key of the first element in the array.
		return key( $setup );
	}

	/**
	 * Get the single category based on the type requested.
	 *
	 * @param  array   $terms   The array of term IDs.
	 * @param  string  $select  What sorting we want.
	 *
	 * @return integer
	 */
	protected function get_single_category( $terms = array(), $select = 'random' ) {

		// Start my switch.
		switch ( esc_attr( $select ) ) {

			case 'popular' :
				return $this->get_popular_category( $terms );
				break;

			case 'first' :
				return current( $terms );
				break;

			case 'last' :
				return end( $terms );
				break;

			case 'random' :
				return array_rand( array_flip( $terms ), 1 );
				break;

			default :
				return array_rand( array_flip( $terms ), 1 );

			// End all case breaks.
		}
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
	 * [--select]
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
			'select'    => 'random',
		));

		// Grab our posts with multiple categories.
		$posts  = $this->get_multi_posts();

		// Bail on no posts, which should have already happened.
		if ( ! $posts ) {
			return;
		}

		// Get the actual count.
		$count  = count( $posts );

		// Prompt to continue the process if the autoloop wasn't set.
		if ( $parsed['counts'] ) {

			// Show the count and bail.
			WP_CLI::success( sprintf( _n( 'You have %d post with multiple categories.', 'You have %d posts with multiple categories.', absint( $count ), 'enforce-single-category' ), absint( $count ) ) );
			WP_CLI::halt( 0 );
		}

		// Show the count as a context.
		WP_CLI::log( sprintf( _n( 'You have %d post with multiple categories.', 'You have %d posts with multiple categories.', absint( $count ), 'enforce-single-category' ), absint( $count ) ) );
		WP_CLI::log( '' );

		// Set a counter.
		$i = 0;

		// Now loop my posts and figure out which one we want.
		foreach ( $posts as $id => $terms ) {

			// Get the single term.
			$single = $this->get_single_category( $terms, $parsed['select'] );

			// Bail on empty or error.
			if ( ! $single ) {

				// Show the warning.
				WP_CLI::warning( __( 'The term not be determined for post ' . absint( $id ), 'enforce-single-category' ) );

				// And move alone.
				continue;
			}

			// Set my new term.
			WP_CLI::runcommand( 'post term set ' . absint( $id ) . ' category ' . absint( $single ) . ' --by=id --quiet=true' );

			// And increment the counter.
			$i++;
		}

		// Show the result and bail.
		WP_CLI::success( sprintf( _n( '%d post has been updated.', '%d posts have been updated.', absint( $i ), 'enforce-single-category' ), absint( $i ) ) );
		WP_CLI::halt( 0 );
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
		// This is blank, just here when I need it.
	}

	// End all custom CLI commands.
}
