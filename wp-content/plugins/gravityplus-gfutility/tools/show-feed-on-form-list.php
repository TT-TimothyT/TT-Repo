<?php
/**
 * Show the feeds that a form has
 *
 * @since 2.4.0
 *
 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
 */
class GFPGFU_Show_Feed_On_Form_List {


	private $_calling_get_addons = false;

	/**
	 * @since 2.4.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 */
	public function run () {
		
		add_action( 'gform_form_list_column_title', array( $this, 'gform_form_list_column_title' ) );

		add_filter( 'gfp_gfutility_get_addons_addon_data', array( $this, 'gfp_gfutility_get_addons_addon_data' ), 10, 2 );

		add_filter( 'gfp_gfutility_enabled_addons_for_form', array( $this, 'gfp_gfutility_enabled_addons_for_form' ), 10, 2 );

		add_filter( 'gfp_gfutility_get_addon_icon', array( $this, 'gfp_gfutility_get_addon_icon' ), 10, 2 );

		add_action( 'admin_head', array( $this, 'admin_head' ) );

	}

	public function admin_head(){

		if ( 'form_list' == GFForms::get_page() ){

			echo "<br /><style>
div.gfp_gfutility_form_list_addons_with_feeds{
    display: grid;
    grid-gap: 4px;
    grid-template-columns: repeat(auto-fill, minmax(1.5em, 1fr) );
    justify-items: center;
}
span.gfp_gfutility_form_list_feed_addon {
    padding: 3px;
    background: #f1f0f0;
}
span.gfp_gfutility_form_list_feed_addon:hover {
 border: 1px solid #dddddd;
    border-radius: 4px;
}
span.gfp_gfutility_show_feed_on_form__icon{word-wrap:normal;}
span.gfp_gfutility_feed_addon_active{font-weight:bold;}
span.gfp_gfutility_feed_addon_inactive{color:rgba(0, 115, 170, 0.41);}
span.gfp_gfutility_show_feed_on_form__icon img {height:1em;}
span.gfp_gfutility_show_feed_on_form__icon svg{height:1em;width:1em;}
</style>";

		}

	}

	/**
	 * Output title and feeds for a form
	 *
	 * @see GF_Form_List_Table::column_title()
	 *
	 * @since 2.4.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @param $form Form object
	 */
	public function gform_form_list_column_title( $form ) {

		echo '<strong><a href="?page=gf_edit_forms&id='. absint( $form->id ) .'">' . esc_html( $form->title ) . '</a></strong>';

		$this->_calling_get_addons = true;

		$addons = GFPGFU_Helper::get_addons( array('\GV\Import_Entries\GF_System_Status_Screen' ) );

		$this->_calling_get_addons = false;

		$enabled_addons = array();

		foreach( $addons as $slug => $addon_data ) {

			if( array_key_exists( $slug, $enabled_addons ) ) {

				continue;

			}


			$addon_feeds = GFAPI::get_feeds( null, $form->id, $slug );

			if ( is_array( $addon_feeds ) ) {

				$enabled_addons[ $slug ] = array( 'icon' => $this->get_addon_icon( $slug, $addon_data, true ),
				                                  'title' => $addon_data['title'] );
			}
			else {

				$addon_feeds = GFAPI::get_feeds( null, $form->id, $slug, false );

				if( is_array( $addon_feeds ) ) {

					$enabled_addons[ $slug ] = array( 'icon' => $this->get_addon_icon( $slug, $addon_data, false ),
					                                  'title' => $addon_data['title'] );

				}

			}


			unset( $addon_feeds );

		}

		$enabled_addons = apply_filters('gfp_gfutility_enabled_addons_for_form', $enabled_addons, $form );

		if ( ! empty( $enabled_addons ) ) {

			echo "<br /><div class='gfp_gfutility_form_list_addons_with_feeds'>";

			foreach ( $enabled_addons as $slug => $addon_info ) {

				echo "<span class='gfp_gfutility_form_list_feed_addon'><a title='{$addon_info['title']}' href='" . ( empty( $addon_info['link'] ) ? admin_url( "admin.php?page=gf_edit_forms&view=settings&subview={$slug}&id={$form->id}" ) : esc_attr( $addon_info['link'] ) ) . "'>{$addon_info['icon']}</a></span>";

			}

			echo "</div><br />";

		}

	}

