<?php
/**
 * WP Search With Algolia instantsearch template file.
 *
 * @author  WebDevStudios <contact@webdevstudios.com>
 * @since   1.0.0
 *
 * @version 2.0.0
 * @package WebDevStudios\WPSWA
 */
get_header();
$cate = get_queried_object();
$cateID = $cate->term_id;
$plp_algolia_category = get_term($cateID);

$urlStartTime = $_GET['start_time'];
$urlEndTime = $_GET['end_time'];
$urlDateFilter = '';
$urlFlag = 0;
if (isset($urlStartTime) && !empty($urlStartTime) && isset($urlEndTime) && !empty($urlEndTime)) {
    $urlDateFilter = "AND start_date_unix:".$urlStartTime." TO ".$urlEndTime;
    $urlFlag = 1;
}

$thumbnail_id = get_term_meta( $cateID, 'thumbnail_id', true );
$image = wp_get_attachment_url( $thumbnail_id );
if ( $image ) {
    $imgTag = '<img src="' . $image . '" alt="' . $cate->name . '" />';
}
else{
    $imgTag = '';
}
$plp_travel_info = get_field('plp_travel_info', $cate);
$plp_travel_info_link = get_field('plp_travel_info_link', $cate);
$emptyBlockContent = '<div class="container no-results"><h2 class="fw-semibold">Sorry, we did not find anything. </h2><p class="fw-normal fs-lg lh-lg">Do you want to try <a href="#">[Suggested Query]</a>?</p></div><hr><div class="container discover-more"><div class="row"><h3 class="fw-semibold">Discover More Ways to Travel</h3>';

// No-Result Suggestions
$noresultSuggestions = get_field('search_noresult_suggestions', 'option'); 
if(!empty($noresultSuggestions)){
    foreach($noresultSuggestions as $item){ 
        $emptyBlockContent .= '<div class="col-12 col-md-4"><div class="card border-0"><img src="'.$item["item_image"]["url"].'" class="card-img-top rounded-1" alt="'.$item["item_title"].'"><div class="card-body ps-0"><a href="'.$item["item_url"].'"><p class="card-text text-start fw-semibold">'.$item["item_title"].'</p></a></div></div></div>';               
    }
}
$emptyBlockContent .= '</div></div>';
?>

<div class="container-fluid plp-container p-0">
    <div class="plp-hero-banner mb-4">
        <div class="banner-section">
            <?php echo $imgTag; ?>
            <h1 class="fw-semibold"><?php echo $plp_algolia_category->name; ?></h1>
        </div>
        <div class="container description-section">
            <h4><?php echo $plp_algolia_category->description; ?></h4>
            <p class="fw-normal fs-md lh-md"><?php echo $plp_travel_info;?>
            <?php if (!empty($plp_travel_info_link)): ?>
                <br><br><a href="<?php echo $plp_travel_info_link;?>" class="view-category-info-link">View <?php echo $plp_algolia_category->name; ?> Travel Info</a>
                <?php endif; ?>
            </p>
            </div>
    </div>
