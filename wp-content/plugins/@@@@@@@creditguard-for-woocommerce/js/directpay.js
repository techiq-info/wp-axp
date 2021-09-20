(function ($) {
    $(function () {
        
        $(document.body).on( "click", "input[type=radio][name=wc-directpay-payment-token]", function() {
            if ($( this ).val() == 'new') {
                $("#directpay-installment_dropdown").show();
            } else {
                $("#directpay-installment_dropdown").show();
            }
        });
        
        $(document.body).on('updated_checkout wc-credit-card-form-init', function () {
                        
            if ($("input[name='wc-directpay-payment-token']:checked").val() == 'new' || typeof $("input[name='wc-directpay-payment-token']:checked").val() === 'undefined') {
                $("#directpay-installment_dropdown").show();
            } else {
                $("#directpay-installment_dropdown").show();
            }

            if($("#cg_savecard_default").val() === 'yes'){
                $("#wc-directpay-new-payment-method").prop('checked', true);
                
                if($("#cg_savecard_hide").val() === 'yes'){
                    $("#wc-directpay-new-payment-method").css('display', 'none');
                    $("#wc-directpay-new-payment-method").next('label').hide();
                }
            }

        });
        
        $( 'form.checkout' ).on( 'change', 'select[name^="wc-directpay-directpay-installment"]', function() {
            $('body').trigger('update_checkout');
        });
        
    });
})(jQuery);
