<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * WP Search With Algolia instantsearch template file.
 *
 * author  WebDevStudios <contact@webdevstudios.com>
 * since   1.0.0
 *
 * version 2.0.0
 * package WebDevStudios\WPSWA
 */

get_header();

/**
 * When you download WP Search with Algolia, it comes with a couple of template files for both autocomplete and instantsearch.
 * These make for solid defaults with minimal display, but we need to make them more robust and fit our needs.
 *
 * In order to customize these files safely, we need to copy
 * the autocomplete.php and instantsearch.php files out of /plugins/wp-search-with-algolia/templates/
 * and into a folder named algolia in your currently active theme.
 *
 * The plugin will automatically detect their existence from the theme and use those copies instead of the prepackaged versions.
 * This allows you to customize and still update the plugin without losing customization.
 *
 * @link https://webdevstudios.com/2022/10/11/wp-search-with-algolia-autocomplete-instantsearch-customization/
 */

// Take the Date/Time filter params from the URL.
$urlStartTime  = $_GET['start_time'];
$urlEndTime    = $_GET['end_time'];
$urlDateFilter = '';
$urlFlag       = 0;
if ( isset( $urlStartTime ) && ! empty( $urlStartTime ) && isset( $urlEndTime ) && ! empty( $urlEndTime ) ) {
    $urlDateFilter = "AND start_date_unix:".$urlStartTime." TO ".$urlEndTime;
    $urlFlag       = 1;
}

// Collect PLP attributes like description, Travel Info, Thumbnail, etc.
$cate                 = get_queried_object();
$cateID               = $cate->term_id;
$plp_algolia_category = get_term($cateID);

// Set up the Algolia filter key.
$filter_name          = 'taxonomies.product_cat';

if( isset( $cate->taxonomy ) && ! empty( $cate->taxonomy ) ) {
    switch ($cate->taxonomy) {
        case 'activity':
            $filter_name = 'taxonomies.activity';
            break;

        case 'destination':
            $filter_name = 'taxonomies.destination';
            break;

        case 'activity-level':
            $filter_name = 'taxonomies.activity-level';
            break;

        case 'trip-style':
            $filter_name = 'taxonomies.trip-style';
            break;

        case 'hotel-level':
            $filter_name = 'taxonomies.hotel-level';
            break;

        case 'trip-duration':
            $filter_name = 'taxonomies.trip-duration';
            break;

        case 'trip-status':
            $filter_name = 'taxonomies.trip-status';
            break;

        case 'trip-class':
            $filter_name = 'taxonomies.trip-class';
            break;

        default:
            $filter_name = 'taxonomies.product_cat';
            break;
    }
}

// Get the thumbnail ID from product_cat_thumbnail.
$thumbnail_id = get_term_meta( $cateID, 'thumbnail_id', true );
// If the thumbnail ID is empty, most probably this is a taxonomy and need to check for a thumbnail from the ACF field.
if( empty( $thumbnail_id ) ) {
    // Get the thumbnail ID from the ACF field.
    $thumbnail_id = get_field( 'plp_thumbnail', $cate );
}

// Get the thumbnail URL.
$image_src = wp_get_attachment_url( $thumbnail_id );
if ( $image_src ) {
    $imgTag = '<img src="' . $image_src . '" alt="' . $cate->name . '" />';
} else {
    $imgTag = '';
}

$plp_travel_info      = get_field('plp_travel_info', $cate);
$plp_travel_info_link = get_field('plp_travel_info_link', $cate);
$emptyBlockContent    = '<div class="container no-results"><h2 class="fw-semibold">Sorry, we did not find anything. </h2><p class="fw-normal fs-lg lh-lg">Do you want to try <a href="#">[Suggested Query]</a>?</p></div><hr><div class="container discover-more"><div class="row"><h3 class="fw-semibold">Discover More Ways to Travel</h3>';

// No-Result Suggestions
$noresultSuggestions = get_field('search_noresult_suggestions', 'option'); 
if(!empty($noresultSuggestions)){
    foreach($noresultSuggestions as $item){ 
        $emptyBlockContent .= '<div class="col-12 col-md-4"><div class="card border-0"><img src="'.$item["item_image"]["url"].'" class="card-img-top rounded-1" alt="'.$item["item_title"].'"><div class="card-body ps-0"><a href="'.$item["item_url"].'"><p class="card-text text-start fw-semibold">'.$item["item_title"].'</p></a></div></div></div>';               
    }
}
$emptyBlockContent .= '</div></div>';

