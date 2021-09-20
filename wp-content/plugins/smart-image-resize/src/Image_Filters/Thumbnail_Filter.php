<?php

namespace WP_Smart_Image_Resize\Image_Filters;

use Intervention\Image\Constraint;
use Intervention\Image\Filters\FilterInterface;
use \Intervention\Image\Image;

class Thumbnail_Filter implements FilterInterface
{

    /**
     * The target thumbnail width/height.
     * @var array $size
     */
    protected $size;
    protected $preserveAspectRatio = true;
    protected $manager = false;
    protected $imageManager;


    public function __construct( $imageManager, $size, $preserveAspectRatio )
    {
        $this->size                = $size;
        $this->preserveAspectRatio = $preserveAspectRatio;
        $this->imageManager        = $imageManager;
    }

    /**
     * @param Image $image
     *
     * @return Image
     */
    public function applyFilter( Image $image )
    {
        $thumbnail = clone $image;

        if ( $this->preserveAspectRatio ) {
            $thumbnail->resize( $this->size[ 'width' ], $this->size[ 'height' ], function (
                $constraint
            ) {
                // Preserve the original aspect-ratio of the given image.
                $constraint->aspectRatio();
                // Let user decide whether to upscale image.
                // By default, upscaling image is disabled.
                if ( ! apply_filters( 'wp_sir_maybe_upscale', true ) ) {
                    $constraint->upsize();
                }
            } );
        } else {
            /** @var $constraint Constraint */
            $thumbnail->fit( $this->size[ 'width' ], $this->size[ 'height' ], function ( $constraint ) {
                if ( ! apply_filters( 'wp_sir_maybe_upscale', true ) ) {
                    $constraint->upsize();
                }
            } );
        }

        return $thumbnail->filter( new Recanvas_Filter( $this->imageManager, $this->size ) );

    }
}