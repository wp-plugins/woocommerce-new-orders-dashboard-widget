<?php
/*
Plugin Name: Woocommerce New Orders Dashboard Widget
Plugin URI: http://odrasoft.com/
Description: woocommerce New Orders Dashboard Widget Shows the recent new orders from the user On the Dashboard .
Version: 2.2
Author: swadeshswain
Author URI: http://odrasoft.com/
License: GPLv2 or later
*/
add_action('wp_dashboard_setup', 'od_woo_dashboard_widgets');
function od_woo_dashboard_widgets() {
global $wp_meta_boxes;
wp_add_dashboard_widget('od_woo_widget', 'WooCommerce Recent Orders', 'od_dashboard_woo');
}
function od_dashboard_woo() {
global $wpdb;
global $woocommerce ;
 $od_woo_odr_no = get_option( 'od_woo_odr_no' );
$customer_orders = get_posts( apply_filters( 'woocommerce_my_account_my_orders_query', array(
	'numberposts' => $od_woo_odr_no,
	'meta_key'    => '_customer_user',
	//'meta_value'  => get_current_user_id(),
	'post_type'   => 'shop_order',
	'post_status' => 'publish'
	
) ) );
?>   
<?php

    if ( isset($_POST['submit']) ) { 
        $nonce = $_REQUEST['_wpnonce'];
        if (! wp_verify_nonce($nonce, 'php-woo-odr-updatesettings' ) ) {
            die('security error');
        }
        $woo_odr_no = $_POST['woo_odr_no'];
        update_option( 'od_woo_odr_no', $woo_odr_no );
    } 
    $od_woo_odr_no = get_option( 'od_woo_odr_no' );
	?>
<?php
if ( $customer_orders ) : ?>
	<table class="shop_table my_account_orders" width="100%">

		<thead>
			<tr>
				<th class="order-number"><span class="nobr"><?php _e( 'Order Id', 'woocommerce' ); ?></span></th>
				<th class="order-date"><span class="nobr"><?php _e( 'Date', 'woocommerce' ); ?></span></th>
				<th class="order-status"><span class="nobr"><?php _e( 'Status', 'woocommerce' ); ?></span></th>
				<th class="order-total"><span class="nobr"><?php _e( 'Total', 'woocommerce' ); ?></span></th>
				<th class="order-actions"><span class="nobr"><?php _e( 'Action', 'woocommerce' ); ?></th>
			</tr>
		</thead>

		<tbody>
		<?php
			foreach ( $customer_orders as $customer_order ) {
				$order = new WC_Order();

				$order->populate( $customer_order );

				$status     = get_term_by( 'slug', $order->status, 'shop_order_status' );
				$item_count = $order->get_item_count();

				?><tr class="order">
					<td class="order-number">
						<a href="<?php echo get_home_url(); ?>/wp-admin/post.php?post=<?php echo $order->get_order_number() ;?>&action=edit">
							<?php echo $order->get_order_number(); ?>
						</a>
					</td>
					<td class="order-date">
						<time datetime="<?php echo date( 'Y-m-d', strtotime( $order->order_date ) ); ?>" title="<?php echo esc_attr( strtotime( $order->order_date ) ); ?>"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $order->order_date ) ); ?></time>
					</td>
					<td class="order-status" style="text-align:left; white-space:nowrap;">
                   <?php $ostatus = $order->status ;if($ostatus == "on-hold"){?>
                    
						<span style="color:#FF0000"><?php echo ucfirst( __( $order->status, 'woocommerce' ) ); ?></span>
                        
                        <?php } ?>
                        <?php $ostatus = $order->status ;if($ostatus == "processing"){?>
                    
						<span style="color:#F8BD27"><?php echo ucfirst( __( $order->status, 'woocommerce' ) ); ?></span>
                        
                        <?php } ?>
                        
                        <?php $ostatus = $order->status ;if($ostatus == "completed"){?>
                    
						<span style="color:#0F9D58"><?php echo ucfirst( __( $order->status, 'woocommerce' ) ); ?></span>
                        
                        <?php } ?>
                        
					</td>
					<td class="order-total">
						<?php echo sprintf( _n( '%s for %s item', '%s for %s items', $item_count, 'woocommerce' ), $order->get_formatted_order_total(), $item_count ); ?>
					</td>
					<td class="order-actions">
						<?php
							$actions = array();

							if ( in_array( $order->status, apply_filters( 'woocommerce_valid_order_statuses_for_payment', array( 'pending', 'failed' ), $order ) ) ) {
								$actions['pay'] = array(
									'url'  => $order->get_checkout_payment_url(),
									'name' => __( 'Pay', 'woocommerce' )
								);
							}

							if ( in_array( $order->status, apply_filters( 'woocommerce_valid_order_statuses_for_cancel', array( 'pending', 'failed' ), $order ) ) ) {
								$actions['cancel'] = array(
									'url'  => $order->get_cancel_order_url( get_permalink( wc_get_page_id( 'myaccount' ) ) ),
									'name' => __( 'Cancel', 'woocommerce' )
								);
							}

							$actions['view'] = array(
								'url'  => $order->get_view_order_url(),
								'name' => __( 'View', 'woocommerce' )
							);
							
							$actions = apply_filters( 'woocommerce_my_account_my_orders_actions', $actions, $order );

							if ($actions) {
								foreach ( $actions as $key => $action ) {
									echo '<a href="' .get_home_url() . '/wp-admin/post.php?post= '. $order->get_order_number().'&action=edit" class="button ' . sanitize_html_class( $key ) . '">' . esc_html( $action['name'] ) . '</a>';
								}
							}
						?>
					</td>
				</tr><?php
			}
		?></tbody>

	</table>
    <div style="border-top: 1px solid #000;">

			<form method="post" action="" id="php_odr_config_page">
				<?php wp_nonce_field('php-woo-odr-updatesettings'); ?>                          
				<table class="form-table">
					<tbody>
                    <tr>
						<th><label>No Of Orders to Display : </label></th>
						<td>
                                         <Input type = 'text' Name ='woo_odr_no' <?php if($od_woo_odr_no!=""){?>value= '<?php echo $od_woo_odr_no ; ?>' <?php } else { ?> value = '5' <?php } ?> />
                        </td>
                    </tr>
					</tbody>
				</table>
				<p class="submit"><input type="submit" value="Save Changes" class="button-primary" id="submit" name="submit" /></p>  
			</form>
</div>
<?php endif; ?>

<?php 
}?>