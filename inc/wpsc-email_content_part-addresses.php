<?php
/**
 * Template for totals in purchase transaction emails
 */
?>
			<tr>
				<td>
					<h2 style="font-weight:normal; margin-bottom:0">billing address</h2>
					<p style="margin-top:0">
						<?php
							$log = new WPSC_Purchase_Log( ECSE_purchase::get_the_purchase_ID() );
							$billing_state = wpsc_get_state_by_id( $log->get( 'billing_region' ), 'code' );
						?>
						<?php echo ECSE_purchase::get_the_billing_prop('billingfirstname') . ' ' . ECSE_purchase::get_the_billing_prop('billinglastname') ?><br />
						<?php echo ECSE_purchase::get_the_billing_prop('billingaddress', ',<br />') ?>
						<?php echo ECSE_purchase::get_the_billing_prop('billingcity', ', ') . $billing_state . ' ' . ECSE_purchase::get_the_billing_prop('billingpostcode').' '.ECSE_purchase::get_the_billing_prop('billingcountry') ?><br />
						<?php echo make_clickable( ECSE_purchase::get_the_checkout_prop('billingemail') ); ?><br />
						<?php echo ECSE_purchase::get_the_checkout_prop('billingphone'); ?><br />
					</p>
				</td>
				<td></td>
				<td rowspan="6" style="width:29%">
					<table>
						<tr>
							<td>
								<img height="85" width="75" src="http://www.jookandnona.com/wp-content/uploads/2011/08/jnlogoblk.png" />
							</td>
							<td>
								<h2>Jook &amp; Nona</h2>
							</td>
						</tr>
					</table>

					<table>
						<tr>
							<td>
								<p style="font-size:11px">Thank you for purchasing with Jook & Nona.  Any items to be shipped will be processed as soon as possible.  All of our items are made to order.  Please allow 1-5 days for Brass Cuffs depending on our in house stock, 1-5 days for fingerprint kits and 3-4 weeks for fingerprint jewelry once prints have been received.  All prices include tax and postage and packaging where applicable.</p>
							</td>
						</tr>
					</table>
					<?php if ( cart_has_kit( ECSE_purchase::get_the_purchase_ID() ) ) : ?>
					<table>
						<tr>
							<td>
					<h3>The Process</h3>
							</td>
						</tr>
					</table>
					<table>
						<tr>
							<td>

					<p style="font-size:11px">Once we have received the completed kit, we
					begin working on your custom piece. Each
					impression is individually crafted, cast in the
					metal of your choice and finished by hand. Once
					a cast has been created, your molds will remain
					on file for any future purchases.</p>

							</td>
						</tr>
					</table>
					<table>
						<tr>
							<td>
					<p style="font-size:11px">The final piece(s) will be shipped within 3-5
					weeks of receipt of the kit.</p>

							</td>
						</tr>
					</table>

					<table>
						<tr>
							<td>
					<p style="font-size:11px">If kit is not received within 4 months of order
					date, price of piece is subject to metal cost
					increases.</p>

							</td>
						</tr>
					</table>
					<?php endif; ?>

					<table>
						<tr>
							<td>
					<h3>Returns and Exchanges</h3>

							</td>
						</tr>
					</table>


					<table>
						<tr>
							<td>
					<p style="font-size:11px">All pieces are final sale. Chains may be
					exchanged for an alternate choice if you find
					your choice too heavy, too thin, too short, or
					too long for your pieces. You will be subject to
					a 20% restocking fee. No exceptions. All
					shipping charges are non- refundable. We stand
					behind the quality of our pieces and are happy
					to discuss any problems you may have at any
					time.</p>
							</td>
						</tr>
					</table>

					<table>
						<tr>
							<td>
					<h3>Contact Us</h3>

							</td>
						</tr>
					</table>

					<table>
						<tr>
							<td>
					<p style="font-size:11px">if you have any questions, please reach us
					at:</p>
					<p style="font-size:11px"><?php echo make_clickable( 'info@jookandnona.com' ); ?><br />
					646.543.9308</p>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td>
					<h2 style="font-weight:normal; margin-bottom:0">shipping address</h2>
					<p style="margin-top:0">
						<?php
							$log = new WPSC_Purchase_Log( ECSE_purchase::get_the_purchase_ID() );
							$shipping_state = wpsc_get_state_by_id( $log->get( 'shipping_region' ), 'code' );
						?>
						<?php echo ECSE_purchase::get_the_shipping_prop('shippingfirstname').' '.ECSE_purchase::get_the_shipping_prop('shippinglastname') ?><br />
						<?php echo ECSE_purchase::get_the_shipping_prop('shippingaddress', ',<br />') ?>
						<?php echo ECSE_purchase::get_the_shipping_prop('shippingcity', ', ') . $shipping_state .' '.ECSE_purchase::get_the_shipping_prop('shippingpostcode').' '.ECSE_purchase::get_the_shipping_prop('shippingcountry') ?>
					</p>
				</td>
				<td></td>
			</tr>