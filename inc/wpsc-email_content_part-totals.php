<?php
/**
 * Template for totals in purchase transaction emails
 */
?>

<tr>
	<td></td>
	<td colspan="2" style="text-align: right; padding-right: 10px;">
		<?php $discount = ECSE_purchase::get_the_total_prop('discount_total');
			if ( $discount > 0 )
				echo '<br />Discount - ' . ECSE_purchase::get_the_purchase_prop('discount_data') . ' (' . wpsc_currency_display( $discount ) . ')<br>';
		?>
		Total: </td>
	<td valign="bottom"><?php echo wpsc_currency_display(ECSE_purchase::get_the_total_prop('shipping_total')) ?></td>
	<td valign="bottom"><?php echo wpsc_currency_display(ECSE_purchase::get_the_total_prop('tax_total')) ?></td>
	<td valign="bottom"><?php echo wpsc_currency_display(ECSE_purchase::get_the_total_prop('grand_total')) ?></td>
</tr>
