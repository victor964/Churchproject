<?php
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
define( 'DB_NAME', 'wordpress_vick' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         '[B;I`C:$N`?bzZ7XbA>)G@/S]j2YGq`y`XRp005xx-H=>/B}CMbzBiTF}O_t%bti' );
define( 'SECURE_AUTH_KEY',  'P$_wTpqYQU~S5Bzt~8BRx $SYpsId#axsaqiK;{KFqXu0DH]{Y48cCkz?&?T]I#[' );
define( 'LOGGED_IN_KEY',    '^$lup~#UEn:_?:lbpLA@-x7Bv^m4}&kkZh]UIqq^w?yR< J~GkUI<!3kns& P5y$' );
define( 'NONCE_KEY',        'VvDM7cuDbPF g?:w>C1KHVm4Awx6cZe;g-ERC el/^zL$Iuc`*v/x@R.e>}$/[ %' );
define( 'AUTH_SALT',        'flLm2(_X9XUnHIgWcSG  =V&&M>W^:aI8B?-Wh}m[o8Elw8K{<8vL)!vqW&O0(Fv' );
define( 'SECURE_AUTH_SALT', 'ay+$<NWlE< ^Y)BY /JkKO>=iQ&P^X8_Xuo/u?DM qynuyler!3uzt(1pj.ilN30' );
define( 'LOGGED_IN_SALT',   '|j:coRKj<<_Z|:*/5z7o$=?[UuA4):oOh(NCz!B+l*~pQnVwv8;~72Nu:.a*SW_%' );
define( 'NONCE_SALT',       'z*{5+wqaErLWg6R{bz!H:rutgm:_o__t]2] M*XDA#h>.@MW3[eaoN- (x5*$%^O' );

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