	private function get_addon_icon( $slug, $addon_info, $active ) {

		$default_addon_icon = strtoupper($addon_info['title'][0] . $addon_info['title'][1]);

		$open_tag = "<span class='gfp_gfutility_show_feed_on_form__icon gfp_gfutility_feed_addon_" . ( $active ? 'active' : 'inactive' ) . "' >";

		$closing_tag = "</span>";

		$icon = GFCommon::get_icon_markup($addon_info);

		if ( empty( $icon) ) {

			$icon = file_exists(GFP_GF_UTILITY_PATH . 'tools/show-feed-on-form-list/' . $slug . '.svg') ? '<img style="height:1em;" src="' . GFP_GF_UTILITY_URL . 'tools/show-feed-on-form-list/' . $slug . '.svg' . '">' : $default_addon_icon;

		}

		$icon = apply_filters( 'gfp_gfutility_get_addon_icon', $icon, $slug );


		return "{$open_tag}{$icon}{$closing_tag}";
	}

	public function gfp_gfutility_get_addon_icon( $icon, $slug ) {

		switch( $slug ){

			case 'gravityflow':

				if ( function_exists('gravity_flow' ) ) {

					$icon = GFCommon::get_icon_markup( array( 'icon' => file_get_contents( gravity_flow()->get_base_path() . '/images/gravityflow-icon-blue.svg' ) ) );

				}


				break;
		}


		return $icon;
	}

	/**
	 * @param array $addon_data
	 * @param GFFeedAddOn $addon
	 *
	 * @return mixed
	 */
	public function gfp_gfutility_get_addons_addon_data( $addon_data, $addon ) {

		if ( $this->_calling_get_addons ) {

			$menu_icon = $addon->get_menu_icon();

			if ( ( ! empty( $menu_icon ) ) && ( 'dashicons-admin-generic' !== $menu_icon ) ) {

				$icon = $menu_icon;

			} else {

				$form_settings_icon = $addon->form_settings_icon();

					if ( ( ! empty( $form_settings_icon ) ) && ( 'dashicons-admin-generic' !== $form_settings_icon ) ) {

						$icon = $form_settings_icon;

					} else {

						$plugin_settings_icon = $addon->plugin_settings_icon();

						if ( ( ! empty( $plugin_settings_icon ) ) && ( 'dashicons-admin-generic' !== $plugin_settings_icon ) ) {

							$icon = $plugin_settings_icon;

						}

					}

			}

			if ( ! empty( $icon ) ) {

				$addon_data = array_merge( $addon_data, array( 'icon' => $icon ) );

			}

		}


		return $addon_data;
	}

	/**
	 * Add non-feed framework add-ons to the list
	 *
	 * @since 2.4.0
	 *
	 * @param array $enabled_addons
	 * @param object $form
	 *
	 * @return mixed
	 */
	public function gfp_gfutility_enabled_addons_for_form( $enabled_addons, $form ) {

		$enabled_addons = $this->add_gravityview( $enabled_addons, $form->id );

		$enabled_addons = $this->add_gfchart( $enabled_addons, $form->id );

		$enabled_addons = $this->add_gravitypdf( $enabled_addons, $form->id );


		return $enabled_addons;
	}

	/**
	 * Add GravityView
	 *
	 * @since 2.4.0
	 *
	 * @param $enabled_addons
	 * @param $form_id
	 *
	 * @return mixed
	 */
	private function add_gravityview( $enabled_addons, $form_id ) {

		if ( function_exists( 'gravityview_get_connected_views' ) ) {

			$connected_views = gravityview_get_connected_views( $form_id );

			if( ! empty( $connected_views ) ) {

				$can_edit_views = false;

				foreach ( (array) $connected_views as $view ) {

					if( GVCommon::has_cap( 'edit_gravityview', $view->ID ) ) {

						$can_edit_views = true;

						break;
					}

				}

				if ( $can_edit_views ) {

					$enabled_addons[ 'gravityview' ] = array(
						'title' => 'GravityView',
						'icon'  => "<span class='gfp_gfutility_show_feed_on_form__icon  gfp_gfutility_feed_addon_active'><i class=\"fa fa-lg gv-icon-astronaut-head gv-icon\"></i></span>",
						'link'  => admin_url( "edit.php?post_type=gravityview&gravityview_form_id={$form_id}" )
					);

				}

			}

		}


		return $enabled_addons;
	}

