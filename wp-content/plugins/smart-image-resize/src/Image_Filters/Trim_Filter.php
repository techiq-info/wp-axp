<?php

namespace WP_Smart_Image_Resize\Image_Filters;

use Imagick;
use \Intervention\Image\Filters\FilterInterface;
use Exception;
use Intervention\Image\Image;
use WP_Smart_Image_Resize\Image_Meta;
use WP_Smart_Image_Resize\Utilities\Env;

class Trim_Filter implements FilterInterface
{

    /**
     * The image meta helper instance.
     * @var Image_Meta $imageMeta
     */
    protected $imageMeta;

    public function __construct( $imageMeta )
    {
        $this->imageMeta = $imageMeta;
    }

    /**
     * Set trimmed image dimensions.
     *
     * @param $image Image
     */
    private function setNewDimensions( $image )
    {
        $this->imageMeta->setMetaItem( '_trimmed_width', $image->getWidth() );
        $this->imageMeta->setMetaItem( '_trimmed_height', $image->getHeight() );
    }

    /**
     * @param Image $image
     *
     * @return Image
     */
    public function applyFilter( Image $image )
    {
        $settings = wp_sir_get_settings();

        if ( ! $settings[ 'enable_trim' ] ) {

            // Chances the trim feature was re-disabled.
            // In this case, we need revert to original dimensions
            // to prevent zoomed image from being stretshed.
            $this->setNewDimensions( $image );

            return $image;
        }

        try {
            /** @var Imagick $core */
            $core = is_object( $image->getCore() ) ? ( clone $image->getCore() ) : null;

            $feather = (int)apply_filters( 'wp_sir_trim_feather', intval( $settings[ 'trim_feather' ] ) );

            $color = sanitize_hex_color( $settings[ 'bg_color' ] ) ?: null;

            $tolerance = (int)apply_filters( 'wp_sir_trim_tolerance', 3 );

            $image->trim( null, null, $tolerance );

            $this->addBorder( $image, $feather );

            /*
             * Retry trimming if `Image::trim()` failed for Imagick.
             * @experimental
             */
            if ( $this->isBlankImage( $image, $feather ) && Env::imagick_loaded() && $core instanceof Imagick ) {
                $core->trimImage( 0 );

                // Let imagick set new width and height.
                $core->setImagePage( 0, 0, 0, 0 );

                // Add a border if present.
                if ( $feather > 0 ) {

                    if ( ! $color ) {
                        $image->setCore( $core );
                        $this->addBorder( $image, $feather );

                    } else {
                        $core->borderImage( $color, $feather, $feather );
                        $image->setCore( $core );
                    }
                }

            } elseif ( is_object( $core ) ) {
                $core->destroy();
            }

        } catch ( Exception $e ) {
        }

        // Change to new dimensions
        // or revert to original ones. 
        $this->setNewDimensions( $image );

        return $image;
    }

    /**
     * @param $image Image
     * @param $feather
     *
     * @return array Array{width,height}
     */
    private function getCanvasSize( $image, $feather )
    {
        return [
            ( $image->getWidth() + ( $feather * 2 ) ),
            ( $image->getHeight() + ( $feather * 2 ) ),
        ];
    }

    private function addBorder( &$image, $feather )
    {

        $blankImage = $this->isBlankImage( $image, $feather );

        if ( $feather > 0 && ! $blankImage ) {
            list( $w, $h ) = $this->getCanvasSize( $image, $feather );
            $image->resizeCanvas( $w, $h, 'center' );
        }
    }

    private function isBlankImage( $image, $feather )
    {
        $trimedWidth  = max( 1, $image->getWidth() - $feather * 2 );
        $trimedHeight = max( 1, $image->getHeight() - $feather * 2 );

        return ( $trimedWidth === 1 || $trimedHeight === 1 );
    }

}