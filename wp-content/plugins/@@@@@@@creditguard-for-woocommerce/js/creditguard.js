(function ($) {
    $(function () {
        
        $(document.body).on( "click", "input[type=radio][name=wc-creditguard-payment-token]", function() {
            if ($( this ).val() == 'new') {
                $("#creditguard-installment_dropdown").show();
            } else {
                $("#creditguard-installment_dropdown").show();
            }
        });
        
        $(document.body).on('updated_checkout wc-credit-card-form-init', function () {
                        
            if ($("input[name='wc-creditguard-payment-token']:checked").val() == 'new' || typeof $("input[name='wc-creditguard-payment-token']:checked").val() === 'undefined') {
                $("#creditguard-installment_dropdown").show();
            } else {
                $("#creditguard-installment_dropdown").show();
            }

            if($("#cg_savecard_default").val() === 'yes'){
                $("#wc-creditguard-new-payment-method").prop('checked', true);
                
                if($("#cg_savecard_hide").val() === 'yes'){
                    $("#wc-creditguard-new-payment-method").css('display', 'none');
                    $("#wc-creditguard-new-payment-method").next('label').hide();
                }
            }

        });
        
        $( 'form.checkout' ).on( 'change', 'select[name^="wc-creditguard-creditguard-installment"]', function() {
            $('body').trigger('update_checkout');
        });
        
    });
})(jQuery);
