
<div class="row">
	<h3>Product Settings</h3>
	<div class="col-md-12">
		<h4>General Settings</h4>
		<form   action="admin-post.php" method="post" id="settings_tm_ns"> 
			<div class="well">
				<input type="hidden" name="action" value="save_tm_ns_settings"> 
				<input type="hidden" name="current_tab_id" value="<?php echo esc_attr( $current_tab_id . '_general_settings' ); ?>">
				<?php wp_nonce_field(); ?>

				<table class="form-table general-form-table">
					<tbody>
						
						<tr valign="top" class="">
							<th scope="row" class="titledesc">
								Enable Product Sync
								<span class="woocommerce-help-tip" data-tip="To enable product sync"></span>
							</th>
							<td class="forminp forminp-checkbox">
								<input name="enableProductSync" 
								<?php
								if ( isset( $options['enableProductSync'] ) && 'on' == $options['enableProductSync'] ) {
									echo 'checked ';
								}
								?>
								id="enableProductSync" type="checkbox">                        
							</td>
						</tr>
						<tr valign="top" class="">

							<th scope="row" class="titledesc">
								NetSuite identifier for syncing products
							</th>
							<td class="forminp">

								<input  class="input-text" type="text" name="ns_woo_identifier" id="ns_woo_identifier" placeholder="Leave Blank If You Want To Sync All Products" value="<?php isset( $options['ns_woo_identifier'] ) ? esc_attr_e( $options['ns_woo_identifier'] ) : ''; ?>">                      
							</td>
						</tr>

					</tbody>
					<tbody>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for="ns_product_autosync_status">Default Product Status
								</label>
								<div class="tooltip dashicons-before dashicons-editor-help">
									<span class="tooltiptext">Select the status that you want to sync
									</span>
								</div>

							</th>
							<td class="forminp">
								<fieldset>
									<?php
									$product_statuses = TMWNI_Settings::$product_statuses;
									?>
									<select class="input-text  " type="text" name="ns_product_autosync_status" id="ns_product_autosync_status">
										<option value="">Please select</option>
										<?php
										foreach ( $product_statuses as $status_key => $status_label ) :
											?>
																						<option value="<?php echo esc_attr( trim( $status_key ) ); ?>" 
												<?php
												if ( isset( $options['ns_product_autosync_status'] ) && $status_key == $options['ns_product_autosync_status'] ) {
													echo 'selected';
												}
												?>
												>
												<?php
												echo esc_attr( trim( $status_label ) );
												?>
											</option>
											<?php
										endforeach;
										?>
									</select>
								</fieldset>
							</td>
						</tr>
						
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for="productSyncFrequency">Product Sync Frequency</label>

								<div class="tooltip dashicons-before dashicons-editor-help">
									<span class="tooltiptext">Set a frequency for product sync from NetSuite</span>
								</div>
							</th>
							<td class="forminp forminp-select">
								<select name="productSyncFrequency" id="productSyncFrequency" style="" class="">
									<?php
									foreach ( TMWNI_Settings::$inventory_sync_frequency as $inventory_sync_frequency_id => $inventory_sync_frequency_name ) {
										?>
										<option 
										<?php
										if ( isset( $options['productSyncFrequency'] ) && $options['productSyncFrequency'] == $inventory_sync_frequency_id ) {
											echo 'selected ';}
										?>
											value="<?php echo esc_attr( $inventory_sync_frequency_id ); ?>"><?php echo esc_attr( $inventory_sync_frequency_name ); ?> </option>
										<?php } ?>
									</select>
								</td>
							</tr>
							<?php
							/**
							 * Product setting hook.
							 *
							 * @since 1.6.7
							 **/
							do_action( 'tm_ns_after_product_settings' );
							?>

		 </tbody>
		 <tr valign="top">
			 <th scope="row" class="titledesc">
				 <input type="submit" class="button-primary" name="save_post" value="Save Settings" /> 
			 </th>

		 </tr>
	 </table>
 </div>
