<?php
/** Enable W3 Total Cache */
define('WP_CACHE', true); // Added by W3 Total Cache

if (strpos($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') !== false)
$_SERVER['HTTPS']='on';
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

/** Database username */
define( 'DB_USER', 'admin' );

/** Database password */
define( 'DB_PASSWORD', 'pass123456' );

/** Database hostname */
define( 'DB_HOST', 'database-1.csglre67cqrg.ap-south-1.rds.amazonaws.com' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         ']=Y$ ?zJg[ 9*^x|vL<f%(=-vd_&Eg T5.,L?8r-`h:l}o-yBD?+^=~=c!o,ptcK' );
define( 'SECURE_AUTH_KEY',  'I>:?0k&:wQ`11Oc6lx4%1k|c<]q,Z5^wi:?byBAbGp#qY|kKPWo)DD~YFt_P|V,P' );
define( 'LOGGED_IN_KEY',    '7GoGWXwunvFGUq`9tUq6Td|+jx+Yew|%xWfq18f2/vGyJof0y;u#-SUu. AQ0[RV' );
define( 'NONCE_KEY',        ')qVpW:J^n##yH-(/8|fQ|COG)#ToggBR}De(A~bdg+z9:Ci]r_fzM/XPjyBO6BfG' );
define( 'AUTH_SALT',        ':F[9.Kcs& xm^mS~E=C:[kyB]#<:Zr%w~AJY$%A:m -Dk571Zn%~_{D4z|.dB .*' );
define( 'SECURE_AUTH_SALT', 'M|PYM%bE3~-beoF@sF0{J5cV8IW<|_~pA@m6k~By|uqk458r5B6ozs1EvrCjl/|E' );
define( 'LOGGED_IN_SALT',   'RMXS(J#]}7b_WpPl JBA1l}d+?r8F~c^4`^7]qpObV~r-]D0Ij-<dSiD+TV:wh&h' );
define( 'NONCE_SALT',       '^{O5[qZX$vE/$2k8k4w=uyNN!pL*z@~G(p-L<xZJlLF+jF$a(cq(Ty=I]d)h{w@}' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
