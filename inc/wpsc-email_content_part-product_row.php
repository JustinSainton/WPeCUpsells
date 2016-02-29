<?php
/**
 * Template for a product row in purchase transaction emails
 */
global $is_gift_wrapped, $polishing_cloths;

$custom_message = ECSE_purchase_product::get_the_custom_message();
$meta           = ECSE_purchase_product::get_the_ID();
?>
<tr>
	<td><?php echo ECSE_purchase_product::get_the_QTY() ?> </td>
	<td><?php echo ECSE_purchase_product::get_the_name(); ?>
		<?php
		if ( ! empty( $custom_message ) )
			echo '<br /><i>' . $custom_message . '</i>'; ?></td>
	<?php if ( ! $is_gift_wrapped ) :  ?>
	<td><?php echo wpsc_currency_display( ECSE_purchase_product::get_the_cost() ) ?></td>
	<td><?php echo wpsc_currency_display( ECSE_purchase_product::get_the_shipping() ); ?></td>
	<td><?php echo wpsc_currency_display( ECSE_purchase::get_the_purchase_prop( 'wpec_taxes_total' ) ); ?></td>
	<td><?php echo ECSE_purchase_product::get_the_cost_subtotal();?></td>
<?php endif; ?>
</tr>