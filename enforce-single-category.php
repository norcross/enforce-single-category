<?php
/**
 * Plugin Name:     Enforce Single Category
 * Plugin URI:      https://github.com/norcross/enforce-single-category
 * Description:     Ensure only a single category is used on a post
 * Author:          Andrew Norcross
 * Author URI:      http://andrewnorcross.com
 * Text Domain:     enforce-single-category
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         EnforceSingleCategory
 */

// Call our namepsace.
namespace EnforceSingleCategory;

// Call our CLI namespace.
use WP_CLI;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Define our version.
define( __NAMESPACE__ . '\VERS', '0.1.0' );

// Plugin Folder URL.
define( __NAMESPACE__ . '\URL', plugin_dir_url( __FILE__ ) );

// Plugin root file.
define( __NAMESPACE__ . '\FILE', __FILE__ );

// Set our assets directory constant.
define( __NAMESPACE__ . '\ASSETS_URL', URL . 'assets' );

// Go and load our files.
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/post-edit.php';
require_once __DIR__ . '/includes/quick-edit.php';
require_once __DIR__ . '/includes/save-post.php';

// Check that we have the constant available.
if ( defined( 'WP_CLI' ) && WP_CLI ) {

	// Load our commands file.
	require_once dirname( __FILE__ ) . '/includes/commands.php';

	// And add our command.
	WP_CLI::add_command( 'enforce-singlecat', Commands::class );
}
