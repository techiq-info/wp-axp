<?php
define('WP_CACHE', false); // Added by WP Rocket
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
define('FS_METHOD', 'direct');
// ** MySQL settings ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'bennys_db' );

/** MySQL database username */
// define( 'DB_USER', 'bennys_wp' );
define( 'DB_USER', 'biri' );

/** MySQL database password */
// define( 'DB_PASSWORD', 'SsiyrdkggJyr' );
define( 'DB_PASSWORD', 'Admin123#' );

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
define( 'AUTH_KEY',          'SZmma J b6iKW5,VQ9`L7MW963(_ds^O7 TxT;x|l,e[Rj;mZXW0=3as0bDts-j7' );
define( 'SECURE_AUTH_KEY',   'M0XPG]3o;1p9PqmT(M_Lo$+NFZvj^[*/B*kO!4P;Hr8DC7e_E-iol&N8qRvd5v[9' );
define( 'LOGGED_IN_KEY',     'ARd}8[aG0oE}${TqNm? iFX!JdG?Rc}0Tn8ScKGfBA?N)l}UIGe<pkIvqsO+k56t' );
define( 'NONCE_KEY',         '.QWBuvej9=(v5)k3:*u-J %ud#]XIE1@=6FiBIWb#VRQN}4(vj87{wZMyiI/6|I}' );
define( 'AUTH_SALT',         ';26I{V`==yd()QZI_p;#wAxYg>!ol0n-GXrxD_HxhV5fJ3UcUw4G+6rtJf*t57_o' );
define( 'SECURE_AUTH_SALT',  '#wI!t&rb }9tkd|VO1.6{HvynkK14UZmjqKO+l%[rYY8#,QuJ?zeOI?6xt9r@t$}' );
define( 'LOGGED_IN_SALT',    'O8hp;#i 20-^rayq=O(hC^UAz8)V):O] Ze`,&yA2~ePY(Vs.=^riMg^KW:gx=gd' );
define( 'NONCE_SALT',        'tWIJii;(x2L|(RzL:`-Gq#,p|&%um?YboF7I*Y.ZfdBTUP$]dq5,!cIQIu[:RZnC' );
define( 'WP_CACHE_KEY_SALT', 'zE=moGu$Izc7]l5/OH!-F(tJdB{5:m)j3FXrtw;(NUX~qOUfxd%U$W,jm2x>K%^x' );

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) )
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
