<?php

namespace WP_Smart_Image_Resize;

use Exception;
use WP_Smart_Image_Resize\Exceptions\Invalid_Image_Meta_Exception;
use WP_Smart_Image_Resize\Image_Filters\Thumbnail_Filter;
use WP_Smart_Image_Resize\Image_Filters\Trim_Filter;
use WP_Smart_Image_Resize\Image_Filters\CreateWebP_Filter;
use WP_Smart_Image_Resize\Utilities\File;

/*
 * Class WP_Smart_Image_Resize\Image_Editor
 *
 * @package WP_Smart_Image_Resize\Inc
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( '\WP_Smart_Image_Resize\Image_Editor' ) ) :

    final class Image_Editor
    {
        use Processable_Trait;
        use Runtime_Config_Trait;

        /**
         * @var \WP_Smart_Image_Resize\Image_Editor
         */
        protected static $instance = null;


        /**
         * @return \WP_Smart_Image_Resize\Image_Editor
         */
        public static function get_instance()
        {
            if ( is_null( static::$instance ) ) {
                static::$instance = new self;
            }

            return static::$instance;
        }

        /**
         * Register hooks.
         */
        public function run()
        {

            // A low priority < 10 to let plugins optimize thumbnails.
            add_filter( 'wp_generate_attachment_metadata', [ $this, 'processImage' ], 9, 2 );

            // Prevent WooCommerce from resizeing images on the fly.
            add_filter( 'woocommerce_image_sizes_to_resize', '__return_empty_array' );

            // Disable photon.
            add_filter( 'jetpack_photon_skip_image', '__return_true' );

            // Don't use remotely-resized images with Jetpack Photon.
            add_filter( 'jetpack_photon_override_image_downsize', '__return_true', 19 );

            // Force 1:1 size for single product thumbnail.
            // @see  force_square_woocommerce_single()
            add_filter( 'woocommerce_get_image_size_single', [ $this, 'forceSquareWooCommerceSingle' ] );

            // Force woocommerce single on single product page.
            // @see force_woocommerce_single()
            add_filter( 'woocommerce_gallery_image_size', [ $this, 'forceWooCommerceSingle' ], PHP_INT_MAX );

            add_filter( 'regenerate_thumbnails_options_onlymissingthumbnails', '__return_false' );
        }

        /**
         * Determine whether the given size is selected.
         *
         * @param array $sizes
         *
         * @return bool
         */
        public function isProcessableSize( $sizes )
        {
            if ( ! is_array( $sizes ) ) {
                $sizes = (array)$sizes;
            }

            $selected_sizes = apply_filters( 'wp_sir_sizes', wp_sir_get_settings()[ 'sizes' ] );

            return count( array_intersect( $sizes, $selected_sizes ) ) === count( $sizes );
        }

        /**
         * Use 1:1 for single size when selected.
         *
         * @param string|array
         *
         * @return array
         */


        public function forceSquareWooCommerceSingle( $size )
        {
            if ( ! $this->isProcessableSize( [ 'woocommerce_single', 'shop_single' ] ) ) {
                return $size;
            }

            // If height is not set, make it square.
            if ( $size[ 'width' ] && ! $size[ 'height' ] ) {
                $size[ 'height' ] = $size[ 'width' ];
            }

            return $size;
        }

        /**
         * Force woocommerce_single size on single product page.
         *
         * @hook woocommerce_gallery_image_size
         *
         * @param string $size
         *
         * @return string
         */
        public function forceWooCommerceSingle( $size )
        {
            if ( ! apply_filters( 'wp_sir_force_woocommerce_single', true ) ) {
                return $size;
            }

            if ( $this->isProcessableSize( [ 'woocommerce_single', 'shop_single' ] ) ) {
                return 'woocommerce_single';
            }

            return $size;
        }

        /**
         * Proceed resize.
         *
         * @param array $metadata
         * @param int $imageId
         *
         * @return array
         */

        public function processImage( $metadata, $imageId )
        {
            try {

                $settings = wp_sir_get_settings();

                if ( ! $settings[ 'enable' ] ) {
                    return $metadata;
                }

                
                if ( Quota::isExceeded() ) {
                    return $metadata;
                }
                

                $imageMeta = new Image_Meta( $imageId, $metadata );

                if ( ! $this->isProcessable( $imageId, $imageMeta ) ) {
                    return $metadata;
                }

                $imageManager = new Image_Manager( [ $metadata[ 'width' ], $metadata[ 'height' ] ] );

                // Let's try to load the given image to memory,
                // To do so, we need to try raising memory limit.
                $image = $imageManager->make( $imageMeta->getOriginalFullPath() );

                @set_time_limit( 0 );

                $imageMeta->setMimeType( $image->mime() );

                $image->filter( new Trim_Filter( $imageMeta ) );


                $imageMeta->setBackup();

                $imageMeta->clearSizes();

                // TODO: Check if server memory limit can be high enough to generate all selected sizes, otherwise
                // limit to only the woocommerce_ sizes.
                $sizesToGenerate = $this->getSizesToGenerate();

                $sameSizes = [];

                foreach ( $sizesToGenerate as $sizeName => $sizeData ) {
                    @set_time_limit( 0 );

                    // Ignore duplicated sizes.
                    $sizeHash = $sizeData[ 'width' ] . '|' . $sizeData[ 'height' ];

                    if ( isset( $sameSizes[ $sizeHash ] ) ) {
                        $imageMeta->setSizeData( $sizeName, $imageMeta->getSizeData( $sameSizes[ $sizeHash ] ) );
                        continue;
                    }

                    // Should force-crop the image or preserve the aspect-ratio.
                    $preserveAspectRatio = filter_var( $settings[ 'preserve_aspect_ratio' ], FILTER_VALIDATE_BOOLEAN );

                    $thumb = $image->filter( new Thumbnail_Filter( $imageManager, $sizeData, $preserveAspectRatio ) );

                    $thumbPath = $this->generateThumbPath( $image->basePath(), $sizeData, $sizeName, $imageId );

                    File::delete( $thumbPath );

                    $quality = 100 - intval( $settings[ 'jpg_quality' ] );

                    $quality = apply_filters( 'jpeg_quality', $quality, 'image_resize' );

                    $thumb->save( $thumbPath, $quality );

                    $imageMeta->setSizeData( $sizeName, [
                        'width'     => $thumb->getWidth(),
                        'height'    => $thumb->getHeight(),
                        'file'      => $thumb->basename,
                        'mime-type' => $thumb->mime(),
                    ] );

                    $sameSizes[ $sizeHash ] = $sizeName;


                    $thumb->destroy();
                }

                $image->destroy();

                $imageMeta->markSizesRegenerated();

                $this->deleteOrphanThumbnails( $metadata, $imageMeta->toArray() );

                
                Quota::consume( $imageId );
                

                return $imageMeta->toArray();

            }
            catch ( Invalid_Image_Meta_Exception $e ) {
                return $metadata;
            }
            catch ( Exception $e ) {
                wp_send_json_error( [
                    'message' => "Smart Image Resize: {$e->getMessage()}",
                ] );

                return $metadata;
            }
        }

        private function deleteOrphanThumbnails( $oldMeta, $newMeta )
        {
            if ( empty( $oldMeta[ 'sizes' ] ) ) {
                return;
            }

            $oldFileNames = array_map( function ( $size ) {
                return $size[ 'file' ];
            }, $oldMeta[ 'sizes' ] );

            $newFileNames = array_map( function ( $size ) {
                return $size[ 'file' ];
            }, $newMeta[ 'sizes' ] );

            $uploadsPath  = wp_get_upload_dir()[ 'basedir' ];
            $imageDirPath = trailingslashit( $uploadsPath ) . trailingslashit( dirname( $oldMeta[ 'file' ] ) );

            foreach ( $oldFileNames as $file ) {

                // Prevent accidently deleting original image.
                if ( $file === basename( $oldMeta[ 'file' ] ) ) {
                    continue;
                }

                if ( ! in_array( $file, $newFileNames ) ) {

                    // Delete old thumbnails, including JPG-converted images as well.
                    File::delete( $imageDirPath . $file );

                    // Delete old WebP images if present.
                    
                    $webp = $imageDirPath . File::mb_pathinfo( $file, PATHINFO_FILENAME ) . '.webp';
                    File::delete( $imageDirPath . $webp );
                }
            }
        }

        /**
         * Return sizes to resize.
         *
         * @return array
         */

        private function getSizesToGenerate()
        {
            $sizeNames = apply_filters( 'wp_sir_sizes', wp_sir_get_settings()[ 'sizes' ] );

            $sizes = [];

            foreach ( $sizeNames as $sizeName ) {
                $size = wp_sir_get_size_dimensions( $sizeName );

                if ( ! empty( $size ) ) {
                    $sizes[ $sizeName ] = $size;
                }
            }

            if ( ! apply_filters( 'wp_sir_enable_hd_sizes', false ) ) {
                unset( $sizes[ '2048x2048' ] );
                unset( $sizes[ '1536x1536' ] );
            }

            return apply_filters( 'wp_sir_sizes', $sizes );
        }


        /**
         * @param string $sourcePath
         * @param array $size
         * @param string $sizeName
         * @param int $imageId
         *
         * @return string
         */

        public function generateThumbPath(
            $sourcePath,
            $size,
            $sizeName,
            $imageId
        ) {
            $sourceInfo = File::mb_pathinfo( $sourcePath );

            if ( wp_sir_get_settings()[ 'jpg_convert' ]
                 && ! in_array( $sourceInfo[ 'extension' ], [ 'jpg', 'jpeg' ] ) ) {
                $extension = 'jpg';
            } else {
                $extension = $sourceInfo[ 'extension' ];
            }
            $basename = sprintf(
                '%s-%dx%d.%s',
                $sourceInfo[ 'filename' ],
                $size[ 'width' ],
                $size[ 'height' ],
                $extension
            );


            $path = trailingslashit( $sourceInfo[ 'dirname' ] ) . $basename;

            return apply_filters( 'wp_sir_thumbnail_save_path', $path, $sizeName, $imageId );
        }

    }


endif;