$dest_terms_args = array(
    'taxonomy'     => 'destination',
    'parent'        => 0,
    'hide_empty'    => true           
);
$dest_terms_top_level = get_terms( $dest_terms_args );
$dest_filters = array();
if ( ! empty( $dest_terms_top_level )  && ! is_wp_error( $dest_terms_top_level ) ) {
    foreach( $dest_terms_top_level as $term ) {
        $dest_filters[$term->slug] = $term->name;
    }
}

// Destinations pre-select filter.
if( 'taxonomies.destination' === $filter_name ) {
    $current_dest      = get_queried_object();
    $current_dest_slug = $current_dest->slug;
    $current_dest_name = $current_dest->name;
    ?>
    <script>
        let current_dest_slug = "<?php echo esc_attr( $current_dest_slug ); ?>";
        let current_dest_name = "<?php echo esc_attr( $current_dest_name ); ?>";
    </script>
    <?php
}
?>

    <div class="plp-container">
        <div class="plp-hero-banner">
            <div class="banner-section">
            <?php echo $imgTag; ?>
                <div class="container">
                    
                    <h1 class="fw-semibold"><?php echo $plp_algolia_category->name; ?></h1>
                </div>
                
            </div>
            <div class="container description-section">
                <div class="row justify-content-between">
                    <div class="col-12 col-lg-6 col-xl-7 my-4">
                        <h4><?php echo $plp_algolia_category->description; ?></h4>
                    </div>
                    <div class="col-12 col-lg-6 col-xl-5 col-xxl-4 my-4">
                        <p class="fw-normal fs-md lh-md"><?php echo $plp_travel_info;?>
                            <?php if (!empty($plp_travel_info_link)): ?>
                            <br><br><a href="<?php echo $plp_travel_info_link;?>" class="view-category-info-link">View <?php echo $plp_algolia_category->name; ?> Travel Info</a>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="plp-list-container">
        <div id="ais-wrapper">
            <main id="ais-main">
                
                <div id="search-nav-tabs" class="my-4 d-none">
                    <nav>
                        <div class="nav nav-tabs" id="nav-tab" role="tablist">

                        </div>
                    </nav>
                </div>

                <div class="algolia-search-box-wrapper-cont">
                    <?php get_template_part('algolia/partials/search-box', 'wrapper' ); ?>
                </div>

                <div class="container list-counter">
                    <p class="fw-normal fs-md lh-md">Showing <span class="fw-bold resultCount"></span> Trips</p>
                    <?php get_template_part('algolia/partials/view-layout', 'switcher' ); ?>
                </div>

                <div class="grid-view container" id="algolia-hits"></div>

                <!-- Modal Container -->
                <div class="container">
                    <!-- Modal -->
                    <?php get_template_part('algolia/partials/filters', 'modal', array( 'dest_filters' => $dest_filters ) ); ?>
                </div> <!-- / Modal .container -->
            </main>
        </div>
    </div>

    <!-- #tmpl-instantsearch-menu-template -->
    <?php get_template_part('algolia/tmpl/instantsearch-menu', 'template' ); ?>

    <!-- #refine-hierarchy-template -->
    <?php get_template_part('algolia/tmpl/refine-hierarchy', 'template' ); ?>

    <!-- #tmpl-instantsearch-hit -->
    <?php get_template_part('algolia/tmpl/instantsearch', 'hit' ); ?>

    <script type="text/javascript">

        let endTime;
        let startTime;

        let calendarStartDate;
        let calendarEndDate;
        let urlFlag = false;


        jQuery(function() {

            if(1===1) {

                if (algolia.indices.searchable_posts === undefined && jQuery('.admin-bar').length > 0) {
                    alert('It looks like you haven\'t indexed the searchable posts index. Please head to the Indexing page of the Algolia Search plugin and index it.');
                }

                /* Instantiate instantsearch.js */
                var search = instantsearch({
                    indexName: algolia.indices.searchable_posts.name,
                    searchClient: algoliasearch( algolia.application_id, algolia.search_api_key ),
                    routing: true
                });

                const analyticsMiddleware = () => {
                    return {
                        onStateChange({ uiState }) {
                            // Google Tag Manager (GTM)
                            // You can use `uiState` to make payloads for third-party trackers.
                            window.dataLayer = window.dataLayer || [];
                            var impressions = [];   
                            var url = window.location.href;
                            var splitUrl = url.split(".com");
                            var textAfterCom = splitUrl[splitUrl.length - 1];             
                            jQuery( ".trip-card-body" ).each(function( index ) {
                                let impression = {
                                    'name': jQuery( this ).find(".trip-title" ).first().text() ,
                                    'id': jQuery( this ).find(".woocommerce-products-compare-checkbox" ).data("product-id") ? jQuery( this ).find(".woocommerce-products-compare-checkbox" ).data("product-id").toString() : '',
                                    'price': jQuery( this ).find(".trip-price").data("price"),
                                    'brand': '',
                                    'category': jQuery( this ).find(".trip-category" ).text(),
                                    'list': textAfterCom,
                                    'position': index+1
                                };                
                                impressions.push(impression)
                            });
                            dataLayer.push ({
                                'event':'view_item_list',
                                'ecommerce': { 
                                    'currencyCode': jQuery("#currency_switcher").val(),
                                    'impressions': impressions
                                }
                            })
                        },
                        subscribe() {},
                        unsubscribe() {},
                    }
                }

                search.addWidgets([

                    /* Search widget */
                    instantsearch.widgets.searchBox({
                        container: '#algolia-search-box'
                    }),

                    /* Stats widget */
                    instantsearch.widgets.stats({
                        container: '#algolia-stats'
                    }),

                    /* Stats widget */
                    instantsearch.widgets.clearRefinements({
                        container: '#clear-refinements'
                    }),

                    /* Hits widget */
                    instantsearch.widgets.infiniteHits({
                        container: '#algolia-hits',
                        hitsPerPage: 24,
                        cssClasses: {
                            loadMore: [
                                "btn",
                                "btn-primary",
                                "btn-lg",
                                "me-md-2",
                                "fs-6",
                                "px-5",
                                "py-4",
                                "rounded-1",
                                "position-relative"
                            ],
                        },
                        templates: {
                            empty: '<?php echo $emptyBlockContent; ?>',
                            item: wp.template('instantsearch-hit'),
                            showMoreText: "View more"
                        },
                        transformData: {
                            item: function (hit) {

                                function replace_highlights_recursive (item) {
                                    if (item instanceof Object && item.hasOwnProperty('value')) {
                                        item.value = _.escape(item.value);
                                        item.value = item.value.replace(/__ais-highlight__/g, '<em>').replace(/__\/ais-highlight__/g, '</em>');
                                    } else {
                                        for (var key in item) {
                                            item[key] = replace_highlights_recursive(item[key]);
                                        }
                                    }
                                    return item;
                                }

                                hit._highlightResult = replace_highlights_recursive(hit._highlightResult);
                                hit._snippetResult = replace_highlights_recursive(hit._snippetResult);

                                return hit;
                            }
                        }
                    }),
                    instantsearch.widgets.sortBy({
                        container: '#ais-sortBy',
                        items: [
                            { label: 'Relevance', value: algolia.indices.searchable_posts.name },
                            { label: 'Price: Low to High', value: 'price_asc' },
                            { label: 'Price: High to Low', value: 'price_desc' },
                            { label: 'A to Z', value: 'sort_az' },
                            { label: 'Z to A', value: 'sort_za' },
                        ],
                        cssClasses: {
                            // root: 'MyCustomSortBy',
                            select: [
                                'sortBy-select'
                            ],
                            option: [
                                'sort-option'
                            ],
                        },
                    }),

                    /* Post types refinement widget */
                    instantsearch.widgets.menu({
                        container: '#nav-tab',
                        attribute: 'post_type_label',
                        sortBy: ['name:desc'],
                        limit: 10,
                        cssClasses: {
                            root: 'nav',
                            list: [
                                'nav-link'
                            ],
                        }
                    }),
                    instantsearch.widgets.refinementList({
                        container: '#duration-facet',
                        attribute: 'Duration',
                        sortBy: ['name:desc'],
                        operator: 'or',
                        limit: 10,
                        templates: {
                            item: wp.template('instantsearch-menu-template')
                        }
                    }),
                    instantsearch.widgets.refinementList({
                        container: '#trip-style-facet',
                        attribute: 'Trip Style',
                        sortBy: ['name:desc'],
                        operator: 'or',
                        limit: 10,
                        templates: {
                            item: wp.template('instantsearch-menu-template')
                        }
                    }),
                    instantsearch.widgets.refinementList({
                        container: '#rider-level-facet',
                        attribute: 'Activity Level',
                        sortBy: ['name:desc'],
                        operator: 'or',
                        limit: 10,
                        templates: {
                            item: wp.template('instantsearch-menu-template')
                        }
                    }),
                    instantsearch.widgets.refinementList({
                        container: '#hotel-level-facet',
                        attribute: 'Hotel Level',
                        sortBy: ['name:desc'],
                        operator: 'or',
                        limit: 10,
                        templates: {
                            item: wp.template('instantsearch-menu-template')
                        }
                    }),
                    instantsearch.widgets.rangeInput({
                        container: '#price-input-facet',
                        attribute: 'Start Price',
                    }),
                    instantsearch.widgets.rangeSlider({
                        container: '#price-slider-facet',
                        attribute: 'Start Price',
                    }),

                    <?php
                        foreach( $dest_filters as $dest_slug => $dest_name ) {
                            ?>
                                instantsearch.widgets.refinementList({
                                    container: '#dest-<?php echo esc_attr( $dest_slug ); ?>',
                                    attribute: 'taxonomies_hierarchical.destination.lvl1',
                                    sortBy: ['name:desc'],
                                    operator: 'or',
                                    limit: 150,
                                    templates: {
                                        item: wp.template('instantsearch-menu-template')
                                    },
                                    transformItems( items ) {
                                        let modified_items = items.filter(
                                            function( item ) {
                                                return item.label.includes( '<?php echo esc_attr( $dest_name ); ?>' )
                                            }
                                        );
                                        modified_items = modified_items.map(item => ({
                                            ...item,
                                            label: item.label.replace('<?php echo esc_attr( $dest_name ); ?> >', '').trim(),
                                        }));
                                        // Remove 'Bike Tours' from the labels.
                                        modified_items = modified_items.map(item => ({
                                            ...item,
                                            label: item.label.replace('Bike Tours', '').trim(),
                                        }));
                                        return modified_items;
                                    },
                                }),
                            <?php
                        }
                    ?>

                    instantsearch.widgets.configure({
                        filters: "<?php echo $filter_name; ?>: ' <?php
							echo $plp_algolia_category->name;
							?> '<?php echo $urlDateFilter; ?>",
                        hitsPerPage: 24,
                        analyticsTags: ['browse', '<?php
							echo $plp_algolia_category->name;
							?>'],
                    })
                ]);
                if (1===1) {
                     var calendarOptions = {
                        "autoUpdateInput": false,
                        "showCustomRangeLabel": true,
                        "singleDatePicker": false,
                        "parentEl": "#calendarTrigger",
                        "autoApply": true,
                        "locale": {
                            "format": "MMMM D",
                            "separator": " - ",
                            "applyLabel": false,
                            "cancelLabel": "Clear Dates"
                        }
                    }
                    if (isMobileDevice()) {
                        calendarOptions.linkedCalendars = false
                    }
                    <?php if ($urlFlag == 1) { ?>
                        convertedStartEpoch = convertEpochToDate(<?php echo $urlStartTime; ?>)
                        convertedEndEpoch = convertEpochToDate(<?php echo $urlEndTime; ?>)
                        calendarOptions.startDate = convertedStartEpoch
                        calendarOptions.endDate = convertedEndEpoch
                    <?php } ?>
                    if(urlFlag) {
                        convertedStartEpoch = convertEpochToDate(calendarStartDate)
                        convertedEndEpoch = convertEpochToDate(calendarEndDate)
                        calendarOptions.startDate = convertedStartEpoch
                        calendarOptions.endDate = convertedEndEpoch
                    }

                    function initCalendar() {
                        jQuery('#search-daterange').daterangepicker(calendarOptions);
                        jQuery('#search-daterange').on('apply.daterangepicker', function (ev, picker) {
                            jQuery(this).val(picker.startDate.format('MMMM D') + ' - ' + picker.endDate.format('MMMM D'));
                            jQuery("#ais-date-selector .fake-selector").text(picker.startDate.format('MMMM D') + " - " + picker.endDate.format('MMMM D'));
                            jQuery('#ais-date-selector .fake-selector').toggleClass("border-dark fw-semibold")
                            jQuery('#start_time').val( picker.startDate._d.valueOf() );
                            jQuery('#end_time').val( picker.endDate._d.valueOf() );

                            if (jQuery("#rangeDateVal").parent().length){
                                jQuery("#rangeDateVal").html(picker.startDate.format('MMMM D') + " - " + picker.endDate.format('MMMM D'));
                                startTime = picker.startDate._d.valueOf();
                                endTime = picker.endDate._d.valueOf();
                                startTime = startTime / 1000;
                                endTime = endTime / 1000;

                                filterTime(Math.round(startTime),Math.round(endTime));
                            }
                            else{
                                jQuery(".range_inputs").prepend("<span id='rangeDateVal'>"+picker.startDate.format('MMMM D') + " - " + picker.endDate.format('MMMM D')+"</span>");
                                startTime = picker.startDate._d.valueOf();
                                endTime = picker.endDate._d.valueOf();
                                startTime = startTime / 1000;
                                endTime = endTime / 1000;

                                filterTime(Math.round(startTime),Math.round(endTime));

                            }
                        });
                        jQuery('#search-daterange').on('cancel.daterangepicker', function (ev, picker) {
                            jQuery(this).val('');
                            jQuery("#rangeDateVal").remove();                        
                            filterTime(0,0);
                            jQuery("#ais-date-selector .fake-selector").text('Date');
                            jQuery('#ais-date-selector .fake-selector').toggleClass("border-dark fw-semibold")

                            // Init a new calendar on clear dates.
                            initCalendar();
                            jQuery('#search-daterange').trigger('click');
                        });
                    }

                    // Load for the first time.
                    initCalendar();
                }
                /* Start */
                search.start();
                search.use(analyticsMiddleware);
                jQuery( '#algolia-search-box input' ).attr( 'type', 'search' ).trigger( 'select' );

                let firstLoad = false;
                search.on("render", () => {
                    // Do something when the template has been rendered.
                    jQuery("#currency_switcher").trigger("change")
                    jQuery(".resultCount").text(jQuery(".ais-Menu-count").text())
                    // Hide the Empty destinations.
                    jQuery('#destinations .ais-RefinementList.ais-RefinementList--noRefinement').closest('div[class^="dest"]').hide()
                    // Show back the destinations with items inside.
                    jQuery('#destinations .ais-RefinementList').not('.ais-RefinementList--noRefinement').closest('div[class^="dest"]').show();

                    // Adjust initial states of select all toggles.
                    let allDestWrappers = jQuery( '#destinations div[class^="dest"]' );
                    allDestWrappers.each(function(i, el) {
                        let allItemsLength         = jQuery(el).find('.ais-RefinementList-item').length;
                        let allSelectedItemsLength = jQuery(el).find('.ais-RefinementList-item--selected').length;

                        if( allItemsLength === allSelectedItemsLength ) {
                            // Make the toggle active.
                            jQuery(el).find('input[name^="select-all"]').prop( "checked", true ).prop( "indeterminate", false );
                            jQuery(el).removeClass( 'select-all-active' );
                        } else if( allSelectedItemsLength === 0 ) {
                            // Make the toggle inactive.
                            jQuery(el).find('input[name^="select-all"]').prop( "checked", false ).prop( "indeterminate", false );
                            jQuery(el).removeClass( 'select-all-inactive' );
                        } else {
                            // Indeterminate.
                            jQuery(el).find('input[name^="select-all"]').prop( "checked", false ).prop( "indeterminate", true );
                        }
                    })

                    if( ! firstLoad && typeof current_dest_name !== 'undefined' && typeof current_dest_slug !== 'undefined' ) {
                        firstLoad = true;
                        jQuery(`input[name="select-all-${current_dest_slug}"]`).click();
                    }

                    // Initial call on page load, and filtering.
                    equalizeContentHeights();
                });

                function equalizeHeights(selector) {
                    const items = jQuery(selector);
                    if (items.length > 0) {
                    let maxHeight = 0;
                    let currentRow = [];

                    items.each(function(index) {
                        const $item = jQuery(this);
                        $item.css('height', 'auto');
                        currentRow.push($item);
                        if ((index + 1) % 3 === 0 || index === items.length) {
                        maxHeight = Math.max(...currentRow.map(item => item.outerHeight()));
                        currentRow.forEach(function(item) {
                            item.css('height', maxHeight + 'px');
                        });

                        currentRow = [];
                        }
                    });
                    }
                }

                function equalizeContentHeights() {
                    setTimeout(() => {
                    equalizeHeights('#algolia-hits .card-body .card-title'); // Equalize title heights
                    equalizeHeights('#algolia-hits .trip-desc');  // Equalize description heights
                    }, 500);
                }

                function filterTime(startTime,endTime) {

                    let urlDateFilter = 0 < startTime && 0 < endTime ? `AND start_date_unix:${startTime} TO ${endTime}` : '';

                    calendarStartDate = startTime;
                    calendarEndDate = endTime;
                    urlFlag = true;

                    search.addWidgets([

                        instantsearch.widgets.configure({
                        filters: `<?php echo $filter_name; ?>: ' <?php
							echo $plp_algolia_category->name;
							?> '${urlDateFilter}`,
                        hitsPerPage: 24,
                        analyticsTags: ['browse', '<?php
							echo $plp_algolia_category->name;
							?>'],
                        })
                    ]);

                }
                
            }
        });
        function getUrlVars() {
            var vars = {};
            var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
                vars[key] = value;
            });
            return vars;
        }

        var submit_range_input_delay = (function(){
            var timer = 0;
            return function( callback, ms ) {
                clearTimeout(timer);
                timer = setTimeout( callback, ms );
            };
        })();

        jQuery(document).on( 'keyup change', '.ais-RangeInput-input', function(ev) { 
            submit_range_input_delay(function(){
                jQuery('.ais-RangeInput-submit').click();
            }, 1000)
        });

        function destinationClick(elm) {
            let data = elm.attr('data-value');
            jQuery('#ais-destination-selector .fake-selector').text( data.replaceAll('Bike Tours', '').trim() );
            jQuery('#ais-destination-selector .fake-selector').toggleClass("border-dark fw-semibold")
            // console.log(elm);
        }

        jQuery( document ).ready(function() {
            jQuery('body').removeClass('elementor-kit-14');
        });
        // Select all functionality for destionations filter.
        jQuery(document).on( 'change', '#destinations [name^="select-all"]', function(ev) {
            if( jQuery(this).is(":checked") ) {
                jQuery(this).closest( 'div[class^="dest"]' ).addClass( 'select-all-active' ).removeClass('select-all-inactive');
                // Should select all destinations from this continent.
                jQuery(this).closest( 'div[class^="dest"]' ).find( 'div[id^="dest"] .ais-anchor' ).each(function(i, el) {
                    // If any of the regions is not selected, select it.
                    if( ! jQuery(el).closest('.ais-RefinementList-item').hasClass( "ais-RefinementList-item--selected" ) ) {
                        this.click();
                    }
                });
            } else {
                jQuery(this).closest( 'div[class^="dest"]' ).removeClass( 'select-all-active' ).addClass('select-all-inactive');
                // Should unselect all destinations from this continent.
                jQuery(this).closest( 'div[class^="dest"]' ).find( 'div[id^="dest"] .ais-anchor' ).each(function(i, el) {
                    // If any of the regions is selected, deselect it.
                    if( jQuery(el).closest('.ais-RefinementList-item').hasClass( "ais-RefinementList-item--selected" ) ) {
                        this.click();
                    }
                });
            }
        })
        jQuery("body").on("click", "#clear-refinements", function() {
            currentUrl = window.location.href
            window.location.href = currentUrl.split('?')[0] 
            // jQuery('.fs-destination').text('Destination');
            // jQuery('#ais-date-selector .fake-selector').text('Date')
            // jQuery('#ais-destination-selector .fake-selector').toggleClass("border-dark fw-semibold")
            // jQuery('#ais-date-selector .fake-selector').toggleClass("border-dark fw-semibold")

        });

        jQuery(window).load(function() {
            window.dataLayer = window.dataLayer || [];
            var impressions = [];
            var url = window.location.href;
            var splitUrl = url.split(".com");
            var textAfterCom = splitUrl[splitUrl.length - 1];                
            jQuery( ".trip-card-body" ).each(function( index ) {                    
                let impression = {
                    'name': jQuery( this ).find(".trip-title" ).first().text() ,
                    'id': jQuery( this ).find(".woocommerce-products-compare-checkbox" ).data("product-id") ? jQuery( this ).find(".woocommerce-products-compare-checkbox" ).data("product-id").toString() : '',
                    'price': jQuery( this ).find(".trip-price").data("price"),
                    'brand': '',
                    'category': jQuery( this ).find(".trip-category" ).text(),
                    'list': textAfterCom,
                    'position': index+1
                };                
                impressions.push(impression)
            });
            dataLayer.push ({
                'event':'view_item_list',
                'ecommerce': { 
                    'currencyCode': 'USD',
                    'impressions': impressions
                }
            })

            <?php if ($urlFlag == 1) { ?>                
                convertedStartEpoch = convertEpochToDate(<?php echo $urlStartTime; ?>)
                convertedEndEpoch = convertEpochToDate(<?php echo $urlEndTime; ?>)
                if ( convertedStartEpoch && convertedEndEpoch ) {
                    jQuery("#ais-date-selector .fake-selector").text(moment(convertedStartEpoch).format('MMM D') + " - " + moment(convertedEndEpoch).format('MMM D'));                
                }
            <?php } ?>

            if( urlFlag ) {
                convertedStartEpoch = convertEpochToDate(calendarStartDate)
                convertedEndEpoch = convertEpochToDate(calendarEndDate)
                if ( convertedStartEpoch && convertedEndEpoch ) {
                    jQuery("#ais-date-selector .fake-selector").text(moment(convertedStartEpoch).format('MMM D') + " - " + moment(convertedEndEpoch).format('MMM D'));  
                }
            }
        })

        jQuery('#filterModal').on('show.bs.modal', function (event) {
            <?php if ($urlFlag == 1) { ?>
                convertedStartEpoch = convertEpochToDate(<?php echo $urlStartTime; ?>)
                convertedEndEpoch = convertEpochToDate(<?php echo $urlEndTime; ?>)
                if ( convertedStartEpoch && convertedEndEpoch ) {
                    if (jQuery("#rangeDateVal").parent().length) {
                        jQuery("#rangeDateVal").html(moment(convertedStartEpoch).format('MMMM D') + " - " + moment(convertedEndEpoch).format('MMMM D'));
                    } else {
                        jQuery(".range_inputs").prepend("<span id='rangeDateVal'>"+moment(convertedStartEpoch).format('MMMM D') + " - " + moment(convertedEndEpoch).format('MMMM D')+"</span>");
                    }
                }
            <?php } ?>
            if( urlFlag ) {
                convertedStartEpoch = convertEpochToDate(calendarStartDate)
                convertedEndEpoch = convertEpochToDate(calendarEndDate)
                if ( convertedStartEpoch && convertedEndEpoch ) {
                    if (jQuery("#rangeDateVal").parent().length) {
                        jQuery("#rangeDateVal").html(moment(convertedStartEpoch).format('MMMM D') + " - " + moment(convertedEndEpoch).format('MMMM D'));
                    } else {
                        jQuery(".range_inputs").prepend("<span id='rangeDateVal'>"+moment(convertedStartEpoch).format('MMMM D') + " - " + moment(convertedEndEpoch).format('MMMM D')+"</span>");
                    }
                }
            }
        })

        function selectItemAnalytics(id) {
            var url = window.location.href;
            var splitUrl = url.split(".com");
            var textAfterCom = splitUrl[splitUrl.length - 1];
            dataLayer.push({"ecommerce" : null})

            jQuery( ".trip-card-body" ).each(function( index ) {
                var cardId = jQuery( this ).find(".woocommerce-products-compare-checkbox" ).data("product-id")
                var price = jQuery( this ).find(".trip-price").data("price")
                if (parseInt(id) == parseInt(cardId)) {
                    dataLayer.push({
                        'event': 'select_item',
                        'ecommerce': {
                        'click': {
                            'actionField': { 'list': textAfterCom },
                                'products': [{
                                    'name': jQuery( this ).find(".trip-title" ).first().text(), // Please remove special characters
                                    'id': id, // Parent ID
                                    'price': parseFloat(price).toFixed(2), // per unit price displayed to the user - no format is ####.## (no '$' or ',')
                                    'brand': '', //
                                    'category': jQuery( this ).find(".trip-category" ).text(), // populate with the 'country,continent' separating with a comma
                                    'position': index+1
                                }]
                            }
                        },
                    })
                    return false                    
                }                    
            });            
        }

    </script>

<?php

get_footer();
