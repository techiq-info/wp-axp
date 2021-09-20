<?php
$activate = get_option('okapi_wasb_activate', 2);
$display_on_mobile = get_option('okapi_wasb_display_on_mobile', 'TRUE');
$display_on_tablet = get_option('okapi_wasb_display_on_tablet', 'TRUE');
$display_on_desktop = get_option('okapi_wasb_display_on_desktop', 'TRUE');
$position = get_option('okapi_wasb_position', 3);
$number = get_option('okapi_wasb_number', '');
$msg = get_option('okapi_wasb_msg', 'Hi');
$width = get_option('okapi_wasb_width', 75);
$height = get_option('okapi_wasb_height', 75);
$margin = get_option('okapi_wasb_margin', 15);
$icon_type = get_option('okapi_wasb_icon_type', 1);
$icon_id = get_option('okapi_wasb_icon_id', 0);
$icon_src = OKAPI_WASB_DEFAULT_IMG;
$default_src = OKAPI_WASB_DEFAULT_IMG;

$icon_attachment = wp_get_attachment_image_src($icon_id, 90);
if(isset($icon_attachment[0]) && $icon_type == 2){
    $icon_src = $icon_attachment[0];
}
?>
<div id="wpbody" role="main">
    <div id="wpbody-content" aria-label="Main content" tabindex="0">
        <h3 style="color: #28D044;"><?php _e('If you have any suggestion or want to free/paid support, feel free to contact me at contact2farazquazi@gmail.com', 'wa-sticky-button'); ?></h3>
        <div class="wrap" style="padding-bottom: 300px;">
            <table class="widefat">
                <thead>
                    <tr>
                        <th colspan="3">
                            <h1><?php _e('WhatsApp Sticky Button - Settings', 'wa-sticky-button'); ?></h1>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="okapi-wasb-td-1">
                            <?php _e('Activate', 'wa-sticky-button'); ?> <span class="required">*</span>  
                        </td>
                        <td class="okapi-wasb-td-2">
                            <select id="okapi-wasb-activate" class="okapi-wasb-form-element">
                                <option value="1" <?php if($activate == '1'){ echo 'selected'; } ?> ><?php _e('Yes', 'wa-sticky-button'); ?></option>
                                <option value="2" <?php if($activate == '2'){ echo 'selected'; } ?> ><?php _e('No', 'wa-sticky-button'); ?></option>
                            </select>
                            <div class="okapi-wasb-error"></div>
                        </td>
                        <td class="okapi-wasb-td-3">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="okapi-wasb-td-1">
                            <?php _e('Display on', 'wa-sticky-button'); ?> <span class="required">*</span>  
                        </td>
                        <td class="okapi-wasb-td-2">
							<label style="display: block;">
								<input type="checkbox" id="okapi-wasb-display-on-mobile" value="TRUE" <?php if($display_on_mobile == 'TRUE'){ echo 'checked'; } ?> > &nbsp; 
								Phone	
							</label>
							<label style="display: block;">
								<input type="checkbox" id="okapi-wasb-display-on-tablet" value="TRUE" <?php if($display_on_tablet == 'TRUE'){ echo 'checked'; } ?> > &nbsp; 
								Tablet	
							</label>
							<label style="display: block;">
								<input type="checkbox" id="okapi-wasb-display-on-desktop" value="TRUE" <?php if($display_on_desktop == 'TRUE'){ echo 'checked'; } ?> > &nbsp; 
								Desktop	
							</label>
                            <div class="okapi-wasb-error"></div>
                        </td>
                        <td class="okapi-wasb-td-3">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="okapi-wasb-td-1">
                            <?php _e('Button Position', 'wa-sticky-button'); ?> <span class="required">*</span>   
                        </td>
                        <td class="okapi-wasb-td-2">
                            <select id="okapi-wasb-position" class="okapi-wasb-form-element">
                                <option value="1" <?php if($position == '1'){ echo 'selected'; } ?> ><?php _e('Top Left', 'wa-sticky-button'); ?></option>
                                <option value="2" <?php if($position == '2'){ echo 'selected'; } ?> ><?php _e('Top Right', 'wa-sticky-button'); ?></option>
                                <option value="3" <?php if($position == '3'){ echo 'selected'; } ?> ><?php _e('Bottom Right', 'wa-sticky-button'); ?></option>
                                <option value="4" <?php if($position == '4'){ echo 'selected'; } ?> ><?php _e('Bottom Left', 'wa-sticky-button'); ?></option>
                            </select>
                            <div class="okapi-wasb-error"></div>
                        </td>
                        <td class="okapi-wasb-td-3">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="okapi-wasb-td-1">
                            <?php _e('WhatsApp Number', 'wa-sticky-button'); ?> <span class="required">*</span>   
                        </td>
                        <td class="okapi-wasb-td-2">
                            <input id="okapi-wasb-number" class="okapi-wasb-form-element" value="<?php echo $number; ?>" type="number">
                            <div class="okapi-wasb-error"></div>
                        </td>
                        <td class="okapi-wasb-td-3">
                            <small class="okapi-wasb-small">
                                <?php _e('WhatsApp number like that 919806886806 <br>(with country code but without any plus, preceding zero, hyphen, brackets, space)', 'wa-sticky-button'); ?>
                            </small>
                        </td>
                    </tr>
                    <tr>
                        <td class="okapi-wasb-td-1">
                            <?php _e('WhatsApp Default Message', 'wa-sticky-button'); ?>
                        </td>
                        <td class="okapi-wasb-td-2">
                            <input id="okapi-wasb-msg" class="okapi-wasb-form-element" value="<?php echo $msg; ?>" type="text">
                            <div class="okapi-wasb-error"></div>
                        </td>
                        <td class="okapi-wasb-td-3">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="okapi-wasb-td-1">
                            <?php _e('Icon Width', 'wa-sticky-button'); ?> <span class="required">*</span>   
                        </td>
                        <td class="okapi-wasb-td-2">
                            <input id="okapi-wasb-width" class="okapi-wasb-form-element" value="<?php echo $width; ?>" type="number">
                            <div class="okapi-wasb-error"></div>
                        </td>
                        <td class="okapi-wasb-td-3">
                            <small class="okapi-wasb-small">
                                <?php _e('In Pixel', 'wa-sticky-button'); ?>
                            </small>
                        </td>
                    </tr>
                    <tr>
                        <td class="okapi-wasb-td-1">
                            <?php _e('Icon Height', 'wa-sticky-button'); ?> <span class="required">*</span>   
                        </td>
                        <td class="okapi-wasb-td-2">
                            <input id="okapi-wasb-height" class="okapi-wasb-form-element" value="<?php echo $height; ?>" type="number">
                            <div class="okapi-wasb-error"></div>
                        </td>
                        <td class="okapi-wasb-td-3">
                            <small class="okapi-wasb-small">
                                <?php _e('In Pixel', 'wa-sticky-button'); ?>
                            </small>
                        </td>
                    </tr>
                    <tr>
                        <td class="okapi-wasb-td-1">
                            <?php _e('Icon Margin', 'wa-sticky-button'); ?> <span class="required">*</span>   
                        </td>
                        <td class="okapi-wasb-td-2">
                            <input id="okapi-wasb-margin" class="okapi-wasb-form-element" value="<?php echo $margin; ?>" type="number">
                            <div class="okapi-wasb-error"></div>
                        </td>
                        <td class="okapi-wasb-td-3">
                            <small class="okapi-wasb-small">
                                <?php _e('In Pixel', 'wa-sticky-button'); ?>
                            </small>
                        </td>
                    </tr>
                    <tr>
                        <td class="okapi-wasb-td-1">
                            <?php _e('Icon Type', 'wa-sticky-button'); ?> <span class="required">*</span>   
                        </td>
                        <td class="okapi-wasb-td-2">
                            <select id="okapi-wasb-icon-type" class="okapi-wasb-form-element">
                                <option value="1" <?php if($icon_type == '1'){ echo 'selected'; } ?> ><?php _e('Default WhatsApp Icon', 'wa-sticky-button'); ?></option>
                                <option value="2" <?php if($icon_type == '2'){ echo 'selected'; } ?> ><?php _e('Select Custom Icon', 'wa-sticky-button'); ?></option>
                            </select>
                            <div class="okapi-wasb-error"></div>
                        </td>
                        <td class="okapi-wasb-td-3">
                            <button id="okapi-wasb-icon-id" data-id="<?php echo $icon_id; ?>" class="button button-large">
                                <?php _e('Select Icon', 'wa-sticky-button'); ?> 
                            </button>
                            <div class="okapi-wasb-error"></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="okapi-wasb-td-1">
                            <?php _e('Icon Preview', 'wa-sticky-button'); ?> <span class="required">*</span>   
                        </td>
                        <td class="okapi-wasb-td-2">
                            <img src="<?php echo $icon_src; ?>" id="okapi-wasb-icon-preview" class="okapi-wasb-add-sub-btn-link" title="WhatsApp" alt="WhatsApp" style="max-height: 75px;">
                        </td>
                        <td class="okapi-wasb-td-3">&nbsp;</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3">
                            <button id="okapi-wasb-save-settings" class="button button-large">
                                <?php _e('Save Changes', 'wa-sticky-button'); ?>
                            </button>
                        </td>
                    </tr>  
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
var default_src = "<?php echo $default_src; ?>";

