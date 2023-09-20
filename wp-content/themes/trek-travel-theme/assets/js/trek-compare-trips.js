  jQuery('body').on('click', '.compare-remove-all-products', function (e) {
    e.preventDefault();
    jQuery.ajax({
      type: 'POST',
      url: trek_JS_obj.ajaxURL,
      data: { action: 'add_compare_product_ids_action', 'product_ids': '' },
      dataType: 'json',
      beforeSend: function () {
        jQuery.blockUI({
          css: {
            border: 'none',
            padding: '15px',
            backgroundColor: '#000',
            '-webkit-border-radius': '10px',
            '-moz-border-radius': '10px',
            opacity: .5,
            color: '#fff'
          }
        });
      },
      success: function (response) {
        setTimeout(jQuery.unblockUI, 500);
      }
    });
    var products = jQuery.wc_products_compare_frontend.getComparedProducts();
    jQuery.each(products, function (key, productId) {
      var removeID = productId;
      // Unset the product from cookie.
      jQuery.wc_products_compare_frontend.unsetComparedProducts(removeID);
      // Uncheck compare checkbox.
      jQuery('input.woocommerce-products-compare-checkbox[data-product-id="' + removeID + '"]').prop('checked', false);
    });
    jQuery('.compare-products-footer-bar').css("display", "none");
  })
  
  jQuery('body').on('wc_compare_product_checked', async function (e, productID) {
    var products_ids = jQuery.wc_products_compare_frontend.getComparedProducts();
    products_ids = [...new Set(products_ids)];
    var compareProductsLength = products_ids.length
    jQuery.cookie.raw = true;
    jQuery.cookie('wc_products_compare_products', products_ids.toString(), { expires: parseInt(10), path: '/' });
    if (jQuery.wc_products_compare_frontend.getComparedProducts() === false || compareProductsLength <= 0) {
      jQuery('.compare-products-footer-bar').css("display", "none")
    }
    else {
      jQuery.ajax({
        type: 'POST',
        url: trek_JS_obj.ajaxURL,
        data: { action: 'add_compare_product_ids_action', 'product_ids': products_ids },
        dataType: 'json',
        beforeSend: function () {
          jQuery.blockUI({
            css: {
              border: 'none',
              padding: '15px',
              backgroundColor: '#000',
              '-webkit-border-radius': '10px',
              '-moz-border-radius': '10px',
              opacity: .5,
              color: '#fff'
            }
          });
        },
        success: function (response) {
          if (response.output) {
            jQuery('.compare-products-footer-bar').css("display", "flex");
            jQuery('#tt_compare_product').html(response.output);
          }
          setTimeout(jQuery.unblockUI, 500);
          updateCompareLink();
        }
      });
    }
  
  })
  
  jQuery('body').on('wc_compare_product_unchecked', async function (e, productID) {
    var products_ids = jQuery.wc_products_compare_frontend.getComparedProducts();
    products_ids = [...new Set(products_ids)];
    var compareProductsLength = products_ids.length
    jQuery.cookie.raw = true;
    jQuery.cookie('wc_products_compare_products', products_ids.toString(), { expires: parseInt(10), path: '/' });
    if (jQuery.wc_products_compare_frontend.getComparedProducts() === false || compareProductsLength <= 0) {
      jQuery('.compare-products-footer-bar').css("display", "none")
    }
    else {
      jQuery('.compare-products-footer-bar').css("display", "flex");
      jQuery.ajax({
        type: 'POST',
        url: trek_JS_obj.ajaxURL,
        data: { action: 'add_compare_product_ids_action', 'product_ids': products_ids },
        dataType: 'json',
        beforeSend: function () {
          jQuery.blockUI({
            css: {
              border: 'none',
              padding: '15px',
              backgroundColor: '#000',
              '-webkit-border-radius': '10px',
              '-moz-border-radius': '10px',
              opacity: .5,
              color: '#fff'
            }
          });
        },
        success: function (response) {
          if (response.output) {
            jQuery('#tt_compare_product').html(response.output);
          }
          setTimeout(jQuery.unblockUI, 500);
          updateCompareLink();
        }
      });
    }
  })

  jQuery('body').on('click', '.remove-compare-page-product', function (e) {
    e.preventDefault();
    var removeID = jQuery( this ).data( 'remove-id' );
    // unset the product from cookie
    jQuery.wc_products_compare_frontend.unsetComparedProducts( removeID );
    location.reload();
  })

  // check if there are no more products to compare
  // if (jQuery.wc_products_compare_frontend.getComparedProducts() == false ) {
  //   jQuery('.compare-products-footer-bar').css("display", "none")
  // }
  var getComparedIds = jQuery.wc_products_compare_frontend.getComparedProducts();
  getComparedIds = [...new Set(getComparedIds)];
  var getComparedIdsLength = getComparedIds.length;
  if (jQuery.wc_products_compare_frontend.getComparedProducts() === false || getComparedIdsLength <= 0) {
      jQuery('.compare-products-footer-bar').css("display", "none")
  }