</form>
<h4>Product Fields Mapping</h4>
<form   action="admin-post.php" method="post" id="settings_tm_ns"> 
	<div class="well">
		<input type="hidden" name="action" value="save_tm_ns_settings"> 
		<input type="hidden" name="current_tab_id" value="
		<?php
		echo esc_attr( $current_tab_id . '_product_mapping' );
		?>
		">
		<?php
		wp_nonce_field();
		?>
		<table class="form-table general-form-table">
			<thead>
				<tr>
					<th>Woo Product Field</th>
					<th>Mapped NetSuite Field</th>
					<th>Checked For Product Creation</th>
					<th>Checked For Product Updation</th>
				</tr>
			</thead>
			<tbody >
				<tr valign="top">
					<th scope="row" class="titledesc">Title</th>
					<td class="forminp">
						<input class="input-text" type="text" name="fields[title][ns_field_title]" 
						value="<?php echo esc_attr( $options['fields']['title']['ns_field_title'] ?? '' ); ?>">
					</td>
					<td class="forminp">
						<input type="checkbox" name="fields[title][create_title]" 
						<?php echo ( isset( $options['fields']['title']['create_title'] ) && 'on' === $options['fields']['title']['create_title'] ) ? 'checked' : ''; ?>>
					</td>
					<td class="forminp">
						<input type="checkbox" name="fields[title][update_title]" 
						<?php echo ( isset( $options['fields']['title']['update_title'] ) && 'on' === $options['fields']['title']['update_title'] ) ? 'checked' : ''; ?>>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row" class="titledesc">Description</th>
					<td class="forminp">
						<input class="input-text" type="text" name="fields[description][ns_field_description]" 
						value="<?php echo esc_attr( $options['fields']['description']['ns_field_description'] ?? '' ); ?>">
					</td>
					<td class="forminp">
						<input type="checkbox" name="fields[description][create_description]" 
						<?php echo ( isset( $options['fields']['description']['create_description'] ) && 'on' === $options['fields']['description']['create_description'] ) ? 'checked' : ''; ?>>
					</td>
					<td class="forminp">
						<input type="checkbox" name="fields[description][update_description]" 
						<?php echo ( isset( $options['fields']['description']['update_description'] ) && 'on' === $options['fields']['description']['update_description'] ) ? 'checked' : ''; ?>>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row" class="titledesc">Short Description</th>
					<td class="forminp">
						<input class="input-text" type="text" name="fields[short_description][ns_field_short_description]" 
						value="<?php echo esc_attr( $options['fields']['short_description']['ns_field_short_description'] ?? '' ); ?>">
					</td>
					<td class="forminp">
						<input type="checkbox" name="fields[short_description][create_short_description]" 
						<?php echo ( isset( $options['fields']['short_description']['create_short_description'] ) && 'on' === $options['fields']['short_description']['create_short_description'] ) ? 'checked' : ''; ?>>
					</td>
					<td class="forminp">
						<input type="checkbox" name="fields[short_description][update_short_description]" 
						<?php echo ( isset( $options['fields']['short_description']['update_short_description'] ) && 'on' === $options['fields']['short_description']['update_short_description'] ) ? 'checked' : ''; ?>>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="titledesc">Tags</th>
					<td class="forminp">
						<input class="input-text" type="text" name="fields[tags][ns_field_tags]" 
						value="<?php echo esc_attr( $options['fields']['tags']['ns_field_tags'] ?? '' ); ?>">
					</td>
					<td class="forminp">
						<input type="checkbox" name="fields[tags][create_tags]" 
						<?php echo ( isset( $options['fields']['tags']['create_tags'] ) && 'on' === $options['fields']['tags']['create_tags'] ) ? 'checked' : ''; ?>>
					</td>
					<td class="forminp">
						<input type="checkbox" name="fields[tags][update_tags]" 
						<?php echo ( isset( $options['fields']['tags']['update_tags'] ) && 'on' === $options['fields']['tags']['update_tags'] ) ? 'checked' : ''; ?>>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="titledesc">Weight</th>
					<td class="forminp">
						<input class="input-text" type="text" name="fields[weight][ns_field_weight]" 
						value="<?php echo esc_attr( $options['fields']['weight']['ns_field_weight'] ?? '' ); ?>">
					</td>
					<td class="forminp">
						<input type="checkbox" name="fields[weight][create_weight]" 
						<?php echo ( isset( $options['fields']['weight']['create_weight'] ) && 'on' === $options['fields']['weight']['create_weight'] ) ? 'checked' : ''; ?>>
					</td>
					<td class="forminp">
						<input type="checkbox" name="fields[weight][update_weight]" 
						<?php echo ( isset( $options['fields']['weight']['update_weight'] ) && 'on' === $options['fields']['weight']['update_weight'] ) ? 'checked' : ''; ?>>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="titledesc">Height</th>
					<td class="forminp">
						<input class="input-text" type="text" name="fields[height][ns_field_height]" 
						value="<?php echo esc_attr( $options['fields']['height']['ns_field_height'] ?? '' ); ?>">
					</td>
					<td class="forminp">
						<input type="checkbox" name="fields[height][create_height]" 
						<?php echo ( isset( $options['fields']['height']['create_height'] ) && 'on' === $options['fields']['height']['create_height'] ) ? 'checked' : ''; ?>>
					</td>
					<td class="forminp">
						<input type="checkbox" name="fields[height][update_height]" 
						<?php echo ( isset( $options['fields']['height']['update_height'] ) && 'on' === $options['fields']['height']['update_height'] ) ? 'checked' : ''; ?>>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="titledesc">Length</th>
					<td class="forminp">
						<input class="input-text" type="text" name="fields[length][ns_field_length]" 
						value="<?php echo esc_attr( $options['fields']['length']['ns_field_length'] ?? '' ); ?>">
					</td>
					<td class="forminp">
						<input type="checkbox" name="fields[length][create_length]" 
						<?php echo ( isset( $options['fields']['length']['create_length'] ) && 'on' === $options['fields']['length']['create_length'] ) ? 'checked' : ''; ?>>
					</td>
					<td class="forminp">
						<input type="checkbox" name="fields[length][update_length]" 
						<?php echo ( isset( $options['fields']['length']['update_length'] ) && 'on' === $options['fields']['length']['update_length'] ) ? 'checked' : ''; ?>>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="titledesc">Width</th>
					<td class="forminp">
						<input class="input-text" type="text" name="fields[width][ns_field_width]" 
						value="<?php echo esc_attr( $options['fields']['width']['ns_field_width'] ?? '' ); ?>">
					</td>
					<td class="forminp">
						<input type="checkbox" name="fields[width][create_width]" 
						<?php echo ( isset( $options['fields']['width']['create_width'] ) && 'on' === $options['fields']['width']['create_width'] ) ? 'checked' : ''; ?>>
					</td>
					<td class="forminp">
						<input type="checkbox" name="fields[width][update_width]" 
						<?php echo ( isset( $options['fields']['width']['update_width'] ) && 'on' === $options['fields']['width']['update_width'] ) ? 'checked' : ''; ?>>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="titledesc">Product Image</th>
					<td class="forminp">
						<input class="input-text" type="text" name="fields[product_image][ns_field_product_image]" 
						value="<?php echo esc_attr( $options['fields']['product_image']['ns_field_product_image'] ?? '' ); ?>">
					</td>
					<td class="forminp">
						<input type="checkbox" name="fields[product_image][create_product_image]" 
						<?php echo ( isset( $options['fields']['product_image']['create_product_image'] ) && 'on' === $options['fields']['product_image']['create_product_image'] ) ? 'checked' : ''; ?>>
					</td>
					<td class="forminp">
						<input type="checkbox" name="fields[product_image][update_product_image]" 
						<?php echo ( isset( $options['fields']['product_image']['update_product_image'] ) && 'on' === $options['fields']['product_image']['update_product_image'] ) ? 'checked' : ''; ?>>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="titledesc">Product Gallery</th>
					<td class="forminp">
						<input class="input-text" type="text" name="fields[product_gallery_images][ns_field_product_gallery_images]" 
						value="<?php echo esc_attr( $options['fields']['product_gallery_images']['ns_field_product_gallery_images'] ?? '' ); ?>">
					</td>
					<td class="forminp">
						<input type="checkbox" name="fields[product_gallery_images][create_product_gallery_images]" 
						<?php echo ( isset( $options['fields']['product_gallery_images']['create_product_gallery_images'] ) && 'on' === $options['fields']['product_gallery_images']['create_product_gallery_images'] ) ? 'checked' : ''; ?>>
					</td>
					<td class="forminp">
						<input type="checkbox" name="fields[product_gallery_images][update_product_gallery_images]" 
						<?php echo ( isset( $options['fields']['product_gallery_images']['update_product_gallery_images'] ) && 'on' === $options['fields']['product_gallery_images']['update_product_gallery_images'] ) ? 'checked' : ''; ?>>
					</td>
				</tr>

				<?php
				/**
				 * Order fulfillment setting hook.
				 *
				 * @since 1.0.0
				 **/
				do_action( 'tm_ns_after_product_mapping_fields' );
				?>
</tbody>
<tbody>
	<tr valign="top">
		<th scope="row" class="titledesc">
			<input type="submit" class="button-primary" name="save_post" value="Save Settings" /> 
		</th>

	</tr>
</tbody>
</table>
</td>
</form>