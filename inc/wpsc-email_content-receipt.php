<?php
/**
 * Template for a customer receipt's content in purchase transaction emails.
 * Only gets used during a WP e-Commerce purchase receipt
 */
	echo ecse_get_the_addresses();

	/* Going straight-up procedural. */
	global $is_gift_wrapped, $polishing_cloths;

	$is_gift_wrapped = false;
	$polishing_cloths = 0;

	$log = new WPSC_Purchase_Log( ECSE_purchase::get_the_purchase_ID() );
	$cart_items = $log->get_cart_contents();

	foreach ( $cart_items as $item ) {
		$meta  = wpsc_get_cart_item_meta( $item->id, '', true );

		if ( isset( $meta['gift-wrap'] ) ) {
			$is_gift_wrapped = true;
		}

		if ( isset( $meta['polishing-cloth'] ) ) {
			$polishing_cloths++;
		}
	}

?>

			<tr>
				<td><?php if ( ecse_is_purchase_receipt_email() ) : ?>Your order on <?php echo date( 'F j, Y', ECSE_purchase::get_the_purchase_prop( 'date' ) ); ?> (purchase #<?php echo ECSE_purchase::get_the_purchase_ID(); ?>) <?php endif; ?></td>
				<td></td>
			</tr>
			<tr>
				<td colspan="2">
					<table class="wpsc-purchase-log-transaction-results" style="width:650px; border-collapse: collapse;">
						<thead>
							<tr>
								<th style="text-align:left;border-top: 1px solid black;border-bottom: 1px solid black">Qty</th>
								<th style="text-align:left;border-top: 1px solid black;border-bottom: 1px solid black;">Item</th>
								<?php if ( ! $is_gift_wrapped ) : ?>
								<th style="text-align:left;border-top: 1px solid black;border-bottom: 1px solid black;">Item Price</th>
								<th style="text-align:left;border-top: 1px solid black;border-bottom: 1px solid black;">Shipping</th>
								<th style="text-align:left;border-top: 1px solid black;border-bottom: 1px solid black;">Tax</th>
								<th style="text-align:left;border-top: 1px solid black;border-bottom: 1px solid black;">Total</th>
								<?php endif; ?>
							</tr>
						</thead>
						<tbody>
							<?php echo ecse_get_the_product_list(); ?>
							<?php
								if ( ! $is_gift_wrapped ) {
									echo ecse_get_the_totals();
								}
							?>
							<?php
								if ( $polishing_cloths > 0 ) :

									?>
									<tr>Included: (<?php echo absint( $polishing_cloths ); ?>) polishing cloths</tr>
									<?php
								endif;
							?>
						</tbody>
					</table>
				</td>
				</tr>
			<tr>
				<td>this completes your order, thanks!</td>
				<td></td>
			</tr>
			<tr>
				<td>
				<?php if ( cart_has_kit( ECSE_purchase::get_the_purchase_ID() ) ) : ?>
				please send your completed kit to:<div>jook &amp; nona<div>232 w. 116th street, #35<div>new york, ny 10026<div><br><div>
				<?php endif; ?>
				</td>
				<td></td>
			</tr>



<?php if(ECSE_purchase::get_the_purchase_prop('notes')!='') { ?>
<tr>
<td>
	Special request: <?php echo ECSE_purchase::get_the_purchase_prop('notes') ?>
</td>
</tr>
<?php } ?>