</div>
<div class="container plp-list-container">
    <div id="ais-wrapper">
        <main id="ais-main">
            
            <div id="search-nav-tabs" class="my-4 d-none">
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">

                    </div>
                </nav>
            </div>
            <!-- Button trigger modal -->

            <!-- <hr class="card-divider" /> -->

            <div class="algolia-search-box-wrapper">
                <p class="fw-normal fs-xs lh-xs mb-0 me-4 align-self-center">Filters</p>
                <div id="algolia-search-box" style="display: none;"></div>
                <div id="ais-sortBy"></div>
                <div id="ais-date-selector" class="mobile-hideme fs-date">
                    <button type="button" class="fake-selector" id="filter-modal" data-bs-toggle="modal" data-bs-target="#filterModal">
                        Date
                    </button>
                </div>
                <div id="ais-destination-selector" class="mobile-hideme">
                    <button type="button" class="fake-selector fs-destination" id="filter-modal" data-bs-toggle="modal" data-bs-target="#filterModal">
                        Destination
                    </button>
                </div>
                <div id="ais-more-selector" class="mobile-hideme">
                    <button type="button" class="fake-selector" id="filter-modal" data-bs-toggle="modal" data-bs-target="#filterModal">
                        More
                    </button>
                </div>
                <div id="ais-more-selector" class="desktop-hideme">
                    <button type="button" class="fake-selector" id="filter-modal" data-bs-toggle="modal" data-bs-target="#filterModal">
                        Filters
                    </button>
                </div>

            </div>

            <div class="list-counter">
                <p class="fw-normal fs-md lh-md">Showing <span class="fw-bold resultCount"></span> Trips</p>
            </div>

            <div id="algolia-hits"></div>
            <!-- Modal Container -->
            <div class="container">


                <!-- Modal -->
                <div class="modal fade modal-search-filter" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">

                            <div class="modal-header">
                                <h5 class="modal-title text-center" id="filterModalLabel">Filters</h5>
                                <span type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                                    <i type="button" class="bi bi-x"></i>
                                                </span>
                            </div>

                            <div class="modal-body">
                                <h5>Date Range</h5>
                                <span id="search-daterange"></span>
                                <div class="row mx-0" id="calendarTrigger"></div>
                                <div id="range-input" style="position: absolute; bottom: 10000px; overflow: hidden;"></div>
                                <hr>
                                <h5>Destinations</h5>
                                <div id="hierarchical-menu"></div>
                                <hr>
                                <!-- <h5>Duration</h5>
                                <div id="duration-facet"></div>
                                <hr> -->
                                <h5>Trip Style</h5>
                                <div id="trip-style-facet"></div>
                                <hr>
                                <h5>Rider Level</h5>
                                <div id="rider-level-facet"></div>
                                <hr>
                                <h5>Hotel Level</h5>
                                <div id="hotel-level-facet"></div>
                                <!-- <hr> -->
                            </div>

                            <div class="modal-footer">
                                <div class="container">
                                    <div class="row mx-0 align-items-center">
                                        <div class="col-3 clear-all-btn">
                                            <span class="modal-a" id="clear-refinements"></span>
                                        </div>
                                        <div class="col d-lg-flex justify-content-lg-end align-items-lg-baseline apply-filters-info">
                                            <button type="button" class="btn btn-secondary d-none" data-bs-dismiss="modal">Close</button>
                                            <span class="filter-results-number" id="algolia-stats"></span>
                                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Apply filters</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div><!-- / .modal-content -->
                    </div><!-- / .modal-dialog -->
                </div><!-- / .modal -->
            </div> <!-- / Modal .container -->
        </main>
    </div>
