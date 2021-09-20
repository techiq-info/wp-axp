<?php

namespace WP_Smart_Image_Resize;

use Exception;
use Intervention\Image\ImageManager;
use WP_Smart_Image_Resize\Utilities\Env;

class Image_Manager extends ImageManager
{
    /**
     * @param $size
     */
    public function __construct( $size )
    {

        $driver = apply_filters( 'wp_sir_driver', $this->checkDriver( $size ) );
        if ( ! empty( $driver ) ) {
            parent::__construct( compact( 'driver' ) );
        } else {
            parent::__construct();
        }
    }

    public function make( $data )
    {
        wp_raise_memory_limit( 'image' );

        return parent::make( $data );
    }

    /**
     * We hope Imagick is compiled with libwebp
     * This will make image manipulation fast.
     *
     * We'll try to check if Imagick can be used
     * whether WebP setting is enabled.
     *
     * @param $size
     *
     * @return bool
     */
    private function checkDriver( $size )
    {
        if ( Env::gd_loaded() && $this->isLargeImage( $size ) && ! wp_sir_get_settings()[ 'enable_trim' ] ) {
            return 'gd';
        }

        if ( Env::imagick_loaded() && ! wp_sir_get_settings()[ 'enable_webp' ] ) {
            return 'imagick';
        }

        if ( Env::imagick_supports_webp() && wp_sir_get_settings()[ 'enable_webp' ] ) {
            return 'imagick';
        }

        if ( Env::imagick_loaded() && ! Env::gd_loaded() ) {
            return 'imagick';
        }

        if ( Env::imagick_loaded() && ! Env::gd_supports_webp() && wp_sir_get_settings()[ 'enable_webp' ] ) {
            return 'imagick';
        }

        return false;
    }

    private function isLargeImage( $size )
    {
        return $size[ 0 ] > 1500 || $size[ 1 ] > 1500;
    }

    /**
     * Check if the manager is using Imagick driver.
     *
     * @return bool
     */

    public function usingImagick()
    {
        return $this->config[ 'driver' ] === 'imagick';
    }

    /**
     * Check if the manager is using GD driver.
     *
     * @return bool
     */
    public function usingGD()
    {
        return $this->config[ 'driver' ] === 'gd';
    }
}
