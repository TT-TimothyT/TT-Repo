<?php
/**
 * Include header for Admin pages
 *
 * @since 8.23.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="ac-header">
   <div class="container-fluid">
      <div class="row">
         <div class="col-md-12">
            <div class="header-wrap">
               <div class="branding">
					<a href=""><img src="<?php echo WCAP_PLUGIN_URL . '/assets/images/ac-logo.svg'; ?>" alt="Logo" /></a>
				</div>
               <nav class="navbar navbar-expand-lg navbar-light ac-navigation">
                  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                     <span class="navbar-toggler-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                           <path fill="none" d="M0 0h24v24H0z"/>
                           <path d="M3 4h18v2H3V4zm0 7h12v2H3v-2zm0 7h18v2H3v-2z" fill="rgba(255,255,255,1)"/>
                        </svg>
                     </span>
                  </button>
                  <div class="collapse navbar-collapse" id="navbarNav">
                     <ul class="navbar-nav menu nav-menu">
								<li class="nav-item <?php echo $active_dashboard_adv; ?>"><a class="nav-link" href="admin.php?page=woocommerce_ac_page" ><?php _e( 'Dashboard', 'woocommerce-ac' );?></a></li>
								<li class="nav-item <?php echo $active_listcart; ?>"><a class="nav-link" href="admin.php?page=woocommerce_ac_page&action=listcart" ><?php _e( 'Abandoned Orders', 'woocommerce-ac' );?></a></li>
								<li class="nav-item <?php echo $active_cart_recovery; ?>"><a class="nav-link" href="admin.php?page=woocommerce_ac_page&action=cart_recovery" ><?php _e( 'Templates', 'woocommerce-ac' );?></a></li>
								<li class="nav-item <?php echo $active_settings; ?>"><a class="nav-link" href="admin.php?page=woocommerce_ac_page&action=emailsettings"><?php _e( 'Settings', 'woocommerce-ac' );?></a></li>
								<li class="nav-item <?php if( isset( $active_emailstats ) ) echo $active_emailstats; ?>"><a class="nav-link" href="admin.php?page=woocommerce_ac_page&action=emailstats" ><?php _e( 'Reminders Sent', 'woocommerce-ac' );?></a></li>
								<li class="nav-item <?php if( isset( $active_report ) ) echo $active_report; ?>"><a class="nav-link" href="admin.php?page=woocommerce_ac_page&action=report" ><?php _e( 'Product Report', 'woocommerce-ac' );?></a></li>
                                <?php
									do_action ('wcap_add_settings_tab');
									do_action ( 'wcap_add_tabs' );
									if ( has_action( 'wcap_add_tabs' ) ) {
										if ( isset( $_GET['action'] ) && 'wcap_crm' == $_GET['action'] ) {
											settings_errors();
										}
									}
								?>
                              </ul>
                           </div>
                        </nav>
                        <div class="ac-version">                          
                           <p class="ver-fig">
                              Abandoned Cart Pro <br/><?php echo 'Version ' . WCAP_PLUGIN_VERSION; ?></p>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <!-- Header End -->