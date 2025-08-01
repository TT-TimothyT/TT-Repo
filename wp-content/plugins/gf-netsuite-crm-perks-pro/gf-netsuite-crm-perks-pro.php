<?php
/**
* Plugin Name: Gravity Forms Netsuite Plugin
* Description: Integrates Gravity Forms with Netsuite allowing form submissions to be automatically sent to your Netsuite account 
* Version: 1.5.8
* Requires at least: 3.8
* Tested up to: 6.4
* Author URI: https://www.crmperks.com
* Plugin URI: https://www.crmperks.com/plugins/gravity-forms-plugins/gravity-forms-netsuite-plugin/
* Author: CRM Perks.
* Text Domain: gravity-forms-netsuite-crm
* Domain Path: /languages/ 
*/
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


if( !class_exists( 'vxg_netsuite' ) ):


class vxg_netsuite {

  
 public  $url = 'https://www.crmperks.com';

  public  $crm_name = 'netsuite';
  public  $id = 'vxg_netsuite';
  public  $domain = 'vxg-netsuite';
  public  $version = "1.5.8";
  public  $update_id = '30006';
  public  $min_gravityforms_version = '1.3.9';
  public $type = 'vxg_netsuite_pro';
  public  $fields = null;
  public  $data = null;

  private $filter_condition;
  private $plugin_dir= '';
  private $temp= '';
  private $crm_arr= false;
  private $entry;
  private $form;
  public $notice_js= false;
  public static $title='Gravity Forms Netsuite Plugin';  
  public static $path = ''; 
  public static $slug = '';
  public static $debug_html = '';
  public static $save_key='';  
  public static  $lic_msg = '';
  public static $db_version='';  
  public static $vx_plugins;  
  public static $note;
  public static $feeds_res;    
  public static $gf_status='';    
  public static $plugin='';    
  public static $gf_status_msg='';
  public static $api_timeout;      

 public function instance(){
  
       self::$path=$this->get_base_path(); 
  add_action( 'plugins_loaded', array( $this, 'setup_main' ) );
register_deactivation_hook(__FILE__,array($this,'deactivate'));
register_activation_hook(__FILE__,(array($this,'activate')));


 } 
  
  /**
  * Plugin starting point. Will load appropriate files
  * 
  */
  public  function init(){

      self::$gf_status= $this->gravity_forms_status();
    if(self::$gf_status !== 1){
  add_action( 'admin_notices', array( $this, 'install_gf_notice' ) );
  $slug=$this->get_slug(); 
add_action( 'after_plugin_row_'.$slug, array( $this, 'install_gf_notice_plugin_row' ) );    
  return;
  } 

       
      //plugin api
$this->plugin_api(true);
//tracking
require_once(self::$path . "includes/add-ons.php");
require_once(self::$path . "includes/crmperks-gf.php");
require_once(self::$path . "includes/plugin-pages.php");  

  }
   /**
  * install plugin
  * 
  */
  public function setup_main(){
  include_once(self::$path. "includes/edit-form.php");
        //handling post submission.
    add_action('gform_entry_created', array($this, 'gf_entry_created_before'), 99, 2); 
  //added via GF API
  add_action("gform_post_add_entry", array($this, 'gf_entry_created_before'), 40, 2);

//  add_action("gform_post_payment_status", array($this, 'gf_entry_paid'), 10, 2); //$feed,$entry
add_action("gform_post_payment_completed", array($this, 'gf_entry_paid_normal'), 10, 2); //$entry,$pay_info
    
add_action('gform_after_submission', array($this, 'gf_entry_created_after'), 99, 2); 
//add_action("gform_post_add_subscription_payment", array($this, 'gf_entry_paid_subscription'), 10, 2); //$entry,$pay_info

    add_filter("gform_confirmation", array($this, 'confirmation_error'));
        add_filter("gform_custom_merge_tags", array($this, 'add_tags'),10,4);
    add_filter( 'gform_replace_merge_tags', array($this,'replace_tags'), 10, 7 );

      if(is_admin()){
add_action('init', array($this,'init'));   
            //loading translations
  load_plugin_textdomain('gravity-forms-netsuite-crm', FALSE,  $this->plugin_dir_name(). '/languages/' );
  
  self::$db_version=get_option($this->type."_version");
  if(self::$db_version != $this->version && current_user_can( 'manage_options' )){
  $data=$this->get_data_object();
  $data->update_table();
  update_option($this->type."_version", $this->version);
  //add post permissions
  require_once(self::$path . "includes/install.php"); 
  $install=new vxg_install_netsuite();
  $install->create_roles();   
    $log_str="Installing ".self::$title."  version=".$this->version;
  $this->log_msg($log_str);
  }

  } 
  }
  
