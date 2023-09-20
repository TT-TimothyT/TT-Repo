<?php
if ( ! defined( 'ABSPATH' ) ) {
     exit;
 }  
// var_dump($info);                                          
$name=$this->post('name',$info);    
 ?>
  <div class="crm_fields_table">
    <div class="crm_field">
  <div class="crm_field_cell1"><label for="vx_name"><?php esc_html_e("Account Name",'gravity-forms-netsuite-crm'); ?></label>
  </div>
  <div class="crm_field_cell2">
  <input type="text" name="crm[name]" value="<?php echo !empty($name) ? esc_attr($name) : 'Account #'.esc_attr($id); ?>" id="vx_name" class="crm_text">

  </div>
  <div class="clear"></div>
  </div>
  
    <!---div class="crm_field">
  <div class="crm_field_cell1"><label for="vx_api"><?php esc_html_e("Integration Method",'gravity-forms-netsuite-crm'); ?></label>
  </div>
  <div class="crm_field_cell2">
  <label for="vx_api"><input type="radio" name="crm[api]" value="" id="vx_api" class="vx_tabs_radio" <?php if($this->post('api',$info) != "token"){echo 'checked="checked"';} ?>> <?php esc_html_e('Username/Password Auth','gravity-forms-netsuite-crm'); ?></label>
  <label for="vx_web" style="margin-left: 15px;"><input type="radio" name="crm[api]" value="token" id="vx_web" class="vx_tabs_radio" <?php if($this->post('api',$info) == 'token'){echo 'checked="checked"';} ?>> <?php esc_html_e('Token Based Auth','gravity-forms-netsuite-crm');  ?></label> 
  </div>
  <div class="clear"></div>
  </div---->
  
  <div class="vx_tabs" id="tab_vx_web" style="<?php //if($this->post('api',$info) != "token"){echo 'display:none';} ?>">
  <div class="crm_field">
  <div class="crm_field_cell1"><label for="vx_key"><?php esc_html_e('Consumer Key','contact-form-netsuite-crm'); ?></label></div>
  <div class="crm_field_cell2">

  <input type="text" id="vx_key" name="crm[key]" class="crm_text req" placeholder="<?php esc_html_e('Consumer Key','contact-form-netsuite-crm'); ?>" value="<?php echo esc_html($this->post('key',$info)); ?>">
 <span class="howto"><?php echo sprintf(__('Go to Setup -> Integration -> Manage Integration -> New , enter App Name and check "Token Based Auth", %sView ScreenShots%s ','gravity-forms-netsuite-crm'),'<a href="https://www.crmperks.com/connect-netsuite-to-wordpress/" target="_blank">','</a>'); ?></span>
  </div>
  <div class="clear"></div>
  </div>
 <div class="crm_field">
  <div class="crm_field_cell1"><label for="vx_sec"><?php esc_html_e('Consumer Secret','contact-form-netsuite-crm'); ?></label></div>
  <div class="crm_field_cell2">
  <div class="vx_tr" >
  <div class="vx_td">
  <input type="password" id="vx_sec" name="crm[secret]" class="crm_text req" placeholder="<?php esc_html_e('Consumer Secret','contact-form-netsuite-crm'); ?>" value="<?php echo esc_html($this->post('secret',$info)); ?>">
  </div>
  <div class="vx_td2">
  <a href="#" class="button vx_toggle_btn vx_toggle_key" title="<?php esc_html_e('Toggle Key','contact-form-netsuite-crm'); ?>"><?php esc_html_e('Show Key','contact-form-netsuite-crm') ?></a>
  
  </div>
  </div>
  </div>
  <div class="clear"></div>
  </div>
  
  <div class="crm_field">
  <div class="crm_field_cell1"><label for="vx_token"><?php esc_html_e('Token ID','contact-form-netsuite-crm'); ?></label></div>
  <div class="crm_field_cell2">

  <input type="text" id="vx_token" name="crm[token]" class="crm_text req" placeholder="<?php esc_html_e('Token ID','contact-form-netsuite-crm'); ?>" value="<?php echo esc_html($this->post('token',$info)); ?>">
 <span class="howto"><?php esc_html_e('Go to Setup -> Users/Roles -> Access Tokens -> New , select application , user and role','gravity-forms-netsuite-crm'); ?></span>
  </div>
  <div class="clear"></div>
  </div>
 <div class="crm_field">
  <div class="crm_field_cell1"><label for="vx_token_sec"><?php esc_html_e('Token Secret','contact-form-netsuite-crm'); ?></label></div>
  <div class="crm_field_cell2">
  <div class="vx_tr" >
  <div class="vx_td">
  <input type="password" id="vx_token_sec" name="crm[token_secret]" class="crm_text req" placeholder="<?php esc_html_e('Token Secret','contact-form-netsuite-crm'); ?>" value="<?php echo esc_html($this->post('token_secret',$info)); ?>">
  </div>
  <div class="vx_td2">
  <a href="#" class="button vx_toggle_btn vx_toggle_key" title="<?php esc_html_e('Toggle Key','contact-form-netsuite-crm'); ?>"><?php esc_html_e('Show Key','contact-form-netsuite-crm') ?></a>
  
  </div>
  </div>
  </div>
  <div class="clear"></div>
  </div>
  
  </div>

  <!--div class="vx_tabs" id="tab_vx_api" style="<?php if($this->post('api',$info) == "token"){echo 'display:none';} ?>">
 <div class="crm_field">
  <div class="crm_field_cell1"><label for="vx_email"><?php esc_html_e('Email','contact-form-netsuite-crm'); ?></label></div>
  <div class="crm_field_cell2">

  <input type="email" id="vx_email" name="crm[email]" class="crm_text" placeholder="<?php esc_html_e('Netsuite Login email','contact-form-netsuite-crm'); ?>" value="<?php echo esc_html($this->post('email',$info)); ?>" required>

  </div>
  <div class="clear"></div>
  </div>
 <div class="crm_field">
  <div class="crm_field_cell1"><label for="vx_pass"><?php esc_html_e('Password','contact-form-netsuite-crm'); ?></label></div>
  <div class="crm_field_cell2">
  <div class="vx_tr" >
  <div class="vx_td">
  <input type="password" id="vx_pass" name="crm[pass]" class="crm_text" placeholder="<?php esc_html_e('Password','contact-form-netsuite-crm'); ?>" value="<?php echo esc_html($this->post('pass',$info)); ?>" required>
  </div>
  <div class="vx_td2">
  <a href="#" class="button vx_toggle_btn vx_toggle_key" title="<?php esc_html_e('Toggle Key','contact-form-netsuite-crm'); ?>"><?php esc_html_e('Show Key','contact-form-netsuite-crm') ?></a>
  
  </div>
  </div>
  </div>
  <div class="clear"></div>
  </div> 
 <div class="crm_field">
  <div class="crm_field_cell1"><label for="vx_app"><?php esc_html_e('Application Id','contact-form-netsuite-crm'); ?></label></div>
  <div class="crm_field_cell2">

  <input type="text" id="vx_app" name="crm[app_id]" class="crm_text" placeholder="<?php esc_html_e('Application Id','contact-form-netsuite-crm'); ?>" value="<?php echo esc_html($this->post('app_id',$info)); ?>" required>
  <span class="howto"><?php esc_html_e('Goto Setup -> Integrations -> Manage Integrations. Here you can create new application or use id of any application','gravity-forms-netsuite-crm'); ?></span>
  </div>
  <div class="clear"></div>
  </div>

  </div---->
   <div class="crm_field">
  <div class="crm_field_cell1"><label for="vx_url"><?php esc_html_e('Netsuite URL','contact-form-netsuite-crm'); ?></label></div>
  <div class="crm_field_cell2">

  <input type="url" id="vx_url" name="crm[url]" class="crm_text" placeholder="https://system.na1.netsuite.com" value="<?php echo esc_html($this->post('url',$info)); ?>" required>
 <span class="howto"><?php esc_html_e('Enter Netsuite system url, not webservice or sandbox url','gravity-forms-netsuite-crm'); ?></span>
  </div>
  <div class="clear"></div>
  </div>
  
     <div class="crm_field">
  <div class="crm_field_cell1"><label for="vx_url_web"><?php esc_html_e('Netsuite WebService URL','contact-form-netsuite-crm'); ?></label></div>
  <div class="crm_field_cell2">

  <input type="url" id="vx_url_web" name="crm[service_url]" class="crm_text" placeholder="https://xxxxxx.suitetalk.api.netsuite.com" value="<?php echo esc_html($this->post('service_url',$info)); ?>">
 <span class="howto"><?php esc_html_e('E.g. https://ACCOUNT_ID.suitetalk.api.netsuite.com','gravity-forms-netsuite-crm'); ?></span>
  </div>
  <div class="clear"></div>
  </div>
  
 <div class="crm_field">
  <div class="crm_field_cell1"><label for="vx_role"><?php esc_html_e('Netsuite User Role','contact-form-netsuite-crm'); ?></label></div>
  <div class="crm_field_cell2">

  <input type="text" id="vx_role" name="crm[role]" class="crm_text" placeholder="3" value="<?php echo esc_html($this->post('role',$info)); ?>">
 <span class="howto"><?php esc_html_e('Enter Netsuite user role Id (optional).','gravity-forms-netsuite-crm'); ?></span>
  </div>
  <div class="clear"></div>
  </div>
  <div class="crm_field">
  <div class="crm_field_cell1"><label for="vx_account"><?php esc_html_e('Account Id','contact-form-netsuite-crm'); ?></label></div>
  <div class="crm_field_cell2">

  <input type="text" id="vx_account" name="crm[account_id]" class="crm_text" placeholder="<?php esc_html_e('Account Id','contact-form-netsuite-crm'); ?>" value="<?php echo esc_html($this->post('account_id',$info)); ?>" required>
  <span class="howto"><?php esc_html_e('Goto Setup -> Integration -> Web Services Preferences','gravity-forms-netsuite-crm'); ?></span>
  </div>
  <div class="clear"></div>
  </div>
  
    <?php if(isset($info['api_token'])  && $info['api_token']!="") { ?>
      <div class="crm_field">
  <div class="crm_field_cell1"><label><?php esc_html_e("Test Connection",'gravity-forms-netsuite-crm'); ?></label></div>
  <div class="crm_field_cell2">      <button type="submit" class="button button-secondary" name="vx_test_connection"><i class="fa fa-refresh"></i> <?php esc_html_e("Test Connection",'gravity-forms-netsuite-crm'); ?></button>
  </div>
  <div class="clear"></div>
  </div> 
  <?php
    }
  ?>
  
  <div class="crm_field">
  <div class="crm_field_cell1"><label for="vx_error_email"><?php esc_html_e("Notify by Email on Errors",'gravity-forms-netsuite-crm'); ?></label></div>
  <div class="crm_field_cell2"><textarea name="crm[error_email]" id="vx_error_email" placeholder="<?php esc_html_e("Enter comma separated email addresses",'gravity-forms-netsuite-crm'); ?>" class="crm_text" style="height: 70px"><?php echo isset($info['error_email']) ? esc_html($info['error_email']) : ""; ?></textarea>
  <span class="howto"><?php esc_html_e("Enter comma separated email addresses. An email will be sent to these email addresses if an order is not properly added to Netsuite. Leave blank to disable.",'gravity-forms-netsuite-crm'); ?></span>
  </div>
  <div class="clear"></div>
  </div>
  
 <div class="crm_field">
  <div class="crm_field_cell1"><label for="vx_cache">
  <?php esc_html_e("Remote Cache Time", 'gravity-forms-netsuite-crm'); ?>
  </label>
 </div>
 <div class="crm_field_cell2">
    <div style="display: table">
  <div style="display: table-cell; width: 85%;">
  <select id="vx_cache" name="crm[cache_time]" style="width: 100%">
  <?php
  $cache=array("60"=>"One Minute (for testing only)","3600"=>"One Hour","21600"=>"Six Hours","43200"=>"12 Hours","86400"=>"One Day","172800"=>"2 Days","259200"=>"3 Days","432000"=>"5 Days","604800"=>"7 Days","18144000"=>"1 Month");
  if($this->post('cache_time',$info) == ""){
   $info['cache_time']="86400";
  }
  foreach($cache as $secs=>$label){
   $sel="";
   if($this->post('cache_time',$info) == $secs){
       $sel='selected="selected"';
   }
  echo '<option value="'.esc_attr($secs).'" '.$sel.' >'.esc_html($label).'</option>';     
  }   
  ?>
  </select></div><div style="display: table-cell;">
  <button name="vx_tab_action" value="refresh_lists_<?php echo esc_attr($this->id) ?>" class="button" style="margin-left: 10px; vertical-align: baseline; width: 110px" autocomplete="off" title="<?php esc_html_e('Refresh Picklists','gravity-forms-netsuite-crm'); ?>">Refresh Now</button>
  </div></div>
  <span class="howto">
  <?php esc_html_e("How long should form and field data be stored? This affects how often remote picklists will be checked for the Live Remote Field Mapping feature. This is an advanced setting. You likely won't need to change this.",'gravity-forms-netsuite-crm'); ?>
  </span></div>
  </div>  
  
  

<p class="submit">
  <button type="submit" value="save" class="button-primary" title="<?php esc_html_e('Save Changes','gravity-forms-netsuite-crm'); ?>" name="save"><?php esc_html_e('Save Changes','gravity-forms-netsuite-crm'); ?></button></p>  
  </div>  
  <script type="text/javascript">
  jQuery(document).ready(function($){
      verify_req();
  $(".vx_tabs_radio").click(function(){
  $(".vx_tabs").hide();   
  $("#tab_"+this.id).show(); 
  verify_req();  
  });
  function verify_req(){
      $(".vx_tabs .crm_text").removeAttr('required');
      $(".vx_tabs:visible").find(".crm_text").attr('required','required');
  } 
  });
  </script>
 