	/**
	 * Add GFChart
	 *
	 * @since 2.4.0
	 *
	 * @param $enabled_addons
	 * @param $form_id
	 *
	 * @return mixed
	 */
	private function add_gfchart( $enabled_addons, $form_id ){

		if ( method_exists( 'GFChart_API', 'get_charts' ) ) {

			$charts = GFChart_API::get_charts( $form_id );

			if( ! empty( $charts ) ) {

				/*$can_edit_views = false;

				foreach ( (array) $connected_views as $view ) {

					if( GVCommon::has_cap( 'edit_gravityview', $view->ID ) ) {

						$can_edit_views = true;

						break;
					}

				}*/

				//if ( $can_edit_views ) {
				global $gfp_gfchart;

					$enabled_addons[ 'gfchart' ] = array(
						'title' => 'GFChart',
						'icon'  => "<span class='gfp_gfutility_show_feed_on_form__icon  gfp_gfutility_feed_addon_active'>" . GFCommon::get_icon_markup( array( 'icon' =>  $gfp_gfchart->get_addon_object()->get_menu_icon() ) ) . "</span>",
						'link'  => admin_url( "edit.php?post_type=gfchart" )
					);

				//}

			}

		}


		return $enabled_addons;

	}

	/**
	 * Add GravityPDF
	 *
	 * @since 2.4.0
	 *
	 * @param $enabled_addons
	 * @param $form_id
	 *
	 * @return mixed
	 */
	private function add_gravitypdf( $enabled_addons, $form_id ) {

		global $gfpdf;

		if ( ! empty( $gfpdf->gform ) ) {

			if ( $gfpdf->gform->has_capability( 'gravityforms_edit_settings' ) ) {

				$form = $gfpdf->gform->get_form( $form_id );

				$pdfs = rgar( $form, 'gfpdf_form_settings' );

				if ( ! empty( $pdfs ) ) {

					$active_pdf = false;

					$icon_class = 'gfp_gfutility_show_feed_on_form__icon';

					$active_class = 'gfp_gfutility_feed_addon_active';

					$inactive_class = 'gfp_gfutility_feed_addon_inactive';

					$pdf_short_title = 'Gravity PDF';

					$pdf_icon_markup = "<i class=\"dashicons dashicons-media-document\"></i>";

					if ( class_exists( 'GFPDF\Controller\Controller_Uninstaller' ) ) {

						$pdf_controller_uninstaller = GFPDF\Controller\Controller_Uninstaller::get_instance();

						$pdf_short_title = $pdf_controller_uninstaller->get_short_title();

						$pdf_icon_markup = GFCommon::get_icon_markup( [ 'icon' => $pdf_controller_uninstaller->get_menu_icon() ], 'dashicon-admin-generic' );

					}

					$pdf_info_active = array(
						'title' => $pdf_short_title,
						'icon'  => "<span class='{$icon_class} {$active_class}'>{$pdf_icon_markup}</span>",
					);

					$pdf_info_inactive = array(
						'title' => $pdf_short_title,
						'icon'  => "<span class='{$icon_class} {$inactive_class}'>{$pdf_icon_markup}</span>",
					);

					foreach( $pdfs as $pdf ) {

						if( $active_pdf ) {

							break;

						}


						if ( ! empty( $pdf['active'] ) ) {

							$enabled_addons[ 'PDF' ] = $pdf_info_active;

							$active_pdf = true;

						}
						else {

							$enabled_addons[ 'PDF' ] = $pdf_info_inactive;

						}

						unset( $pdf );

					}

				}

				}

		}


		return $enabled_addons;
	}

}

$gfpgfu_show_feed = new GFPGFU_Show_Feed_On_Form_List();

$gfpgfu_show_feed->run();