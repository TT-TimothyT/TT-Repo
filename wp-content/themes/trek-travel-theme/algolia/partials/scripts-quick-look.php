<?php
/**
 * Template part for the Quick Look Scripts on search and archive product pages.
 *
 * Note: Would be better to move this in the JS file. The same functions are used in the pdp-dates-pricing.php.
 */

global $woocommerce;
$cart_contents_arr = $woocommerce->cart->cart_contents;
$data              = $cart_contents_arr[ array_key_first( $cart_contents_arr ) ]['data'];
?>

<script>
	jQuery(document).on('submit', 'form.cart.grouped_form', function () {
		var childSku = jQuery(this).closest(".accordion-item").data("sku")
		dataLayer.push({ 'ecommerce': null });  // Clear the previous ecommerce object.
		dataLayer.push({
			'event':'quick_view_add_to_cart',
			'ecommerce': {
				'currencyCode': jQuery("#currency_switcher").val(), // use the correct currency code value here
				'add': {
					'products': [{
						'name': jQuery('input[name="parent_product_name"]').val(), // Please remove special characters
						'id': jQuery('input[name="parent_product_id"]').val(), // Parent ID
						'price': jQuery( this ).find("span.amount").data("price"), // per unit price displayed to the user - no format is ####.## (no '$' or ',')
						'brand': '', //
						'category': '<?php echo strip_tags(wc_get_product_category_list( get_the_id())); ?>', // populate with the 'country,continent' separating with a comma
						'variant': childSku, //this is the SKU of the product
						'quantity': '1' //the number of products added to the cart
					}]
				}
			}
		})
	})

	function removeCartAnalytics() {
		dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
		dataLayer.push({
			'event':'quick_view_remove_from_cart',
			'ecommerce': {
				'currencyCode': jQuery("#currency_switcher").val(), // use the correct currency code value here
				'remove': {
					'products': [{
					'name': "<?php echo preg_replace('/[^\w\s]/', '', $data->name); ?>", // Please remove special characters
					'id': '<?php echo $data->id; ?>', // Parent ID
					'price': '<?php echo number_format((float)$data->price, 2, '.', ''); ?>', // per unit price displayed to the user - no format is ####.## (no '$' or ',')
					'brand': '', //
					'category': '', // populate with the 'country,continent' separating with a comma
					'variant': '<?php echo $data->sku; ?>', //this is the SKU of the product
					'quantity': '1' //the number of products added to the cart
					}]
				}
			}
		})
	}

	jQuery(document).on( 'click', '.dates-pricing-book-now.qv-book-now-btn', function () {
		dataLayer.push({
			'event': 'quick_view_book_now'
		});
	});

	jQuery(document).on( 'click', '.quick-view-button', function () {
		const id = jQuery( this ).attr( 'data-bs-product-id' );
		quickViewBtnClickEvent( id );
	});

	function quickViewBtnClickEvent( id ) {
		window.dataLayer = window.dataLayer || [];
		// Go trough all the cards displayed.
		jQuery( ".trip-card-body" ).each(function( index ) {
			const card_id = jQuery( this ).find( ".quick-view-button" ).data("bs-product-id");
			const price   = jQuery( this ).find( ".trip-price").data( "price" );
			// Find the clicked card.
			if ( parseInt(id) == parseInt(card_id) ) {
				dataLayer.push({
					'event': 'quick_view_button_click',
					'ecommerce': {
						'click': {
							'products': [{
								'name': jQuery( this ).find(".trip-title" ).first().text(), // Please remove special characters
								'id': id, // Parent ID
								'price': parseFloat( price ).toFixed(2), // per unit price displayed to the user - no format is ####.## (no '$' or ',')
								'brand': '', //
								'category': '', //
								'position': index + 1
							}]
						}
					},
				});
				return false;
			}
		});
	}
</script>
