<?php

namespace WP_Smart_Image_Resize\Image_Filters;

use WP_Smart_Image_Resize\Utilities\File;
use Intervention\Image\Filters\FilterInterface;
use Intervention\Image\Image;
use Exception;

class CreateWebP_Filter implements FilterInterface
{

    /**
     * The output image full path.
     *
     * @var string $path
     */
    protected $path;

    public function __construct( $path )
    {
        $this->path = $path;
    }

    public function applyFilter( Image $image )
    {
        if ( ! wp_sir_get_settings()[ 'enable_webp' ] ) {
            return $image;
        }

        try {
            File::delete( $this->path );

            $_image = clone $image;
            $_image->save( $this->path )->destroy();
        } catch ( Exception $e ) {
        }

        return $image;

    }
}