<?php
/**
 * Algolia Template for the single article box on search and archive product pages.
 */

?>

<script type="text/html" id="tmpl-instantsearch-hit">
	<#
		const TT_ACTIVITY_DASHBOARD_NAME_BIKING = '<?php echo esc_attr( TT_ACTIVITY_DASHBOARD_NAME_BIKING ); ?>';
		const TT_ACTIVITY_DASHBOARD_NAME_HW     = '<?php echo esc_attr( TT_ACTIVITY_DASHBOARD_NAME_HW ); ?>';
	#>

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
			} #>
		<# jQuery(".ais-InfiniteHits-item").removeClass("ais-Hits-item-articles-pages");
		jQuery(".ais-InfiniteHits-list").removeClass(" ais-Hits-list-articles-pages") #>

		<div class="card mb-3 border-0 trip-card-body">
			<div class="c-card row g-0 mx-0">

				<div class="col-lg-4 gallery-carousel">
					<div id="carouselExampleIndicators{{ data.post_id }}" class="carousel lazy-load slide h-100">
						<div class="carousel-indicators">
							<# if ( data.gallery_images ) { #>
								<# data.gallery_images.forEach(function (item, index) { #>
									<button type="button" data-bs-target="#carouselExampleIndicators{{ data.post_id }}" data-bs-slide-to="{{index}}" class="<# if ( index == 0 ) { #> active <# } #>"></button>
								<# }) #>
							<# } #>
						</div>

						<div class="carousel-inner">
							<# if (data.gallery_images) { #>
								<# data.gallery_images.forEach(function (item, index) { #>
									<# let add_img_src = ( 0 == index ); #>
									<div class="carousel-item h-100 <# if (index == 0) { #> active <# } #>">
										<a href="{{ data.permalink }}" title="{{ data.post_title }}" class="ais-hits--thumbnail-link"
										onclick="selectItemAnalytics({{ data.post_id }})">
											<img <# if ( add_img_src ) { #> src="{{ item }}" <# } else { #> src="#" data-src="{{ item }}" <# } #> alt="{{ data.post_title }}" title="{{ data.post_title }}" class="d-block w-100 h-100"
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

				<div class="col-lg-6 d-block d-lg-none">
					<div class="product-head-info my-3">
						<div class="badge-container">
							<# if (data.taxonomies.product_tag) {
								data.taxonomies.product_tag.sort((a, b) =>  b.localeCompare(a, 'en', { sensitivity: 'base' }));
								#>
								<# data.taxonomies.product_tag.forEach(function (badge, index) { #>
									<span class="badge <# if (badge == 'Hiking + Walking') { #>hw<# } else { #>bg-dark<# } #>">{{ badge }}</span>   
								<# }) #>
							<# } #>
						</div>
						<# if ( data.taxonomies.city ) { #>
						<p class="mb-0">
						<span class="trip-category d-none">{{ data.taxonomies.product_cat }}</span>
							<small class="text-muted">{{ data.taxonomies.city }}<# if ( data['Region'] ) { #> , {{data['Region']}}<# } #></small>
						</p>
						<# } #>
						<a href="{{ data.permalink }}" title="{{ data.post_title }}" class="ais-hits--title-link text-decoration-none" itemprop="url" onclick="selectItemAnalytics({{ data.post_id }})">
						<h4 class="card-title fw-semibold trip-title">{{{ data._highlightResult.post_title.value }}}</h4>
						</a>
						<# if ( data['Product Subtitle'] ) { #>
							<p class="short-description">
								<small>
									<# if ( data['Product Subtitle'].length > 175 ) { #>
										{{data['Product Subtitle'].substring(0,175)}}...
									<# } else { #>
										{{data['Product Subtitle']}}
									<# } #>
								</small>
							</p>
						<# } else if ( data.content ) { #>
							<p class="short-description">{{data.content.substring(0,175)}}...</p>
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

								<# if ( data['Activity Level'] ) { #>
								<ul class="list-inline mb-1">
									<# if ( data.taxonomies.activity == TT_ACTIVITY_DASHBOARD_NAME_BIKING ) { #>
										<li class="list-inline-item"><i class="fa-solid fa-person-biking"></i></li>
									<# } #>
									<# if (data.taxonomies.activity != TT_ACTIVITY_DASHBOARD_NAME_BIKING ) { #>
										<li class="list-inline-item hw"><i class="fa-solid fa-person-hiking"></i></li>
									<# } #>
									<li class="list-inline-item fs-sm dl-riderlevel">{{data['Activity Level'].replace(/&amp;/g, ' & ')}}</li>
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
							<div class="card-footer bg-transparent border-0">
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
						</div>
					</div>
				</div>

				<div class="col-lg-6 d-none d-lg-block card-info">
					<div class="card-body ms-md-4 pt-0">
						<div class="badge-container">
							<# if (data.taxonomies.product_tag) { 
								data.taxonomies.product_tag.sort((a, b) =>  b.localeCompare(a, 'en', { sensitivity: 'base' }));
								#>
								<# data.taxonomies.product_tag.forEach(function (badge, index) { #>
									<span class="badge <# if (badge == 'Hiking + Walking') { #>hw<# } else { #>bg-dark<# } #>">{{ badge }}</span>
								<# }) #>
							<# } #>
						</div>

						<# if ( data.taxonomies.city ) { #>
						<p class="mb-0">
						<span class="trip-category d-none">{{ data.taxonomies.product_cat }}</span>
							<small class="text-muted">{{ data.taxonomies.city }}<# if ( data['Region'] ) { #> , {{data['Region']}}<# } #></small>
						</p>
						<# } #>

						<div>
							<a href="{{ data.permalink }}" title="{{ data.post_title }}" class="ais-hits--title-link text-decoration-none" itemprop="url" onclick="selectItemAnalytics({{ data.post_id }})">
							<h4 class="card-title fw-semibold trip-title">{{{ data._highlightResult.post_title.value }}}</h4>
							</a>
							<# if ( data['Product Subtitle'] ) { #>
								<p class="trip-desc">
									<small>
										<# if ( data['Product Subtitle'].length > 200 ) { #>
											{{data['Product Subtitle'].substring(0,200)}}...
										<# } else { #>
											{{data['Product Subtitle']}}
										<# } #>
									</small>
								</p>
							<# } else if ( data.content ) { #>
								<p class="trip-desc"><small>{{data.content.substring(0,200)}}...</small></p>
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

							<# if ( data['Activity Level'] ) { #>
								<ul class="list-inline mb-0">
									<# if (data.taxonomies.activity != TT_ACTIVITY_DASHBOARD_NAME_BIKING ) { #>
										<li class="list-inline-item"><i class="fa-solid fa-person-hiking"></i></li>
									<# } #>
									<# if (data.taxonomies.activity == TT_ACTIVITY_DASHBOARD_NAME_BIKING ) { #>
										<li class="list-inline-item"><i class="fa-solid fa-person-biking"></i></li>
									<# } #>
									<li class="list-inline-item fs-sm dl-riderlevel">{{data['Activity Level'].replace(/&amp;/g, ' & ')}}</li>
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

					</div>

				</div>

				<div class="col-lg-2 position-relative d-none d-lg-block card-price">
					<# if ( data['Start Price'] ) { #>
					<div class="card-body mt-5 pricing">
						<small class="text-muted">Starting from</small>

						<h5 class="trip-price" data-price="{{data['Start Price']}}">
							<span class="amount"><span class="woocommerce-Price-currencySymbol">$</span>{{data['Start Price']}}</span><span class="fw-normal fs-sm">pp</span>
						</h5>
					</div>
					<# } #>
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
		</div>
	</article>
</script>
