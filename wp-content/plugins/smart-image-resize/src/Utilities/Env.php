<?php

namespace WP_Smart_Image_Resize\Utilities;

use Imagick;

class Env
{
    public static function imagick_loaded()
    {
        return extension_loaded( 'imagick' ) && class_exists( Imagick::class );
    }

    public static function gd_loaded()
    {
        return extension_loaded( 'gd' ) && function_exists( 'gd_info' );
    }

    /**
     * Check whether Imagick extension is loaded and support WebP.
     * @return bool
     */
    public static function imagick_supports_webp()
    {
        return self::imagick_loaded() && Imagick::queryFormats( 'WEBP' );
    }

    /**
     * Check whether GD extension is loaded and support WebP.
     * @return bool
     */

    public static function gd_supports_webp()
    {
        return function_exists( 'imagewebp' );
    }

    public static function browser_supposts_webp()
    {
        // TODO: Ajax requests don't include the 'image/webp' in the Accept header.
        return isset( $_SERVER[ 'HTTP_ACCEPT' ] ) && strpos( $_SERVER[ 'HTTP_ACCEPT' ], 'image/webp' ) !== false;

    }

    public static function supports_webp()
    {
        return self::imagick_supports_webp() || self::gd_supports_webp();
    }
}
