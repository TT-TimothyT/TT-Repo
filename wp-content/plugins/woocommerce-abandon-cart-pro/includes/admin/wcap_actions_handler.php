<?php
/** 
 * It will delete the record from the database.
 * @author  Tyche Softwares
 * @package Abandoned-Cart-Pro-for-WooCommerce/Admin/Action
 * @since 5.0
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists('Wcap_Actions_Handler' ) ) {

    /**
     * It will delete the record from the database.
     * @since 5.0
     */
    class Wcap_Actions_Handler{

        /**
         * This function will delete the Abandoned cart when we perform the Bulk action from the abandoned cart page.
         * @param int | string $abandoned_cart_id Abandoned cart id 
         * @param int | string $count The total count of record need to delete
         * @globals mixed $wpdb
         * @since 5.0
         */
        function wcap_delete_bulk_action_handler_function( $abandoned_cart_id, $count ) {
            global $wpdb;
            $get_user_id         = "SELECT user_id FROM `" . WCAP_ABANDONED_CART_HISTORY_TABLE . "` WHERE id = '$abandoned_cart_id' ";
            $results_get_user_id = $wpdb->get_results( $get_user_id );
            $user_id_of_guest    = $results_get_user_id[0]->user_id;

            $return = WCAP_CART_HISTORY_MODEL::delete( array( 'id'=> $abandoned_cart_id ) );

            if ( $user_id_of_guest >= '63000000' ) {				
				WCAP_CART_HISTORY_MODEL::delete( array( 'id'=> $user_id_of_guest ) );
                //guest user
            }

            $query_delete_sent_history        = "DELETE FROM `" . WCAP_EMAIL_SENT_HISTORY_TABLE . "` WHERE cart_id = '$abandoned_cart_id' ";
            $results_delete_sent_history      = $wpdb->get_results( $query_delete_sent_history );

            $wcap_meta_of_ac_id = get_post_meta ( $abandoned_cart_id, 'wcap_atc_report' );
            if ( is_array( $wcap_meta_of_ac_id ) && count( $wcap_meta_of_ac_id ) > 0 ){
                delete_post_meta ( $abandoned_cart_id, 'wcap_atc_report' );
            }
/*
            wp_safe_redirect( admin_url( "/admin.php?page=woocommerce_ac_page&action=listcart&wcap_section=wcap_trash_abandoned&wcap_deleted=YES&wcap_count=$count" ) );
			*/
			
			return $return;
        }

        /**
         * This function will delete the Template when we perform the Bulk action from the template page.
         * @param int | string $template_id Template id 
         * @globals mixed $wpdb
         * @since 5.0
         */

        function wcap_delete_template_bulk_action_handler_function( $template_id ) {
            global $wpdb;
            $id_remove    = $template_id;
            $query_remove = "DELETE FROM `" . WCAP_NOTIFICATION_TEMPLATES_TABLE . "` WHERE id='" . $id_remove . "' ";
            $return = $wpdb->query( $query_remove );

           /* wp_safe_redirect( admin_url( '/admin.php?page=woocommerce_ac_page&action=cart_recovery&section=emailtemplates&wcap_template_deleted=YES' ) );
		   */
		   return $return;
        }

        /**
         * It will delete cart automatically after X days
         * @hook admin_init
         * @globals mixed $wpdb
         * @since 5.0
         */
        public static function wcap_delete_abandoned_carts_after_x_days() {
            global $wpdb;
            
            $delete_ac_after_days = get_option( 'ac_delete_abandoned_order_days', 0 );
            if ( 0 == $delete_ac_after_days || ''  == $delete_ac_after_days ) { // If for some reason, it is blanks or 0, reset it to 365.
                $delete_ac_after_days = '365';
            }
            if( '' != $delete_ac_after_days && 0 != $delete_ac_after_days ) {
            
                $delete_ac_after_days_time = $delete_ac_after_days * 86400;
                $current_time              = current_time( 'timestamp' );
                $check_time                = $current_time - $delete_ac_after_days_time;
            
                $query = "SELECT * FROM `" . WCAP_ABANDONED_CART_HISTORY_TABLE . "` WHERE recovered_cart = '0' AND abandoned_cart_time < %s";
                $carts = $wpdb->get_results ( $wpdb->prepare( $query, $check_time ) );
            
                foreach( $carts as $cart_key => $cart_value ) {
                    self::delete_ac_carts( $cart_value );
                }
            }

            // Delete any empty carts that might be present
            $blank_cart_info   = "'{\"cart\":[],\"shipping_charges\":0}', '{\"cart\":[]}'";
            $delete_empty = "DELETE FROM `" . WCAP_ABANDONED_CART_HISTORY_TABLE . "` WHERE abandoned_cart_info IN (" . stripslashes( $blank_cart_info ) . ")";
            $wpdb->query( $delete_empty );
            
        }

        /**
         * It will delete expired and used coupons automatically after 15 days
         * @hook admin_init
         * @globals mixed $wpdb
         * @since 5.0
         */
        public static function wcap_delete_coupon_codes_after_x_days() {
            $wcap_ajax = new Wcap_Ajax();
            $wcap_ajax::wcap_delete_expired_used_coupon_code();
        }

        /**
         * It will delete the abandoned cart data from database.
         * It will also delete the email history for that abandoned cart.
         * If the user id guest user then it will delete the record from users table.
         * @param object $value Value of cart.
         * @globals mixed $wpdb
         * @since 5.0
         */
        public static function delete_ac_carts( $value ) {
            global $wpdb;

            $abandoned_id = isset( $value->id ) ? $value->id : 0;
            $user_id      = isset( $value->user_id ) ? $value->user_id : 0;
            $user_type    = isset( $value->user_type ) ? $value->user_type : '';
            
            if( $abandoned_id > 0 && '' != $user_type ) {
                // delete from the sent history table.
				$get_sent_ids = $wpdb->get_col(
                    $wpdb->prepare(
                        'SELECT id FROM ' . WCAP_EMAIL_SENT_HISTORY_TABLE . ' WHERE cart_id = %s',
                        $abandoned_id
                    )
                );
                $get_sent_ids = implode( ',', $get_sent_ids );
				if ( '' !== $get_sent_ids ) {
					$wpdb->query( 'DELETE FROM ' . WCAP_NOTIFICATION_OPENED_TABLE . ' WHERE notification_sent_id IN (' . $get_sent_ids . ')' );
					$wpdb->query( 'DELETE FROM ' . WCAP_LINK_CLICKED_TABLE . ' WHERE notification_sent_id IN (' . $get_sent_ids . ')' );
				}
				$query_delete_sent_history = $wpdb->delete( WCAP_EMAIL_SENT_HISTORY_TABLE, array( 'cart_id' => $abandoned_id ) );
            
                // delete the user meta
                $query_delete_cart     = $wpdb->delete( $wpdb->prefix ."usermeta", array( 'user_id' => $user_id, 'meta_key' => '_woocommerce_persistent_cart' ) );
            
                // abandoned cart history
                
				WCAP_CART_HISTORY_MODEL::delete( array( 'user_id' => $user_id, 'id' => $abandoned_id ) ); //phpcs:ignore
            
                // guest cart history
                if ( 'GUEST' == $user_type && $user_id >= 63000000 ) {
					WCAP_CART_HISTORY_MODEL::delete( array( 'id' => $user_id ) );
                }

				// Tiny URLs.
				$wpdb->delete(
					WCAP_TINY_URLS,
					array(
						'cart_id' => $abandoned_id
					)
				);
            }
        }

        /**
         * This function will delete the Recovered cart when we perform the Bulk action from the Recovered Orders page.
         * @param int | string $abandoned_cart_id Abandoned cart id 
         * @globals mixed $wpdb
         * @since 5.0
         */
        function wcap_recovered_delete_bulk_action_handler( $abandoned_cart_id ) {
            global $wpdb;
            $get_user_id         = "SELECT user_id FROM `" . WCAP_ABANDONED_CART_HISTORY_TABLE . "` WHERE id = '$abandoned_cart_id' ";
            $results_get_user_id = $wpdb->get_results( $get_user_id );
            $user_id_of_guest    = $results_get_user_id[0]->user_id;

			$return = WCAP_CART_HISTORY_MODEL::delete_cart_history( array( 'id'=> $abandoned_cart_id ) );
			

            if ( $user_id_of_guest >= '63000000' ) {
				$return = WCAP_CART_HISTORY_MODEL::delete_cart_history( array( 'id'=> $user_id_of_guest ) );				
            }

            $query_delete_sent_history        = "DELETE FROM `" . WCAP_EMAIL_SENT_HISTORY_TABLE . "` WHERE cart_id = '$abandoned_cart_id' ";
            $results_delete_sent_history      = $wpdb->get_results( $query_delete_sent_history );
			
			return $return;

            /* wp_safe_redirect( admin_url( '/admin.php?page=woocommerce_ac_page&action=stats&wcap_rec_deleted=YES' ) ); */
        }

        /**
         * This function will move the Recovered cart in Trash when we perform the Bulk action from the Recovered Orders page.
         * @param int | string $abandoned_cart_id Abandoned cart id 
         * @param int | string $count The total count of record need to Trash
         * @globals mixed $wpdb
         * @since 5.0
         */
        function wcap_recovered_trash_bulk_action_handler( $abandoned_cart_id, $count ) {
            global $wpdb;
			return WCAP_CART_HISTORY_MODEL::update( array( 'wcap_trash' => 1 ), array( 'id' => $abandoned_cart_id ) );
           /* wp_safe_redirect( admin_url( "/admin.php?page=woocommerce_ac_page&action=stats&wcap_section=wcap_all_rec&wcap_rec_trash=YES&wcap_count=$count" ) );
		   */
        }

        /**
         * This function will restores the trash Recovered cart when we perform the Bulk action from the Recovered Orders page.
         * @param int | string $abandoned_cart_id Abandoned cart id 
         * @param int | string $count The total count of record need to Trash
         * @globals mixed $wpdb
         * @since 5.0
         */
        function wcap_recovered_restore_bulk_action_handler( $abandoned_cart_id, $count ) {
            global $wpdb;

			return WCAP_CART_HISTORY_MODEL::update( array( 'wcap_trash' => '' ), array( 'id' => $abandoned_cart_id ) ) ;
            /* wp_safe_redirect( admin_url( "/admin.php?page=woocommerce_ac_page&action=stats&wcap_section=wcap_trash_rec&wcap_rec_restore=YES&wcap_count=$count" ) );
			*/
        }

        /**
         * This function will move the Abanodned cart in Trash when we perform the Bulk action from the Recovered Orders page.
         * @param int | string $abandoned_cart_id Abandoned cart id 
         * @param int | string $count The total count of record need to Trash
         * @globals mixed $wpdb
         * @since 5.0
         */
        function wcap_abandoned_trash_bulk_action_handler( $abandoned_cart_id, $count ) {
            global $wpdb;

			$return = WCAP_CART_HISTORY_MODEL::update( array( 'wcap_trash' => 1 ), array( 'id' => $abandoned_cart_id ) ) ;		

            // Remove the Cart ID from the SMS Templates Cart list to which SMS reminders are pending
            Wcap_Common::wcap_delete_cart_notification( $abandoned_cart_id );
            return $return;
            //wp_safe_redirect( admin_url( "/admin.php?page=woocommerce_ac_page&action=listcart&wcap_abandoned_trash=YES&wcap_count=$count" ) );
        }
        
        /**
         * Trash all visitor carts.
         *
         * @since 8.7.0
         */
        function wcap_abandoned_trash_visitor_bulk_action_handler() {
            global $wpdb;

			$return = WCAP_CART_HISTORY_MODEL::update( array( 'wcap_trash' => 1 ), array( 'user_id' => 0 ) );

            // Remove the Cart ID from the SMS Templates Cart list to which SMS reminders are pending
            $get_visitors = $wpdb->get_col( 'SELECT id FROM ' . WCAP_ABANDONED_CART_HISTORY_TABLE . " WHERE wcap_trash = '1' AND user_id = 0" );

            if ( is_array( $get_visitors ) && count( $get_visitors ) > 0 ) {
                foreach ( $get_visitors as $abandoned_cart_id ) {
                    Wcap_Common::wcap_delete_cart_notification( $abandoned_cart_id );
                }
            }
            
			return $return;
			
            /* wp_safe_redirect( admin_url( "/admin.php?page=woocommerce_ac_page&action=listcart&wcap_abandoned_trash_visitor=YES" ) );
			*/
        }
        
        /**
         * Trash all guest user carts.
         *
         * @since 8.7.0
         */
        function wcap_abandoned_trash_guest_bulk_action_handler() {
            global $wpdb;
         
			$return =  WCAP_CART_HISTORY_MODEL::update( array( 'wcap_trash' => 1 ), array( 'user_type' => 'GUEST' ) ) ;

            // Remove the Cart ID from the SMS Templates Cart list to which SMS reminders are pending
            $get_guests = $wpdb->get_col( 'SELECT id FROM ' . WCAP_ABANDONED_CART_HISTORY_TABLE . " WHERE wcap_trash = '1' AND user_type = 'GUEST'" );

            if ( is_array( $get_guests ) && count( $get_guests ) > 0 ) {
                foreach ( $get_guests as $abandoned_cart_id ) {
                    Wcap_Common::wcap_delete_cart_notification( $abandoned_cart_id );
                }
            }
            
			return $return;
            /* wp_safe_redirect( admin_url( "/admin.php?page=woocommerce_ac_page&action=listcart&wcap_abandoned_trash_guest=YES" ) );
			*/
        }

        /**
         * Trash all registered user carts.
         *
         * @since 8.7.0
         */
        function wcap_abandoned_trash_registered_bulk_action_handler() {
            global $wpdb;

			$return  = WCAP_CART_HISTORY_MODEL::update( array( 'wcap_trash' => 1 ), array( 'user_type' => 'REGISTERED' ) );

            // Remove the Cart ID from the SMS Templates Cart list to which SMS reminders are pending
            $get_registered = $wpdb->get_col( 'SELECT id FROM ' . WCAP_ABANDONED_CART_HISTORY_TABLE . " WHERE wcap_trash = '1' AND user_type = 'REGISTERED'" );

            if ( is_array( $get_registered ) && count( $get_registered ) > 0 ) {
                foreach ( $get_registered as $abandoned_cart_id ) {
                    Wcap_Common::wcap_delete_cart_notification( $abandoned_cart_id );
                }
            }
            return $return;
            //wp_safe_redirect( admin_url( "/admin.php?page=woocommerce_ac_page&action=listcart&wcap_abandoned_trash_registered=YES" ) );
        }

        /**
         * Trash all the carts.
         *
         * @since 8.7.0
         */
        function wcap_abandoned_trash_all_bulk_action_handler() {
            global $wpdb;

            $return = WCAP_CART_HISTORY_MODEL::update( array( 'wcap_trash' => 1 ), array( 'wcap_trash' =>'' ) ) ;
            
            // Remove the Cart ID from the SMS Templates Cart list to which SMS reminders are pending
            $get_all = $wpdb->get_col( 'SELECT id FROM ' . WCAP_ABANDONED_CART_HISTORY_TABLE . " WHERE wcap_trash = '1'" );

            if ( is_array( $get_all ) && count( $get_all ) > 0 ) {
                foreach ( $get_all as $abandoned_cart_id ) {
                    Wcap_Common::wcap_delete_cart_notification( $abandoned_cart_id );
                }
            }
            return $return;
			/*
            wp_safe_redirect( admin_url( "/admin.php?page=woocommerce_ac_page&action=listcart&wcap_abandoned_trash_all=YES" ) );
			*/
        }

		/**
		 * Sync the selected carts manually.
		 *
		 * @since 8.16.0
		 */
		function wcap_sync_carts_manually_bulk_action_handler() {
			$connectors_common = Wcap_Connectors_Common::get_instance();
			$connectors_common->wcap_bulk_sync_manually();
			return true;
			//wp_safe_redirect( admin_url( "/admin.php?page=woocommerce_ac_page&action=listcart&wcap_sync_manually=YES" ) );
		}

        /**
         * This function will restores the trash Abandoned cart when we perform the Bulk action from the Abandoned Orders page.
         * @param int | string $abandoned_cart_id Abandoned cart id 
         * @param int | string $count The total count of record need to Trash
         * @globals mixed $wpdb
         * @since 5.0
         */
        function wcap_abandoned_restore_bulk_action_handler( $abandoned_cart_id, $count ) {
            global $wpdb;

			 WCAP_CART_HISTORY_MODEL::update( array( 'wcap_trash' => '' ), array( 'id' => $abandoned_cart_id ) );

            $query      = "SELECT COUNT(id) FROM `".WCAP_ABANDONED_CART_HISTORY_TABLE."` WHERE wcap_trash = '1'";
            $results    = $wpdb->get_results( $query );
            /*
			$link       = ( $results[0]->{'COUNT(id)'} > 0 ) ? "/admin.php?page=woocommerce_ac_page&action=listcart&wcap_section=wcap_trash_abandoned&wcap_abandoned_restore=YES&wcap_count=$count" : "/admin.php?page=woocommerce_ac_page&action=listcart&wcap_abandoned_restore=YES&wcap_count=$count";
            wp_safe_redirect( admin_url( $link ) );
			*/
			return $count;
        }
    }
}
