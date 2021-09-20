jQuery(document).ready(function($) {

   
   $('#WcfeUpload').on('click', function() {
 
    var file_data = $('#wcfe_file').prop('files')[0];   
    var form_data = new FormData();                  
    form_data.append('file', file_data);

    $.ajax({
                url: MyAjax.fileurl, // point to server-side PHP script 
                dataType: 'text',  // what to expect back from the PHP script, if anything
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,               
                type: 'post',
                success: function(php_script_response){
                    alert(php_script_response); // display response from the PHP script, if any
                }
     });
});


});