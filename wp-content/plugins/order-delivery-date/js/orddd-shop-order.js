jQuery( document ).ready( function(){
    jQuery( '.orddd_datepicker' )
    .datepicker({
        minDate: 0,
        dateFormat: "yy-mm-dd",
        onSelect: function(selected,evnt) {
            if ( 'orddd_custom_startdate' == evnt.id ) {
                jQuery('#orddd_custom_enddate').datepicker('option', 'minDate', new Date( selected ) );
                if ( jQuery( '#orddd_custom_enddate' ).val() == '' ) {
                    jQuery( '#orddd_custom_enddate' ).val( selected );
                }
            }

            if ( 'orddd_custom_enddate' == evnt.id ) {
                if ( jQuery( '#orddd_custom_startdate' ).val() == '' ) {
                    jQuery( '#orddd_custom_startdate' ).val( selected );
                }
            }
        }
    });

    jQuery( 'select#order_delivery_date_filter' ).select2();

    jQuery( '.orddd_filter' ).on( 'change', function(){
        let value = jQuery( '.orddd_filter' ).val();
        if ( value == 'custom' ) {
            jQuery( "#orddd_custom_startdate" ).removeAttr( "style" );
            jQuery( "#orddd_custom_enddate" ).removeAttr( "style" );
        } else {
            jQuery( "#orddd_custom_startdate" ).css( "display", "none" );
            jQuery( "#orddd_custom_enddate" ).css( "display", "none" );
            jQuery( '#orddd_custom_startdate' ).val('');
            jQuery( '#orddd_custom_enddate' ).val('');
        }
    });
});