jQuery(document).on("change", "#okapi-wasb-icon-type", function(){
    var value = jQuery(this).val();
    if(value == 1){
        jQuery("#okapi-wasb-icon-id").fadeOut();
        jQuery("#okapi-wasb-icon-preview").attr("src", default_src);
    }else{
        jQuery("#okapi-wasb-icon-id").fadeIn();
    }
});

jQuery(document).ready(function(){
    jQuery("#okapi-wasb-icon-type").trigger("change");

    jQuery(document).on("click", "#okapi-wasb-icon-id", function(){
        var file_frame = wp.media.frames.file_frame = wp.media({
            title: "Choose Icon Image",
            button: {
                text: "Select",
            },
            multiple: false,
        });
        file_frame.on("select", function(){
            attachment = file_frame.state().get("selection").first().toJSON();
            console.log(attachment);
            jQuery("#okapi-wasb-icon-id").data("id", attachment.id);
            jQuery("#okapi-wasb-icon-preview").attr("src", attachment.url);
        });
        file_frame.open();
    });
});

jQuery(document).on("click", "#okapi-wasb-save-settings", function(){
    jQuery(".okapi-wasb-error").html("");
    var status = true;
    var activate = jQuery("#okapi-wasb-activate").val().trim();
    var display_on_mobile = jQuery("#okapi-wasb-display-on-mobile:checked").val();
    var display_on_tablet = jQuery("#okapi-wasb-display-on-tablet:checked").val();
    var display_on_desktop = jQuery("#okapi-wasb-display-on-desktop:checked").val();
    var position = jQuery("#okapi-wasb-position").val().trim();
    var number = jQuery("#okapi-wasb-number").val().trim();
    var msg = jQuery("#okapi-wasb-msg").val().trim();
    var width = jQuery("#okapi-wasb-width").val().trim();
    var height = jQuery("#okapi-wasb-height").val().trim();
    var margin = jQuery("#okapi-wasb-margin").val().trim();
    var icon_type = jQuery("#okapi-wasb-icon-type").val().trim();
    var icon_id = jQuery("#okapi-wasb-icon-id").data("id");
    if(number == ""){
        jQuery("#okapi-wasb-number").next(".okapi-wasb-error").html("Please enter valid WhatsApp number.");
        status = false;
    }
    if(width == ""){
        jQuery("#okapi-wasb-width").next(".okapi-wasb-error").html("Please enter icon width in pixel.");
        status = false;
    }
    if(height == ""){
        jQuery("#okapi-wasb-height").next(".okapi-wasb-error").html("Please enter icon height in pixel.");
        status = false;
    }
    if(margin == ""){
        jQuery("#okapi-wasb-margin").next(".okapi-wasb-error").html("Please enter icon margin in pixel.");
        status = false;
    }
    if(icon_type == 2 && icon_id == 0){
        jQuery("#okapi-wasb-icon-id").next(".okapi-wasb-error").html("Please select icon image.");
        status = false;
    }
    if(status == true){
        jQuery("#okapi-wasb-save-settings").html('Saving...');
        jQuery.ajax({
            type: "POST",
            url: "<?php echo get_admin_url(); ?>admin-ajax.php",
            data: {
                action: "okapi_wasb_save_settings",
                activate: activate,
				display_on_mobile: display_on_mobile,
				display_on_tablet: display_on_tablet,
				display_on_desktop: display_on_desktop,               
                position: position,
                number: number,
                msg: msg,
                width: width,
                height: height,
                margin: margin,
                icon_type: icon_type,
                icon_id: icon_id,
            },
            success: function(res){
                jQuery("#okapi-wasb-save-settings").html('Save Changes');
                alert("Saved Successfully.");
            }
        }); 
    }
});
</script>

<style type="text/css">
.okapi-wasb-small{
    display: block;
    color: #28D044;
    font-size: 13px;
    font-weight: 500;
}
#okapi-wasb-icon-id, #okapi-wasb-save-settings{
    background-color: #28D044;
    color: #FFFFFF;
    font-weight: 500;
}
.okapi-wasb-td-1{
    vertical-align: middle !important;
    width: 180px;
    font-weight: bold;
}
.okapi-wasb-td-2{
    vertical-align: middle !important;
    width: 220px;
}
.okapi-wasb-td-3{
    vertical-align: middle !important;
}
.okapi-wasb-error{
    color: red;
    display: block;
}
.okapi-wasb-form-element{
    width: 100%;
    height: 36px !important;
    font-size: 16px;
}
</style>