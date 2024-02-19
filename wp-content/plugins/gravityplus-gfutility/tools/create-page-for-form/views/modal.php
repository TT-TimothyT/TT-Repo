<?php
add_thickbox();
?>
<div id="gfp_gfutil_create_page_modal" style="display:none;">

    <form id="gfp_gfutil_create_page_modal_form">

        <div class="gfp_gfutil_create_page_modal_container">

            <div class="setting-row">

                <label for="new_page_title"><?php esc_html_e( 'New Page Title', 'gravityformsutility' ); ?>

                    <span class="gfield_required">*</span></label><br />

                <input type="text" class="regular-text" value="" id="new_page_title" tabindex="9000">

            </div>

            <div class="submit-row">

				<?php echo '<input id="save_new_page_utility" type="submit" class="button button-large button-primary" value="' . esc_html__( 'Create Page', 'gravityformsutility' ) . '" tabindex="9002" />' ; ?>

                <div id="gfp_gfutility-error" style="display:block;"></div>

            </div>

        </div>

    </form>

</div>