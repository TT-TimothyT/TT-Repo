<form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
<div class="clearfix" id="three" style="padding-top: 20px;">
	<h2>Digioh Settings </h2>
	<?php if(isset($_POST['saveChanges'])){ ?>
    <div class="notice notice-success is-dismissible">
        <p>Changes saved successfully.</p>
    </div>
    <?php } ?>  
	<div class="tab_container">
		<div id="tab1" class="tab_content"> 
			<div class="items product">
				<div class="item rule"> 
						<div class="content" style="padding-top: 20px;">
							<label for="">Client GUID</label> 
							<input type="text" name="oh_client_id" value="<?php echo esc_attr($oh_client_id); ?>" style="width: 350px;"><br/>
						</div> 
				</div> 
				<div class="item rule"> 
					<div class="content" style="padding-top: 20px;">
						<label for="">Tag Type</label> 
						<select name="oh_tagtype" value="<?php echo esc_attr($oh_tagtype); ?>" style="width: 350px;">
							<?php if($options) foreach ($options as $key => $value) {
								?><option value="<?php echo esc_attr($key); ?>" <?php if($key==$oh_tagtype) echo 'selected'; ?>><?php echo esc_html($value); ?></option><?php
							} ?>
						</select><br/>
					</div> 
				</div> 
			</div>
		</div>
	</div>
</div>
<div id="button-wrapper" style="padding-top: 20px;">
	<button class="button button-primary" name="saveChanges" value="yes">Save Changes</button>
</div>
</form>

<p>To get your Client ID, log into <a href=https://account.digioh.com/>Digioh</a> and click on your name top right to open the account menu. You can click to copy your GUID, second from the bottom. It looks like this: b7ff1936-c035-4b50-b1c8-a453a8494733</p>

<p>If you don't have an account, go to <a href="https://www.digioh.com/">digioh.com</a> and click Request Demo.</p>

<ul>
<li><strong>Low Impact</strong> - Minimizes the performance impact to your page load by delaying download of Digioh. Not recommended if you have inline boxes above the fold.</li>
<li><strong>Fast Activation</strong> - Initializes Digioh immediately on DOM ready, to ensure Boxes display quickly.</li>
<li><strong>Site Compatibility</strong> - Use only if you are having problems with Digioh. This is uncommon. Requires WordPress 5.7 or greater.</li>
</ul>

<p>More information is available on the <a href=https://help.digioh.com/>Digioh help site</a>. You can reach Digioh Support by emailing contact@digioh.com</p>