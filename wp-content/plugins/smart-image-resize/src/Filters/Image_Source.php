<?php

namespace WP_Smart_Image_Resize\Filters;

use Exception;

use WP_Smart_Image_Resize\Utilities\Request;
use WP_Smart_Image_Resize\Utilities\Env;
use WP_Smart_Image_Resize\Image_Filters\CreateWebP_Filter;
use WP_Smart_Image_Resize\Image_Manager;
use WP_Smart_Image_Resize\Image_Meta;

class Image_Source extends Base_Filter
{
    public function listen()
    {
    }


}
