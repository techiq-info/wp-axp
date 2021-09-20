<?php

namespace WP_Smart_Image_Resize;

class Process_Media_Library_Upload
{
    use Singleton_Trait;

    function initialize(){
        add_action( 'pre-html-upload-ui', [ $this, 'render_form' ] );
        add_action( 'pre-plupload-upload-ui', [ $this, 'render_form' ] );
    }

    static function is_media_screen()
    {

        require_once(ABSPATH . 'wp-admin/includes/screen.php');

        if( ! function_exists('get_current_screen') ){
            return false;
        }

        $screen = get_current_screen();

        return $screen
            && is_object( $screen )
            && in_array( $screen->id , ['media', 'upload']);
    }

    function render_form()
    {
        if ( ! static::is_media_screen() ) {
            return;
        }
        ?>
        <div class="wpsirProcessMediaLibraryImageWraper">
            <h3 class="wpsirProcessMediaLibraryImageTitle">Smart Image Resize:</h3>
            <label for="processMediaLibraryImage"><input
                        id="processMediaLibraryImage"
                        type="checkbox"
                        class="wp-sir-as-toggle">
                <span ><?php esc_html_e( 'When uploading an image, resize it to match your settings.',
                        'wp-smart-image-resize' ); ?></span></label>
        </div>
        <?php
    }
}

Process_Media_Library_Upload::instance()->initialize();
