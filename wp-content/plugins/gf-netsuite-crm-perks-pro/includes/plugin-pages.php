<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


if( !class_exists( 'vxg_netsuite_pages' ) ) {

/**
* Main class
*
* @since       1.0.0
*/
class vxg_netsuite_pages   extends vxg_netsuite{
    public $ajax=false;
/**
* initialize plugin hooks
*  
*/
  public function __construct() {
  
  $this->data=$this->get_data_object();
global $pagenow;
  if(in_array($pagenow, array("admin-ajax.php"))){
  add_action('wp_ajax_update_feed_'.$this->id, array($this, 'update_feed'));
  add_action('wp_ajax_update_feed_sort_'.$this->id, array($this, 'update_feed_sort'));
  add_action('wp_ajax_get_field_map_'.$this->id, array($this, 'get_field_map_ajax'));
  add_action('wp_ajax_get_field_map_object_'.$this->id, array($this, 'get_field_map_object_ajax'));
  add_action('wp_ajax_get_objects_'.$this->id, array($this, 'get_objects_ajax'));
  add_action('wp_ajax_log_detail_'.$this->id, array($this, 'log_detail')); 
    add_action('wp_ajax_refresh_data_'.$this->id, array($this, 'refresh_data'));
  }

  if($this->is_crm_page()){

 require_once(GFCommon::get_base_path() . "/tooltips.php");
 add_filter('gform_tooltips', array($this, 'tooltips'));
 }

  //creates the subnav left menu
 //add_filter("gform_addon_navigation", array($this, 'create_menu'), 20);
  add_filter("gform_logging_supported", array($this, "set_logging_supported"));
  add_action( 'gform_form_settings_menu', array( $this, 'add_form_settings_menu' ), 10, 2 );
 add_action( 'gform_form_settings_page_' . $this->id, array( $this, 'form_settings_page' ) );
add_filter("admin_menu", array($this, 'setup'), 10);

//  add_action('gform_post_note_added', array($this, 'create_note'),10,6);
 // add_action('gform_pre_note_deleted', array($this, 'delete_note'),10,2);
  //add_action('gform_delete_lead', array($this, 'delete_entry'));
  //trash , restore entry
  add_action('gform_update_status', array($this, 'entry_status'),10,3);
  //update entry
  add_action('gform_after_update_entry', array($this, 'update_entry'),10,2);
  add_action('gform_entry_detail_sidebar_middle', array($this, 'send_entry_btn'),10,2);
     add_action( 'gform_entry_info', array($this, 'entry_info_send_checkbox'), 99, 2);
  
    add_action( 'admin_notices', array( $this, 'admin_notices' ) ); 
     add_filter('plugin_action_links', array($this, 'plugin_action_links'), 10, 2);  
  


  }
 public function send_entry_btn($form,$lead){
      if(!$this->show_send_to_crm_button()) { return ''; }
           $entry_id=$this->post('lid');
      $form_id = rgget('id');
      if(empty($entry_id)){
          $entry_id=$this->get_entry_id($form_id);
      }
      $log_url=admin_url( 'admin.php?page=gf_edit_forms&view=settings&subview='.$this->id.'&tab=log&id='.$_GET['id'].'&entry_id='.$entry_id);  

   $data=$this->get_data_object();
$log=$data->get_log_by_lead($entry_id); 
require_once(self::$path . 'templates/crm-entry-box.php'); 
 }
   /**
  * Display custom notices
  * show netsuite response
  * 
  */
  public function admin_notices(){

  $debug = !empty(self::$debug_html) && current_user_can($this->id.'_edit_settings');
  if($debug){ 
  echo "<div class='error'><p>".wp_kses_post(self::$debug_html)."</p></div>"; 
  self::$debug_html='';
  }
  if(isset($_GET[$this->id."_logs"]) && current_user_can($this->id.'_read_settings')){
      $msg=__('Error While Clearing Netsuite Logs','gravity-forms-netsuite-crm');
      $level="error";
      if(!empty($_GET[$this->id."_logs"])){
      $msg=__('Netsuite Logs Cleared Successfully','gravity-forms-netsuite-crm');   
      $level="updated";
      }
      $this->screen_msg($msg,$level);
  }
 // if(isset($_REQUEST[$this->id.'_msg'])){ //send to crm in order page message
  $msg=get_option($this->id.'_msg');
  update_option($this->id.'_msg','');
  if(isset($msg['class'])){
      $this->screen_msg($msg['msg'],$msg['class']);
  }

 // }

  }
    /**
  * Add settings and support link
  * 
  * @param mixed $links
  * @param mixed $file
  */
  public function plugin_action_links( $links, $file ) {
   $slug=$this->get_slug();
      if ( $file == $slug ) {
          $settings_link=$this->link_to_settings();
            array_unshift( $links, '<a href="' .$settings_link. '">' . esc_html__('Settings', 'gravity-forms-netsuite-crm') . '</a>' );
        }
        return $links;
   }
   public function entry_status($id,$status,$old){
             $meta=get_option($this->type.'_settings',array());
         $option = '';
             if($status == 'active'){
              $option= 'restore';   
             }else if($status == 'trash'){
                 $option='delete';
             }
         
      if( !empty($option) && !empty($meta[$option])){
        //  $option= $option == 'restore' ? '' : $option;
        $entry=$this->get_gf_entry($id);
        $form=array();
        if(!empty($entry['form_id'])){
        $form = RGFormsModel::get_form_meta($entry['form_id']);    
        }
       $this->push($entry,$form,$option); 
      } 
  
  }
  /**
     * Renders the form settings page.
     *
     * @ignore
     */
    public function form_settings_page() {
    GFFormSettings::page_header( self::$title );
    $this->mapping_page();
    GFFormSettings::page_footer();
    }
    /**
     * Add the form settings tab.
     *
     * Override this function to add the tab conditionally.
     *
     *
     * @param $tabs
     * @param $form_id
     *
     * @return array
     */
    public function add_form_settings_menu( $tabs, $form_id ) {

        $tabs[] = array( 'name' => $this->id, 'label' => esc_html__("Netsuite", 'gravity-forms-netsuite-crm') , 'query' => array( 'fid' => null),'icon' => 'dashicons-cloud dashicons' );

        return $tabs;
    }
    /**
    * Send entry to crm on update
    * 
    * @param mixed $form
    * @param mixed $lead_id
    */
public function update_entry($form,$lead_id){


    $meta=get_option($this->type.'_settings',array());

      if(!empty($meta['update']) || isset($_POST[$this->id.'_update'])){
  $entry=$this->get_gf_entry($lead_id);

    $push=$this->push($entry,$form,'update');
        if(!empty($push['msg'])){
  $this->screen_msg($push['msg'],$push['class']);  
  }
}
  

}


/**
* send entry note to crm
*   
* @param mixed $id
* @param mixed $lead_id
* @param mixed $user_id
* @param mixed $user_name
* @param mixed $note
* @param mixed $note_type
*/
public function create_note($id, $lead_id, $user_id, $user_name, $note, $note_type){
if(!empty($_POST['add_note'])){
        $meta=get_option($this->type.'_settings',array());

      if(!empty($meta['notes'])){
  $entry=$this->get_gf_entry($lead_id);
  $title=substr($note,0,100);
self::$note=array('id'=>$id,'body'=>$note,'title'=>$title);
if(isset($entry['form_id'])){
$form=array('id'=>$entry['form_id']);
$this->push($entry,$form,'add_note');
}
}
}
  }
  /**
  * Creates left nav menu under Forms
  * 
  * @param mixed $menus
  */
  public  function create_menu($menus){
  // Adding submenu if user has access
  $menus[] = array("name" => $this->id, "label" => esc_html__('Netsuite','gravity-forms-netsuite-crm'), "callback" =>  array($this, "mapping_page"), "permission" => $this->id.'_read_feeds');
  
  return $menus;
  }

    /**
  * Creates or updates database tables. Will only run when version changes
  * 
  */
  public  function setup(){

      RGForms::add_settings_page(array('name' => $this->id,'tab_label' => esc_html__('Netsuite','gravity-forms-netsuite-crm'),'icon' => 'dashicons-cloud dashicons',"handler"=>array($this, "settings_page")));
 
           global $wpdb; 
  if($this->post('vx_tab_action_'.$this->id)=="export_log"){
  check_admin_referer('vx_nonce','vx_nonce');
  if(!current_user_can($this->id."_export_logs")){ 
  $msg=__('You do not have permissions to export logs','gravity-forms-netsuite-crm');
  $this->display_msg('admin',$msg);
  return;   
  }
  header('Content-disposition: attachment; filename='.date("Y-m-d",current_time('timestamp')).'.csv');
  header('Content-Type: application/excel');
  $data=$this->get_data_object();
  $sql_end=$data->get_log_query();
  $forms=array();
  $sql="select * $sql_end limit 3000";
  $results = $wpdb->get_results($sql , ARRAY_A );
  $fields=array(); $field_titles=array("#",__('Status','gravity-forms-netsuite-crm'),__('Netsuite ID','gravity-forms-netsuite-crm') ,__('Entry ID','gravity-forms-netsuite-crm'),__('Description','gravity-forms-netsuite-crm'),__('Time','gravity-forms-netsuite-crm'));
  $fp = fopen('php://output', 'w');
  fputcsv($fp, $field_titles);
  $sno=0;
  foreach($results as $row){
  $sno++;
  $row=$this->verify_log($row);
  fputcsv($fp, array($sno,$row['title'],$row['_crm_id'],$row['entry_id'],$row['desc'],$row['time']));    
  }
  fclose($fp);
  die();
  }
  
  if($this->post('vx_tab_action_'.$this->id)=="clear_logs" ){
  check_admin_referer('vx_nonce','vx_nonce');
  if(!current_user_can($this->id."_edit_settings")){ 
  $msg=__('You do not have permissions to clear logs','gravity-forms-netsuite-crm');
  $this->display_msg('admin',$msg);
  return;   
  }
  $data=$this->get_data_object();
  $clear=$data->clear_logs();
   $log_str="Clearing Log";
  $this->log_msg($log_str);
  wp_redirect(admin_url("admin.php?page=".$this->post('page')."&view=".$this->post('view')."&".$this->id."_logs=".$clear));
  die();
  } 
          //send to crm
  if(isset($_POST[$this->id.'_send']) ){
     // Verify authenticity of request
  check_admin_referer('gforms_save_entry', 'gforms_save_entry');
    // For admin_init hook, get the entry ID from the URL

  $entry_id = rgget('lid');
  $form_id = rgget('id');
  
  // fetch alternative entry id: look for gf list details when using pagination
  if(empty($entry_id)) {
  $entry_id=$this->get_entry_id($form_id);
  }
  $form = RGFormsModel::get_form_meta($form_id);
  
  if(!current_user_can($this->id."_send_to_crm")){ 
         return;  
       }
  
  $entry=$this->get_gf_entry($entry_id);

  // Export the entry
  $push=$this->push($entry, $form,"",true); 

    if(!empty($push['msg'])){
        update_option($this->id.'_msg',array('msg'=>$push['msg'],'class'=>$push['class']));  
  }
     
  }
     
  $this->setup_plugin();
  }
 
  /**
  * CRM menu page
  * 
  */
  public  function mapping_page(){
  $view = isset($_GET["tab"]) ? $this->post("tab") : '';
   if( !empty($_GET["fid"]) ) {
  $this->edit_page($this->post("fid"));
  }else if($view == "log") {
  $this->log_page();
  }  else {
  $this->list_page();
  }
  
  }


  
  /**
  * Displays the crm feeds list page
  * 
  */
  private  function list_page(){ 
  if(!current_user_can($this->id.'_read_feeds')){
  esc_html_e('You do not have permissions to access this page','gravity-forms-netsuite-crm');    
  return;
  }
  $is_section=apply_filters('add_page_html_'.$this->id,false);

  if($is_section === true){
    return;
} 
  $offset=$this->time_offset();
  wp_enqueue_script( 'jquery-ui-sortable');
  if(isset($_POST["action"]) && $_POST["action"] == "delete"){
  check_admin_referer("vx_crm_ajax");
  
  $id = absint($this->post("action_argument"));
  $this->data->delete_feed($id);
  ?>
  <div class="updated fade" style="margin:10px 0;">
  <p>
  <?php esc_html_e("Feed deleted.", 'gravity-forms-netsuite-crm') ?>
  </p>
  </div>
  <?php
  }
  else if (!empty($_POST["bulk_action"])){
  check_admin_referer("vx_crm_ajax");
  $selected_feeds =$this->post("feed");
  if(is_array($selected_feeds)){
  foreach($selected_feeds as $feed_id)
  $this->data->delete_feed($feed_id);
  }
  ?>
  <div class="updated fade" style="margin:10px 0;">
  <p>
  <?php esc_html_e("Feeds deleted.", 'gravity-forms-netsuite-crm') ?>
  </p>
  </div>
  <?php
  }
  $form_id=$this->post('id');
  $feeds = $this->data->get_feed_by_form($form_id); 

$page_link=$this->link_to_settings();
  $menu_links=$this->get_menu_links('feed');
  $data=$this->get_data_object();
  $accounts=$data->get_accounts(true);
  //
  $config = $this->data->get_feed('new_form');
   $new_feed_link=$this->get_feed_link($config['id']);
  $valid_accounts= is_array($accounts) && count($accounts) > 0 ? true : false;
include_once(self::$path . "templates/feeds.php");
  }
  /**
  * Displays the crm feeds list page
  * 
  */
  public  function log_page(){
  
  if(!current_user_can($this->id.'_read_logs')){
  esc_html_e('You do not have permissions to access this page','gravity-forms-netsuite-crm');    
  return;
  }
  $is_section=apply_filters('add_page_html_'.$this->id,false);

  if($is_section === true){
    return;
}

/*$entry=$this->get_gf_entry(1);

//self::$note=array('id'=>674,'body'=>'test note','title'=>'note title is here');
//if(isset($entry['form_id'])){
$form=array('id'=>$entry['form_id']);
$this->push($entry,$form,'');
//}
//die();*/
$offset=$this->time_offset(); 
  $log_ids=array();
   $bulk_action=$this->post('bulk_action');
  if($bulk_action!=""){
   $log_id=$this->post('log_id');  
   if(is_array($log_id) && count($log_id)>0){
    foreach($log_id as $id){
     if(is_numeric($id)){
    $log_ids[]=(int)$id;     
     }   
    }
    if($bulk_action == "delete"){
$count=$this->data->delete_log($log_ids);
  $this->screen_msg(sprintf(__('Successfully Deleted %d Item(s)','gravity-forms-netsuite-crm'),$count));  
    }
    else if(in_array($bulk_action,array("send_to_crm_bulk","send_to_crm_bulk_force"))){
         self::$api_timeout='1000';
       foreach($log_ids  as $id){
  $log = $this->data->get_log_by_id($id); 
  $form_id=$this->post('form_id',$log);
  $entry_id=$this->post('entry_id',$log);
  if(!empty($form_id) && !empty($entry_id)){
  $form = RGFormsModel::get_form_meta($form_id);
  $entry=$this->get_gf_entry($entry_id); 
  if(is_array($entry)){ 
    $push=$this->push($entry,$form,$log['event'],true,$log);
  }else{
    $push=array('class'=>'error','msg'=>__('Entry Not Found','gravity-forms-netsuite-crm'));  
  }
    if(is_array($push) && isset($push['class'])){
    $this->screen_msg($push['msg'],$push['class']); 
    }
  } ///var_dump($log_ids,$log); die();  
    }
   
   }
   }
    unset($_GET['bulk_action']);
    unset($_GET['vx_nonce']);
    $log_q=$this->clean($_GET); $logs_link=admin_url('admin.php?'.http_build_query($log_q));
    //wp_redirect($logs_link);
    // die();
  }
  wp_enqueue_script('jquery-ui-datepicker' );
     wp_enqueue_style('vx-datepicker');
  $times=array("today"=>"Today","yesterday"=>"Yesterday","this_week"=>"This Week","last_7"=>"Last 7 Days","last_30"=>"Last 30 Days","this_month"=>"This Month","last_month"=>"Last Month","custom"=>"Select Range"); 
  $data= $this->data->get_log(); $items=count($data['feeds']);

  $crm_order=$entry_order=$desc_order=$time_order="up"; 
  $crm_class=$entry_class=$desc_class=$time_class="vx_hide_sort";
  $order=$this->post('order');
    $order_icon= $order == "desc" ? "down" : "up"; 
  if(isset($_REQUEST['orderby'])){
  switch($_REQUEST['orderby']){
  case"crm_id": $crm_order=$order_icon;  $crm_class="";   break;    
  case"entry_id": $entry_order=$order_icon; $entry_class="";    break;    
  case"object": $desc_order=$order_icon; $desc_class="";   break;    
  case"time": $time_order=$order_icon; $time_class="";   break;    
  }          
  }
    $bulk_actions=array(""=>__('Bulk Action','gravity-forms-netsuite-crm'),"delete"=>__('Delete','gravity-forms-netsuite-crm'),
  'send_to_crm_bulk'=>__('Send to Netsuite','gravity-forms-netsuite-crm'),'send_to_crm_bulk_force'=>__('Force Send to Netsuite - Ignore filters','gravity-forms-netsuite-crm'));
  $base_url=$this->get_base_url();

$objects=$this->get_objects();
      $statuses=array("1"=>__("Created",'gravity-forms-netsuite-crm'),"2"=>__("Updated",'gravity-forms-netsuite-crm'),"error"=>__("Failed",'gravity-forms-netsuite-crm'),"4"=>__("Filtered",'gravity-forms-netsuite-crm'),"5"=>__("Deleted",'gravity-forms-netsuite-crm')); 

  $menu_links=$this->get_menu_links('log');

include_once(self::$path . "templates/logs.php");
  }
/**
* Menu links
*   
*/
public function get_menu_links($current_page=""){
      $settings_link=$this->link_to_settings();
      $id=isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
  $logs_link=admin_url( "admin.php?page=gf_edit_forms&view=settings&subview={$this->id}&tab=log&id={$id}" );
  $feeds_link=admin_url( "admin.php?page=gf_edit_forms&view=settings&subview={$this->id}&tab=feed&id={$id}" );
  
      $menu_links=array(
  'settings'=> array( 
  "title"=>__('Netsuite Settings','gravity-forms-netsuite-crm'),
  "link"=>$settings_link,
  "current"=>$current_page == 'settings' ? true : false
  ),
  'feed'=> array( 
  "title"=>__('Netsuite Feeds','gravity-forms-netsuite-crm'),
  "link"=>$feeds_link,
   "current"=>$current_page == 'feed' ? true : false
  ),
  'log'=> array( 
  "title"=>__('Netsuite Log','gravity-forms-netsuite-crm'),
  "link"=>$logs_link,
   "current"=>$current_page == 'log' ? true : false
  ));
$menu_links=apply_filters('menu_links_'.$this->id,$menu_links); 
return $menu_links;
} 
/**
* feed link
* 
* @param mixed $id
*/
public function get_feed_link($id="",$form_id=""){
        if(empty($form_id) && isset($_GET['id'])){ 
    $form_id=$this->post('id');
    }
    $str="admin.php?page=gf_edit_forms&view=settings&subview={$this->id}&id={$form_id}" ;
    if(!empty($id)){
    $str.="&tab=feed&fid={$id}";    
    }else{
     $str.='&tab=feed';   
    }
  return  admin_url( $str );
} 
/**
* get logs link
* 
* @param mixed $id
*/
public function get_log_link($id="",$form_id=""){ 
    if(empty($form_id) && isset($_GET['id'])){ 
    $form_id=$this->post('id');
    }
    $str="admin.php?page=gf_edit_forms&view=settings&subview={$this->id}&id={$form_id}" ;
    if(!empty($id)){
    $str.="&tab=log&log_id={$id}";    
    }else{
     $str.='&tab=log';   
    }
  return  admin_url( $str );
}   
    public function get_search_fields($object){
      
      if($object == 'Task'){
      
  $json='["title","assigned","sendEmail","timedEvent","estimatedTime","estimatedTimeOverride","actualTime","timeRemaining","percentTimeComplete","percentComplete","parent","startDate","endDate","dueDate","completedDate","priority","status","message","accessLevel","reminderType","reminderMinutes","createdDate","lastModifiedDate","owner","contactList","timeItemList","externalId"]';
          
      }else if($object == 'PhoneCall'){
      $json='["title","message","phone","externalId"]';
  }else{
      $json='["customForm","altName","isPerson","phoneticName","salutation","firstName","middleName","lastName","companyName","entityStatus","parent","phone","fax","email","url","defaultAddress","isInactive","category","title","printOnCheckAs","altPhone","homePhone","mobilePhone","altEmail","language","comments","numberFormat","negativeNumberFormat","dateCreated","image","emailPreference","subsidiary","representingSubsidiary","salesRep","territory","contribPct","partner","salesGroup","vatRegNumber","accountNumber","taxExempt","terms","creditLimit","creditHoldOverride","monthlyClosing","overrideCurrencyFormat","displaySymbol","symbolPlacement","balance","overdueBalance","daysOverdue","unbilledOrders","consolUnbilledOrders","consolOverdueBalance","consolDepositBalance","consolBalance","consolAging","consolAging1","consolAging2","consolAging3","consolAging4","consolDaysOverdue","priceLevel","currency","prefCCProcessor","depositBalance","shipComplete","taxable","taxItem","resaleNumber","aging","aging1","aging2","aging3","aging4","startDate","alcoholRecipientType","endDate","reminderDays","shippingItem","thirdPartyAcct","thirdPartyZipcode","thirdPartyCountry","giveAccess","estimatedBudget","accessRole","sendEmail","password","password2","requirePwdChange","campaignCategory","leadSource","receivablesAccount","drAccount","fxAccount","defaultOrderPriority","webLead","referrer","keywords","clickStream","lastPageVisited","visits","firstVisit","lastVisit","billPay","openingBalance","lastModifiedDate","openingBalanceDate","openingBalanceAccount","stage","emailTransactions","printTransactions","faxTransactions","syncPartnerTeams","isBudgetApproved","globalSubscriptionStatus","externalId"]';
      }
      $fields=json_decode($json,true);

    $arr=array();
  foreach($fields as $k=>$v){
  $type='Text';   
   ///
   $arr[$v]=array('name'=>$v,'label'=>$v,'type'=>$type);
   if(in_array($v,array('email','lastName','firstName') ) || ($object == 'Task' && in_array($v,array('title'))) || ($object == 'PhoneCall' && in_array($v,array('title','phone'))) ){
   $arr[$v]['req']='true';    
   }   
  }
return $arr;
}
  /**
  * Field mapping HTML
  * 
  * @param mixed $feed
  * @param mixed $settings
  * @param mixed $refresh
  * @return mixed
  */
  private  function get_field_mapping($feed,$info="",$refresh=false){
  $fields=array(); 
   if($info == ""){
       $account=$this->post('account',$feed);
  $info=$this->get_info($account);
  }

  if(empty($feed['form_id']) || empty($feed['object']))
  return ''; 
  $module=''; $form_id=0;
  if(isset($feed['object']))
  $module=$feed['object'];
  if(isset($feed['form_id']))
  $form_id=$feed['form_id'];
  //
$api_type=isset($info['data']['api']) ? $info['data']['api'] : '';
$info_meta= isset($info['meta']) && is_array($info['meta']) ? $info['meta'] : array(); 
$feed_meta= isset($feed['meta']) && is_array($feed['meta']) ? $feed['meta'] : array(); 
$info_data= isset($info['data']) && is_array($info['data']) ? $info['data'] : array(); 

  $meta=isset($feed['data']) && is_array($feed['data']) ? $feed['data'] : array();
  $map=isset($meta['map']) && is_array($meta['map']) ? $meta['map'] : array(); 
  $optin_field=isset($meta['optin_field']) ?$meta['optin_field'] : ''; 
  if($this->ajax){ 
  $api=$this->get_api($info);
  $fields=$api->get_crm_fields($module); 
  if(is_array($fields)){ 
  $info_meta['fields']=$fields;     
  $info_meta['object']=$module;     
  $info_meta['feed_id']=$this->post('id');   
  $this->update_info( array('meta'=>$info_meta),$info['id']);        
  }   
  }else{
 $fields=$this->post('fields',$feed_meta); 
  } 

$search_fields=$this->get_search_fields($module); 
  
 
  if(!is_array($fields)){
  $fields= $fields == "" ? "Error while getting fields" : $fields;   
  ?>
  <div class="error below-h2">
  <p><?php echo $fields?></p>
  </div>
  <?php
  return;
  }
 

  
  $vx_op=$this->get_filter_ops(); 
  if(isset($meta['filters']) && is_array($meta['filters'])&& count($meta['filters'])>0){
  $filters=$meta['filters'];    
  }else{
  $filters=array("1"=>array("1"=>array("field"=>"")));   
  }
  
  $map_fields=array();

  foreach($fields as $k=>$v){
      $req=$this->post('req',$v);
      if($req == 'true'){
   $map_fields[$k]=$v;       
      }  
  }
//mapping fields
foreach($map as $field_k=>$field_v){
  if(isset($fields[$field_k])){
  $map_fields[$field_k]=$fields[$field_k];    
  }  
}



  $sel_fields=array(""=>__("Standard Field",'gravity-forms-netsuite-crm'),"value"=>__("Custom Value",'gravity-forms-netsuite-crm'));
  
include_once(self::$path . "templates/fields-mapping.php"); 
  }
 
  /**
  * Updates feed
  * 
  */
  public  function update_feed(){
  check_ajax_referer('vx_crm_ajax','vx_crm_ajax');
  if(!current_user_can($this->id."_edit_feeds")){ 
  return;   
  }
  $id = $this->post("feed_id");
  $feed = $this->data->get_feed($id);
  $this->data->update_feed(array("is_active"=>$this->post("is_active")),$id);
  } 
  
  /**
  * Update the feed sort order
  *
  * @since  3.1
  * @return void
  */
  public  function update_feed_sort(){
  check_ajax_referer('vx_crm_ajax','vx_crm_ajax');
    if(!current_user_can($this->id."_edit_feeds")){ 
  return;   
  }
  if( empty( $_POST['sort'] ))
  {
  exit(false);
  }
  
    $sort=$this->post('sort');
  $this->data->update_feed_order($sort);
  }
  public function set_logging_supported($plugins) {
      $slug=$this->plugin_dir_name(); 
        $plugins[$slug] = esc_html__('Netsuite','gravity-forms-netsuite-crm');
        return $plugins;
    }
  /**
  * Field map ajax method
  * 
  */
  public  function get_field_map_ajax(){
        check_ajax_referer('vx_crm_ajax','vx_crm_ajax');
  if(!current_user_can($this->id."_read_feeds")){ 
  return;   
  }
  $this->ajax=true;
  //loading Gravity Forms tooltips
  require_once(GFCommon::get_base_path() . "/tooltips.php");
  $msg="";
  if(empty($_REQUEST['module'])){
  $msg=__("Please Choose Object",'gravity-forms-netsuite-crm');
  }else  if(empty($_REQUEST['form_id'])){
  $msg=__("Please Choose Form",'gravity-forms-netsuite-crm');
  }
  if($msg !=""){
  ?>
  <div class="error below-h2" style="background: #f3f3f3">
  <p><?php echo wp_kses_post($msg); ?></p>
  </div>
  <?php
  die();
  }     
  $module=$this->post('module');
  $form_id=$this->post('form_id');
  $refresh=$_REQUEST['refresh'] == "true" ? true: false;
    $id=$this->post('id');
  $feed=$this->data->get_feed($id);
    $this->account=$account=$this->post('account');

  $info=$this->get_info($account); 
/*  $object=$this->post('object',$feed);
  if(!$refresh && $object != $module){
   $refresh=true;   
  } */
  $feed['form_id']=$form_id;  
  $feed['object']=$module;  
  $this->get_field_mapping($feed,$info,true); 
  die();
  } 
  public  function get_field_map_object_ajax(){
        check_ajax_referer('vx_crm_ajax','vx_crm_ajax');
  if(!current_user_can($this->id."_read_feeds")){ 
  return;   
  }
  $this->ajax=true;
  //loading Gravity Forms tooltips
  require_once(GFCommon::get_base_path() . "/tooltips.php");
  $msg="";
  if(empty($_REQUEST['account'])){
  $msg=__("Please Choose Account",'gravity-forms-netsuite-crm');
  }
  if($msg !=""){
  ?>
  <div class="error below-h2" style="background: #f3f3f3">
  <p><?php echo wp_kses_post($msg); ?></p>
  </div>
  <?php
  die();
  }     
  $this->account=$account=$this->post('account');
    $id=$this->post('id');
    $feed= $this->data->get_feed($id);

  $info=$this->get_info($account); 
/*  $object=$this->post('object',$feed);
  if(!$refresh && $object != $module){
   $refresh=true;   
  } */
$this->field_map_object($account,$feed,$info);
  die();
  }
    /**
  * available operators for custom filters
  * 
  */
  public function get_filter_ops(){
           return array("is"=>"Exactly Matches","is_not"=>"Does Not Exactly Match","contains"=>"(Text) Contains","not_contains"=>"(Text) Does Not Contain","is_in"=>"(Text) Is In","not_in"=>"(Text) Is Not In","starts"=>"(Text) Starts With","not_starts"=>"(Text) Does Not Start With","ends"=>"(Text) Ends With","not_ends"=>"(Text) Does Not End With","less"=>"(Number) Less Than","greater"=>"(Number) Greater Than","less_date"=>"(Date/Time) Less Than","greater_date"=>"(Date/Time) Greater Than","equal_date"=>"(Date/Time) Equals","empty"=>"Is Empty","not_empty"=>"Is Not Empty"); 
  }
  /**
  * crm fields select options
  * 
  * @param mixed $fields
  * @param mixed $selected
  */
  public function crm_select($fields,$selected,$first_empty=true){
  $field_options="";
    if($first_empty){ 
  $field_options="<option value=''></option>";
  } 
    if(is_array($fields)){
        foreach($fields as $k=>$v){
              if(isset($v['label'])){
  $sel=$selected == $k ? 'selected="selected"' : "";
  $field_options.="<option value='".$k."' ".$sel.">".$v['label']."</option>";      
  }
        }
    }
  return $field_options;    
  }
      /**
  * general(key/val) select options
  * 
  * @param mixed $fields
  * @param mixed $selected
  */
  public function gen_select($fields,$selected,$placeholder=""){
  $field_options="<option value=''>".$placeholder."</option>"; 
    if(is_array($fields)){
        foreach($fields as $k=>$v){
  $sel=$selected == $k ? 'selected="selected"' : "";
  $field_options.='<option value="'.esc_attr($k).'" '.$sel.'>'.esc_html($v).'</option>';       
        }
    }
  return $field_options;    
  }
    /**
  * refresh data , ajax method
  * 
  */
  public function refresh_data(){
      check_ajax_referer("vx_crm_ajax","vx_crm_ajax"); 
  if(!current_user_can($this->id."_read_settings")){ 
   die();  
 }   
  $res=array();
  $action=$this->post('vx_action');
  $camp_id_sel=$this->post('camp_id');

  $account=$this->post('account');
  $status_sel=$this->post('status');
  $folder_sel=$this->post('folder');
  $owner_sel=$this->post('owner');

 $info=array(); $meta=array();
  if(!empty($account)){
 $info=$this->get_info($account);
 if(!empty($info['meta']) ){
   $meta=$info['meta'];  
 }
  }

    $api=$this->get_api($info);
  switch($action){ 
  case"refresh_folders":
    $folders=$api->get_folders(); 
    
    $data=array();
    if(is_array($folders)){
    $res['status']="ok";
   
    }else{
     $res['error']=$folders;   
    }
    $data['crm_sel_folder']=$this->gen_select($folders,$folder_sel,__('Select Folder','gravity-forms-netsuite-crm'));
  $meta['folders']=$folders;  
  $res['data']=$data;   
      break;
            case"refresh_lead_source":
    $folders=$api->get_lead_source(); 
    $data=array();
    if(is_array($folders)){
    $res['status']="ok";  
    }else{
     $res['error']=$folders;   
    }
    $data['crm_sel_source']=$this->gen_select($folders,$folder_sel,__('Select Lead Source','gravity-forms-netsuite-crm'));
  $meta['source_list']=$folders;  
  $res['data']=$data;   
      break;
  case"refresh_status":
    $users=$api->get_status_list(); 
    
    $data=array();
    if(is_array($users)){
    $res['status']="ok";
   
    }else{
     $res['error']=$users;   
    }
    $data['crm_sel_user']=$this->gen_select($users,$status_sel,__('Select Status','gravity-forms-netsuite-crm'));
  $meta['status_list']=$users;  
  $res['data']=$data;   
      break;
      
        case"refresh_note_types":
    $users=$api->get_note_types(); 
    
    $data=array();
    if(is_array($users)){
    $res['status']="ok";
   
    }else{
     $res['error']=$users;   
    }
    $data['crm_note_type']=$this->gen_select($users,$status_sel,__('Select Type','gravity-forms-netsuite-crm'));
  $meta['note_types']=$users;  
  $res['data']=$data;   
      break;
      
        case"refresh_emp":
    $users=$api->get_users(); 
    
    $data=array();
    if(is_array($users)){
    $res['status']="ok";
   
    }else{
     $res['error']=$users;   
    }
    $data['crm_sel_emp']=$this->gen_select($users,$status_sel,__('Select Person','gravity-forms-netsuite-crm'));
  $meta['emp_list']=$users;  
  $res['data']=$data;   
      break;

  }

  if(isset($info['id'])){
    $this->update_info( array("meta"=>$meta) , $info['id'] );
}
if(isset($res['error'])){
    $res['status']='error';
    if(empty($res['error'])){
    $res['error']=__('Unknown Error','gravity-forms-netsuite-crm');
    }
}
  die(json_encode($res));    
  }
    /**
  * plugin start 
  * 
  */
  public function setup_plugin(){
      
if(isset($_REQUEST[$this->id.'_tab_action']) && $_REQUEST[$this->id.'_tab_action']=="get_code"){
   $part=array('code'=>'');
if(isset($_REQUEST['code'])){
$part['code']=$this->post('code');    
}
if(isset($_REQUEST['error'])){
$part['error']=$this->post('error');   
$part['error_description']=$this->post('error_description');    
}
$redir= urldecode($_REQUEST['state'])."&".http_build_query($part);
wp_redirect($redir);
die();
  }

if(isset($_REQUEST[$this->id.'_tab_action']) && $_REQUEST[$this->id.'_tab_action']=="get_token"){
  check_admin_referer('vx_nonce','vx_nonce');
  if(!current_user_can($this->id."_edit_settings")){ 
  $msg=__('You do not have permissions to add token','gravity-forms-netsuite-crm');
  $this->display_msg('admin',$msg);
  return;   
  }
  $id=$this->post('id');
  $info=$this->get_info($id);
  $api=$this->get_api($info);
$info=$api->handle_code();
    //get objects after saving acces token
  $token=$this->post('access_token',$info);
  if(!empty($token)){
    $this->get_objects($info['api'],true,$info);  
  }
  $redir=$this->link_to_settings();
wp_redirect($redir.'&id='.$id);
die();  
  }

if(isset($_REQUEST[$this->id.'_tab_action']) && $_REQUEST[$this->id.'_tab_action']=="del_account"){ 
 check_admin_referer('vx_nonce','vx_nonce');
 if( current_user_can($this->id."_edit_settings")){ 
$id=$this->post('id');
$data=$this->get_data_object();
$res=$data->del_account($id);
 if($res){
       $msg=__('Account Deleted Successfully','gravity-forms-netsuite-crm');
  $msg_arr=array('msg'=>$msg,'class'=>'updated');   
 }else{
       $msg=__('Error While Removing Account','gravity-forms-netsuite-crm');
  $msg_arr=array('msg'=>$msg,'class'=>'error');      
 }
  update_option($this->id.'_msg',$msg_arr);
 }
  $redir=$this->link_to_settings();
wp_redirect($redir.'&'.$this->id.'_msg=1');
die();
  }


  }
  /**
  * Log detail
  * 
  */
  public function log_detail(){
$log_id=$this->post('id');
$log=$this->data->get_log_by_id($log_id); 
  $data=json_decode($log['data'],true); 
  $response=json_decode($log['response'],true);
    $triggers=array('manual'=>'Submitted Manually','submit'=>'Form Submission','update'=>'Entry Update'
  ,'delete'=>'Entry Deletion','add_note'=>'Entry Note Created','delete_note'=>'Entry Note Deleted','restore'=>'Entry Restored');
  $event= empty($log['event']) ? 'manual' : $log['event'];
  $extra=array('Object'=>$log['object']);
  if(isset($triggers[$event])){
    $extra['Trigger']=$triggers[$event];  
  }
  $extra_log=json_decode($log['extra'],true);
  if(is_array($extra_log)){
      $extra=array_merge($extra,$extra_log);
  }
  $error=true; 
  $vx_ops=$this->get_filter_ops();
  $form_id=$this->post('form_id',$log);
  $labels=array("url"=>"URL","body"=>"Search Body","response"=>"Search Response","filter"=>"Filter",'note_object_link'=>'Note Object ID'); 
include_once(self::$path . "templates/log.php");
      die();
  }

        /**
     * Get Objects , AJAX method
     * @return null
     */
public function get_objects_ajax(){
    check_ajax_referer('vx_crm_ajax','vx_crm_ajax');
    
$object=$this->post('object');
$account=$this->post('account');
$info=$this->get_info($account);
  $objects=$this->get_objects($info,true); 

$field_options="<option>".__("Select Object",'gravity-forms-netsuite-crm')."</option>"; 
  if(is_array($objects)){
  foreach($objects as $k=>$v){
      $sel="";
      if($k == $object){
          $sel='selected="selected"';
      }
  $field_options.='<option value="'.esc_attr($k).'" '.$sel.'>'.esc_html($v).'</option>';       
  }  
  }
echo   $field_options;

die();
}
public function get_object_feeds($form_id,$account,$object){
$feeds=$this->data->get_object_feeds($form_id,$account,$object);
$arr=array();
if(is_array($feeds) && count($feeds)>0){
    foreach($feeds as $k=>$feed){
      if(isset($feed['id'])){
      $arr[$feed['id']]=$feed['name'];    
      }  
    }
}
return $arr;
}

  /**
  * Settings page
  * 
  */
  public  function settings_page(){ 
  if(!current_user_can($this->id.'_read_settings')){
  $msg_text=__('You do not have permissions to access this page','gravity-forms-netsuite-crm');   
  $this->display_msg('admin',$msg_text); 
  return;
  }
  $is_section=apply_filters('add_page_html_'.$this->id,false);

  if($is_section === true){
    return;
} 
$str='this is test sdadsd';
$enc=$this->en_crypt($str);
 
  $msgs=array(); $lic_key=false;
  $message=$force_check= false;
  $offset=$this->time_offset();
   $id=$this->post('id');
  if(!empty($_POST[$this->id."_uninstall"])){
  check_admin_referer("vx_nonce");
  if(!current_user_can($this->id."_uninstall")){
  return;
  }    
  $this->uninstall();
  $uninstall_msg=sprintf(__("Gravity Forms Netsuite Plugin has been successfully uninstalled. It can be re-activated from the %s plugins page %s.", 'gravity-forms-netsuite-crm'),"<a href='plugins.php'>","</a>");
$this->screen_msg($uninstall_msg);
  return;
  }
  
  else if(!empty($_POST['crm'])){ 
  check_admin_referer("vx_nonce");
  if(!current_user_can($this->id."_edit_settings")){ 
  $msg=__('You do not have permissions to save settings','gravity-forms-netsuite-crm');
  $this->display_msg('admin',$msg);
  return;   
  }
  $msgs['submit']=array('class'=>'updated','msg'=>__('Settings Changed Successfully','gravity-forms-netsuite-crm'));
  $valid_email=true;
  if($this->post('error_email',$_POST['crm']) !=""){
   $emails=explode(",",$this->post('error_email',$_POST['crm']));
  foreach($emails as $email){
      $email=trim($email);
    if($email !="" && !$this->is_valid_email($email)){
  $valid_email=false; 
    }  
  }   
  }
  if(!$valid_email){
      $msgs['submit']=array("class"=>"error","msg"=>__('Invalid Email(s)','gravity-forms-netsuite-crm'));
  }
   $crm=$this->get_info($id);
   $data=isset($crm['data'])  && is_array($crm['data']) ? $crm['data'] : array();  
  /////////////

  $data=array_merge($data,$this->post('crm'));
  $data=$this->validate_api($data,true);    
 // $data['disable_log']=$this->post('disable_log',$_POST['crm']);
  $this->update_info(array('data'=> $data),$id);
  ////////////////////
  }                

  $data=$this->get_data_object();
  $new_account_id=$data->get_new_account();
 $page_link=$this->link_to_settings();
 $new_account=$page_link."&id=".$new_account_id;
  if(!empty($id)){
  $info=$this->get_info($id);    
  if(!is_array($info) || !isset($info['id'])){
   $id="";   
  } }
  if(!empty($id)){   
  $valid_user=false;
  

  $api=$this->get_api($info);
  if(empty($_POST)){
   $api->timeout="5";   
  }

  $link=$this->link_to_settings();
  
  
  //
    if($this->post('vx_tab_action')== "refresh_lists_".$this->id){ 
  check_admin_referer('vx_nonce');
  if(!current_user_can($this->id."_read_settings")){ 
  $msg=__('You do not have permissions to refresh lists','gravity-forms-netsuite-crm');
  $this->display_msg('admin',$msg);
  return;   
  }
  $meta=$this->post('meta',$info);
  if(isset($meta['lists'])){
      unset($meta['lists']);
  }
  $this->update_info(array('meta'=>$meta),$id);
  $msgs['refresh']=array("class"=>"updated","msg"=>__('Successfully Refreshed Picklists','gravity-forms-netsuite-crm')); 

  }

  $force_check=false;
  if(isset($_POST['vx_test_connection'])){
    $force_check=true;  
  } 
if(!empty($info['data'])){
  $info=$info['data'];  
}

  //verify connection
  $info=$this->validate_api($info,$force_check); 
 // $tooltips=self::$tooltips ; 
  if($force_check){
       $this->update_info( array("data"=> $info),$id);
  }
  
  $conn_class=$this->post('class',$info);
  if(!empty($conn_class)){
  $msgs['connection']=array('class'=>$info['class'],'msg'=>$info['msg']);
  }
 if(isset($_POST['vx_test_connection'])){
  $msg=__('Connection to Netsuite is Working','gravity-forms-netsuite-crm');
  
  if($conn_class != "updated" ){
      $msg=__('Connection to Netsuite is NOT Working','gravity-forms-netsuite-crm');  
  }
  $title=__('Test Connection: ','gravity-forms-netsuite-crm');
  $msgs['test']=array('class'=>$conn_class,'msg'=>'<b>'.$title.'</b>'.$msg);
  }

  }else{
      $accounts=$data->get_accounts();

  }
            $meta=get_option($this->type.'_settings',array());

      if(!empty($_POST['save'])){ 
             if(current_user_can($this->id."_edit_settings")){ 

  $meta=$this->post('meta'); if(!is_array($meta)){ $meta=array(); }

  $msgs['submit']=array('class'=>'updated','msg'=>__('Settings Changed Successfully','gravity-forms-netsuite-crm'));
  update_option($this->type.'_settings',$meta);
  }      
      }
      
 
    $nonce=wp_create_nonce("vx_nonce");
include_once(self::$path . "templates/settings.php");

  } 

    /**
  * Create or edit crm feed page
  * 
  */
  private  function edit_page($fid=""){
  if(!current_user_can($this->id.'_read_feeds')){
  esc_html_e('You do not have permissions to access this page','gravity-forms-netsuite-crm');    
  return;
  }
$base_url=$this->get_base_url();
$sel2_js=$base_url. 'js/select2.min.js';
$sel2_css=$base_url. 'css/select2.min.css';
  $is_section=apply_filters('add_page_html_'.$this->id,false);

  if($is_section === true){
    return;
} 

  if(!function_exists('$this->tooltip')) {
  require_once(GFCommon::get_base_path() . "/tooltips.php");
  }
  $feed= $this->data->get_feed($fid); 

         //updating meta information
  if(isset($_POST[$this->id."_submit"])){ 
  check_admin_referer("vx_nonce");
  if(!current_user_can($this->id.'_edit_feeds')){
  esc_html_e('You do not have permissions to edit/save feed','gravity-forms-netsuite-crm'); 
  return;
  }
  //
  $time = current_time( 'mysql' ,1);
  $feed_update=array("data"=>$this->post("meta"),"name"=>$this->post('name'),"account"=>$this->post('account'),"object"=>$this->post('object'),"form_id"=>$this->post('form_id'),"time"=>$time);
if(!empty($_POST['account'])){
  $info=$this->get_info($this->post('account'));
  if(isset($info['meta']['feed_id']) && isset($info['meta']['fields']) && !empty($info['meta']['feed_id']) && $info['meta']['feed_id'] == $fid ){

 $meta=isset($feed['meta']) && is_array($feed['meta']) ? $feed['meta'] : array();
 $meta['fields']=$info['meta']['fields'];
 $feed_update['meta']=$meta;
 unset($info['meta']['feed_id']); 

 $this->update_info(array('meta'=>$info['meta']),$info['id']);
} }

if(is_array($feed_update) && is_array($feed)){
    $feed=array_merge($feed,$feed_update);
} 
//var_dump($feed_update); 
  $is_valid=$this->data->update_feed($feed_update,$fid);

  $msg=''; $class='updated';
  if($is_valid){
      $feed_link=$this->get_feed_link();
$msg=sprintf(__("Feed Updated. %sback to list%s", 'gravity-forms-netsuite-crm'), '<a href="'.$feed_link.'">', "</a>");
  }
  else{
$msg=__("Feed could not be updated. Please enter all required information below.", 'gravity-forms-netsuite-crm');
$class='error';
  }
  if(!empty($msg)){
      $this->screen_msg($msg,$class);
  }
  }   
    //getting  API
  $menu_links=$this->get_menu_links('feed');
  $_data=$this->get_data_object();
  $accounts=$_data->get_accounts(true); 
   
  //die();
     $this->account=$account=$this->post('account',$feed);
  $info=$this->get_info($account);
 $form_id=isset($_GET['id']) ? $this->post('id') : '';
 if(!empty($_POST['form_id'])){
   $form_id=$this->post('form_id');  
 }
  $config = $this->data->get_feed('new_form');
   $new_feed_link=$this->get_feed_link($config['id']);

     $feeds_link=admin_url( "admin.php?page=gf_edit_forms&view=settings&subview={$this->id}&tab=feed&id=$form_id" );
include_once(self::$path . "templates/feed-account.php");
  
  }  
  /**
  * field mapping box's Contents
  * 
  */
  public function field_map_object($account,$feed,$info) {
     
     
  $api_type=$this->post('api',$info);

  //get objects from crm
  $objects=$this->get_objects($info); 

  if(empty($feed['object'])){
      $feed['object']="";
  }
  if(!empty($feed['object']) && is_array($objects) && !isset($objects[$feed['object']])){
  $feed['object']="";     
  }  

  $meta=$this->post('meta',$info);

 if(!is_array($objects) && !empty($objects)){
 $this->screen_msg($objects,'error'); 
 return;  
 }
  
 include_once(self::$path."templates/feed-object.php");  
  }
       /**
  * Formats Log table row
  * 
  * @param mixed $row
  */
  public function verify_log($row){
  $crm_id=$link="N/A"; $desc="Added to ";
  $status_imgs=array("1"=>"created","5"=>"deleted","2"=>"updated","4"=>"filtered");
    $row['status_img']=isset($status_imgs[$row["status"]]) ? $status_imgs[$row["status"]] : 'failed';
    
  $objects=$this->get_objects("");
  if(isset($objects[$row['object']])){
      $row['object']=$objects[$row['object']];
  }
 // var_dump($row);
  if( !empty($row['status'])){
      if(!empty($row['crm_id'])){
     $link=$row['crm_id'];     
      }
  if($row['link'] !=""){
  $link='<a href="'.$row['link'].'" title="'.$row['crm_id'].'" target="_blank">'.$row['crm_id'].'</a>';
  $crm_id=$row['crm_id'];
  }   
  if($row['status'] == 2){
  $desc="Updated to ";    
  }
  if($row['status'] == 3){
  $row['status']=1; 
  $desc.=" Web2".$row['object'];
  }else   if($row['status'] == 4){
   $desc=sprintf(__('%s Filtered','gravity-forms-netsuite-crm'),$row['object']);   
  }else   if($row['status'] == 5){
   $desc=sprintf(__('%s Deleted','gravity-forms-netsuite-crm'),$row['object']);  
  }else{
  $desc.=$row['object'];
  }
  }else{
  $desc= !empty($row['error']) ? $row['error'] : "Unknown Error";
  }

  $title=__("Failed",'gravity-forms-netsuite-crm');   
  if( $row['status'] == 1){
  $title=__("Created",'gravity-forms-netsuite-crm');   
  }else if($row['status'] == 2){
  $title=__("Updated",'gravity-forms-netsuite-crm');   
  }else if($row['status'] == 4){
  $title=__("Filtered",'gravity-forms-netsuite-crm');   
  }else if($row['status'] == 5){
  $title=__("Deleted",'gravity-forms-netsuite-crm');   
  }
  $row['_crm_id']= $crm_id;
  $row['a_link']=$link;
  $row['desc']=$desc;
  $row['title']=$title;
  return $row;
  }
    /**
  * gravity forms form fields
  * 
  * @param mixed $form_id
  */
  public  function get_gf_fields($form_id){
      if($this->fields){
     return $this->fields;     
      }
  $form = RGFormsModel::get_form_meta($form_id);
  $fields = array();
  

  array_push($form['fields'],array("id" => "status" , "label" => esc_html__('Entry Status', 'gravity-forms-netsuite-crm')));
  
  if(is_array($form['fields'])){
  foreach($form['fields'] as $field){
  if(isset($field["inputs"]) && is_array($field["inputs"]) && $field['type'] !== 'checkbox' && $field['type'] !== 'select'){
  
  //If this is an address field, add full name to the list
  if(RGFormsModel::get_input_type($field) == "address") {
      $fields[] =  array($field["id"], GFCommon::get_label($field) . " (" . _x("Full" , 'Full field label', 'gravity-forms-netsuite-crm') . ")");
  }
  
  foreach($field["inputs"] as $input)
      $fields[] =  array($input["id"], GFCommon::get_label($field, $input["id"]));
  }
  else if(empty($field["displayOnly"])){
  $fields[] =  array($field["id"], GFCommon::get_label($field));
  }
  }
  }
  $fields[]=array('id',__('Entry ID','gravity-forms-freshdesk-crm'));
  $fields[]=array('form_id',__('Form ID','gravity-forms-freshdesk-crm'));
  $fields[]=array('entry_url',__('Entry URL','gravity-forms-freshdesk-crm'));
    $fields[]=array('date_created',__('Entry Date','gravity-forms-netsuite-crm'));
  $fields[]=array('ip',__('User IP','gravity-forms-netsuite-crm'));
  $fields[]=array('source_url',__('Source Url','gravity-forms-netsuite-crm'));
  $fields[]=array('form_title',__('Form Title','gravity-forms-netsuite-crm'));
  $fields[]=array('status',__('Entry Status','gravity-forms-netsuite-crm'));
  $this->fields=array('gf'=>array("title"=>__('Gravity Forms Fields','gravity-forms-netsuite-crm'),"fields"=>$fields)); 
  $this->fields=$fields=apply_filters('vx_mapping_standard_fields',$this->fields);
  ///var_dump($fields); die();
  return $fields;
  }
  /**
  * gravity forms fields label
  * 
  * @param mixed $form_id
  * @param mixed $key
  */
  public function get_gf_field_label($form_id,$key){
  $fields=$this->get_gf_fields($form_id);    
  $label=$key;
  if(is_array($fields)){
  foreach($fields as $ke=>$field){
      if(isset($field['fields']) && is_array($field['fields']) ){
          foreach($field['fields'] as $k=>$v){     
                if($ke == "gf"){
   $k=$v[0];   
  }
  if($k == $key ){
    if($ke == "gf"){
   $label=$v[1];     
    }else if(isset($v['label'])){
   $label= $v['label'];     
    }  

  }
  
          }
      }
      
  }}

  return $label;
  }


  /**
  * gravity forms field select options
  * 
  * @param mixed $form_id
  * @param mixed $selected_val
  */
  public  function  gf_fields_options($form_id,$sel_val=""){
  if($this->fields == null){
  $this->fields=$this->get_gf_fields($form_id);
  } 
    if(!is_array($sel_val)){
$sel_val=array($sel_val);
      }
  $sel="<option value=''></option>";
  $fields=$this->fields;
  if(is_array($fields)){
  foreach($fields as $key=>$fields_arr){
if(is_array($fields_arr['fields'])){
    $sel.='<optgroup label="'.esc_html($fields_arr['title']).'">';
      foreach($fields_arr['fields'] as $k=>$v){
          $option_k=$k;
          $option_name=$v;
  if($key == "gf"){
   $option_k=$v[0]; $option_name=$v[1];   
  }else{
    $option_name=$v['label'];  
  }
          $select="";
           if( in_array($option_k,$sel_val)){
  $select='selected="selected"';
  }

  $sel.='<option value="'.esc_attr($option_k).'" '.$select.'>'.esc_html($option_name).'</option>';    
  }    }
  }}  
  return $sel;    
  }  
  /**
  * validate API
  * 
  * @param mixed $info
  * @param mixed $force_check
  */
  public function validate_api($info,$check=false){
 
  $time=current_time('timestamp'); 
  /*$check=$force_check; $auto_check=false;
  if(!$force_check && $api_check<$time){ //check validity period in settings tab
  $check=true; $auto_check=true;
  }*/ 

  if($check){ 
      $api=$this->get_api(array('data'=>$info));
  $info=$api->get_token(); 
  } 

  if(isset($info['valid_token'])  && $info['valid_token']!="") { 
  $msg=__( 'Successfully Connected to Netsuite','gravity-forms-netsuite-crm' );
     if(!empty($info['_time'])){
       $msg.=" - ".date('F d, Y h:i:s A',$info['_time']);
   }
      $info['msg']=$msg; 
  $info['class']="updated";     
  
  }else{
  $info['class']="";  
  if(isset($info['account_id'])){
  $info['msg']=!empty($info['error']) ? $info['error'] : 'API Key is Not Valid'; 
  $info['class']="error"; 
  } 
        }

  if($check){ 
  $info['_time']=$time;     
  }
  return $info;
  }
}
}
new vxg_netsuite_pages();
