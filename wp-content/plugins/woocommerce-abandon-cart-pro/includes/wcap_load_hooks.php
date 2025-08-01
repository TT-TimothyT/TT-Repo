<?php
/**
 * Abandoned Cart Pro for WooCommerce
 *
 * This file will load all hooks, filters which will be used all over the plugin.
 * 
 * @author   Tyche Softwares
 * @package  Abandoned-Cart-Pro-for-WooCommerce/Classes
 * @category Classes
 * @since    5.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if ( !class_exists('Wcap_Load_Hooks' ) ) {
    /** 
     * It will add the hooks, filters, menu and the variables and all the necessary actions for the plugins which will be used all over the plugin.
     * 
     * @since 2.5
     */
    class Wcap_Load_Hooks {
        /** 
         * This function is used for adding the hooks, filters, menu and the variables and all the necessary actions for the plugins.
         * 
         * @since 2.5
         */
        public static function wcap_load_hooks_and_filters() {
            
            //add_action( 'init',                         array( 'Wcap_Load_Hooks',    'wcap_load_component_file' ) );
            
            if( is_plugin_active('aelia-currency-switcher-addon-for-abandoned-cart/aelia-currency-switcher-addon-for-abandoned-cart.php') ){
                add_action( 'admin_init', array( 'Wcap_Load_Hooks', 'wcap_deactivate_aelia_addon' ) );
            }

            if ( class_exists( 'Wcap_Update_Check' ) ) {
                add_action( 'admin_init', array( 'Wcap_Load_Hooks', 'wcap_check_update_code_run' ) );
                add_action( 'wcap_update_db', array( 'Wcap_Update_Check', 'wcap_update_db_check' ) );
            }
            
            $wcap_plugin_dir  = dirname( dirname( plugin_basename( __FILE__ ) ) );
            $wcap_plugin_dir .= '/woocommerce-ac.php';
            // Add settings link on plugins page
            add_filter( 'plugin_action_links_' . $wcap_plugin_dir,     array( 'Wcap_Common',         'wcap_plugin_action_links' ) );
            //Add plugin doc and forum link in description
            add_filter( 'plugin_row_meta',                             array( 'Wcap_Common',         'wcap_plugin_row_meta' ), 10, 2 );
            
            if ( class_exists( 'Wcap_Cart_Updated' ) ) {
                //add_action( 'woocommerce_cart_updated',                array( 'Wcap_Cart_Updated',   'wcap_store_cart_timestamp_new' ), 100 );
                if ( ! wcap_get_popup_active_status( 'atc' ) ) {
                    add_action( 'woocommerce_cart_updated', array( 'Wcap_Cart_Updated', 'wcap_update_session_id' ), 100 );
                }

                add_action( 'woocommerce_add_to_cart',                     array( 'Wcap_Cart_Updated',   'wcap_store_cart_timestamp' ), 999 );
                add_action( 'woocommerce_cart_item_removed',               array( 'Wcap_Cart_Updated',   'wcap_store_cart_timestamp' ), 100 );
                add_action( 'woocommerce_cart_item_restored',              array( 'Wcap_Cart_Updated',   'wcap_store_cart_timestamp' ), 100 );
                add_action( 'woocommerce_after_cart_item_quantity_update', array( 'Wcap_Cart_Updated',   'wcap_store_cart_timestamp' ), 100 );
                add_action( 'woocommerce_calculate_totals',                array( 'Wcap_Cart_Updated',   'wcap_store_cart_timestamp' ), 100 );
                add_filter( 'wcap_block_crawlers', array( 'Wcap_Cart_Updated', 'wcap_detect_crawlers' ), 10, 1 );
                /**
                 * @since 7.10.0 added filter when user logs in after adding products to cart
                 */
                add_filter( 'woocommerce_login_redirect',              array( 'Wcap_Cart_Updated',   'wcap_delete_non_logged_in_cart' ), 10, 2 );
                add_action( 'woocommerce_before_checkout_form',        array( 'Wcap_Cart_Updated', 'wcap_add_banner' ), 100 );
                if ( wcap_get_atc_coupon_msg_cart() ) {
                    add_action( 'woocommerce_before_cart_table',       array( 'Wcap_Cart_Updated', 'wcap_add_banner' ), 100 );
                }
                add_action( 'woocommerce_removed_coupon',              array( 'Wcap_Cart_Updated', 'wcap_update_coupon_details' ), 99, 1 );
            }
			// WordPress Administration Menu.
			add_action( 'admin_menu', array( 'Wcap_Menu', 'wcap_admin_menu' ) );
			// delete added temp fields after order is placed.
			if ( class_exists( 'Wcap_Order_Received' ) ) {
				add_filter( 'woocommerce_order_details_after_order_table', array( 'Wcap_Order_Received', 'wcap_action_after_delivery_session' ) );
			}
            add_action( 'admin_enqueue_scripts',                       array( 'Wcap_Load_Scripts',   'wcap_enqueue_scripts_js' ) );
            add_action( 'admin_enqueue_scripts',                       array( 'Wcap_Load_Scripts',   'wcap_enqueue_scripts_css' ) );
            // track links
            add_filter( 'template_include',                            array( 'Wcap_Populate_Cart_Of_User', 'wcap_email_track_links' ), 99, 1 );
            
            $sms_setting = get_option( 'wcap_enable_sms_reminders' );
            $fb_setting = get_option( 'wcap_enable_fb_reminders' );
            if ( ( isset( $sms_setting ) && $sms_setting === 'on' ) ||
                 ( isset( $fb_setting ) && $fb_setting === 'on' ) ) {
                add_filter( 'template_include',                            array( 'Wcap_Populate_Cart_Of_User', 'wcap_shortcode_redirects' ), 100, 1 );
                add_filter( 'template_include',                            array( 'Wcap_Populate_Cart_Of_User', 'wcap_sms_redirects' ), 101, 1 );
            }
            
            if( class_exists( 'Wcap_Tiny_Mce' ) ) {
                add_action( 'admin_init',                              array( 'Wcap_Tiny_Mce',              'wcap_add_tiny_mce_button_and_plugin' ) );            
            }
            add_action( 'template_include',                            array( 'Wcap_Populate_Cart_Of_User', 'wcap_email_track_open_and_unsubscribe') );
            add_action( 'template_include',                            array( 'Wcap_Common', 'wcap_test_email_track_open_and_unsubscribe' ), 20, 1 );
            add_action( 'template_include',                            array( 'Wcap_Populate_Cart_Of_User', 'wcap_if_email_address_exists') );
            add_action( 'wp_login', array( 'Wcap_Populate_Cart_Of_User', 'wcap_populate_wc_session' ) );
			if ( '' === get_option( 'wcap_auto_login_users', 'on' ) && false !== get_transient( 'wcap_track_redirect_links' ) ) {
				add_filter( 'woocommerce_login_redirect', array( 'Wcap_Populate_Cart_Of_User', 'ts_redirect_login' ), 12, 1 );
			}
            $display_tracked_coupons = get_option( 'ac_track_coupons' );
            // This is added as we have decided that we will always show the coupon code and eventually we will delte the setting of coupon code. 
            if( $display_tracked_coupons == '' || $display_tracked_coupons == null ) {
                update_option( 'ac_track_coupons', 'on' );
                $display_tracked_coupons = 'on';
            }
            if( $display_tracked_coupons == 'on' && class_exists( 'Wcap_Coupon' ) ) {
                add_action( 'woocommerce_coupon_error',                array( 'Wcap_Coupon', 'wcap_capture_coupon_error' ), 15, 2 );
                add_action( 'woocommerce_applied_coupon',              array( 'Wcap_Coupon', 'wcap_capture_applied_coupon' ), 15, 2 );
            }
            
            if( class_exists( 'Wcap_Coupon' ) ) {
                // Add coupon when user views cart page
                add_action( 'woocommerce_before_cart_table',               array( 'Wcap_Coupon', 'wcap_apply_direct_coupon_code' ) );
                // Add coupon when user views checkout page (would not be added otherwise, unless user views cart first).
                add_action( 'woocommerce_before_checkout_form',            array( 'Wcap_Coupon', 'wcap_apply_direct_coupon_code' ) );
            }
			if ( is_admin() ) {
				if ( class_exists( 'Wcap_Dashboard_Widget' ) ) {
					add_action( 'wp_dashboard_setup', array( 'Wcap_Dashboard_Widget', 'wcap_register_dashboard_widget' ), 10 );
				}
				self::wcap_load_ajax_function();
			}
            // Language translation
            add_action( 'init',                                        array( __CLASS__ ,   'wcap_update_po_file' ) );
            if ( class_exists( 'Wcap_Localization' ) ) {
                add_action( 'admin_init',                              array( 'Wcap_Localization',   'wcap_register_template_string_for_wpml' ) );
            }
            if ( class_exists( 'Wcap_EDD' ) ) {
                add_action( 'admin_init',                              array( 'Wcap_EDD',            'wcap_edd_ac_register_option' ) );
                add_action( 'admin_init',                              array( 'Wcap_EDD',            'wcap_edd_ac_deactivate_license' ) );
                add_action( 'admin_init',                              array( 'Wcap_EDD',            'wcap_edd_ac_activate_license' ) );
            }
            add_action( 'woocommerce_order_status_changed',            array( 'Wcap_Admin_Recovery', 'wcap_email_admin_recovery' ), 11, 3 );
            // Cron Job call action, which will run the function based on standard wordpress way.
            // It has been done to over come the Wp-Load.php file include issue.
            $wcap_auto_cron = get_option ( 'wcap_use_auto_cron' );
            if ( isset( $wcap_auto_cron ) && $wcap_auto_cron != false && '' != $wcap_auto_cron ) {
            }
            //delete abandoned order after X number of days
            if ( class_exists( 'Wcap_Actions_Handler' ) ) {
                add_action( 'wcap_clear_carts',                        array( 'Wcap_Actions_Handler', 'wcap_delete_abandoned_carts_after_x_days' ) );
                add_action( 'woocommerce_ac_delete_coupon_action',     array( 'Wcap_Actions_Handler', 'wcap_delete_coupon_codes_after_x_days' ) );
            }
            add_action('update_option_wcap_cron_time_duration',        array( 'Wcap_Common', 'wcap_cron_time_duration' ) );

            if ( class_exists( 'Wcap_Print_And_CSV' ) ) {
                add_action( 'wp_ajax_wcap_data_export',                array( 'Wcap_Print_And_CSV', 'wcap_do_export_data' ) );
                add_action( 'admin_init',                                    array( 'Wcap_Print_And_CSV', 'wcap_download_csv' ) );
            }
            if( class_exists( 'WCAP_On_Placed_Order' ) ) {
                add_action( 'woocommerce_checkout_order_processed',      array( 'WCAP_On_Placed_Order' , 'wcap_order_placed' ), 10, 1 );
				add_action( 'woocommerce_store_api_checkout_update_order_from_request', array( 'WCAP_On_Placed_Order', 'wcap_order_placed_blocks' ), 10, 1 );
                //add_filter( 'woocommerce_payment_complete_order_status', array( 'WCAP_On_Placed_Order' , 'wcap_order_complete_action'), 10, 2 );
                add_action( 'woocommerce_order_status_changed',          array( 'WCAP_On_Placed_Order', 'wcap_cart_details_update' ), 10, 3 );
                add_filter( 'woocommerce_cancel_unpaid_order',           array( 'WCAP_On_Placed_Order', 'wcap_update_cart_status' ), 10, 2 );
            }    
            add_action( 'admin_init',                                  array( 'Wcap_Common', 'wcap_output_buffer') );
            add_action( 'wp_login',                                    array( 'Wcap_Common', 'wcap_remove_action_hook' ), 1 );
            add_filter( 'wc_session_expiring',                         array( 'Wcap_Common', 'wcap_set_session_expiring' ),10,1 );
            add_filter( 'wc_session_expiration',                       array( 'Wcap_Common', 'wcap_set_session_expired' ),10,1 );
            add_filter( 'woocommerce_checkout_fields',                 array( 'Wcap_Common','guest_checkout_fields' ) );
            if( class_exists( 'Wcap_Load_Scripts' ) ) {
                /**
                 * Begin Aero Checkout Compatibility
                 */
                // Enqueue scripts in Footer.
                add_action( 'wfacp_footer_before_print_scripts',       array( 'Wcap_Load_Scripts', 'wcap_include_js_for_guest' ) );
                // In some cases Aero Checkout Hook does not register above hook, hence use in Head as backup.
                add_action( 'wfacp_header_print_in_head',              array( 'Wcap_Load_Scripts', 'wcap_include_js_for_guest' ) );
                add_filter( 'wfacp_default_values',                    array( 'Wcap_Common','wfacp_guest_checkout_fields' ), 10, 3 );
                // Aero Checkout Compatibility end
                add_action( 'woocommerce_after_checkout_billing_form', array( 'Wcap_Load_Scripts', 'wcap_include_js_for_guest' ) ) ;
                add_action( 'wp_enqueue_scripts',                      array( 'Wcap_Load_Scripts', 'wcap_include_js_atc_coupon' ), 9999 ) ;
                add_action( 'wp_enqueue_scripts',                      array( 'Wcap_Load_Scripts',   'wcap_enqueue_scripts_atc_modal' ), 9999 );
                add_action( 'wp_enqueue_scripts',                      array( 'Wcap_Load_Scripts', 'wcap_enqueue_ei_scripts' ), 1000 );
                //add_action( 'plugins_loaded',                          array( 'Wcap_Load_Scripts',   'wcap_dequeue_scripts_atc_modal' ) );
                add_action( 'wp_enqueue_scripts',                      array( 'Wcap_Load_Scripts',   'wcap_enqueue_css_atc_modal' ),10, 1 );
				add_action( 'wp_footer', array( 'Wcap_Load_Scripts', 'wcap_js_checkout_blocks' ), 10, 1 );
				add_action( 'woocommerce_blocks_loaded', array( 'WCap_Load_Scripts', 'wcap_load_guest_blocks_scripts' ) );
            }
            // This condition confirm that the lite plugin active, so we need to perform further action.
            if ( in_array( 'woocommerce-abandoned-cart/woocommerce-ac.php', (array) get_option( 'active_plugins', array() ) ) || 
                ( isset( $_GET ['wcap_plugin_link'] ) && 'wcap-update' == $_GET ['wcap_plugin_link'] ) ) {
                // Import information page.
                // Allows customers to import data of the lite version to the pro.
                add_action( 'admin_menu',                              array( 'Wcap_Import_Lite_to_Pro', 'wcap_admin_menus' ) );
                add_action( 'admin_init',                              array( 'Wcap_Import_Lite_to_Pro', 'wcap_admin_init' ) );
            }
            Wcap_Load_Hooks::wcap_load_front_end_ajax_function();
            add_action( 'media_buttons', array( 'Wcap_Load_Hooks', 'wcap_add_extra_button' ), 10, 1 );
            
            add_action( 'admin_init', array( 'Wcap_Load_Hooks', 'wcap_load_admin_ajax' ), 20 );

            /**
             * @since version 7.10.0
             * @todo Remove or modify with newer versions
             */
            add_action( 'admin_notices', array( 'Wcap_Common', 'wcap_admin_promotions' ) );
            add_action( 'admin_notices', array( 'Wcap_Add_Settings', 'wcap_show_auto_loggin_notice' ) );
            add_action( 'wp_ajax_wcap_dismiss_new_feature', array( 'Wcap_Common', 'wcap_hide_notices' ) );
            add_action( 'wp_ajax_wcap_dismiss_admin_notice', array( 'Wcap_Common', 'wcap_dismiss_admin_notice' ) );
            add_filter( 'pre_update_option_wcap_auto_login_users', array( 'Wcap_Add_Settings', 'wcap_update_admin_notice_value' ), 10, 2 );
            
            /**
             * @since 7.11.0
             * Added Screen ID for toolTip
             */
            add_filter( 'woocommerce_screen_ids', array( 'Wcap_Load_Hooks', 'wcap_set_wc_screen_ids' ) );

            // Add Action Scheduler Library.
            add_action( 'plugins_loaded', array( 'Wcap_Load_Hooks', 'wcap_load_as_library' ) );
            add_action( 'init', array( 'Wcap_Load_Hooks', 'wcap_create_recurring_action' ) );
            add_action( 'update_option_wcap_cron_time_duration', array( 'Wcap_Common', 'wcap_update_cron_interval' ), 10, 2 );
            add_action( 'update_option_wcap_delete_coupon_data', array( 'Wcap_Common', 'wcap_deleting_coupon_data' ), 10, 1 );
            add_action( 'update_option_wcap_use_auto_cron',      array( 'Wcap_Common', 'wcap_use_auto_cron' ), 10, 2 );
			// Add a one time action to update the _woocommerce_ac_coupon in post meta table.
            add_action( 'wcap_update_coupons', array( 'Wcap_Update_Check', 'wcap_update_postmeta_coupons' ) );
            // Enable Reminder EMails setting update. 
            add_action( 'update_option_ac_enable_cart_emails', array( 'Wcap_Add_Settings', 'wcap_update_ac_enable_cart_emails' ), 10, 2 );
            // Enable Emails Reports setting update.
            add_action( 'add_option_wcap_email_reports_frequency', array( 'Wcap_Default_Settings', 'wcap_email_reports_frequency_scheduled_action' ), 10, 2 );
            add_action( 'update_option_wcap_email_reports_frequency', array( 'Wcap_Default_Settings', 'wcap_email_reports_frequency_scheduled_action' ), 10, 2 );
			
			// Email notifications for cart abandonment.
            add_filter( 'pre_update_option_wcap_email_admin_custom_addresses', array( 'Wcap_Add_Settings', 'wcap_update_email_admin_custom_addresses' ), 10, 2 );
			add_action( 'wp_ajax_wcap_get_settings', array( 'Wcap_Add_Settings', 'wcap_get_settings' ) );
			add_action( 'wp_ajax_wcap_save_settings', array( 'Wcap_Add_Settings', 'wcap_save_settings' ) );
			add_action( 'wp_ajax_ac_deactivate_license', array( 'Wcap_EDD', 'wcap_edd_ac_deactivate_license' ) );
            add_action( 'wp_ajax_ac_activate_license', array( 'Wcap_EDD', 'wcap_edd_ac_activate_license' ) );
            add_action( 'wp_ajax_ac_get_popup_templates', array( 'Wcap_Common', 'wcap_get_popup_templates' ) );
            add_action( 'wp_ajax_ac_get_email_templates', array( 'Wcap_Common', 'wcap_get_email_templates' ) );
            add_action( 'wp_ajax_ac_get_sms_templates', array( 'Wcap_Common', 'wcap_get_sms_templates' ) );
            add_action( 'wp_ajax_ac_get_fb_templates', array( 'Wcap_Common', 'wcap_get_fb_templates' ) );
            add_action( 'wp_ajax_ac_get_abandoned_orders', array( 'Wcap_Common', 'wcap_get_abandoned_orders' ) );
            add_action( 'wp_ajax_ac_get_email_reminders', array( 'Wcap_Common', 'wcap_get_email_reminders' ) );
            add_action( 'wp_ajax_ac_get_sms_reminders', array( 'Wcap_Common', 'wcap_get_sms_reminders' ) );
            add_action( 'wp_ajax_ac_get_product_reports', array( 'Wcap_Common', 'wcap_get_product_reports' ) );
			add_action( 'wp_ajax_wcap_save_sms_template', array( 'Wcap_SMS', 'wcap_save_sms_template' ) );
			
			add_action( 'wp_ajax_wcap_delete_template', array( 'Wcap_Common', 'wcap_delete_template' ) );
			add_action( 'wp_ajax_wcap_cart_bulk_action', array( 'Wcap_Common', 'wcap_cart_bulk_action' ) );
			add_action( 'wp_ajax_wcap_cart_row_action', array( 'Wcap_Common', 'wcap_cart_row_action' ) );
            add_action( 'wp_ajax_wcap_display_dashboard', array( 'Wcap_Dashboard_Advanced', 'wcap_display_dashboard' ) );
            add_action( 'wp_ajax_wcap_display_dashboard_line_graph_data', array( 'Wcap_Dashboard_Advanced', 'wcap_display_dashboard' ) );
            add_action( 'wp_ajax_wcap_delete_atc_template', array( 'Wcap_Common', 'wcap_delete_atc_template' ) );
            add_action( 'wp_ajax_wcap_add_to_cart_popup_save_settings', array( 'Wcap_Add_Cart_Popup_Modal', 'wcap_add_to_cart_popup_save_settings' ) );
            add_action( 'wp_ajax_wcap_add_to_cart_popup_upload_files', array( 'Wcap_Add_Cart_Popup_Modal', 'wcap_add_to_cart_popup_upload_files' ) );
            add_action( 'wp_ajax_wcap_add_to_cart_popup_delete_files', array( 'Wcap_Add_Cart_Popup_Modal', 'wcap_add_to_cart_popup_delete_files' ) );
            add_action( 'wp_ajax_wcap_emailtemplates_save_settings', array( 'Wcap_Email_Template_List', 'wcap_save_email_template' ) );
            add_action( 'wp_ajax_wcap_send_manual_email', array( 'Wcap_Send_Manual_Email', 'wcap_create_and_send_manual_email' ) );
            
        }

        /**
         * Loading action scheduler library from Woocommerce.
         *
         * @since 8.6
         */

        public static function wcap_load_as_library() {
            if ( file_exists( WP_PLUGIN_DIR . '/woocommerce/packages/action-scheduler/action-scheduler.php' ) ) {
				require_once WP_PLUGIN_DIR . '/woocommerce/packages/action-scheduler/action-scheduler.php';
			} else {
				add_action( 'admin_notices', array( 'Wcap_Load_Hooks' , 'wcap_as_failed_notice' ) );
			}
        }

        		/**
		 * Action Scheduler library load failed. So add an admin notice.
		 *
		 * @since 8.15.0
		 */
		public static function wcap_as_failed_notice() {
			$as_link = "<a href='https://www.tychesoftwares.com/moving-to-the-action-scheduler-library/' target='_blank'>Action Scheduler library</a>";
			?>
			<div class="notice notice-error">
				<p>
					<?php
					echo wp_kses_post( __( "Abandoned Cart Pro for WooCommerce was unable to load the $as_link. Please ensure you are running WooCommerce 4.0.0 or higher to send automated reminder emails. Currrently no automated reminder emails are being sent for abandoned carts.", 'woocommerce-ac' ) ); // phpcs:ignore
					?>
				</p>
			</div>
			<?php
		}
        
        /**
         * Add recurring action for sending reminders.
         *
         * @since 8.6
         */
        public static function wcap_create_recurring_action() {
            $cron_interval = intval( get_option( 'wcap_cron_time_duration', 15 ) ) * 60;

            $reminders_list = wcap_get_enabled_reminders();
            if ( false === as_next_scheduled_action( 'woocommerce_ac_send_email_action' ) && count( $reminders_list ) > 0 && 'on' == get_option( 'wcap_use_auto_cron', '' ) ) {
                wp_clear_scheduled_hook( 'woocommerce_ac_send_email_action' );
                as_schedule_recurring_action( time(), $cron_interval, 'woocommerce_ac_send_email_action' );
            }
			if ( 'yes' !== get_option( 'wcap_coupons_data_updated', '' ) && false === as_next_scheduled_action( 'wcap_update_coupons' ) ) {
				as_schedule_single_action( time() + 60, 'wcap_update_coupons' );
				// Update the DB record, so multiple actions are not scheduled.
				update_option( 'wcap_coupons_data_updated', 'yes' );
            }
        }

        /**
         * Check and run the update code for any changes if needed.
         *
         * @since 8.9.1
         */
        public static function wcap_check_update_code_run() {
            if ( false === get_option( 'woocommerce_ac_db_version', false ) ) {
                add_option( 'woocommerce_ac_db_version', '8.13.1' );
            }
            // Always make sure that the default value in the get_option() is the latest version.
            if ( function_exists( 'as_enqueue_async_action' ) && false === as_next_scheduled_action( 'wcap_update_db' ) && get_option( 'woocommerce_ac_db_version', WCAP_PLUGIN_VERSION ) != WCAP_PLUGIN_VERSION ) {
                as_enqueue_async_action( 'wcap_update_db' );
            }
        }
        /**
         * Adds AJAX calls for SMS in WP Dashboard
         * @since 7.9
         */
        public static function wcap_load_admin_ajax() {
            add_action( 'wp_ajax_wcap_delete_sms_template', array( 'Wcap_SMS', 'wcap_delete_sms' ) );
            add_action( 'wp_ajax_wcap_save_bulk_sms_template', array( 'Wcap_SMS', 'wcap_save_bulk_sms' ) );
            add_action( 'wp_ajax_wcap_test_sms', array( 'Wcap_SMS_settings', 'wcap_send_test_sms' ) );
            add_action( 'wp_ajax_wcap_display_connector_settings', array( 'Wcap_Integrations', 'wcap_load_connector_settings' ) );
        }
        
        /** 
         * This function is used for loading AJAX which we will use for Add To Cart Popup Modal, Test email, Preview email template, capturing cart from checkout page.
         * 
         * @since 6.0
         */
        public static function wcap_load_ajax_function() {
            add_action( 'wp_ajax_wcap_preview_email_sent',             array( 'Wcap_Ajax', 'wcap_preview_email_sent' ) );
            add_action( 'wp_ajax_wcap_toggle_template_status',         array( 'Wcap_Ajax', 'wcap_toggle_template_status' ) );
            add_action( 'wp_ajax_wcap_abandoned_cart_info',            array( 'Wcap_Ajax', 'wcap_abandoned_cart_info' ) );
            add_action( 'wp_ajax_wcap_admin_unsubscribe_cart',         array( 'Wcap_Ajax', 'wcap_admin_unsubscribe_cart' ) );
            add_action( 'wp_ajax_wcap_edit_guest_email', array( 'Wcap_Ajax', 'wcap_edit_guest_email' ) );
            add_action( 'wp_ajax_wcap_json_find_coupons',              array( 'Wcap_Ajax', 'wcap_json_find_coupons' ) );
            add_action( 'wp_ajax_wcap_json_find_products',             array( 'Wcap_Ajax', 'wcap_json_find_products' ) );
			add_action( 'wp_ajax_wcap_json_find_product_cat',          array( 'Wcap_Ajax', 'wcap_json_find_product_cat' ) );
            add_action( 'wp_ajax_wcap_json_find_product_tag',          array( 'Wcap_Ajax', 'wcap_json_find_product_tag' ) );
			add_action( 'wp_ajax_wcap_json_find_cart_status', array( 'Wcap_Ajax', 'wcap_json_find_cart_status' ) );
			add_action( 'wp_ajax_wcap_json_find_send_to', array( 'Wcap_Ajax', 'wcap_json_find_send_to' ) );
			add_action( 'wp_ajax_wcap_json_find_order_status', array( 'Wcap_Ajax', 'wcap_json_find_order_status' ) );
            add_action( 'wp_ajax_wcap_json_find_pages',                array( 'Wcap_Ajax', 'wcap_json_find_pages' ) );
            add_action( 'wp_ajax_wcap_change_manual_email_data',       array( 'Wcap_Ajax', 'wcap_change_manual_email_data' ) );
            add_action( 'wp_ajax_wcap_json_search_wc_order',           array( 'Wcap_Ajax', 'wcap_json_search_wc_order' ) );
            add_action( 'wp_ajax_wcap_mark_recovered_admin',           array( 'Wcap_Ajax', 'wcap_mark_order_recovered_admin' ) );
            add_action( 'wp_ajax_wcap_popup_templates_info',           array( 'Wcap_Ajax', 'wcap_popup_template_info' ) );
            // We keep this function in the dashboard widget file.
            if( class_exists( 'Wcap_Dashboard_Widget' ) ) {
                add_action( 'wp_ajax_wcap_dashboard_widget_report',        array( 'Wcap_Dashboard_Widget', 'wcap_dashboard_widget_report' ), 10 );
            }
            /** 
             * Enable or disable the ATC modal 
             *  @since: 6.0
             */
            add_action( 'wp_ajax_wcap_toggle_atc_enable_status',       array( 'Wcap_Ajax', 'wcap_toggle_atc_enable_status' ) );
            /** 
             *  Mandatory email fields for the ATC modal
             *  @since: 6.0
             */
            add_action( 'wp_ajax_wcap_atc_reset_setting',              array( 'Wcap_Ajax', 'wcap_atc_reset_setting' ) );
            /** 
             * Reset to default values in ATC modal 
             *  @since: 6.0
             */
            $guest_cart = get_option( 'ac_disable_guest_cart_email' );
            if ( $guest_cart != "on" ) {
                add_action( 'wp_ajax_nopriv_wcap_save_guest_data',     array( 'Wcap_Ajax','wcap_save_guest_data' ) );
                add_action( 'wp_ajax_wcap_save_guest_data',            array( 'Wcap_Ajax','wcap_save_guest_data' ) );
                add_action( 'wp_ajax_nopriv_wcap_gdpr_refused',        array( 'Wcap_Ajax', 'wcap_gdpr_refused' ) );
                add_action( 'wp_ajax_nopriv_wcap_coupon_countdown_dismissed', array( 'Wcap_Ajax','wcap_coupon_countdown_dismissed' ) );
            }
            add_action( 'wp_ajax_wcap_gdpr_refused', array( 'Wcap_Ajax', 'wcap_gdpr_refused' ) );
            
            /**
             * This ajax check if the atc is enabled or not when we disabled the guest cart capturing.
             * @since: 7.0
             */
            add_action( 'wp_ajax_wcap_is_atc_enable',                  array( 'Wcap_Ajax', 'wcap_is_atc_enable' ) );
            
            /**
             * This ajax create the preview email content for the WC setting.
             * @since: 7.0
             */
            add_action( 'wp_ajax_wcap_preview_wc_email',               array( 'Wcap_Ajax', 'wcap_preview_wc_email' ) );

            /**
             * This ajax create the preview email content for the without WC setting.
             * @since: 7.0
             */
            add_action( 'wp_ajax_wcap_preview_email',                  array( 'Wcap_Ajax', 'wcap_preview_email' ) );

            /**
             * This function will import the data of the lite version.
             * @since: 8.0
             */
            add_action( 'wp_ajax_wcap_import_lite_data',               array( 'Wcap_Ajax', 'wcap_import_lite_data' ) );
            add_action( 'wp_ajax_wcap_do_not_import_lite_data',        array( 'Wcap_Ajax', 'wcap_do_not_import_lite_data' ) );

            /**
             * This ajax check if the atc is enabled or not when we disabled the guest cart capturing.
             * @since: 7.0
             */
            add_action( 'wp_ajax_wcap_delete_expired_used_coupon_code',array( 'Wcap_Ajax', 'wcap_delete_expired_used_coupon_code' ) );  


            add_action( 'edit_user_profile',                           array( 'Wcap_Common', 'wcap_add_restrict_user_meta_field' ),50 );    
            add_action( 'show_user_profile',                           array( 'Wcap_Common', 'wcap_add_restrict_user_meta_field' ),50  );    

            add_action( 'personal_options_update',                     array( 'Wcap_Common', 'wcap_save_restrict_user_meta_field' ), 50 );
            add_action( 'edit_user_profile_update',                    array( 'Wcap_Common', 'wcap_save_restrict_user_meta_field' ),50  );  
        }

        /**
         * It will load the boilerplate components file. In this file we have included all boilerplate files.
         * We need to inlcude this file after the init hook.
         * @hook init
         */
        public static function wcap_load_component_file (){
            
            $is_admin = is_admin();

            if ( true === $is_admin ) {
                require_once( "wcap_all_component.php" );
            }
        }

        public static function wcap_deactivate_aelia_addon(){
            deactivate_plugins( 'aelia-currency-switcher-addon-for-abandoned-cart/aelia-currency-switcher-addon-for-abandoned-cart.php' );
            add_action( 'admin_notices', array( 'Wcap_Load_Hooks', 'wcap_deactivate_error_notice' ) );
        }

        public static function wcap_deactivate_error_notice(){
            $class = 'notice notice-error is-dismissible';
            $message = __( '<b>Aelia Currency Switcher addon for Abandoned Cart Plugin</b> has been deactivated since the addon is now part of the core module.', 'woocommerce-ac' );
            printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
        }

        /**
         * We have used WC ajax because the wp-ajax was not runing on the shop page. When we run the wp-admin ajax, it was giving the 302 status of the ajax call.
         *
         * @since 6.0 
         */
        public static function wcap_load_front_end_ajax_function() {
            add_action( 'wp_loaded',                                  array( 'Wcap_Ajax', 'wcap_add_ajax_for_atc'  ) );
            add_action( 'wp_ajax_nopriv_wcap_atc_stats_record',       array( 'Wcap_Ajax', 'wcap_atc_stats_record' ) );
            add_action( 'wp_ajax_nopriv_wcap_ei_stats_update',        array( 'Wcap_Ajax', 'wcap_ei_stats_update' ) );
            add_action( 'wp_ajax_nopriv_wcap_atc_store_guest_email',  array( 'Wcap_Ajax', 'wcap_atc_store_guest_email' ) );
            add_action( 'wc_ajax_wcap_track_notice', array( 'Wcap_Ajax', 'wcap_track_carts' ) );

            add_action( 'wp_ajax_nopriv_wcap_add_email_to_cart',  array( 'Wcap_Ajax', 'wcap_add_email_to_cart' ) );
            add_action( 'wp_ajax_wcap_add_email_to_cart',  array( 'Wcap_Ajax', 'wcap_add_email_to_cart' ) );
            add_action( 'wp_ajax_wcap_ei_stats_update', array( 'Wcap_Ajax', 'wcap_ei_stats_update' ) );
            add_action( 'wp_ajax_wcap_sms_consent', array( 'Wcap_Ajax', 'wcap_sms_consent' ) );
            add_action( 'wp_ajax_nopriv_wcap_sms_consent', array( 'Wcap_Ajax', 'wcap_sms_consent' ) );
            add_action( 'wp_ajax_nopriv_wcap_verify_email_validity', array( 'Wcap_Ajax', 'wcap_verify_email_validity' ) );
        }

        /**
         * This function is used for loading the text domain of the plugin.  
         *   
         * @hook init
         *    
         * @since 4.5
         */
        Public static function wcap_update_po_file() {
            $domain = 'woocommerce-ac';
            $locale = apply_filters( 'plugin_locale', get_locale(), $domain );
            if ( $loaded = load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '-' . $locale . '.mo' ) ) {
                return $loaded;
            } else {
                load_plugin_textdomain( $domain, FALSE, basename( dirname( __FILE__ ) ) . '/i18n/languages/' );
            }
        }

        public static function wcap_add_extra_button( $param ) {
            if ( 'woocommerce_ac_email_body' === $param ) {
                print( '<input type="button" class="button ac-insert-template add_media" data-toggle="modal" data-target=".wcap-preview-modal" value="' . __( 'Insert Template', 'woocommerce-ac' ) . '" />' );
                print( '<input type="button" class="button ac-import-template add_media" value="' . __( 'Import Template', 'woocommerce-ac' ) . '" />' );
            }
        }

        public static function wcap_set_wc_screen_ids( $screen ){
            $screen[] = 'woocommerce_page_woocommerce_ac_page';
            return $screen;
        }

    }
}
