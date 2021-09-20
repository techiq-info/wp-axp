<?php

namespace WP_Smart_Image_Resize\Filters;

use Exception;
use WP_Smart_Image_Resize\Helper;

class Filter_Processable_Regenerate_Thumbnails extends Base_Filter
{

    public function listen()
    {
        add_filter( 'rest_attachment_query', [ $this, 'filterProcessableImages' ], 99, 2 );
    }

    /**
     * TODO: handle '_processed_at' flag when a post type or tax is no longer processable.
     */
    public function findProcessableImages()
    {
        global $wpdb;

        $post_types             = wp_sir_get_processable_post_types();
        $taxonomies             = wp_sir_get_processable_taxonomies();
        $post_types_placeholder = Helper::array_sql_placeholder( $post_types );
        $taxonomies_placeholder = Helper::array_sql_placeholder( $taxonomies );

        $sqlParts = [];

// Post-attached images to process.
        if ( !empty( $post_types ) ) {
            $sqlParts[] = "( SELECT pm.meta_value FROM $wpdb->posts p INNER JOIN $wpdb->postmeta pm ON pm.post_id = p.ID WHERE pm.meta_key IN ( '_thumbnail_id','_product_image_gallery' ) AND p.post_type IN ( $post_types_placeholder ) )";
        }

// Taxonomy-attached images to process.
        if ( !empty( $taxonomies ) ) {
            $sqlParts[] = "( SELECT tm.meta_value FROM $wpdb->termmeta tm INNER JOIN $wpdb->term_taxonomy tt ON tt.term_id = tm.term_id WHERE tm.meta_key = 'thumbnail_id' AND tt.taxonomy IN ( $taxonomies_placeholder ) )";
        }

// Already processed images.
        $sqlParts[] = "(SELECT p.ID FROM $wpdb->posts p INNER JOIN $wpdb->postmeta pm ON p.ID = pm.post_id WHERE pm.meta_key = '_processed_at' AND pm.meta_value != '' )";

        $sql = implode( ' UNION ', $sqlParts );

        if ( !empty( $taxonomies ) || !empty( $post_types ) ) {
            $sql = $wpdb->prepare( $sql, array_merge( $post_types, $taxonomies ) );
        }

        $image_ids = $wpdb->get_col( $sql );
        $image_ids = explode( ',', implode( ',', $image_ids ) );

        // Do some clean up.
        return array_map( 'intval', array_unique( array_filter( $image_ids ) ) );

    }

    /**
     * Filter out processable images
     *
     * @param array $args
     * @param \WP_REST_Request $request
     * @return array|null
     */
    public function filterProcessableImages( $args, $request )
    {
        if ( !wp_sir_get_settings()[ 'enable' ] ) {
            return $args;
        }

        if ( !$request->get_param( 'is_regeneratable' ) ) {
            return $args;
        }

        // Let developers disable filtering.
        if ( !apply_filters( 'wp_sir_filter_processable_images_rt', true ) ) {
            return $args;
        }

        try {

            if ( !has_filter( 'wp_sir_is_attached_to' ) ) {
                $image_ids = $this->findProcessableImages();
            } else {
                global $wpdb;

                $sql = "SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%' AND post_status != 'trash'";

                $image_ids = $wpdb->get_col( $sql );
                $image_ids = array_filter( $image_ids, 'wp_sir_is_processable' );
            }

            if ( empty( $image_ids ) ) {
                $image_ids = [ -1 ];
            }

            $args[ 'post__in' ] = $image_ids;
        } catch ( Exception $e ) {

        }
        return $args;
    }

}