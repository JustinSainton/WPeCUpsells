jQuery( document ).ready( function( $ ) {

	$( 'a.add-upsell-conditions' ).click( function( event ) {
		var $this = $( this );

		$( '.upsell-conditions' ).slideToggle( 200, function(){
			if ( $( 'div.upsell-conditions' ).is( ':visible' ) ) {
				$this.text( wpsc_adminL10n.upsells_remove_conditions );
			} else {
				$this.text( wpsc_adminL10n.upsells_add_conditions );
			}
		});
		$( '.coupon-condition input' ).focus();
	});

	$( '#the-list' ).on('click', 'a.editinline', function( event ) {
		var tag_id   = $( this ).parents( 'tr ').attr( 'id' ),
		upsell_price = $( '.input_upsell_price', '#' + tag_id ).val(),
		conditions   = $.parseJSON( $( '.input_conditions'  , '#' + tag_id ).val() );

		console.log( conditions );

		$( ': input[name="upsell_price"]', '.inline-edit-row' ).val( upsell_price );
		return true;
	});

});