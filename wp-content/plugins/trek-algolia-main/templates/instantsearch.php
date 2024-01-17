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
$newval = get_search_query();
@session_start();
$tt_compare_products = ( isset($_SESSION['tt_compare_products']) ? $_SESSION['tt_compare_products'] : array()  );
$compare_div_style = ( $tt_compare_products && count($tt_compare_products) > 0 ? 'display:flex;' : 'display:none;' );

    $params = $_GET;
    $svar = $_GET['s'];
    if (!$svar){
        header( "Location: /?s=algolia&wp_searchable_posts[query]=".$newval."&wp_searchable_posts[menu][post_type_label]=Trips" );
        exit();
    }
    if ($svar !='algolia') {
	    header( "Location: /?s=algolia&wp_searchable_posts[query]=".$newval."&wp_searchable_posts[menu][post_type_label]=Trips" );
	    exit();
    }

get_header();

?>

                <div class="container search-results-container">
                    <div id="ais-wrapper">
                        <main id="ais-main">
                            <div class="search-summary my-4 p-4">
                                <h2 class="fw-semibold"><span id="searchCount"></span> Results Found</h2>
                                <p class="fw-normal fs-lg lh-lg">Showing Results for “<span id="searchTerm"><?php echo $_GET['wp_searchable_posts']['query'] ?></span>”</p>
                            </div>
                            
                            <div id="search-nav-tabs" class="my-4">
                                <nav>
                                    <div class="nav nav-tabs" id="nav-tab" role="tablist">

                                    </div>
                                </nav>
                            </div>
                            <!-- Button trigger modal -->

                            <hr class="card-divider" />

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
                                <div id="ais-more-selector">
                                    <button type="button" class="fake-selector" id="filter-modal" data-bs-toggle="modal" data-bs-target="#filterModal">
                                        More
                                    </button>
                                </div>

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
                                                <div class="row" id="calendarTrigger">
                                                    <span id="search-daterange"></span>
                                                </div>
                                                <div id="range-input" style="position: absolute; z-index: -1; left: -10000px; overflow: hidden;"></div>
                                                <div id="start-date" style="position: absolute; z-index: -1; left: -10000px; overflow: hidden;"></div>
                                                <hr>
                                                <h5>Destinations</h5>
                                                <div id="hierarchical-menu"></div>
                                                <hr>
                                                <h5>Duration</h5>
                                                <div id="duration-facet"></div>
                                                <hr>
                                                <h5>Trip Style</h5>
                                                <div id="trip-style-facet"></div>
                                                <hr>
                                                <h5>Rider Level</h5>
                                                <div id="rider-level-facet"></div>
                                                <hr>
                                                <h5>Hotel Level</h5>
                                                <div id="hotel-level-facet"></div>
                                                <hr>
                                            </div>

                                            <div class="modal-footer">
                                                <div class="container">
                                                    <div class="row align-items-center">
                                                        <div class="col-3">
                                                            <span class="modal-a" id="clear-refinements"></span>
                                                        </div>
                                                        <div class="col text-end">
                                                            <button type="button" class="btn btn-secondary d-none" data-bs-dismiss="modal">Close</button>
                                                            <span class="filter-results-number" id="algolia-stats"></span>
                                                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Apply filters</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </main>
                    </div>
                </div>

                    <script type="text/html" id="tmpl-instantsearch-menu-template">
                            <div class="menu-facet-container">
                                <a class="ais-anchor" href="{{data.url}}">
                                    <span class="f-check"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/checkback.png" /></span>
                                    <span class="f-check-active"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/checkactive.png" /></span>
                                    <span class="">{{data.label}}</span>
                                    <span class="">{{data.count.toLocaleString()}}</span>
                                </a>
                            </div>
                    </script>

                    <script type="text/html" id="tmpl-instantsearch-date-template">
                        <div class="menu-facet-container">
                            <a class="ais-anchor" href="{{data.url}}">
                                <span class="f-check"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/checkback.png" /></span>
                                <span class="f-check-active"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/checkactive.png" /></span>
                                <span class="algolia-start-dates">{{data.label}}</span>
                                <span class="">{{data.count.toLocaleString()}}</span>
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

                        <# if ( data.post_type_label === 'Trips') { #>

                            <# jQuery(".ais-InfiniteHits-item").removeClass("ais-Hits-item-articles-pages"); 
                            jQuery(".ais-InfiniteHits-list").removeClass(" ais-Hits-list-articles-pages") #>

                        <div class="card mb-3 border-0 trip-card-body">
                            <div class="row g-0 mx-0">
                                <div class="col-md-4 col-sm-2">

                                    <div id="carouselExampleIndicators{{ data.post_id }}" class="carousel slide h-100" data-bs-ride="carousel">

                                    <div class="carousel-indicators">
                                        <# if ( data.gallery_images ) { #>
                                            <# data.gallery_images.forEach(function (item, index) { #>
                                                <button type="button" data-bs-target="#carouselExampleIndicators{{ data.post_id }}" data-bs-slide-to="{{index}}" class="<# if ( index == 0 ) { #> active <# } #>"></button>
                                            <# }) #>
                                        <# } #>
                                    </div>

                                        <div class="carousel-inner h-100">
                                            <# if ( data.gallery_images ) { #>
                                            <# data.gallery_images.forEach(function (item, index) { 
                                                let imageUrl = item.replace('-300x300', '-886x664');
                                                let imageUrlOriginalSize = item.replace('-300x300', '');
                                                #>
                                            <div class="carousel-item h-100 <# if ( index == 0 ) { #> active <# } #>">
                                                <a href="{{ data.permalink }}" title="{{ data.post_title }}" class="ais-hits--thumbnail-link" onclick="selectItemAnalytics({{ data.post_id }})">
                                                    <img src="{{ imageUrl }}" alt="{{ data.post_title }}" title="{{ data.post_title }}" class=" d-block w-100"
                                                        onerror="this.onerror=null;this.src='{{ imageUrlOriginalSize }}';" />
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

                                        <form class="cart" action="" method="post" enctype="multipart/form-data">
                                        <?php if( is_user_logged_in() ) { ?>
                                            <input type="hidden" name="wlid" id="wlid"/>
                                            <input type="hidden" name="add-to-wishlist-type" value="{{ data.taxonomies.product_type }}"/>
                                            <input type="hidden" name="wl_from_single_product" value="{{data.post_type == 'product' ? '1' : '0'}}"/>
                                            <input type="hidden" name="quantity[{{ data.post_id }}]" value="1"/>
                                            <a rel="nofollow" href="" data-productid="{{ data.post_id }}" data-listid="<?php echo $add_to_wishlist_args['single_id']; ?>" class="wl-add-to btn add-wishlist h-100 ">
                                                <!-- <i class="bi bi-heart"></i><i class="bi bi-heart-fill"></i> -->
                                            </a>
                                            <?php } else { ?>
                                                <a class="btn add-wishlist h-100" href="<?php echo site_url('login'); ?>">
                                                    <!-- <i class="bi bi-heart"></i><i class="bi bi-heart-fill"></i> -->
                                                </a>
                                            <?php } ?>
                                        </form>	

                                    </div>

                                </div>


                                <div class="col-md-6 desktop-hideme">
                                    <div class="product-head-info my-3">
                                            <# if ( data['Badge'] ) { #>
                                                <span class="badge bg-dark mb-2">{{ data['Badge'] }}</span>
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
                                                        <li class="list-inline-item"><i class="bi bi-bicycle"></i></li>
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
                                                <div class="card-footer bg-transparent border-success px-0 border-0 pt-5">
                                                    <div class="form-check woocommerce-products-compare-compare-button text-start m-0">
                                                        <input type="checkbox" class="woocommerce-products-compare-checkbox form-check-input" data-product-id="{{ data.post_id }}" id="woocommerce-products-compare-checkbox-{{ data.post_id }}"/>
                                                        <label class="form-check-label fs-md" for="defaultCheck1">
                                                            Compare Trip
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    
                                <div class="col-md-6 col-sm-3 mobile-hideme">
                                    <div class="card-body ms-md-4 pt-0">
                                        <# if ( data['Badge'] ) { #>
                                        <span class="badge bg-dark">{{ data['Badge'] }}</span>
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
                                            <li class="list-inline-item"><i class="bi bi-info-circle"></i></li>
                                        </ul>
                                        <# } #>

                                        <# if ( data['Duration'] ) { #>
                                        <ul class="list-inline mb-0">
                                            <li class="list-inline-item"><i class="bi bi-calendar"></i></li>
                                            <li class="list-inline-item fs-sm">{{ data['Duration'].replace(/&amp;/g, '/') }}</li>
                                            <li class="list-inline-item"><i class="bi bi-info-circle"></i></li>
                                        </ul>
                                        <# } #>

                                        <# if ( data['Rider Level'] ) { #>
                                        <ul class="list-inline mb-0">
                                            <li class="list-inline-item"><i class="bi bi-bicycle"></i></li>
                                            <li class="list-inline-item fs-sm dl-riderlevel">{{data['Rider Level']}}</li>
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
                                        <span class="fw-semibold"><i class="bi bi-star"></i> {{ (parseFloat(data['review_score']) % 1 === 0) ? parseFloat(data['review_score']).toFixed(0) : parseFloat(data['review_score']).toFixed(2) }} </span>
                                        <span class="text-muted review-text"> rating based on </span>
                                        <span class="fw-semibold">{{data['total_review']}} </span>
                                        <span class="text-muted review-text"> reviews </span>
                                    </div>
                                    <# } #>



                                </div>

                                <div class="col-md-2 col-sm-1 position-relative mobile-hideme">
                                    <# if ( data['Start Price'] ) { #>
                                    <div class="card-body mt-5 pricing">
                                        <small class="text-muted">Starting from</small>

                                        <h5 class="trip-price" data-price="{{data['Start Price']}}">
                                            <span class="amount"><span class="woocommerce-Price-currencySymbol">$</span>{{data['Start Price']}}</span><span class="fw-normal fs-sm">pp</span>
                                        </h5>

                                    </div>
                                    <# } #>
                                    <div class="card-footer compare-trip bg-transparent border-0 position-absolute bottom-0 px-3">
                                        <div class="form-check woocommerce-products-compare-compare-button">
                                            <input type="checkbox" class="woocommerce-products-compare-checkbox form-check-input" data-product-id="{{ data.post_id }}" id="woocommerce-products-compare-checkbox-{{ data.post_id }}">                                            
                                            <label class="form-check-label fs-sm" for="defaultCheck1">
                                                Compare Trip
                                            </label>
                                        </div>
                                    </div>
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
                    // routing: {
                    //     router: instantsearch.routers.history(),
                    //     stateMapping: instantsearch.stateMappings.simple(),
                    // },
                });

                // Create the render function
                const renderList = ({ items, createURL }) => `
                  <ul>
                    ${items
                                    .map(
                                        item => `
                            <li class="destinations">
                            <span class="d-check-i-${item.isRefined ? 'destinations-active' : 'destinations-inactive'}"><img src="/wp-content/themes/trek-travel-theme/assets/images/checkback.png" /></span>
                            <span class="d-check-a-${item.isRefined ? 'destinations-active' : 'destinations-inactive'}"><img src="/wp-content/themes/trek-travel-theme/assets/images/checkactive.png" /></span>
                            <a class="refineLink" onClick="destinationClick(jQuery(this));"
                              href="${createURL(item.value)}"
                              data-value="${item.value}"
                              style="font-weight: ${item.isRefined ? 'bold' : ''}"
                            >

                              ${item.label} (${item.count})
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
                        widgetParams.container.appendChild(button);
                    }

                    const children = renderList({ items, createURL });

                    widgetParams.container.querySelector('div').innerHTML = children;
                    widgetParams.container.querySelector('button').textContent = isShowingMore
                        ? 'Show less'
                        : 'Show more';

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
                            jQuery( ".trip-card-body" ).each(function( index ) {                    
                                let impression = {
                                    'name': jQuery( this ).find(".trip-title" ).first().text() ,
                                    'id': jQuery( this ).find(".woocommerce-products-compare-checkbox" ).data("product-id").toString(),
                                    'price': jQuery( this ).find(".trip-price").data("price"),
                                    'brand': '',
                                    'category': jQuery( this ).find(".trip-category" ).text(),
                                    'list': 'search',
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
                };

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
                        clickAnalytics: true,
                        // userToken: 'test-skyler',
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
                            empty: '<div class="container no-results"><h2 class="fw-semibold">Sorry, we didn\'t find anything. </h2><p class="fw-normal fs-lg lh-lg">Do you want to try <a href="#">[Suggested Query]</a>?</p></div><hr><div class="container discover-more"><div class="row m-0"><h3 class="fw-semibold">Discover More Ways to Travel</h3><div class="col-6 col-md-4"><div class="card border-0"><img src="/wp-content/uploads/2022/08/Rectangle-2725.png" class="card-img-top rounded-1" alt="discover more image 1"><div class="card-body ps-0"><a href="#"><p class="card-text text-start fw-semibold">Classic-Guided Tours</p></a></div></div></div><div class="col-6 col-md-4"><div class="card border-0"><img src="/wp-content/uploads/2022/08/Rectangle-2725.png" class="card-img-top rounded-1" alt="discover more image 2"><div class="card-body ps-0"><a href="#"><p class="card-text text-start fw-semibold">Self-Guided Tours</p></a></div></div></div><div class="col-6 col-md-4"><div class="card border-0"><img src="/wp-content/uploads/2022/08/Rectangle-2725.png" class="card-img-top rounded-1" alt="discover more image 3"><div class="card-body ps-0"><a href="#"><p class="card-text text-start fw-semibold">E-Bike Tours</p></a></div></div></div></div></div>',
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
                    instantsearch.widgets.menu({
                        container: '#duration-facet',
                        attribute: 'Duration',
                        sortBy: ['name:desc'],
                        limit: 10,
                        templates: {
                            item: wp.template('instantsearch-menu-template')
                        }
                    }),
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
                        container: '#start-date',
                        attribute: 'Start Date',
                        sortBy: ['name:desc'],
                        limit: 20,
                        templates: {
                            item: wp.template('instantsearch-date-template')
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
                    instantsearch.widgets.rangeInput({
                        container: '#range-input',
                        attribute: 'start_date_unix'
                    }),
                    /* Categories refinement widget */
                   customHierarchicalMenu({
                        container: document.querySelector('#hierarchical-menu'),
                        separator: ' > ',
                        attributes: [
                            'taxonomies_hierarchical.product_cat.lvl0',
                            'taxonomies_hierarchical.product_cat.lvl1',
                            'taxonomies_hierarchical.product_cat.lvl2',
                            'taxonomies_hierarchical.product_cat.lvl3'
                        ],
                        limit: 5,
                        showMoreLimit: 10,
                    }),

                    instantsearch.widgets.configure({
                        hitsPerPage: 24
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
                    jQuery('#search-daterange').daterangepicker(calendarOptions);
                    jQuery('#search-daterange').on('apply.daterangepicker', function (ev, picker) {
                        let startdateTest = picker.startDate.format('DDMMYY');
                        let enddateTest = picker.endDate.format('DDMMYY');
                        console.log('start date is ' + startdateTest + ' end date is ' + enddateTest);

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
                        let minVal = jQuery(".ais-RangeInput-input--min").attr("min");
                        let maxVal = jQuery(".ais-RangeInput-input--max").attr("max");
                        filterTime(minVal,maxVal);
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
                    postRenderOps()
                });
            }
        });

        function filterTime(startTime,endTime) {
            console.log(startTime);
            console.log(endTime);
            let startDateArray = [];

            jQuery( ".algolia-start-dates" ).each(function( index ) {
                let dateText = jQuery( this ).text();
                let splitText = dateText.split('/');
                console.log('date text is');
                console.log(splitText);
                let newDate = splitText[1] + '/' + splitText[0] + '/' + splitText[2];

                startDateArray.push(newDate);
            });
            startDateArray.sort();
            console.log(startDateArray);
            let lastElement = startDateArray.slice(-1);



            let minVal = jQuery(".ais-RangeInput-input--min").attr("min");
            let maxVal = jQuery(".ais-RangeInput-input--max").attr("max");

            const submit = document.querySelector('.ais-RangeInput-submit');


            if (startTime >= minVal && startTime <= maxVal) {
                jQuery(".ais-RangeInput-input--min").attr("placeholder","");
                jQuery(".ais-RangeInput-input--min").val("");
                jQuery(".ais-RangeInput-input--min").focus();
                document.execCommand('insertText', false, startTime);
            }
            else {
                alert('Please Select a Date Between ' + startDateArray[0] + ' and ' + lastElement);
                jQuery(".ais-RangeInput-input--min").attr("placeholder","");
                jQuery(".ais-RangeInput-input--min").val("");
                jQuery(".ais-RangeInput-input--min").focus();
                document.execCommand('insertText', false, minVal);
            }

            if (endTime <= maxVal && endTime >= minVal) {
                jQuery(".ais-RangeInput-input--max").attr("placeholder","");
                jQuery(".ais-RangeInput-input--max").val("");
                jQuery(".ais-RangeInput-input--max").focus();
                document.execCommand('insertText', false, endTime);
            }
            else {
                jQuery(".ais-RangeInput-input--max").attr("placeholder","");
                jQuery(".ais-RangeInput-input--max").val("");
                jQuery(".ais-RangeInput-input--max").focus();
                document.execCommand('insertText', false, maxVal);
            }

            submit.addEventListener('click', function(e) {
                console.log('Simulated click');
            });

            const simulatedDivClick = document.createEvent('MouseEvents');

            simulatedDivClick.initEvent(
                'click', /* Event type */
                true, /* bubbles */
                true, /* cancelable */
                document.defaultView, /* view */
                0, /* detail */
                0, /* screenx */
                0, /* screeny */
                0, /* clientx */
                0, /* clienty */
                false, /* ctrlKey */
                false, /* altKey */
                false, /* shiftKey */
                0, /* metaKey */
                null, /* button */
                null /* relatedTarget */
            );

            // Automatically click after .5 seconds
            setTimeout(function() {
                submit.dispatchEvent(simulatedDivClick);
            }, 500);

        }

        function destinationClick(elm) {
            let data = elm.attr('data-value');
            jQuery('#ais-destination-selector .fake-selector').text(data);
            jQuery('#ais-destination-selector .fake-selector').toggleClass("border-dark fw-semibold")
            console.log(elm);
        }

        jQuery( document ).ready(function() {
            setTimeout(function() {
                
                window.dataLayer = window.dataLayer || [];
                var impressions = [];
                
                jQuery( ".trip-card-body" ).each(function( index ) {                    
                    let impression = {
                        'name': jQuery( this ).find(".trip-title" ).first().text() ,
                        'id': jQuery( this ).find(".woocommerce-products-compare-checkbox" ).data("product-id").toString(),
                        'price': jQuery( this ).find(".trip-price").data("price"),
                        'brand': '',
                        'category': jQuery( this ).find(".trip-category" ).text(),
                        'list': 'search',
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
            }, 500);
            jQuery('body').removeClass('elementor-kit-14');
        });
        jQuery("body").on("click", "#clear-refinements", function() {
            jQuery('.fs-destination').text('Destination');
            jQuery('#ais-date-selector .fake-selector').text('Date')
            jQuery('#ais-destination-selector .fake-selector').toggleClass("border-dark fw-semibold")
            jQuery('#ais-date-selector .fake-selector').toggleClass("border-dark fw-semibold")

        });

        function postRenderOps() {
            let resultText = jQuery('.filter-results-number span.ais-Stats-text').text();
            let resultCount = resultText ? parseInt(resultText.substring(0, resultText.indexOf(' '))) : 0;
            let searchParams = new URLSearchParams(window.location.search);
            let search_term = searchParams.get('wp_searchable_posts[query]');
            window.dataLayer = window.dataLayer || [];
            dataLayer.push({
                'event': 'view_search_results', 
                'search_term': search_term, //phrase entered by the user
                'search_results': resultCount //number of results returned to user - 0 if unsuccessfull
            });
            if (!resultCount) {
                jQuery(".search-summary, #search-nav-tabs, hr.card-divider, .algolia-search-box-wrapper").addClass("d-none")
            }
            else{
                jQuery(".search-summary, #search-nav-tabs, hr.card-divider, .algolia-search-box-wrapper").removeClass("d-none")
                jQuery("#searchCount").text(resultCount)
            }
        }

        function selectItemAnalytics(id) {
            window.dataLayer = window.dataLayer || [];
            jQuery( ".trip-card-body" ).each(function( index ) {
                var cardId = jQuery( this ).find(".woocommerce-products-compare-checkbox" ).data("product-id")
                var price = jQuery( this ).find(".trip-price").data("price")
                if (parseInt(id) == parseInt(cardId)) {
                    dataLayer.push({
                        'event': 'select_item',
                        'ecommerce': {
                        'click': {
                            'actionField': { 'list': 'search' },
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
