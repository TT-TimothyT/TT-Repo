<?php

// add_action( 'user_register', 'register_new_user_cb' );

function register_new_user_cb(){
    ?>
    <script>
        window.dataLayer = window.dataLayer || [];
        dataLayer.push({
            'event' : 'account_creation'
        });
    </script>
    <?php
}

function cart_product_add_remove_cb(){
    ?>
    <script>
        dataLayer.push ({
            'event':'add_to_cart', // remove_from_cart/add_to_cart
            'ecommerce': {
            'currencyCode': 'USD', // use the correct currency code value here
            'add': 
                {
                    'products': [{
                        'name': "Tuscany Bike Tour", // Please remove special characters
                        'id': '123456', // Parent ID
                        'price': '3799.00', // per unit price displayed to the user - no format is ####.## (no '$' or ',')
                        'brand': '', //
                        'category': 'italy,europe', // populate with the 'country,continent' separating with a comma
                        'variant': '123456-0', //this is the SKU of the product
                        'quantity': '1' //the number of products added to the cart
                    }]
                }
            }
        }); 
    </script>
    <?php
}
function view_cart_cb(){
    ?>
    <script>
        dataLayer.push({
            'event':'view_cart',
            'currency': "USD",
            'value': 7.77, //the total value of the cart (product price * quantity + discounts)
            'items': [{
                    'item_id': "123456", //match what was sent on product.id
                    'item_name': "Tuscany Bike Tour", //match what was sent on product.name
                    'coupon': "SUMMER_FUN", //name of the coupon if one is applied. use pipes to delimit values
                    'currency': "USD",
                    'discount': '2.22', //total value of the coupons applied
                    'index': 0, 
                    'item_brand': "", //match what was sent on product.brand
                    'item_category': "italy", //match what was sent on level one of product.category
                    'item_category2': "europe", //match what was sent on level two of product.category
                    'item_category3': "", //match what was sent on level three of product.category
                    'item_category4': "", //match what was sent on level four of product.category
                    'item_category5': "", //match what was sent on level five of product.category
                    'item_list_id': "pdp: you may also like", //match what was sent on product.list
                    'item_variant': "123456-0", //match what was sent on product.variant
                    'price': '3799.00', //match what was sent on product.price
                    'quantity': '1' //match what was sent on product.quantity
                }]
            })                
    </script>
<?php
}

add_action('wp_footer', 'get_pageView_cb');

function get_pageView_cb()
{
    $pageType = returnPage();
    if (isset($pageType) && !empty($pageType)) {    
        $userInfo = wp_get_current_user();
        $userId = '';
        $userEmail = '';
        if( isset($userInfo) && !empty($userInfo) ){
            $userId = $userInfo->ID;	
            $userEmail = hash('sha256', strtolower($userInfo->email)) ;	
        }
        $loginState = is_user_logged_in() ? "registered" : "guest";
        ?>
        <script>
            metaTitle = jQuery("title").text()
            dataLayer.push({
                'page_name': '<?php echo $pageType; ?>:'+ metaTitle, // populate dynamically using <page_type>:<meta title>
                'page_type': '<?php echo $pageType; ?>', // pageType lookup list is below
                'page_region': '<?php echo $pageType; ?>', //if the user is on a page that has a region (europe, asia) assigned to it, send the region name, if not, send the      pageType value
                'user_login_state': '<?php echo $loginState; ?>', // guest or registered
                'user_email_sha256': '<?php echo $userEmail; ?>', // convert to lowercase prior to sha256 hash
                'user_id': '<?php echo $userId; ?>' // SFCC ID: populate for authenticated users only else leave empty
            });
        </script>
    <?php
    }
}

function returnPage() {
    global $wp_query;
    $loop = '';

    // Page Type:
    // home = home
    // PDP = product
    // successful search results = search_results
    // null search results = no_search_results
    // about us, info page = content
    // product list page = listing
    // blog page = blog
    // category = category
    // cart = cart
    // checkout pages + order confirmation = checkout
    // account pages = account

    if ( $wp_query->is_home || is_front_page() ) {
        $loop = 'home';
    } elseif ( $wp_query->is_product ) {
        $loop = 'product';
    } elseif ( $wp_query->is_search ) {
        $loop = 'search_results';
    } elseif ( is_category() ) {
        $loop = 'category';
    } elseif ( is_checkout() ) {
        $loop = 'checkout';
    }
    
    return $loop;
} 