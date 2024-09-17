<!-- overview bar section TREK-235 -->
<?php
defined('ABSPATH') || exit;

global $product;

$product_overview = get_field('product_overview');
$pdp_bikes = get_field('bikes');

$product = wc_get_product( get_the_ID() );
$product_id = $product->get_id();

$activity_terms = get_the_terms( $product_id, 'activity' );

foreach ( $activity_terms as $activity_term) {
	$activity = $activity_term->name;   
}

// $product->is_type( $type ) checks the product type, string/array $type ( 'simple', 'grouped', 'variable', 'external' ), returns boolean
$is_simple_product = $product->is_type( 'simple' );

// Check child product is marked as Private/Custom trip.
$is_private_custom_trip = get_field( 'is_private_custom_trip', $product->id );

$activity_level = tt_get_custom_product_tax_value( $product_id, 'activity-level', true );
$trip_style     = tt_get_custom_product_tax_value( $product_id, 'trip-style', true );
$hotel_level    = tt_get_custom_product_tax_value( $product_id, 'hotel-level', true );
$trip_duration  = tt_get_custom_product_tax_value( $product_id, 'trip-duration', true );

$trip_duration_terms   = get_the_terms( $product_id, 'trip-duration' );
$trip_duration_term_id = ! empty( $trip_duration_terms ) && ! is_wp_error( $trip_duration_terms ) ? $trip_duration_terms[0]->term_id : false;

if ( ! empty( $trip_duration ) ) {
    $trip_duration = str_replace( [' Days', ' Nights', '&amp;'], [' D', ' N', '/'], $trip_duration );
} else {
    $trip_duration = '';
}

if ( $trip_duration_term_id ) {
    $pdp_name = get_field( 'pdp_name', 'trip-duration_' . $trip_duration_term_id );
    if ( ! empty( $pdp_name ) ) {
        $trip_duration = $pdp_name;
    }
}
?>

<?php
if ($product_overview) :
    $product_subtitle = $product_overview['product_subtitle'];
?>

    <div class="container my-5 pdp-overview-bar">
        <div class="row">
            <div class="col-lg-5 product-main-info">
                <nav class="mb-0" aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item fs-sm"><a href="<?php echo get_site_url(); ?>">Home</a></li>
                        <li class="breadcrumb-item active fs-sm" aria-current="page"><?php echo get_the_title(); ?></li>
                    </ol>
                </nav>
                <h1 class="h4 fw-semibold"><?php echo get_the_title(); ?></h1>
                <?php if ($product_subtitle) : ?>
                    <p class="fw-normal fs-md lh-lg mb-0"><?php echo $product_subtitle; ?></p>
                <?php endif; ?>
                <ul class="list-inline mb-0 star-rating-custom">
                <?php function_exists('wc_yotpo_show_buttomline') ? wc_yotpo_show_buttomline() : ''; ?>
                </ul>
            </div>
            <div class="overview-details col-lg-5">
                <div class="tour-duration">
                    <p class="fw-normal fs-sm lh-sm mb-0 text-muted">Tour Duration</p>
                    <p class="fw-medium fs-md lh-md"><?php echo esc_html( $trip_duration ); ?></p>
                </div>
                <div class="trip-style">
                    <p class="fw-normal fs-sm lh-sm mb-0 text-muted">Trip Style <i class="bi bi-info-circle pdp-trip-styles"></i></p>
                    <p class="fw-medium fs-md lh-md mb-0"><?php echo $trip_style ? esc_html( $trip_style ) : ''; ?></p>
                </div>
                <div class="rider-level">
                    <p class="fw-normal fs-sm lh-sm mb-0 text-muted">Activity Level <i class="bi bi-info-circle pdp-rider-level"></i></p>
                    <p class="fw-medium fs-md lh-md"><?php echo $activity_level ? esc_html( $activity_level ) : ''; ?></p>
                </div>
                <div class="hotel-level">
                    <p class="fw-normal fs-sm lh-sm mb-0 text-muted">Hotel Level <i class="bi bi-info-circle pdp-hotel-levels"></i></p>
                    <p class="fw-medium fs-md lh-md mb-0"><?php echo $hotel_level ? esc_html( $hotel_level ) : ''; ?></p>
                </div>
                <?php if (!empty($activity) && $activity == TT_ACTIVITY_DASHBOARD_NAME_BIKING): ?>
                <div class="bikes">
                    <p class="fw-normal fs-sm lh-sm mb-0 text-muted">Bikes</p>
                    <p class="fw-medium fs-sm lh-sm mb-0">
                        <?php 
                        if ($pdp_bikes) {
                            foreach ($pdp_bikes as $key => $bike) {
                                if ($key < 4) {
                                    setup_postdata($bike);
                                    echo $bike->post_title.'<br>';
                                }
                            }
                        }
                        wp_reset_postdata();
                        ?>
                    </p>
                    <a class="fs-sm view-details" href="#bikes-guides">View details</a>
                </div>
                <?php endif; ?>
            </div>
            <div class="col-lg-2 pricing">
                <p class="fw-normal fs-sm lh-sm mb-0 text-muted starting-from">Starting from</p>
                <p class="fw-bold fs-xl lh-xl">
                    <span class="amount">
                        <span class="woocommerce-Price-currencySymbol"></span><?php
                            //echo !$product->sale_price ? $product->price : $product->sale_price;
                            $start_price = tt_get_lowest_starting_from_price( $product->id );

                            if( !empty( $start_price ) ){
                                echo( wc_price( $start_price ) );
                            }
                            ?>
                    </span>
                    <span class="fw-normal fs-sm">per person</span>
                </p>
                <?php
                    // Show Book now button if the product is simple and marked as Private/Custom trip.
                    if( $is_simple_product && $is_private_custom_trip ):
                ?>
                    <form class="cart grouped_form" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ) ?>" method="post" enctype="multipart/form-data" target="_blank">
                        <button type="submit" class="btn btn-primary btn-md rounded-1 dates-pricing-book-now" data-return-url="/?trip=<?php echo $product->name ?>">Book now</button>                                    
                        <input type="hidden" name="<?php echo esc_attr( 'quantity[' . $product->id . ']' ) ?>" value="1" class="wc-grouped-product-add-to-cart-checkbox" />
                        <input type="hidden" name="add-to-cart" value="<?php echo $product->id ?>" />
                    </form>
                <?php else: ?>
                    <a class="btn btn-primary btn-lg rounded-1 w-100" href="#dates-pricing">See dates</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <hr class="overview-divider m-0">
<?php endif; ?>
<!-- overview bar section end -->
