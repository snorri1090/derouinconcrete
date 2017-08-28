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
define('DB_NAME', 'derouinc_wp4');

/** MySQL database username */
define('DB_USER', 'derouinc_wp4');

/** MySQL database password */
define('DB_PASSWORD', 'ZzP5Y6qrtAcvu9Ba');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '`b/W2B?~F+h9y-nG#&*Fp]02C>]t(,Qk&sU=G81?;6HOSsdm.T<e^litX#mp7Ur|');
define('SECURE_AUTH_KEY',  'hJQTe+^Iyx?znKz*}4--KYm({L(N/+KUyLsUFjhL_P27;&e]r;EITf?(ZR-HZXQO');
define('LOGGED_IN_KEY',    '*=])k*$_BQM UKejeg:)wc<68u!Mc0EJH.XX../+ggIKaH^%.~}j?.C-[m6(j3-G');
define('NONCE_KEY',        'T+>8zI8;|UP SwdBmdo6*jntR+`Hk?oo[YtIj#7|U8lUG 4.m>{H,Y6`$|-+bJ68');
define('AUTH_SALT',        '<?M+[=S_wf+fm;##&or m31xjH$G[i,g|#_V4g+t9t}sf* L3(CAo4+(?@e !6mJ');
define('SECURE_AUTH_SALT', 'eh(0{H!6Q#1)c-yZb|q,*TA(X(7g|tl>CI:}dB!-uI F{r0_^B-.LhFjoNmjhMn|');
define('LOGGED_IN_SALT',   'H2zn+W$]#lI37boaTUNDBPzW_IDQ>lz+Y$PmsgjS98~N^qJ9vKD+(~!B|-&GsU~.');
define('NONCE_SALT',       'iG:qdwX[KUckblqT8qfr[C`-=2oUxrS{zh,.F7g-Vmtw=O#B+E=ve|~VOJ?A|d|-');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