 public  function plugin_api($start_instance=false){
     $file=self::$path . "includes/plugin-api.php";
    if(!class_exists('vxcf_plugin_api') && file_exists($file)){   
require_once($file);
}
if(class_exists('vxcf_plugin_api')){
 $slug=$this->get_slug();
 $settings_link=$this->link_to_settings();
 $is_plugin_page=$this->is_crm_page(); 
self::$plugin=new vxcf_plugin_api($this->id,$this->version,$this->type,$this->domain,$this->update_id,self::$title,$slug,self::$path,$settings_link,$is_plugin_page);
if($start_instance){
self::$plugin->instance();
}
} }

public function add_tags( $merge_tags, $form_id, $fields, $element_id ) {
      $data_db=$this->get_data_object(); 
  $feeds=$data_db->get_feed_by_form($form_id,true);
  foreach($feeds as $v){
    $merge_tags[] = array('label' => substr($v['name'],0,20).' Netsuite Link', 'tag' => '{netsuitelink_'.$v['id'].'}');
  }
    return $merge_tags;
}
public function replace_tags( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {
 
        $custom_merge_tag = '{netsuitelink_';
     if ( strpos( $text, $custom_merge_tag ) === false ) {
        return $text;
    }
    if(!empty($form['id'])){
      $data_db=$this->get_data_object(); 
  $feeds=$data_db->get_feed_by_form($form['id'],true);
$tags=array();
  foreach($feeds as $v){
      $id=$v['id'];
      if( !empty(self::$feeds_res[$id]) ){
 $link= !empty(self::$feeds_res[$id]['link']) ? self::$feeds_res[$id]['link'] : '#'.self::$feeds_res[$id]['id'];         
   $tags['{netsuitelink_'.$v['id'].'}']=$link;   
      }
  }
 
 $text = str_replace( array_keys($tags), array_values($tags), $text );
    }   
     return $text;
}

  public function install_gf_notice(){
        $message=self::$gf_status_msg;
  if(!empty($message)){
  $this->display_msg('admin',$message,'gravity'); 
     $this->notice_js=true; 
  
  }
  }
  /**
  * Install Gravity Forms Notice (plugin row)
  * 
  */
  public function install_gf_notice_plugin_row(){
  $message=self::$gf_status_msg;
  if(!empty($message)){
   $this->display_msg('',$message,'gravity');
  } 
  }
  /**
  * display admin notice
  * 
  * @param mixed $type
  * @param mixed $message
  * @param mixed $id
  */
  public function display_msg($type,$message,$id=""){
  //exp 
  global $wp_version;
  $ver=floatval($wp_version);
  if($type == "admin"){
     if($ver<4.2){
  ?>
    <div class="error vx_notice notice" data-id="<?php echo esc_html($id) ?>"><p style="display: table"><span style="display: table-cell; width: 98%"><span class="dashicons dashicons-megaphone"></span> <b><?php esc_html_e('Gravity Forms Netsuite Plugin','gravity-forms-netsuite-crm') ?>. </b><?php echo wp_kses_post($message);?> </span>
<span style="display: table-cell; padding-left: 10px; vertical-align: middle;"><a href="#" class="notice-dismiss" title="<?php esc_html_e('Dismiss Notice','gravity-forms-netsuite-crm') ?>">dismiss</a></span> </p></div>
  <?php
     }else{
  ?>
  <div class="error vx_notice notice is-dismissible" data-id="<?php echo esc_html($id) ?>"><p><span class="dashicons dashicons-megaphone"></span> <b><?php esc_html_e('Gravity Forms Netsuite Plugin','gravity-forms-netsuite-crm') ?>. </b> <?php echo wp_kses_post($message);?> </p>
  </div>    
  <?php
     }
  }else{
  ?>
  <tr class="plugin-update-tr"><td colspan="5" class="plugin-update">
  <style type="text/css"> .vx_msg a{color: #fff; text-decoration: underline;} .vx_msg a:hover{color: #eee} </style>
  <div style="background-color: rgba(224, 224, 224, 0.5);  padding: 9px; margin: 0px 10px 10px 28px "><div style="background-color: #d54d21; padding: 5px 10px; color: #fff" class="vx_msg"> <span class="dashicons dashicons-info"></span> <?php echo wp_kses_post($message) ?>
</div></div></td></tr>
  <?php
  }   
  }
   /**
  * admin_screen_message function.
  * 
  * @param mixed $message
  * @param mixed $level
  */
  public  function screen_msg( $message, $level = 'updated') {
  echo '<div class="'. esc_attr( $level ) .' fade notice is-dismissible"><p>';
  echo wp_kses_post($message);
  echo '</p></div>';
  } 



/**
* Gravity forms status
* 
*/
  public  function gravity_forms_status() {
  
  $installed = 0;
  if(!class_exists('RGForms')) {
  if(file_exists(WP_PLUGIN_DIR.'/gravityforms/gravityforms.php')) {
  $installed=2;   
  }
  }else{
  $installed=1;
  if(!$this->is_gravityforms_supported()){
  $installed=3;   
  }      
  }
  if($installed !=1){
    if($installed === 0){ // not found
  $message = sprintf(__("%sGravity Forms%s is required. %sPurchase it today!%s", 'gravity-forms-netsuite-crm'), "<a href='http://www.gravityforms.com/'>", "</a>", "<a href='http://www.gravityforms.com/'>", "</a>");   
  }else if($installed === 2){ // not active
  $message = sprintf(__('Gravity Forms is installed but not active. %sActivate Gravity Forms%s to use the Gravity Forms Netsuite Plugin','gravity-forms-netsuite-crm'), '<strong><a href="'.wp_nonce_url(admin_url('plugins.php?action=activate&plugin=gravityforms/gravityforms.php'), 'activate-plugin_gravityforms/gravityforms.php').'">', '</a></strong>');  
  } else if($installed === 3){ // not supported
  $message = sprintf(__("A higher version of %sGravity Forms%s is required. %sPurchase it today!%s", 'gravity-forms-netsuite-crm'), "<a href='http://www.gravityforms.com/'>", "</a>", "<a href='http://www.gravityforms.com/'>", "</a>");
  }
  self::$gf_status_msg=$message;
  }else if (!class_exists('SoapClient') ) {
  self::$gf_status_msg = 'The SOAP PHP extension is not loaded. Please modify the extension settings in php.ini accordingly.';
  $installed=4; 
  }
  return $installed;   
  }
  /**
  * display error to admin only in form front
  * 
  * @param mixed $confirmation
  * @param mixed $form
  * @param mixed $lead
  * @param mixed $ajax
  */
  public  function confirmation_error($confirmation, $form = '', $lead = '', $ajax ='' ) {
  if(current_user_can('administrator') && !empty($_REQUEST['VXGNetsuiteError'])) { 
  $confirmation .= sprintf(__('%sThe entry was not added to Netsuite because: %s. %sYou are only being shown this because you are an administrator. Other users will not see this message.%s', 'gravity-forms-netsuite-crm'), '<div class="error" style="text-align:center; color:#790000; font-size:14px; line-height:1.5em; margin-bottom:16px;background-color:#FFDFDF; margin-bottom:6px!important; padding:6px 6px 4px 6px!important; border:1px dotted #C89797">','<strong>'.esc_html($_REQUEST['VXGNetsuiteError']). '</strong>', '<br /><em>', '</em></div>');
  }
  return $confirmation;
  }
  
  /**
  * Returns true if the current page is an Feed pages. Returns false if not
  * 
  * @param mixed $page
  */
  public  function is_crm_page($page=""){
  if(empty($page)) {
  $page = $this->post("page");
  }
  if(isset($_GET['subview'])){
   $page = $this->post("subview");   
  }else if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'get_field_map_'.$this->id){
  $page=$this->id;    
  }
  
  return $page == $this->id;
  } 
  /**
  * Called when entry is manually updated in the Single Entry view of Gravity Forms.
  * 
  * @param mixed $form
  * @param mixed $entry_id
  */
  public  function manual_export( $form, $entry_id = NULL ) {

  global $plugin_page;

  // Is this the Gravity Forms entries page?
  if(false === ($this->is_gravity_page('gf_entries') && rgget("view") == 'entry' && (rgget('lid') || !rgblank(rgget('pos'))))) {
  return;
  }
  $entry=array();
  // Both admin_init and gforms_after_update_entry will have this set
  if( empty( $_POST['gforms_save_entry'] ) || empty( $_POST['action'] ) ) { return; }
  
  // Different checks since admin_init runs in both cases but we need to wait for entry update
  $current_hook = current_filter(); 

  if( $current_hook == 'admin_init' && empty( $_POST[$this->id.'_send'] ) ) { return; } 
  if( $current_hook == 'gform_after_update_entry' && empty( $_POST[$this->id.'_update'] ) ) { return; }
  
  // Verify authenticity of request
  check_admin_referer('gforms_save_entry', 'gforms_save_entry');
  
  // For admin_init hook, get the entry ID from the URL
  if(empty($entry_id)) {
  $entry_id = rgget('lid');
  $form_id = rgget('id');
  
  // fetch alternative entry id: look for gf list details when using pagination
  if(empty($entry_id)) {
  $entry_id=$this->get_entry_id($form_id);
  }
  $form = RGFormsModel::get_form_meta($form_id);
  }
  $entry=$this->get_gf_entry($entry_id);
  if(!current_user_can($this->id."_send_to_crm")){ 
         return;  
       }
       
  // Export the entry
  $push=$this->push($entry, $form,"",true); 
    if(!empty($push['msg'])){
  $this->screen_msg($push['msg'],$push['class']);  
  }
  // Don't send twice.
  unset($_POST[$this->id.'_update']);
  unset($_POST[$this->id.'_send']);
  }
  /**
  * get entry id 
  * 
  * @param mixed $form_id
  */
public function get_entry_id($form_id){
    $entry_id='';
  $position = rgget('pos');
  $paging = array('offset' => $position, 'page_size' => 1);
  
  $entries = GFAPI::get_entries($form_id, array(), null, $paging);
  
  if(!empty($entries)) { 
  // pluck first entry to use id from, should always only be one
  $entry = array_shift($entries);
  $entry_id = $entry['id'];
  } 
  
  return $entry_id;
}
  /**
  * Get Objects from local options or from Netsuite
  *     
  * @param mixed $check_option
  * @return array
  */
  public function get_objects($info="",$refresh=false){
$objects=array('Customer'=>'Customer','Contact'=>'Contact','Task'=>'Task','PhoneCall'=>'Phone Call','SupportCase'=>'Support Case' );  //,'Opportunity'=>'Opportunity'   
  return $objects;    
 }
  
 

  /**
  * settings link
  * 
  * @param mixed $escaped
  */
  public  function link_to_settings( $escaped = true ) {
  
  $url = admin_url('admin.php?page=gf_settings&subview='.$this->id);
  
  return  $url;
  }

  /**
  * Get CRM info
  * 
  */
  public function get_info($id){
$data=$this->get_data_object();
      $info=$data->get_account($id);
  $info_arr=$data=array();  $meta=array(); 
if(is_array($info)){
if(!empty($info['data'])){ 

    $info['data']=trim($info['data']);  
    if(strpos($info['data'],'{') !== 0){
        $info['data']=$this->de_crypt($info['data']);
    }
  $info_arr=json_decode($info['data'],true);
if(!is_array($info_arr)){
  $info_arr=array();
}
}
$info_arr['time']=$info['time']; 
$info_arr['id']=$info['id']; 
 $info['data']=$info_arr;
if(!empty($info['meta'])){ 
  $meta=json_decode($info['meta'],true); 
}
$info['meta']=is_array($meta) ? $meta : array();   

if(!empty($info['time'])){ 
$info['time']=strtotime($info['time']); 
}
}
  return $info;    
  }
  /**
  * update account
  * 
  * @param mixed $data
  * @param mixed $id
  */
  public function update_info($data,$id) {

if(empty($id)){
    return;
}

 $time = current_time( 'mysql' ,1);

  $sql=array('updated'=>$time);
  if(is_array($data)){

  
    if(isset($data['meta'])){
  $sql['meta']= json_encode($data['meta']);    
  }
  if( isset($data['data']) && is_array($data['data'])){
      $_data=$this->get_data_object();
     $acount=$_data->get_account($id);
     if(empty($acount['time'])){
  $sql['time']= $time;      
  } 
  $sql['status']='2';
  if(isset($data['data']['class'])){
  $sql['status']= $data['data']['class'] == 'updated' ? '1' : '2'; 
  }
  if(isset($data['data']['meta'])){
      unset($data['data']['meta']);
  }
  if(isset($data['data']['status'])){
      unset($data['data']['status']);
  }
  if(isset($data['data']['name'])){
     $sql['name']=$data['data']['name']; 
  // unset($data['data']['name']);
  }else if(isset($_GET['id'])){
      $sql['name']="Account #".$this->post('id');  
  }
  
     $enc_str=json_encode($data['data']);
 // $enc_str=$this->en_crypt($enc_str);
  $sql['data']=$enc_str;
  }
  } 


 $data=$this->get_data_object();
$result = $data->update_account($sql,$id);

return $result;
}

  /**
  * gravity forms field values, modify check boxes etc
  * 
  * @param mixed $entry
  * @param mixed $form
  * @param mixed $gf_field_id
  * @param mixed $crm_field_id
  * @param mixed $custom
  */
  public  function verify_field_val($entry,$form,$gf_field_id,$crm_field_id="",$custom=""){
  $value=false;
/*  if(empty($field)){
      return $value;
  }*/

  if(isset($entry[$gf_field_id])){  
  $value=maybe_unserialize($entry[$gf_field_id]);
  $val=true;
       if(is_numeric($gf_field_id)){
  $field = RGFormsModel::get_field($form, $gf_field_id);
if( (isset($field->storageType) && $field->storageType == 'json') || $field->type == 'fileupload' ){
   $value=json_decode($value,1);   
  }
  
   if($field->type == 'date' ){
    $formats=array('mdy'=>'m/d/Y','dmy'=>'d/m/Y','dmy_dash'=>'d-m-Y','dmy_dot'=>'d.m.Y','ymd_slash'=>'Y/m/d','ymd_dash'=>'Y-m-d','ymd_dot'=>'Y.m.d');
    if( !empty($field->dateFormat) && isset($formats[$field->dateFormat])){
   $value=date($formats[$field->dateFormat],strtotime($value));     
    }   
   }else if($field->type == 'list' && is_array($value)){
       $v_temp=array();
       foreach($value as $v){
           if(is_array($v)){
           $v=trim(implode(', ',array_values($v)));    
           }
        $v_temp[]=$v;   
       }
     $value=trim(implode(" - \n",$v_temp));  
   } }
   
  }else{ //check if full address
      if($gf_field_id=='entry_url'){
  $value=add_query_arg(array('page'=>'gf_entries','view'=>'entry','lid'=>$entry['id'],'id'=>$entry['form_id']), admin_url('admin.php'));
   $val=true;
    }else if($gf_field_id=="form_title"){
  $value=$form['title'];
  $val=true;
  }else{
   $field = RGFormsModel::get_field($form, $gf_field_id);
  if($field['type'] == "address"){
  $address_type="";
  if($crm_field_id!=""){ //nimble address accepts json only
  $address_type=isset($custom[$crm_field_id]['type']) && $custom[$crm_field_id]['type'] == "address" ? "json" :  "";
  }
  $value=$this->get_address($entry,$gf_field_id,$address_type);  
  $val=true;
  }else{
  // This is for checkboxes
  $elements = array();
 foreach($entry as $key => $val_e) {
      if(is_numeric($key) && floor($key) == floor($gf_field_id) && !empty($val_e)) { 
          $elements[] = htmlspecialchars($val_e);
      }}
  if(count($elements)>0){
  $value=$elements;   $val=true;
  }        
  }
  }
  }
  if($value && is_array($value)){
 if(count($value) == 1){
     $value=$value[0];
 }else{
 // $value=implode(", ",$value); disabled for allowing multiselect array pass via online forms 
  } }
  return $value;        
  }
  /**
  * filter enteries
  * 
  * @param mixed $feed
  * @param mixed $entry
  * @param mixed $form
  */
  public  function check_filter($feed,$entry,$form){
  $filters=$this->post('filters',$feed);
  $final=$this->filter_condition=null;
  if(is_array($filters)){
   $time=current_time('timestamp'); 
   foreach($filters as $filter_s){
  $check=null; $and=null;  
  if(is_array($filter_s)){
  foreach($filter_s as $filter){
  $field=$filter['field'];
  $fval=$filter['value'];
  $val=$this->verify_field_val($entry,$form,$field);
  switch($filter['op']){
  case"is": $check=$fval == $val;     break;
  case"is_not": $check=$fval != $val;     break;
  case"contains": $check=strpos($val,$fval) !==false;     break;
  case"not_contains": $check=strpos($val,$fval) ===false;     break;
  case"is_in": $check=strpos($fval,$val) !==false;     break;
  case"not_in": $check=strpos($fval,$val) ===false;     break;
  case"starts": $check=strpos($val,$fval) === 0;     break;
  case"not_starts": $check=strpos($val,$fval) !== 0;     break;
  case"ends": $check=(strpos($val,$fval)+strlen($fval)) == strlen($val);  break;
  case"not_ends": $check=(strpos($val,$fval)+strlen($fval)) != strlen($val);  break;
  case"less": $check=(float)$val<(float)$fval; break;
  case"greater": $check=(float)$val>(float)$fval;  break;
  case"less_date": $check=strtotime($val,$time) < strtotime($fval,$time);  break;
  case"greater_date": $check=strtotime($val,$time) > strtotime($fval,$time);  break;
  case"equal_date": $check=strtotime($val,$time) == strtotime($fval,$time);  break;
  case"empty": $check=$val == "";  break;
  case"not_empty": $check=$val != "";  break;
  }
  $and_c[]=array("check"=>$check,"field_val"=>$fval,"input"=>$val,"field"=>$field,"op"=>$filter['op']);
  if($check !== null){
  if($and !== null){
  $and=$and && $check;    
  }else{
  $and=$check;    
  }   
  }  
  } //end and loop filter
  }
  if($and !== null){
  if($final !== null){
  $final=$final || $and;  
  }else{
  $final=$and;
  }    
  }
    $this->filter_condition[]=$and_c;
  } // end or loop
  }
  return $final === null ? true : $final;
  }
  
  /**
  * get address components
  *  
  * @param mixed $entry
  * @param mixed $field_id
  * @param mixed $type
  */
  private  function get_address($entry, $field_id,$type=""){
  $street_value = str_replace("  ", " ", trim($entry[$field_id . ".1"]));
  $street2_value = str_replace("  ", " ", trim($entry[$field_id . ".2"]));
  $city_value = str_replace("  ", " ", trim($entry[$field_id . ".3"]));
  $state_value = str_replace("  ", " ", trim($entry[$field_id . ".4"]));
  $zip_value = trim($entry[$field_id . ".5"]);
  if(method_exists('GF_Field_Address','get_country_code')){
  $field_c=new GF_Field_Address();
  $country_value=$field_c->get_country_code(trim($entry[$field_id . ".6"]));
  }else{
  $country_value = GFCommon::get_country_code(trim($entry[$field_id . ".6"]));       
  }
  $country =trim($entry[$field_id . ".6"]);
  $address = $street_value;
  $address .= !empty($address) && !empty($street2_value) ? "  $street2_value" : $street2_value;
  if($type =="json"){
  $arr=array("street"=>$address,"city"=>$city_value,"state"=>$state_value,"zip"=>$zip_value,"country"=>$country);
  return json_encode($arr);
  }
  $address .= !empty($address) && (!empty($city_value) || !empty($state_value)) ? "  $city_value" : $city_value;
  $address .= !empty($address) && !empty($city_value) && !empty($state_value) ? "  $state_value" : $state_value;
  $address .= !empty($address) && !empty($zip_value) ? "  $zip_value" : $zip_value;
  $address .= !empty($address) && !empty($country_value) ? "  $country_value" : $country_value;
  
  return $address;
  }
  /**
  * if gravity forms page
  * 
  * @param mixed $page
  */
  public  function is_gravity_page($page = array()){
  if(!class_exists('RGForms')) { return false; }
  $current_page = trim(strtolower(RGForms::get("page")));
  if(empty($page)) {
  $gf_pages = array("gf_edit_forms","gf_new_form","gf_entries","gf_settings","gf_export","gf_help");
  } else {
  $gf_pages = is_array($page) ? $page : array($page);
  }
  
  return in_array($current_page, $gf_pages);
  }
  /**
  * Add checkbox to entry info - option to send entry to crm
  * 
  * @param mixed $form_id
  * @param mixed $lead
  */
  public  function entry_info_send_checkbox( $form_id, $lead ) {
  
  // If this entry's form isn't connected to crm, don't show the checkbox
  if(!$this->show_send_to_crm_button() ) { return; }
  
  // If this is not the Edit screen, get outta here.
  if(empty($_POST["screen_mode"]) || $_POST["screen_mode"] === 'view') { return; }
  
   if(!current_user_can($this->id."_send_to_crm")){return; }
  
  if( apply_filters( $this->id.'_show_manual_export_button', true ) ) {
  printf('<input type="checkbox" name="'.$this->id.'_update" id="'.$this->id.'_update" value="1" /><label for="'.$this->id.'_update" title="%s">%s</label><br /><br />', esc_html__('Create or update this entry in Netsuite. The fields will be mapped according to the form feed settings.', 'gravity-forms-netsuite-crm'), esc_html__('Send to Netsuite', 'gravity-forms-netsuite-crm'));
  } else {
  echo '<input type="hidden" name="'.$this->id.'_update" id="'.$this->id.'_update" value="1" />';
  }
  }
  /**
  * Add button to entry info - option to send entry to crm
  * 
  * @param mixed $button
  */
  public  function entry_info_send_button( $button = '' ) {
  // If this entry's form isn't connected to crm, don't show the button
  if(!$this->show_send_to_crm_button()) { return $button; }
if(!current_user_can($this->id."_send_to_crm")){return; }
  // Is this the view or the edit screen?
  $mode = empty($_POST["screen_mode"]) ? "view" : $this->post("screen_mode");
  if($mode === 'view') {
            $margin="";
      if(defined("vx_btn")){
      $margin="margin-top: 5px;";    
      }else{define('vx_btn','true');}
  $button.= '<input type="submit" class="button button-large button-secondary alignright" name="'.$this->id.'_send" style="margin-left:5px; '.$margin.'" title="'.__('Create or update this entry in Netsuite. The fields will be mapped according to the form feed settings.','gravity-forms-netsuite-crm').'" value="'.__('Send to Netsuite', 'gravity-forms-netsuite-crm').'" onclick="jQuery(\'#action\').val(\'send_to_crm\')" />';
  //logs button

      $entry_id=$this->post('lid');
      $form_id = rgget('id');
      if(empty($entry_id)){
          $entry_id=$this->get_entry_id($form_id);
      }
      $log_url=admin_url( 'admin.php?page=gf_edit_forms&view=settings&subview='.$this->id.'&tab=log&id='.$_GET['id'].'&entry_id='.$entry_id);  
    $button.= '<a class="button button-large button-secondary alignright" style="margin-left:5px; margin-top:5px; " title="'.__('Go to Netsuite Logs','gravity-forms-netsuite-crm').'" href="'.$log_url.'">'.__('Netsuite Logs','gravity-forms-netsuite-crm').'</a>';
  
  } 
  return $button;
  }
  /**
  * Whether to show the Entry "Send to CRM" button or not
  *
  * If the entry's form has been mapped to CRM feed, show the Send to CRM button. Otherwise, don't.
  *
  * @return boolean True: Show the button; False: don't show the button.
  */
  public  function show_send_to_crm_button() {
  
  $form_id = rgget('id');
  
  return $this->has_feed($form_id);
  }
  /**
  * Does the current form have a feed assigned to it?
  * @param  INT      $form_id Form ID
  * @return boolean
  */
  function has_feed($form_id) {
  $data=$this->get_data_object();
  $feeds = $data->get_feed_by_form( $form_id , true);
  
  return !empty($feeds);
  }
  
  /**
  * Add note to GF Entry
  * @param int $id   Entry ID
  * @param string $note Note text
  */
  private function add_note($id, $note) {
  
  RGFormsModel::add_note($id, 0, esc_html__('Gravity Forms Netsuite Plugin','gravity-forms-netsuite-crm'), $note);
  }
  
  /**
  * if gravity forms installed and supported
  * 
  */
  private  function is_gravityforms_supported(){
  if(class_exists("GFCommon")){
  $is_correct_version = version_compare(GFCommon::$version, $this->min_gravityforms_version, ">=");
  return $is_correct_version;
  }
  else{
  return false;
  }
  }
  /**
  * uninstall plugin
  * 
  */
  public  function uninstall(){
  //droping all tables
 require_once(self::$path . "includes/install.php"); 
  $install=new vxg_install_netsuite();
    do_action('uninstall_vx_plugin_'.$install->id);
  $install->remove_data();
  }
    /**
  * email validation
  * 
  * @param mixed $email
  */
  public function is_valid_email($email){
         if(function_exists('filter_var')){
      if(filter_var($email, FILTER_VALIDATE_EMAIL)){
      return true;    
      }
       }else{
       if(strpos($email,"@")>1){
      return true;       
       }    
       }
   return false;    
  }
  /**
  * deactivate
  * 
  * @param mixed $action
  */
  public function deactivate($action="deactivate"){ 
  do_action('plugin_status_'.$this->type,$action);
  }
  /**
  * activate plugin
  * 
  */
  public function activate(){ 
$this->plugin_api(true);
do_action('plugin_status_'.$this->type,'activate');  
  }
    /**
  * Send CURL Request
  * 
  * @param mixed $body
  * @param mixed $path
  * @param mixed $method
  */
  public function request($path="",$method='POST',$body="",$head=array()) {

        $args = array(
            'body' => $body,
            'headers'=> $head,
            'method' => strtoupper($method), // GET, POST, PUT, DELETE, etc.
            'sslverify' => false,
            'timeout' => 20,
        );

       $response = wp_remote_request($path, $args);

        if(is_wp_error($response)) { 
            return  $response->get_error_message();
            
        }
   $result=wp_remote_retrieve_body($response);
        return $result;
    }

  /**
  * Adds feed tooltips to the list of tooltips
  * 
  * @param mixed $tooltips
  */
  public  function tooltips($tooltips){
  $crm_tooltips = array(
    'vx_feed_name' => '<h6>' . esc_html__('Feed Name', 'gravity-forms-netsuite-crm') . '</h6>' . esc_html__('Enter feed name of your choice.', 'gravity-forms-netsuite-crm'),
  'vx_sel_object' => '<h6>' .__('Netsuite Object', 'gravity-forms-netsuite-crm') . '</h6>' . esc_html__('Select the Object to Create when a Form is Submitted.', 'gravity-forms-netsuite-crm'),
   'vx_sel_account' => '<h6>' .__('Netsuite Account', 'gravity-forms-netsuite-crm') . '</h6>' . esc_html__('Select the Netsuite account you would like to export entries to.', 'gravity-forms-netsuite-crm'),
  'vx_sel_form' => '<h6>' . esc_html__('Gravity Form', 'gravity-forms-netsuite-crm') . '</h6>' . esc_html__('Select the Gravity Form you would like to integrate with Netsuite. Contacts generated by this form will be automatically added to your Netsuite account.', 'gravity-forms-netsuite-crm'),
  
  'vx_map_fields' => '<h6>' . esc_html__('Map Standard Fields', 'gravity-forms-netsuite-crm') . '</h6>' . esc_html__('Associate your Netsuite fields to the appropriate Gravity Form fields.', 'gravity-forms-netsuite-crm'),
  
  'vx_optin_condition' => '<h6>' . esc_html__('Opt-In Condition', 'gravity-forms-netsuite-crm') . '</h6>' . esc_html__('When the opt-in condition is enabled, form submissions will only be exported to Netsuite when the condition is met. When disabled all form submissions will be exported.', 'gravity-forms-netsuite-crm'),
  
  'vx_manual_export' => '<h6>' . esc_html__('Manual Export', 'gravity-forms-netsuite-crm') . '</h6>' . esc_html__('If you do not want all entries sent to Netsuite, but only specific, approved entries, check this box. To manually send an entry to Netsuite, go to Entries, choose the entry you would like to send to Netsuite, and then click the "Send to Netsuite" button.', 'gravity-forms-netsuite-crm'),
  
    'vx_entry_notes' => '<h6>' . esc_html__('Entry Notes', 'gravity-forms-netsuite-crm') . '</h6>' . esc_html__('Enable this option if you want to synchronize Gravity Forms entry notes to Netsuite Object notes. For example , when you add a note to a Gravity Forms entry, it will be added to the Netsuite Object selected in the feed.', 'gravity-forms-netsuite-crm'),
    
      'vx_primary_key' => '<h6>' . esc_html__('Primary Key', 'gravity-forms-netsuite-crm') . '</h6>' . esc_html__('Which field should be used to update existing objects?', 'gravity-forms-netsuite-crm'),
    

    'vx_status_check'=>'<h6>' . esc_html__('Enable Status', 'gravity-forms-netsuite-crm') . '</h6>' .__('Enable this option if you want to assign a status to customer.','gravity-forms-netsuite-crm'),  
    'vx_folders_list'=>'<h6>' . esc_html__('Netsuite Folders', 'gravity-forms-netsuite-crm') . '</h6>' .__('Get Folders from netsuite.','gravity-forms-netsuite-crm'),
      
'vx_status_list'=>'<h6>' . esc_html__('Netsuite Status List', 'gravity-forms-netsuite-crm') . '</h6>' .__('Get Lead Status list from netsuite.','gravity-forms-netsuite-crm'),
'vx_sel_status'=>'<h6>' . esc_html__('Select Status', 'gravity-forms-netsuite-crm') . '</h6>' .__('Which Status should be assigned to this object.','gravity-forms-netsuite-crm'),
'vx_priority'=>'<h6>' . esc_html__('Select Ticket Priority', 'gravity-forms-netsuite-crm') . '</h6>' .__('Which Priority should be assigned to this object.','gravity-forms-netsuite-crm'),
'vx_status'=>'<h6>' . esc_html__('Select Ticket Status', 'gravity-forms-netsuite-crm') . '</h6>' .__('Which Status should be assigned to this object.','gravity-forms-netsuite-crm'),
'vx_type'=>'<h6>' . esc_html__('Select Ticket Type', 'gravity-forms-netsuite-crm') . '</h6>' .__('What should be Ticket Type.','gravity-forms-netsuite-crm'),

 'vx_assign_company'=>'<h6>' . esc_html__('Assign Company', 'gravity-forms-netsuite-crm') . '</h6>' .__('Enable this option if you want to assign an company to this object.','gravity-forms-netsuite-crm'),
   'vx_sel_company'=>'<h6>' . esc_html__('Select Company', 'gravity-forms-netsuite-crm') . '</h6>' .__('Select Company feed. Company created by this feed will be assigned to this object','gravity-forms-netsuite-crm'),


   'vx_assign_person'=>'<h6>' . esc_html__('Netsuite Person', 'gravity-forms-netsuite-crm') . '</h6>' .__('Enable this option if you want to assign a person to this object.','gravity-forms-netsuite-crm'),
   
      'vx_assign_check'=>'<h6>' . esc_html__('Assign Person', 'gravity-forms-netsuite-crm') . '</h6>' .__('Enable this option if you want to assign a user to this conversation.','gravity-forms-netsuite-crm'),
   'vx_sel_person'=>'<h6>' . esc_html__('Select Person', 'gravity-forms-netsuite-crm') . '</h6>' .__('Person created by selected feed will be added to this feed Object.','gravity-forms-netsuite-crm'),
   
   
   'vx_owner_check'=>'<h6>' . esc_html__('Assign Owner', 'gravity-forms-netsuite-crm') . '</h6>' .__('Enable this option if you want to assign an owner to this object.','gravity-forms-netsuite-crm'),
   
   'vx_owners'=>'<h6>' . esc_html__('Netsuite Users', 'gravity-forms-netsuite-crm') . '</h6>' .__('Get Users list from Netsuite','gravity-forms-netsuite-crm'),
   
      'vx_sel_owner'=>'<h6>' . esc_html__('Select Owner', 'gravity-forms-netsuite-crm') . '</h6>' .__('Select a user as a owner of this object','gravity-forms-netsuite-crm'),
   
      'vx_box_check'=>'<h6>' . esc_html__('Add to mailbox', 'gravity-forms-netsuite-crm') . '</h6>' .__('Enable this option if you want to add conversation in a mailbox.','gravity-forms-netsuite-crm'),
   
   'vx_boxes'=>'<h6>' . esc_html__('Netsuite Mailboxes', 'gravity-forms-netsuite-crm') . '</h6>' .__('Get mailboxes from Netsuite','gravity-forms-netsuite-crm'),
   
      'vx_sel_folder'=>'<h6>' . esc_html__('Select Folder', 'gravity-forms-netsuite-crm') . '</h6>' .__('Select a folder where you want to add uploaded files.','gravity-forms-netsuite-crm'),
   
   
   
   'vx_order_notes'=>'<h6>' . esc_html__('Entry Notes', 'gravity-forms-netsuite-crm') . '</h6>' .__('Enable this option if you want to synchronize Entry notes to Netsuite Object notes. For example, when you add a note to a Entry, it will be added to the Netsuite Object selected in the feed.','gravity-forms-netsuite-crm'),
  

         'vx_entry_note'=>'<h6>' . esc_html__('Entry Note', 'gravity-forms-netsuite-crm') . '</h6>' .__('Check this option if you want to send more data as CRM entry note.', 'gravity-forms-netsuite-crm'),
   'vx_note_fields'=>'<h6>' . esc_html__('Note Fields', 'gravity-forms-netsuite-crm') . '</h6>' .__('Select fields which you want to send as a note', 'gravity-forms-netsuite-crm'),
   'vx_disable_note'=>'<h6>' . esc_html__('Note Fields', 'gravity-forms-netsuite-crm') . '</h6>' .__('Enable this option if you want to add note only for new CRM entry', 'gravity-forms-netsuite-crm')
    
    );
  return  array_merge($tooltips,$crm_tooltips);
  }
 
  /**
  * Formates User Informations and submitted form to string
  * This string is sent to email and netsuite
  * @param  array $info User informations 
  * @param  bool $is_html If HTML needed or not 
  * @return string formated string
  */
  public  function format_user_info($info,$is_html=false){
  $str=""; $file="";
  if($is_html){
  if(file_exists(self::$path."templates/email.php")){    
  ob_start();
  include_once(self::$path."templates/email.php");
  $file= ob_get_contents(); // data is now in here
  ob_end_clean();
  }
  if(trim($file) == "")
  $is_html=false;
  }
  if(isset($info['info']) && is_array($info['info'])){
  if($is_html){
  if(isset($info['info_title'])){
  $str.='<tr><td style="font-family: Helvetica, Arial, sans-serif;background-color: #C35050; height: 36px; color: #fff; font-size: 24px; padding: 0px 10px">'.$info['info_title'].'</td></tr>'."\n";
  }
  if(is_array($info['info']) && count($info['info'])>0){
  $str.='<tr><td style="padding: 10px;"><table border="0" cellpadding="0" cellspacing="0" width="100%;"><tbody>';      
  foreach($info['info'] as $f_k=>$f_val){
  $str.='<tr><td style="padding-top: 10px;color: #303030;font-family: Helvetica;font-size: 13px;line-height: 150%;text-align: right; font-weight: bold; width: 28%; padding-right: 10px;">'.$f_k.'</td><td style="padding-top: 10px;color: #303030;font-family: Helvetica;font-size: 13px;line-height: 150%;text-align: left; word-break:break-all;">'.$f_val.'</td></tr>'."\n";      
  }
  $str.="</table></td></tr>";             
  }
  }else{
  if(isset($info['title']))
  $str.="\n".$info['title']."\n";    
  foreach($info['info'] as $f_k=>$f_val){
  $str.=$f_k." : ".$f_val."\n";      
  }
  }
  }
  if($is_html){
  $str=str_replace(array("{title}","{msg}","{sf_contents}"),array($info['title'],$info['msg'],$str),$file);
  }
  return $str;   
  }
 

  /**
  * if plugin user is valid
  * 
  * @param mixed $update
  */
  
  public function is_valid_user($update){
  return is_array($update) && isset($update['user']['user']) && $update['user']['user']!=""&& isset($update['user']['expires']);
  }     
  /**
  * Get variable from array
  *  
  * @param mixed $key
  * @param mixed $arr
  */
  public function post($key, $arr="") {
  if(is_array($arr)){
  return isset($arr[$key])  ? $arr[$key] : "";
  }
  //clean when getting extrenals
  return isset($_REQUEST[$key]) ? $this->clean($_REQUEST[$key]) : "";
  }
public function clean($var){
    if ( is_array( $var ) ) {
        return array_map( array($this,'clean'), $var );
    } else {
        return sanitize_text_field(wp_unslash($var));
    }
}/**
  * Get WP Encryption key
  * @return string Encryption key
  */
  public static  function get_key(){
  $k='Wezj%+l-x.4fNzx%hJ]FORKT5Ay1w,iczS=DZrp~H+ve2@1YnS;;g?_VTTWX~-|t';
  if(defined('AUTH_KEY')){
  $k=AUTH_KEY;
  }
  return substr($k,0,30);        
  }
  /**
  * check if other version of this plugin exists
  * 
  */
  public function other_plugin_version(){ 
  $status=0;
  if(class_exists('vxg_netsuite_wp')){
      $status=1;
  }else if( file_exists(WP_PLUGIN_DIR.'/gravity-forms-netsuite-crm/gravity-forms-netsuite-crm.php')) {
  $status=2;
  } 
  return $status;
  }
    /**
  * Get time Offset 
  * 
  */
  public function time_offset(){
 $offset = (int) get_option('gmt_offset');
  return $offset*3600;
  } 
  /**
  * Decrypts Values
  * @param array $info Netsuite encrypted API info 
  * @return array API settings
  */
  public static function de_crypt($info){
  $info=trim($info);
  if($info == "")
  return '';
  $str=base64_decode($info);
  $key=self::get_key();
      $decrypted_string='';
     if(function_exists("openssl_encrypt") && strpos($str,':')!==false ) {
$method='AES-256-CBC';
$arr = explode(':', $str);
 if(isset($arr[1]) && $arr[1]!=""){
 $decrypted_string=openssl_decrypt($arr[0],$method,$key,false,base64_decode($arr[1]) );    
 }
 }else{
     $decrypted_string=$str;
 }
  return $decrypted_string;
  }   
  /**
  * Encrypts Values
  * @param  string $str 
  * @return string Encrypted Value
  */
  public static function en_crypt($str){
  $str=trim($str);
  if($str == "")
  return '';
  $key=self::get_key();
if(function_exists("openssl_encrypt")) {
$method='AES-256-CBC';
$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
$enc_str=openssl_encrypt($str,$method, $key,false,$iv);
$enc_str.=":".base64_encode($iv);
  }else{
      $enc_str=$str;
  }
  $enc_str=base64_encode($enc_str);
  return $enc_str;
  }
  
  /**
  * Get variable from array
  *  
  * @param mixed $key
  * @param mixed $key2
  * @param mixed $arr
  */
  public function post2($key,$key2, $arr="") {
  if(is_array($arr) && isset($arr[$key]) && is_array($arr[$key])){
  return isset($arr[$key][$key2])  ? $arr[$key][$key2] : "";
  }
  return isset($_REQUEST[$key][$key2]) && is_array($_REQUEST[$key]) ? $this->clean($_REQUEST[$key][$key2]) : "";
  }
  /**
  * Get variable from array
  *  
  * @param mixed $key
  * @param mixed $key2
  * @param mixed $arr
  */
  public function post3($key,$key2,$key3, $arr="") {
  if(is_array($arr)){
  return isset($arr[$key][$key2][$key3])  ? $arr[$key][$key2][$key3] : "";
  }
  return isset($_REQUEST[$key][$key2][$key3]) ? $this->clean($_REQUEST[$key][$key2][$key3]) : "";
  }
  /**
  * get base url
  * 
  */
  public function get_base_url(){
  return plugin_dir_url(__FILE__);
  }
    /**
  * get plugin direcotry name
  * 
  */
  public function plugin_dir_name(){
  if(!empty($this->plugin_dir)){
  return $this->plugin_dir;
  }
  if(empty(self::$path)){
  self::$path=$this->get_base_path(); 
  }
  $this->plugin_dir=basename(self::$path);
  return $this->plugin_dir;
  }
  /**
  * get plugin slug
  *  
  */
  public function get_slug(){
  return plugin_basename(__FILE__);
  }
public function do_actions(){
     if(!is_object(self::$plugin) ){ $this->plugin_api(); }
      if(method_exists(self::$plugin,'valid_addons')){
       return self::$plugin->valid_addons();  
      }
    
   return false;   
  }
  /**
  * Returns the physical path of the plugin's root folder
  * 
  */
  public function get_base_path(){
  return plugin_dir_path(__FILE__);
  }
 /**
  * Writes an error message to the Gravity Forms log.
  * 
  */
  public function log_msg($message,$type=""){
        if (class_exists("GFLogging")) {
            GFLogging::include_logger();
            $slug=$this->plugin_dir_name();
            $log_type=KLogger::DEBUG;
            if($type == "error"){
            $log_type=KLogger::ERROR;   
            }
            GFLogging::log_message($slug, $message,$log_type);
        }
    }
    /**
  * get api object
  * 
  * @param mixed $settings
  * @return vxg_api_netsuite
  */
  public  function get_api($crm=""){
  $api = false;
  if(!class_exists("vxg_netsuite_api"))
  require_once($this->get_base_path()."api/api.php");
     
  $this->api=$api= new vxg_netsuite_api($crm);
  
  return $api;
  }
  /**
  * get gravity forms entry
  * 
  */
  public function get_gf_entry($entry_id){
      $entry=array();
  // Fetch entry (use new GF API from version 1.8)
  if( class_exists( 'GFAPI' ) && !empty( $entry_id ) ) {
  $entry = GFAPI::get_entry( $entry_id );
  } elseif( class_exists( 'RGFormsModel' ) && !empty( $entry_id ) ) {
  $entry = RGFormsModel::get_lead( $entry_id );
  }
  return $entry;
  }
    /**
  * get data object
  * 
  */
  public function get_data_object(){
  require_once(self::$path . "includes/data.php");     
  if(!is_object($this->data))
  $this->data=new vxg_netsuite_data();
  return $this->data;
  }
    public function gf_entry_created_before($entry, $form){
      $this->gf_entry_created($entry, $form);  
    }
    public function gf_entry_created_after($entry, $form){
      $this->gf_entry_created($entry, $form,'after_submit');  
    }
  /**
  * gravity forms entry created
  * 
  * @param mixed $entry
  * @param mixed $form
  */
  public function gf_entry_created( $entry, $form, $event='submit'){

      if(is_array($entry) && isset($entry['status']) && $entry['status'] == 'active' && empty($entry['partial_entry_percent'])){
      
        $entry_id=$this->post('id',$entry);
        if($this->do_actions()){
     do_action('vx_addons_save_entry',$entry_id,$entry,'gf',$form);   
        }
      $this->push($entry,$form,$event,false);     
      }
  }
  
  public function gf_entry_paid_normal($entry,$pay_info){
     $this->gf_entry_paid($entry,$pay_info); 
  }  
  public function gf_entry_paid_subscription($entry,$pay_info){
     $this->gf_entry_paid($entry,$pay_info,'subscription_paid'); 
  }
   public function gf_entry_paid($entry,$pay_info,$event='paid'){
    // if($entry['payment_status'] == 'Paid'){
        $entry_id=$this->post('id',$entry);
        $form=array('id'=>$entry['form_id'],'title'=>'form id '.$entry['form_id']);
   if(!empty($feed['meta']['feedName'])){
            $form['title']=$feed['meta']['feedName'];
        }
        if($this->do_actions()){
     do_action('vx_addons_save_entry',$entry_id,$entry,'gf',$form);   
        }
      $this->push($entry,$form,'paid',false);
   //  }     
  }

  /**
  * push form data to crm
  * 
  * @param mixed $entry
  * @param mixed $form
  * @param mixed $is_admin
  */
  public  function push($entry, $form,$event="",$is_admin=false,$log=""){ 

     $data_db=$this->get_data_object(); 
     $log_id='';   $feeds_meta=array();
   if(!empty($log)){
          if(isset($log['id'])){
       $log_id=$log['id'];
       }
       $log_feed=$data_db->get_feed($log['feed_id']);
   if(!empty($log_feed)){
       $feeds_meta=array($log_feed);
   }
   }else{   
  //get feeds of a form
  $feeds=$data_db->get_feed_by_form($entry['form_id'],true);
 
  if(is_array($feeds) && count($feeds)>0){
  $k=1000; $e=2000; $i=1;
    foreach($feeds as $feed){
          $data=isset($feed['data']) ? json_decode($feed['data'],true) : array(); 
  $meta=isset($feed['meta']) ? json_decode($feed['meta'],true) : array();
  $feed['meta']=$meta;
  $feed['data']=$data;
$object=$this->post('object',$feed); 
if(!empty($data['contact_check'])){
  $feeds_meta[$e++]=$feed;   //add case with contact at end for company
 }
 else if( !empty($data['deal_check']) || !empty($data['person_check']) || !empty($data['company_check'])){

  $feeds_meta[$k++]=$feed; 
 
 }else{
     $feeds_meta[$i++]=$feed; 
 }
    }
       ksort($feeds_meta); 
  // 
  }
   }
//var_dump($feeds_meta); die();
      $form_id=0;
 if(isset($form['id'])){
    $form_id=$form['id']; 
 }

  $entry_id=$this->post('id',$entry);
  if(isset($entry['__vx_id'])){
   $entry_id=$entry['__vx_id'];   
  }else{
$entry=apply_filters('vx_crm_post_fields',$entry,$entry_id,'gf',$form); 
  }

   $screen_msg_class="updated"; $notice="";
  if(is_array($feeds_meta) && count($feeds_meta)>0){
  foreach($feeds_meta as $feed){
        $temp=array();
  $force_send=false;
      $post_comment=true;
      $screen_msg="";
      $parent_id=0;
                   if(isset($entry['__vx_parent_id'])){
  $parent_id=$entry['__vx_parent_id'];  
}
  $object=$this->post('object',$feed);
  if(empty($object)){
      continue;
  }   
  $data=$feed['data']; 
  $meta=$feed['meta'];  
if( in_array($event,array('restore','update','delete','add_note','delete_note'))){
$is_admin=true;
$search_object=$object;
if(in_array($event,array('add_note','delete_note')) && !empty($log)){
   self::$note=array('id'=>$log['parent_id']);
   if($event == 'add_note'){
        $note=json_decode($log['data'],true); 
        if(!empty($note['title']['value'])){
      self::$note['title']=$note['title']['value'];
      self::$note['body']=$note['body']['value'];
        }
   } 
}
   if($event == 'delete_note' && !empty(self::$note)){
         $parent_id=self::$note['id'];
   }
 
    if(in_array($event,array('delete_note','add_note'))){
        //check feed
    $order_notes=$this->post('entry_notes',$data); //if notes sync not enabled in feed return

    if( empty($order_notes)){
        continue;
    }
         //change main object to Note
         $feed['related_object']=$object;
        $object=$feed['object']='Note';   
 }
 if($event == 'delete_note'){
//when deleting note search note object 
     $search_object='Note';
 }
 $_data=$this->get_data_object();
$feed_log=$_data->get_feed_log($feed['id'],$entry_id,$search_object,$parent_id); 

 
 if($event == 'restore' && $feed_log['status'] != 5) { // only allow successfully deleted records
     continue;
 }
  if( in_array($event,array('update','delete') ) && !in_array($feed_log['status'],array(1,2) )  ){ // only allow successfully sent records
     continue;
 }

if(empty($feed_log['crm_id']) || empty($feed_log['object']) || $feed_log['object'] != $search_object){
    
   continue; 
}
//if($event !='restore'){
 $feed['crm_id']=$feed_log['crm_id'];
    unset($data['primary_key']);
//}

   $feed['event']=$event;  
// add note and save related extra info
 if( $event == 'add_note' && !empty(self::$note)){
         $temp=array('title'=>array('value'=>self::$note['title']),'body'=>array('value'=>self::$note['body']),'parent_id'=>array('value'=> $feed['crm_id']),'object'=>array('value'=> $search_object)); 

$parent_id=self::$note['id']; 
$object_link=$feed_log['crm_id'];
 $feed['note_object_link']='<a href="'.$feed_log['link'].'" target="_blank">'.$feed_log['crm_id'].'</a>';
 } 
 // delete not and save extra info
 if( $event == 'delete_note'){
     
     $feed_log_arr= json_decode($feed_log['extra'],true);
     if(isset($feed_log_arr['note_object_link'])){
         $feed['note_object_link']=$feed_log_arr['note_object_link'];
     }
  $temp=array('parent_id'=>array('value'=> $feed['crm_id']));     
 }
 //delete object
 if( $event == 'delete'){
   $temp=array('Id'=>array('value'=> $feed['crm_id']));      
 }
//
  if(!in_array($event , array('update','restore') )){ 
     //do not apply filters when adding note , deleting note , entry etc
      $force_send=true;   
  } 
   if($event == 'restore'){ // send as new entry
    unset($feed['crm_id']);   
   }
        //do not post comment in al other cases 
     $post_comment=false; 

 } 

 $feed_event=$this->post('manual_export',$data);
 if(!$is_admin){
  if($event == 'submit' && $feed_event != ''){ //if manual export is yes
  continue;   
  } 
    if($event == 'after_submit' && $feed_event != '4'){ 
  continue;   
  } 
  if($event == 'subscription_paid' && $feed_event != '3'){ 
  continue;   
  } 
    if($event == 'paid' && $feed_event != '2'){ // only process paid event, if set in feed
  continue;   
  } 
 }

if(!$force_send && isset($data['map']) && is_array($data['map']) && count($data['map'])>0){
$custom= isset($meta['fields']) && is_array($meta['fields']) ? $meta['fields'] : array();
  foreach($data['map'] as $k=>$v){ 
  $value=false; 
  if(!empty($v)){ //if value not empty
    if($this->post('type',$v) == "value"){ //custom value
  $value=trim($this->post('value',$v)); 
  //starts with { and ends } , any char in brackets except {
  preg_match_all('/\{[^\{]+\}/',$value,$matches);
  if(!empty($matches[0])){
      $vals=array();
   foreach($matches[0] as $m){
       $m=trim($m,'{}'); 
    $vals['{'.$m.'}']=$this->verify_field_val($entry,$form,$m,$k,$custom);    
   }
   
  $value=str_replace(array_keys($vals),array_values($vals),$value);    
  }

  }else{ //general field
  $field=$this->post('field',$v);
  if($field !=""){
  $value=$this->verify_field_val($entry,$form,$field,$k,$custom); 
        
  }}
 
  if($value !== false ){
  if(isset($custom[$k]['name'])){

  $temp[$k]=array('value'=>$value,'label'=>$custom[$k]['label']);    
 
      }
      } }
  }

  if(!empty($data['emp_check']) && !empty($data['emp'])){
   $feed['emp']=apply_filters('vx_assigned_user_id',$data['emp'],$this->id,$feed['id'],$entry,$form);   
  } 
    if(!empty($data['company_check']) && !empty($data['object_company'])){
     $company_feed=$data['object_company']; 
       if( isset(self::$feeds_res[$company_feed]) ){
   $company_res=self::$feeds_res[$company_feed];
  if(!empty($company_res['id'])){
   $feed['vx_company_id']=array('value'=> $company_res['id'],'label'=>'company');   
  }else{ //if empty continue
      continue;
  }    
   }
    }
  
     if(!empty($data['contact_check']) && !empty($data['object_contact'])){
     $contact_feed=$data['object_contact']; 
       if( isset(self::$feeds_res[$contact_feed]) ){
   $contact_res=self::$feeds_res[$contact_feed];
  if(!empty($contact_res['id'])){
   $feed['vx_contact_id']=array('value'=> $contact_res['id'],'label'=>'contact');   
  }else{ //if empty continue
      continue;
  }    
   }
    }   

  //add note 
   if(!empty($data['note_check']) && !empty($data['note_fields']) && is_array($data['note_fields'])){
          $entry_note=''; $entry_note_title='';
          foreach($data['note_fields'] as $e_note){ 
              $value=$this->verify_field_val($entry,$form,$e_note,'',$custom); 
           if(!empty($value)){ 
               if(!empty($entry_note)){
                   $entry_note.="\n";
               }
           $entry_note.=$value;    
           }   
           if(empty($entry_note_title)){
            $entry_note_title=substr($entry_note,0,100);   
           }
          }
          if(!empty($entry_note)){
     $feed['__vx_entry_note']=array('title'=>$entry_note_title,'body'=>$entry_note);      
          }

  }
  
  }
 
$no_filter=true;    
  //not submitted by admin
  if(!$is_admin && $this->post('manual_export',$data) == "1"){ //if manual export is yes
  continue;   
  }         
    if(isset($_REQUEST['bulk_action']) && $_REQUEST['bulk_action'] =="send_to_crm_bulk_force" && !empty($log_id)){
  $force_send=true;
  }
  if(!$force_send && $this->post('optin_enabled',$data) == "1"){ //apply filters if not sending by force and optin is enabled
  $no_filter=$this->check_filter($data,$entry,$form); 
  $res=array("status"=>"4","extra"=>array("filter"=>$this->filter_condition),"data"=>$temp);  
  }

$account=$this->post('account',$feed);
$feed['meta']=$meta;
$feed['data']=$data;
  $info=$this->get_info($account); 
$info_data=array();
if(isset($info['data'])){
    $info_data=$info['data'];
}

  if($no_filter){ //get $res if no filter , other wise use filtered $res
  $api=$this->get_api($info);
  $feed_arr=$feed;
  if(is_array($meta) && is_array($data)){
      $feed_arr=array_merge($meta,$data,$feed);
  }
//  var_dump($feed_arr); echo '<hr>'; var_dump($data);  die();
  $res=$api->push_object($feed['object'],$temp,$feed_arr);
  }

  $feed_id=$this->post('id',$feed);
  self::$feeds_res[$feed_id]=$res;
  $status=$res['status'];  $error=""; $id="";
  if($this->post('id',$res)!=""){ 
      $id=$res['id'];
      $action=$this->post('action',$res);
      if($action == "Added"){
          if(empty($res['link'])){
  $msg=sprintf(__('Successfully Added to Netsuite (%s) with ID # %s .', 'gravity-forms-netsuite-crm'),$feed['object'],$res['id']);
          }else{
  $msg=sprintf(__('Successfully Added to Netsuite (%s) with ID # %s . View entry at %s', 'gravity-forms-netsuite-crm'),$feed['object'],$res['id'],$res['link']);
          }
  $screen_msg=__( 'Entry added in Netsuite', 'gravity-forms-netsuite-crm');
      }else{
            if(empty($res['link'])){
  $msg=sprintf(__('Successfully Updated to Netsuite (%s) with ID # %s . View entry at %s', 'gravity-forms-netsuite-crm'),$feed['object'],$res['id'],$res['link']);   
            }else{
  $msg=sprintf(__('Successfully Updated to Netsuite (%s) with ID # %s .', 'gravity-forms-netsuite-crm'),$feed['object'],$res['id']);   
            }
     $screen_msg=__( 'Entry updated in Netsuite', 'gravity-forms-netsuite-crm');
      }
   
  
  }else if($this->post('status',$res) == 4){
  $screen_msg=$msg=__( 'Entry filtered', 'gravity-forms-netsuite-crm');    
  }else{
  $status=0; $screen_msg_class="error";
  $screen_msg=__('Errors when adding to Netsuite. Entry not sent! Check the Entry Notes below for more details.' , 'gravity-forms-netsuite-crm' );
  if($log_id!=""){
      //message for  bulk actions in logs
  $screen_msg=__('Errors when adding to Netsuite. Entry not sent' , 'gravity-forms-netsuite-crm' );    
  }
  $msg=sprintf(__('Error while creating %s', 'gravity-forms-netsuite-crm'),$feed['object']);
  if($this->post('error',$res)!=""){
      $error= is_array($res['error']) ? json_encode($res['error']) : $res['error'];
  $msg.=" ($error)";
  
  $_REQUEST['VXGNetsuiteError']=$msg; //front end form error for admin only
  }   
  if(!$is_admin){ 
      $info_data['msg']=$msg;
$this->send_error_email($info_data,$entry,$form);
  }    
  } 
  //insert log
  $arr=array("object"=>$feed["object"],"form_id"=>$form_id,"status"=>$status,"entry_id"=>$entry_id,"crm_id"=>$id,"meta"=>substr($error,0,245),"time"=>date('Y-m-d H:i:s'),"data"=>$this->post('data',$res),"response"=>$this->post('response',$res),"extra"=>$this->post('extra',$res),"feed_id"=>$this->post('id',$feed),"link"=>$this->post('link',$res),'parent_id'=>$parent_id,'event'=>$event);
  
    
 // $settings=get_option($this->type.'_settings',array());
 // if($this->post('disable_log',$settings) !="yes"){ 
   $insert_id=$data_db->insert_log($arr,$log_id); 
//  } 
    if(!empty($insert_id)){ // 
          $log_url=admin_url( 'admin.php?page=gf_edit_forms&view=settings&subview='.$this->id.'&tab=log&log_id='.$insert_id.'&id='.$form_id);   
  $log_link=' <a href="'.$log_url.'" class="vx_log_link" data-id="'.$insert_id.'">'.__('View Detail','gravity-forms-netsuite-crm')."</a>";
 $screen_msg.=$log_link;
    }
    if($post_comment){
  //insert entry comment 

//  $this->add_note($entry["id"], $msg);
    } 
    
    if($notice!=""){
  $notice.='<br/>';
  } 
  $notice.='<b>'.$object.': </b>'.$screen_msg;  
   
  }
  }

  return array("msg"=>$notice,"class"=>$screen_msg_class);
  }

  /**
  * Send error email
  * 
  * @param mixed $info
  * @param mixed $entry
  * @param mixed $form
  */
  public function send_error_email($info,$entry,$form){
        if( trim($this->post('error_email',$info))!=""){
  $subject="Error While Posting to Netsuite";
  $entry_link=add_query_arg(array('page' => 'gf_entries','view'=>'entry', 'id' => $entry['form_id'],'lid'=>$entry['id']), admin_url('admin.php'));  
  $page_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; 
  
  $detail=array(
  "Time"=>date('d/M/y H:i:s',current_time('timestamp')),
  "Page URL"=>'<a href="'.$page_url.'" style="word-break:break-all;">'.$page_url.'</a>',
  "Entry ID"=>'<a href="'.$entry_link.'" target="_blank" style="word-break:break-all;">'.$entry_link.'</a>'
  );
  if(isset($form['title'])){
    $detail["Form Name"]=$form['title'];
  $detail["Form Id"]=$form['id'];
  }
    $email_info=array("msg"=>$info['msg'],"title"=>__('Netsuite','gravity-forms-netsuite-crm')." Error","info_title"=>"More Detail","info"=>$detail);
  $email_body=$this->format_user_info($email_info,true);

  $error_emails=explode(",",$info['error_email']); 
  $headers = array('Content-Type: text/html; charset=UTF-8');
  foreach($error_emails as $email)   
  wp_mail(trim($email),$subject, $email_body,$headers);
  }
  }

    /**
  * check if user conected to crm
  *     
  * @param mixed $settings
  */
  public function api_is_valid($info="") {

  if(isset($info['data']) && is_array($info['data']) && isset($info['data']['valid_token']) && !empty($info['data']['valid_token'])){ 
  return true;
  }else{
  return false;}       
  }
}

endif;
$vxg_netsuite=new vxg_netsuite();
$vxg_netsuite->instance();
$vx_gf['vxg_netsuite']='vxg_netsuite';
