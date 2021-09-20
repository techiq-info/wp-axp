<?php

namespace WP_Smart_Image_Resize\Utilities;

class File
{
    public static function delete( $files )
    {
        if ( ! is_array( $files ) ) {
            $files = (array)$files;
        }

        foreach ( $files as $file ) {
            if ( file_exists( $file ) ) {
                @unlink( $file );
            }
        }
    }

   
    public static function mb_pathinfo($path, $options = null)
    {
        $locale = setlocale(LC_ALL, 0);

        setlocale(LC_ALL,'en_US.UTF-8');

       if( is_null( $options ) ){
           $info = pathinfo($path);
       }
       else{
           $info = pathinfo($path, $options);
       }

        setlocale(LC_ALL, $locale);

       return $info;

    }

}
