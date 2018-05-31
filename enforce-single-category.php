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

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Define our version.
define( __NAMESPACE__ . '\VERS', '0.1.0' );

// Go and load our files.
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/post-edit.php';
require_once __DIR__ . '/includes/quick-edit.php';
