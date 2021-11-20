<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', 'root' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'D3aJF/mf2cQU8DeO7tRunsCORDk19JIL86AcJFYJ+MTKvMLb0OZXr6VAzBJN01dEnQlm1icx7OKt8g75Nl1n2w==');
define('SECURE_AUTH_KEY',  'UsudDl483L9qfehHS6fUcKJiJCO7sRY4t78VGA0k7qsdfXVmPNQbADS3nkfDWRmmUxG8UYuL7tC5dD/H9X0xiA==');
define('LOGGED_IN_KEY',    'D5LMW3Zjgwy3r+7+OGLIVY0LHZV6PYVE8irFkKhLgJ2iu5pgrUjcAo0GPmDXblVYD8L6g9Sw98ZcXboRgsHaHg==');
define('NONCE_KEY',        '0FzdqV6W0lLc40MiX1zAaHDj0AApaGr9gxp99H1J9WGEnqxHU9dawmNPx3dVmz/b0KDvV7yGTmEXsw+IUCyQZg==');
define('AUTH_SALT',        'KM47LQ2NxKMB0ncj1W/4ABJUJ09U9YOV9Zy/BcqVqlPsI4mYyMnoMIY5oXb9t+CRzDRzuMVe9SF7rPUko/UEZg==');
define('SECURE_AUTH_SALT', 'A9gwaUfp1kq/FsLJqwvdaldEuG04EYlclugMxftg7Uv/1TqJodtBa+9HtmVYQzbL1UqEXBf6u1v+O0Z7FV7UaQ==');
define('LOGGED_IN_SALT',   'SUvyDF1I1ObVBkvHIwpkVLpg7DCQNKDkr/kohwq0Zh7djN1iMbynZtIobJ69FrpM4Oa2ABlBnwl9dTh/iJ2d2Q==');
define('NONCE_SALT',       'rse2PkPtVBVfL6B7gdZFBsZX8gtVWcqXBsoo4JgYcGra9KDnHHf92yzztyi87oUMGLTlb8xGXKujdGvgKlVn6g==');

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';




/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
