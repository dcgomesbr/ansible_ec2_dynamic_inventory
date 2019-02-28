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
define('DB_NAME', 'wordpressdb');

/** MySQL database username */
define('DB_USER', 'wordpressdba');

/** MySQL database password */
define('DB_PASSWORD', 'somepassword');

/** MySQL hostname */
define('DB_HOST', 'wordpressdb.c5drxn62ygrq.us-east-1.rds.amazonaws.com');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

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
define('AUTH_KEY',         '_yxzGHl`Dy9h% +w%ceR}1v@6|cW0-KbC#%Dq2H?Nn2<C,Wjk|g3U+I(nee~VkzA');
define('SECURE_AUTH_KEY',  '/$sz;,r%9|@n]7Lo,r>r{}5;Nk=E~C4s7ep.GfHU]:]U:33e`[@JFOcE]aXz$/u1');
define('LOGGED_IN_KEY',    '?-8Of&VG|`T##_rN)Jf6Q%i[#X3I_7j}D0>#:_qJRJhPo)JOGzaLMb5F}s[W#oX+');
define('NONCE_KEY',        'E3m~pjNGkG6 1uC7%S_6{<//Ho>y[RyOe}~i.-xs++qL=rB?1]s>3*>Lu5Z.WSV/');
define('AUTH_SALT',        '<V_VC}Rt1`xlapd:c7<C#p-&g~}?-*QtW-bYK51kT>Equ>|}y8H?yq~?k/WFa,<r');
define('SECURE_AUTH_SALT', 'CiFvZHoTVGF_/y|Kl!N$yt&leq~sNDkY)+Os!iZwBZxL3e9*UDDd-M,~YV%X`ORC');
define('LOGGED_IN_SALT',   'gP E[/dovK.?e3zS9xx>:1d~ev76rTdJT=&%{1wpRp^Z;uMbQ)R@>n/;u`5SD&0.');
define('NONCE_SALT',       '?e}%ALi!k>Ge;}-<ldbD),m{,:=6]=x{q`QrXj05%wZQ?Q6$t>JonA=ymdp5>{Cz');

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
