<?php

/**
 * Do not show form if the user has already submitted it
 *
 * @since 1.3.0
 * 
 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
 */
class GFPGFU_Hide_Form_User_Submitted {

	/**
	 * GFPGFU_Hide_Form_User_Submitted constructor.
	 */
	public function __construct() {

		$this->add_form_setting();

		$this->add_form_check();

	}

	/**
	 * Add form setting
	 * 
	 * @since
	 * 
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 */
	public function add_form_setting() {

		if ( is_admin() && ( 'settings' == rgget( 'view' ) ) ) {

			add_filter( 'gform_form_settings_fields', array( $this, 'gform_form_settings_fields' ), 10, 2 );

			add_filter( 'gform_pre_form_settings_save', array( $this, 'gform_pre_form_settings_save' ) );

		}

	}

	/**
	 * Add form check
	 * 
	 * @since
	 * 
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 */
	public function add_form_check() {

		add_filter( 'gform_get_form_filter', array( $this, 'gform_get_form_filter' ), 10, 2 );

	}

	/**
	 * Add setting to form settings
	 * 
	 * @since 3.0.0
	 * 
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 * 
	 * @param $settings
	 * @param $form
	 *
	 * @return mixed
	 */
	public function gform_form_settings_fields( $settings, $form ) {

		ob_start();

		include( GFP_GF_UTILITY_PATH . '/tools/hide-form-user-submitted/gform-form-settings.php' );
		

		$settings['restrictions']['fields'][] =

					array(
						'name'    => 'form_hide_form_user_submitted',
						'type'    => 'checkbox',
						'label'   => __( 'Hide form if user already submitted', 'gravityformsutility' ),
						'default_value' => rgar( $form, 'hideFormUserSubmitted' ) ? $form['hideFormUserSubmitted'] : 0,
						'choices' => array(
							array(
								'name'    => 'form_hide_form_user_submitted',
								'label' => __( 'Hide form if logged-in user already submitted this form', 'gravityformsutility' ),
							),
						),
					);

		ob_end_clean();


		return $settings;

	}

	/**
	 * Save form setting
	 * 
	 * @since
	 * 
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 * 
	 * @param $form
	 *
	 * @return mixed
	 */
	function gform_pre_form_settings_save( $form ) {

		$form[ 'hideFormUserSubmitted' ] = rgpost( '_gform_setting_form_hide_form_user_submitted' );


		return $form;

	}


	/**
	 * Empty the form 
	 * 
	 * @since 
	 * 
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 * 
	 * @param string $form_string
	 * @param array  $form
	 *
	 * @return array|null
	 */
	public function gform_get_form_filter( $form_string, $form ) {

		if ( ! rgempty( 'hideFormUserSubmitted', $form ) ) {

			if ( is_null( $this->hide_form_if_user_already_submitted( $form ) ) ) {

				$form_string = '';

			}

		}


		return $form_string;

	}

	/**
	 * Find out if user already has an entry for this form. If so, make the form null.
	 * 
	 * @since
	 * 
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 * 
	 * @param array $form
	 *
	 * @return array|null
	 */
	private function hide_form_if_user_already_submitted( $form ) {

		if ( is_user_logged_in() ) {

			$search_criteria[ 'field_filters' ][ ] = array( 'key' => 'created_by', 'value' => get_current_user_id() );

			$user_entries = GFAPI::count_entries( $form[ 'id' ], $search_criteria );

			if ( 0 < $user_entries ) {

				$form = null;

			}

		}

		return $form;
	}

}

$gfpgfu_hide_form_user_submitted = new GFPGFU_Hide_Form_User_Submitted();