</div>
    <script type="text/html" id="tmpl-instantsearch-menu-template">
        <div class="menu-facet-container">
            <a class="ais-anchor" href="{{data.url}}">
                <span class="f-check"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/checkback.png" /></span>
                <span class="f-check-active"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/checkactive.png" /></span>
                <span class="">{{data.label}}</span>
                <!-- Hide the count for the moment -->
                <!-- <span class="">{{data.count.toLocaleString()}}</span> -->
            </a>
        </div>
    </script>

    <script type="text/html" id="refine-hierarchy-template">
        <label class="ais-hierarchical-menu--link">
            <input type="checkbox" {{#isRefined}}checked{{/isRefined}} />
            <span class="ais-hierarchical-menu--name">{{label}}</span>
            <span class="ais-hierarchical-menu--count">({{count}})</span>
        </label>
    </script>

    <script type="text/html" id="tmpl-instantsearch-hit">

        <article itemtype="http://schema.org/Article" id="algoliaSearchResults">

            <# if ( data.post_type_label === 'Trips') {
                var comparedIdsCookie = jQuery.cookie( 'wc_products_compare_products');
                var comparedIds = [];
                var checkedTick = '';
                if( comparedIdsCookie != undefined ){
                    comparedIds = comparedIdsCookie.split(',');
                    var post_id = data.post_id;
                    post_id = post_id.toString();
                    var is_checked = comparedIds.includes(post_id);
                    checkedTick = is_checked == true ? 'checked' : '';
                }
             #>
            <# jQuery(".ais-InfiniteHits-item").removeClass("ais-Hits-item-articles-pages");
            jQuery(".ais-InfiniteHits-list").removeClass(" ais-Hits-list-articles-pages") #>

            <div class="card mb-3 border-0 trip-card-body">
                <div class="row g-0 mx-0">
                    <div class="col-md-4 gallery-carousel">

                        <div id="carouselExampleIndicators{{ data.post_id }}" class="carousel slide h-100" data-bs-ride="carousel">
                            <div class="carousel-indicators">
                                <# if ( data.gallery_images ) { #>
                                    <# data.gallery_images.forEach(function (item, index) { #>
                                        <button type="button" data-bs-target="#carouselExampleIndicators{{ data.post_id }}" data-bs-slide-to="{{index}}" class="<# if ( index == 0 ) { #> active <# } #>"></button>
                                    <# }) #>
                                <# } #>
                            </div>

                            <div class="carousel-inner h-100">
                                <# if (data.gallery_images) { #>
                                    <# data.gallery_images.forEach(function (item, index) { #>
                                        <#
                                            let imageUrl = item.replace('-300x300', '-886x664');
                                            let imageUrlOriginalSize = item.replace('-300x300', '');
                                        #>
                                        <div class="carousel-item h-100 <# if (index == 0) { #> active <# } #>">
                                            <a href="{{ data.permalink }}" title="{{ data.post_title }}" class="ais-hits--thumbnail-link"
                                            onclick="selectItemAnalytics({{ data.post_id }})">
                                                <img src="{{ imageUrl }}" alt="{{ data.post_title }}" title="{{ data.post_title }}" class="d-block w-100"
                                                    onerror="this.onerror=null;this.src='{{ imageUrlOriginalSize }}';"
                                                    id="imgElement{{ index }}" /> <!-- Use a unique ID for each image element -->
                                            </a>
                                        </div>
                                    <# }) #>
                                <# } #>
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators{{ data.post_id }}" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators{{ data.post_id }}" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        </div>

                    </div>
                    <div class="col-md-6 desktop-hideme">
                       <div class="product-head-info my-3">
                            <# if (data.taxonomies.pa_badge) { 
                                <!-- data.taxonomies.pa_badge = data.taxonomies.pa_badge.sort(); -->
                                #>
                                <# data.taxonomies.pa_badge.forEach(function (badge, index) { #>
                                    <span class="badge <# if (badge == 'Hiking + Walking') { #>hw<# } else { #>bg-dark<# } #>">{{ badge }}</span>
                                <# }) #>
                            <# } #>
                            <!-- <# if ( data['Badge'] ) { #>
                                <# if ( data['Badge'].includes('Hiking') ) { #>
                                    <span class="badge hw">{{ data['Badge'] }}</span>
                                <# } else { #>
                                    <span class="badge bg-dark">{{ data['Badge'] }}</span>
                                <# } #>
                            <# } #> -->
                           <# if ( data.taxonomies.pa_city ) { #>
                           <p class="mb-0">
                            <span class="trip-category d-none">{{ data.taxonomies.product_cat }}</span>
                               <small class="text-muted">{{ data.taxonomies.pa_city }}<# if ( data['Region'] ) { #> , {{data['Region']}}<# } #></small>
                           </p>
                           <# } #>
                            <a href="{{ data.permalink }}" title="{{ data.post_title }}" class="ais-hits--title-link text-decoration-none" itemprop="url" onclick="selectItemAnalytics({{ data.post_id }})">
                            <h4 class="card-title fw-semibold trip-title">{{{ data._highlightResult.post_title.value }}}</h4>
                            </a>
                            <# if ( data.content ) { #>
                            <p class="short-description">{{data.content.substring(0,50)}}...</p>
                            <# } #>
                       </div>
                       <div class="trip-features d-flex justify-content-between">
                            <div class="card border-0">
                                <div class="card-body">
                                    <# if ( data['Trip Style'] ) { #>
                                    <ul class="list-inline mb-1">
                                        <li class="list-inline-item"><i class="bi bi-briefcase"></i></li>
                                        <li class="list-inline-item fs-sm">{{data['Trip Style']}}</li>
                                        <li class="list-inline-item"><i class="bi bi-info-circle pdp-trip-styles"></i></li>
                                    </ul>
                                    <# } #>

                                    <# if ( data['taxonomies.product_cat'] ) { #>
                                    <ul class="list-inline mb-1">
                                        <li class="list-inline-item"><i class="bi bi-calendar"></i></li>
                                        <li class="list-inline-item fs-sm">{{ data['Duration'].replace(/&amp;/g, '/') }}</li>
                                        <li class="list-inline-item"></li>
                                    </ul>
                                    <# } #>

                                    <# if ( data['Duration'] ) { #>
                                    <ul class="list-inline mb-1">
                                        <li class="list-inline-item"><i class="bi bi-calendar"></i></li>
                                        <li class="list-inline-item fs-sm">{{ data['Duration'].replace(/&amp;/g, '/') }}</li>
                                        <li class="list-inline-item"></li>
                                    </ul>
                                    <# } #>

                                    <# if ( data['Rider Level'] ) { #>
                                    <ul class="list-inline mb-1">
                                        <# if (data['taxonomies.activity'] === 'Biking') { #>
                                            <li class="list-inline-item"><i class="fa-solid fa-bicycle tt-b"></i></li>
                                        <# } #>
                                        <# if (data['taxonomies.activity'] != 'Biking') { #>
                                            <li class="list-inline-item hw"><i class="fa-solid fa-person-hiking"></i></li>
                                        <# } #>
                                        <li class="list-inline-item fs-sm dl-riderlevel">{{data['Rider Level'].replace(/&amp;/g, ' & ')}}</li>
                                        <li class="list-inline-item"><i class="bi bi-info-circle pdp-rider-level"></i></li>
                                    </ul>
                                    <# } #>

                                    <# if ( data['Hotel Level'] ) { #>
                                    <ul class="list-inline mb-1">
                                        <li class="list-inline-item"><i class="bi bi-house"></i></li>
                                        <li class="list-inline-item fs-sm">{{data['Hotel Level']}}</li>
                                        <li class="list-inline-item"><i class="bi bi-info-circle pdp-hotel-levels"></i></li>
                                    </ul>
                                    <# } #>
                                </div>
                                <# if ( data['review_score'] ) { #>
                                <div class="card-footer bg-transparent border-0 ms-md-4">
                                    <span class="fw-semibold"><i class="bi bi-star"></i> {{ (parseFloat(data['review_score']) % 1 === 0) ? parseFloat(data['review_score']).toFixed(0) : parseFloat(data['review_score']).toFixed(2) }} </span>
                                    <span class="text-muted review-text"> rating based on </span>
                                    <span class="fw-semibold reviews-count">{{data['total_review']}} </span>
                                    <span class="text-muted review-text"> reviews </span>
                                </div>
                                <# } #>
                            </div>

                            <div class="card text-end border-0">
                                <div class="card-body text-start">
                                    <# if ( data['Start Price'] ) { #>                                    
                                        <small class="text-muted">Starting from</small>
                                        <h5 class="trip-price" data-price="{{data['Start Price']}}">
                                            <span class="amount"><span class="woocommerce-Price-currencySymbol">$</span>{{data['Start Price']}}</span><span class="fw-normal fs-sm">pp</span>
                                        </h5>                                    
                                    <# } #>
                                </div>
                                <!-- <div class="card-footer bg-transparent border-success px-0 border-0 pt-5">
                                    <div class="form-check woocommerce-products-compare-compare-button text-start m-0">
                                        <input type="checkbox" class="woocommerce-products-compare-checkbox form-check-input" data-product-id="{{ data.post_id }}" id="woocommerce-products-compare-checkbox-{{ data.post_id }}"  {{checkedTick}}/>
                                        <label class="form-check-label fs-md" for="defaultCheck1">
                                            Compare Trip
                                        </label>
                                    </div>
                                </div> -->
                            </div>
                       </div>
                    </div>



                    <div class="col-lg-6 col-md-5 mobile-hideme">
                        <div class="card-body ms-md-4 pt-0">

                            <# if (data.taxonomies.pa_badge) { 
                                <!-- data.taxonomies.pa_badge = data.taxonomies.pa_badge.sort(); -->
                                #>
                                <# data.taxonomies.pa_badge.forEach(function (badge, index) { #>
                                    <span class="badge <# if (badge == 'Hiking + Walking') { #>hw<# } else { #>bg-dark<# } #>">{{ badge }}</span>
                                <# }) #>
                            <# } #>

                            <# if ( data.taxonomies.pa_city ) { #>
                            <p class="mb-0">
                            <span class="trip-category d-none">{{ data.taxonomies.product_cat }}</span>
                                <small class="text-muted">{{ data.taxonomies.pa_city }}<# if ( data['Region'] ) { #> , {{data['Region']}}<# } #></small>
                            </p>
                            <# } #>

                            <a href="{{ data.permalink }}" title="{{ data.post_title }}" class="ais-hits--title-link text-decoration-none" itemprop="url" onclick="selectItemAnalytics({{ data.post_id }})">
                            <h4 class="card-title fw-semibold trip-title">{{{ data._highlightResult.post_title.value }}}</h4>
                            </a>

                            <# if ( data.content ) { #>
                            <p><small>{{data.content.substring(0,150)}}...</small></p>
                            <# } #>

                            <# if ( data['Trip Style'] ) { #>
                            <ul class="list-inline mb-0">
                                <li class="list-inline-item"><i class="bi bi-briefcase"></i></li>
                                <li class="list-inline-item fs-sm">{{data['Trip Style']}}</li>
                                <li class="list-inline-item"><i class="bi bi-info-circle pdp-trip-styles"></i></li>
                            </ul>
                            <# } #>

                            <# if ( data['taxonomies.product_cat'] ) { #>
                            <ul class="list-inline mb-0">
                                <li class="list-inline-item"><i class="bi bi-calendar"></i></li>
                                <li class="list-inline-item fs-sm">{{ data['Duration'].replace(/&amp;/g, '/') }}</li>
                                <li class="list-inline-item"></li>
                            </ul>
                            <# } #>

                            <# if ( data['Duration'] ) { #>
                            <ul class="list-inline mb-0">
                                <li class="list-inline-item"><i class="bi bi-calendar"></i></li>
                                <li class="list-inline-item fs-sm">{{ data['Duration'].replace(/&amp;/g, '/') }}</li>
                                <li class="list-inline-item"></li>
                            </ul>
                            <# } #>

                            <# if ( data['Rider Level'] ) { #>
				<ul class="list-inline mb-0">
					<# if ( data.taxonomies.activity == 'Hiking' || data.taxonomies.activity == 'Walking' ){ #>
                                		<li class="list-inline-item"><i class="fa-solid fa-person-hiking"></i></li>
					<# } else  { #>
		                                <li class="list-inline-item"><i class="bi bi-bicycle"></i></li>
					<# } #>
                                	<li class="list-inline-item fs-sm dl-riderlevel">{{data['Rider Level'].replace(/&amp;/g, ' & ')}}</li>
                                	<li class="list-inline-item"><i class="bi bi-info-circle pdp-rider-level"></i></li>
                            	</ul>
                            <# } #>

                            <# if ( data['Hotel Level'] ) { #>
                            <ul class="list-inline mb-0">
                                <li class="list-inline-item"><i class="bi bi-house"></i></li>
                                <li class="list-inline-item fs-sm">{{data['Hotel Level']}}</li>
                                <li class="list-inline-item"><i class="bi bi-info-circle pdp-hotel-levels"></i></li>
                            </ul>
                            <# } #>

                        </div>

                        <# if ( data['review_score'] ) { #>
                        <div class="card-footer bg-transparent border-0 ms-md-4">
                            <span class="fw-semibold"><i class="bi bi-star"></i> {{ (parseFloat(data['review_score']) % 1 === 0) ? parseFloat(data['review_score']).toFixed(0) : parseFloat(data['review_score']).toFixed(2) }}  </span>
                            <span class="text-muted review-text"> rating based on </span>
                            <span class="fw-semibold">{{data['total_review']}} </span>
                            <span class="text-muted review-text"> reviews </span>
                        </div>
                        <# } #>

                    </div>

                    <div class="col-lg-2 col-md-3 position-relative mobile-hideme">
                        <# if ( data['Start Price'] ) { #>
                        <div class="card-body mt-5 pricing">
                            <small class="text-muted">Starting from</small>

                            <h5 class="trip-price" data-price="{{data['Start Price']}}">
                                <span class="amount"><span class="woocommerce-Price-currencySymbol">$</span>{{data['Start Price']}}</span><span class="fw-normal fs-sm">pp</span>
                            </h5>

                        </div>
                        <# } #>
                        <!-- <div class="card-footer compare-trip bg-transparent border-0 position-absolute bottom-0 px-3">
                            <div class="form-check woocommerce-products-compare-compare-button">
                                <input type="checkbox" class="woocommerce-products-compare-checkbox form-check-input" data-product-id="{{ data.post_id }}" id="woocommerce-products-compare-checkbox-{{ data.post_id }}"  {{checkedTick}}/>
                                <label class="form-check-label fs-sm" for="defaultCheck1">
                                    Compare Trip
                                </label>
                            </div>
                        </div> -->
                    </div>
                </div>

                <hr class="card-divider" />
                <div class="ais-clearfix"></div>

                <# } #>


                <# if ( data.post_type_label === 'Articles') { #>

                <# jQuery(".ais-InfiniteHits-item").addClass("ais-Hits-item-articles-pages");
                jQuery(".ais-InfiniteHits-list").addClass("ais-Hits-list-articles-pages") #>

                <div class="col articles-column">
                    <a href="{{ data.permalink }}" title="{{ data.post_title }}" class="text-decoration-none">
                        <div class="card border-0">
                            <# if ( data.images.thumbnail ) { #>
                            <img height="230" src="{{data.images.thumbnail.url}}" class="card-img-top" alt="article featured image">
                            <# } #>
                            <div class="card-body">
                                <h4 class="card-title fw-bold fs-6 lh-lg">{{data.post_title}}</h4>
                                <p class="card-text">{{data.content.substring(0,50)}}...</p>
                            </div>
                        </div>
                    </a>
                </div>

                <# } #>


                <# if ( data.post_type_label === 'Pages') { #>


                <# jQuery(".ais-InfiniteHits-item").addClass("ais-Hits-item-articles-pages");
                jQuery(".ais-InfiniteHits-list").addClass("ais-Hits-list-articles-pages") #>

                <div class="col articles-column">
                    <a href="{{ data.permalink }}" title="{{ data.post_title }}" class="text-decoration-none">
                        <div class="card border-0">
                            <# if ( data.images.thumbnail ) { #>
                            <img height="230" src="{{data.images.thumbnail.url}}" class="card-img-top" alt="article featured image">
                            <# } #>
                            <div class="card-body">
                                <h4 class="card-title fw-bold fs-6 lh-lg">{{data.post_title}}</h4>
                                <p class="card-text">{{data.content.substring(0,50)}}...</p>
                            </div>
                        </div>
                    </a>
                </div>


                <# } #>





        </article>

    </script>


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

                // Create the render function
                const renderList = ({ items, createURL }) => `
                  <ul>
                    ${items
                    .map(
                        item => `
                            <li class="destinations">
                            <span class="d-check d-check-i-${item.isRefined ? 'destinations-active' : 'destinations-inactive'}"><img src="/wp-content/themes/trek-travel-theme/assets/images/checkback.png" /></span>
                            <span class="d-check d-check-a-${item.isRefined ? 'destinations-active' : 'destinations-inactive'}"><img src="/wp-content/themes/trek-travel-theme/assets/images/checkactive.png" /></span>
                            <a class="refineLink" onClick="destinationClick(jQuery(this));"
                              href="${createURL(item.value)}"
                              data-value="${item.value}"
                              style="font-weight: ${item.isRefined ? 'bold' : ''}"
                            >

                              ${item.label}
                            </a>
                            ${item.data ? renderList({ items: item.data, createURL }) : ''}
                            </li>
                        `
                    )
                    .join('')}
                  </ul>
                `;

                const renderHierarchicalMenu = (renderOptions, isFirstRender) => {
                    const {
                        items,
                        isShowingMore,
                        refine,
                        toggleShowMore,
                        createURL,
                        widgetParams,
                    } = renderOptions;

                    if (isFirstRender) {
                        const list = document.createElement('div');
                        const button = document.createElement('button');

                        button.addEventListener('click', () => {
                            toggleShowMore();
                        });

                        widgetParams.container.appendChild(list);
                        // The code below is for Show More button, disable it for now.
                        // widgetParams.container.appendChild(button);
                    }

                    const children = renderList({ items, createURL });

                    widgetParams.container.querySelector('div').innerHTML = children;
                    // The code below is for Show More button, disable it for now.
                    // widgetParams.container.querySelector('button').textContent = isShowingMore
                    //     ? 'Show less'
                    //     : 'Show more';

                    [...widgetParams.container.querySelectorAll('a')].forEach(element => {
                        element.addEventListener('click', event => {
                            event.preventDefault();
                            refine(event.target.dataset.value);
                        });
                    });
                };


                // Create the custom widget
                const customHierarchicalMenu = instantsearch.connectors.connectHierarchicalMenu(
                    renderHierarchicalMenu
                );

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
                            { label: 'Price (asc)', value: 'instant_search_price_asc' },
                            { label: 'Price (desc)', value: 'instant_search_price_desc' },
                            { label: 'New', value: 'instant_search_new' },
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
                    // instantsearch.widgets.menu({
                    //     container: '#duration-facet',
                    //     attribute: 'Duration',
                    //     sortBy: ['name:desc'],
                    //     limit: 10,
                    //     templates: {
                    //         item: wp.template('instantsearch-menu-template')
                    //     }
                    // }),
                    instantsearch.widgets.menu({
                        container: '#trip-style-facet',
                        attribute: 'Trip Style',
                        sortBy: ['name:desc'],
                        limit: 10,
                        templates: {
                            item: wp.template('instantsearch-menu-template')
                        }
                    }),
                    instantsearch.widgets.menu({
                        container: '#rider-level-facet',
                        attribute: 'Rider Level',
                        sortBy: ['name:desc'],
                        limit: 10,
                        templates: {
                            item: wp.template('instantsearch-menu-template')
                        }
                    }),
                    instantsearch.widgets.menu({
                        container: '#hotel-level-facet',
                        attribute: 'Hotel Level',
                        sortBy: ['name:desc'],
                        limit: 10,
                        templates: {
                            item: wp.template('instantsearch-menu-template')
                        }
                    }),
                    // instantsearch.widgets.rangeInput({
                    //     container: '#range-input',
                    //     attribute: 'start_date_unix'
                    // }),
                    /* Categories refinement widget */
                    customHierarchicalMenu({
                        container: document.querySelector('#hierarchical-menu'),
                        separator: ' > ',
                        attributes: [
                            'taxonomies_hierarchical.product_cat.lvl0',
                            'taxonomies_hierarchical.product_cat.lvl1',
                            'taxonomies_hierarchical.product_cat.lvl2',
                        ],
                        // limit: 5,
                        // showMoreLimit: 10,
                        transformItems( items ) {
                            // Return only Destinations from all product_cat items.
                            items = items.filter( item => 'Destinations' === item.value );
                            // Remove 'Bike Tours' from the labels.
                            return items.map(item => ({
                                ...item,
                                data: item.data && item.data.map(subitem => ({
                                    ...subitem,
                                    label: subitem.label.replace('Bike Tours', '').trim(),
                                    data: subitem.data && subitem.data.map(subsubitem => ({
                                        ...subsubitem,
                                        label: subsubitem.label.replace('Bike Tours', '').trim()
                                    }))
                                })),
                            }));
                        },
                    }),
                    instantsearch.widgets.configure({
                        filters: "taxonomies.product_cat: ' <?php
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
                    });
                }
                /* Start */
                search.start();
                search.use(analyticsMiddleware);
                jQuery( '#algolia-search-box input' ).attr( 'type', 'search' ).trigger( 'select' );

                search.on("render", () => {
                    // Do something when the template has been rendered.
                    jQuery("#currency_switcher").trigger("change")
                    jQuery(".resultCount").text(jQuery(".ais-Menu-count").text())
                });
                
                function filterTime(startTime,endTime) {
                    
                    let urlDateFilter = 0 < startTime && 0 < endTime ? `AND start_date_unix:${startTime} TO ${endTime}` : '';

                    calendarStartDate = startTime;
                    calendarEndDate = endTime;
                    urlFlag = true;

                    search.addWidgets([

                        instantsearch.widgets.configure({
                        filters: `taxonomies.product_cat: ' <?php
							echo $plp_algolia_category->name;
							?> '${urlDateFilter}`,
                        hitsPerPage: 24,
                        analyticsTags: ['browse', '<?php
							echo $plp_algolia_category->name;
							?>'],
                        })
                    ]);

                    /**
                     * The old logic is below, keep it for reference for now.
                     */
                    // currentUrl = new URL(window.location.href)
                    // // console.log(currentUrl)
                    // if (!startTime || !endTime) {
                    //     currentUrl = removeParamFromUrl("start_time", currentUrl.toString())
                    //     currentUrl = removeParamFromUrl("end_time", currentUrl)
                    //     window.location.href = currentUrl
                    // }
                    // else {
                    //     // console.log('The query parameter is set');
                    //     currentUrl.searchParams.set('start_time', startTime);
                    //     currentUrl.searchParams.set('end_time', endTime);
                    //     window.location.href = currentUrl
                    // }
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
        

        function destinationClick(elm) {
            let data = elm.attr('data-value');
            jQuery('#ais-destination-selector .fake-selector').text( data.replaceAll('Bike Tours', '').trim() );
            jQuery('#ais-destination-selector .fake-selector').toggleClass("border-dark fw-semibold")
            // console.log(elm);
        }

        jQuery( document ).ready(function() {
            jQuery('body').removeClass('elementor-kit-14');
        });
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
                jQuery("#ais-date-selector .fake-selector").text(moment(convertedStartEpoch).format('MMM D') + " - " + moment(convertedEndEpoch).format('MMM D'));                
            <?php } ?>

            if( urlFlag ) {
                convertedStartEpoch = convertEpochToDate(calendarStartDate)
                convertedEndEpoch = convertEpochToDate(calendarEndDate)
                jQuery("#ais-date-selector .fake-selector").text(moment(convertedStartEpoch).format('MMM D') + " - " + moment(convertedEndEpoch).format('MMM D'));  
            }
        })

        jQuery('#filterModal').on('show.bs.modal', function (event) {
            <?php if ($urlFlag == 1) { ?>
                convertedStartEpoch = convertEpochToDate(<?php echo $urlStartTime; ?>)
                convertedEndEpoch = convertEpochToDate(<?php echo $urlEndTime; ?>)
                if (jQuery("#rangeDateVal").parent().length) {
                    jQuery("#rangeDateVal").html(moment(convertedStartEpoch).format('MMMM D') + " - " + moment(convertedEndEpoch).format('MMMM D'));
                } else {
                    jQuery(".range_inputs").prepend("<span id='rangeDateVal'>"+moment(convertedStartEpoch).format('MMMM D') + " - " + moment(convertedEndEpoch).format('MMMM D')+"</span>");
                }
            <?php } ?>
            if( urlFlag ) {
                convertedStartEpoch = convertEpochToDate(calendarStartDate)
                convertedEndEpoch = convertEpochToDate(calendarEndDate)
                if (jQuery("#rangeDateVal").parent().length) {
                    jQuery("#rangeDateVal").html(moment(convertedStartEpoch).format('MMMM D') + " - " + moment(convertedEndEpoch).format('MMMM D'));
                } else {
                    jQuery(".range_inputs").prepend("<span id='rangeDateVal'>"+moment(convertedStartEpoch).format('MMMM D') + " - " + moment(convertedEndEpoch).format('MMMM D')+"</span>");
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
