<?php 
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'vxa_modify_field_pages' ) ):

/**
* Main  class
*
* @since       1.0.0
*/
class vxa_modify_field_pages extends vxa_modify_field { 
    private $crms;
    private $types=array('random'=>'Assign Randomly','each'=>'Assign One by One','percent'=>'Assign by %','count'=>'Assign by Count','time'=>'Assign by Time','day'=>'Assign by Day','conditions'=>'Conditional Assignment');
public function __construct(){
add_action( 'admin_menu', array($this,'admin_menu'),999 );
add_action( 'admin_init', array($this,'form_actions'));
          global $pagenow; 

  if(in_array($pagenow,array("admin.php"))){
    self::$db_version=get_option($this->page."_version");

  if(self::$db_version != self::$version && current_user_can( 'manage_options' )){
  global $wpdb;

  $wpdb->hide_errors();
  $table_name = $this->get_table_name();

  require_once(ABSPATH . '/wp-admin/includes/upgrade.php');
  
  $sql = "CREATE TABLE $table_name (
  id int(11) unsigned not null auto_increment,
  `form_id` varchar(100) not null,
  `field` varchar(100) null,
    `type` varchar(40) null,
  `field_custom` varchar(200) null,
  `data` text null,
  `status` int(4) NOT NULL default 0,
  `created` datetime,
  `updated` datetime,
  PRIMARY KEY  (id)
  ) ";
  
   dbDelta($sql);

  update_option($this->page."_version", self::$version); 

  }
  }
   //ajax functions
 if(in_array($pagenow, array("admin-ajax.php"))){
add_action( 'wp_ajax_vxa_edit_field_get_html_section', array($this,'html_section_ajax')); 
add_action( 'wp_ajax_update_vxa_modify_field', array($this,'update_user_status')); 
} 
      if(in_array($pagenow,array("plugins.php"))){
add_filter('plugin_action_links', array($this, 'plugin_action_links'), 10, 2);
    }
          
}

public function form_actions(){
      $base_url=$this->get_base_url();
  wp_register_style('vx-fonts', $base_url. 'css/font-awesome.min.css');
    
    if(isset($_GET['page']) && $_GET['page'] == $this->page ){
    if(!empty($_POST['vx_form_action']) && $_POST['vx_form_action'] == 'save'){
     check_admin_referer('vx_nonce','vx_nonce'); 
$data=array();  $time=current_time( 'mysql' );
if(!empty($_REQUEST['options'])){
    $data=$_REQUEST['options']; 
}


if(!empty($_REQUEST['assign'])){
    $data['data']=json_encode($_REQUEST['assign']); 
}

if(!empty($data)){
    if(!isset($data['type'])){ $data['type']='';  }
global $wpdb; $table=$this->get_table_name();
$id='';
$data['updated']=$time;
 if(!empty($_GET['id'])){
     $id=$_GET['id'];
$wpdb->update($table,$data,array('id'=>$id));
 }else{
     $data['created']=$time;
     $data['status']='1';
    //  $wpdb->show_errors();
 $wpdb->insert($table,$data); 
$id=$wpdb->insert_id; 
 }   
$link=$this->link_to_settings(); 
$link.='&id='.$id;
if(!empty($id)){
$link.='&msg=1';
}
wp_redirect($link);
die();
} 
}
if(isset($_GET['tab_action']) && $_GET['tab_action'] == 'del_user'){
     check_admin_referer('vx_nonce','vx_nonce');
   global $wpdb; $table=$this->get_table_name();
   $wpdb->delete($table,array('id'=>$_GET['id'])); 
   $link=$this->link_to_settings(); 
wp_redirect($link.'&msg=2');
die();
}
    
    }
}
public function update_user_status(){
   check_ajax_referer( 'vx_crm_ajax', 'vx_crm_ajax' ); 
     global $wpdb; $table=$this->get_table_name();
     $id=$_POST['id']; 
     $status=$_POST['status'];
     $wpdb->update($table,array('status'=>$status),array('id'=>$id));
     die(''); 
}

public function html_section_ajax(){
    check_ajax_referer( 'vx_nonce', 'vx_nonce' ); 

$data= !empty($_REQUEST['options']) ? $_REQUEST['options'] : array();
if(empty($data['form_id'])){
  die('');  
}

 $this->crm_feeds($data);   

die();
}

public function add_users_page($data){

  //  var_dump($data);
   $assign=array();
    if(!empty($data['assign'])  && is_array($data['assign'])){$assign=$data['assign'];}else{$assign=array('12345'=>array()); }
    $vx_op=$this->get_filter_ops();
    $fields=$this->get_form_fields($data['form_id']);
   // $options['temp']=array();
?>
<div id="vx_user_wrapper">
<?php    
    foreach($assign as $k=>$v){
        ?>
<div class="crm_panel crm_panel_100" data-id="<?php echo $k ?>" <?php if($k == 'temp'){ echo 'id="vx_user_temp"  style="display:none"'; } ?>>
<div class="crm_panel_head2">
<div class="crm_head_div"><span class="crm_head_text crm_text_label"><?php _e('Field Condition','crmperks-addons'); ?></span></div>
<div class="crm_btn_div"><i class="fa fa-trash vx_remove_panel"></i>  </div>
<div class="crm_clear"></div> </div>
<div class="more_options crm_panel_content">
<div class="vx_margin">

<div class="entry_row">
<div class="entry_col1 vx_label"><?php _e('Field Value','crmperks-addons'); ?></div>
<div class="entry_col2">
<textarea name="assign[<?php echo $k; ?>][val]" data-name="val" class="vx_input_100 vx_text"><?php echo $this->post('val',$v); ?></textarea>
<div class="howto"><?php echo sprintf(__('You can add a form field %s in custom value from following form fields.','cf7-salesforce'),'<code>{field_id}</code>','<code>?</code>')?></div>
 <select class="crm_sel_tag vx_input_100" >
<option value=""><?php _e('Select Field','crmperks-addons'); ?></option>
  <?php
  foreach($fields as $field_name=>$field){
  $sel=''; if(!empty($s_v['field']) && $field_name == $s_v['field'] ){$sel='selected="selected"';}      
   echo '<option value="'.$field_name.'" '.$sel.'>'.$field['label'].'</option>';    

     }      ?>
  </select>
</div>
<div class="crm_clear"></div>
</div>   

<div class="entry_row" id="crm_optin_div">
  <div>
  <?php
    
  if(isset($v['filters']) && is_array($v['filters'])&& count($v['filters'])>0){
  $filters=$v['filters'];    
  }else{
  $filters=array("1"=>array("1"=>array("field"=>"")));   
  }
  $sno=0;
  foreach($filters as $filter_k=>$filter_v){ 
  $sno++;
                              ?>
  <div class="vx_filter_or" data-id="<?php echo $filter_k ?>">
  <?php if($sno>1){ ?>
  <div class="vx_filter_label">
  <?php _e('OR','crmperks-addons') ?>
  </div>
  <?php } ?>
  <div class="vx_filter_div">
  <?php
  if(is_array($filter_v)){
  $sno_i=0;
  foreach($filter_v as $s_k=>$s_v){   
  $sno_i++; 
  
  ?>
  <div class="vx_filter_and">
  <?php if($sno_i>1){ ?>
  <div class="vx_filter_label">
  <?php _e('AND','crmperks-addons') ?>
  </div>
  <?php } ?>
  <div class="vx_filter_field vx_filter_field1">
  <select id="crm_optin_field" data-name="field" name="assign[<?php echo $k ?>][filters][<?php echo $filter_k ?>][<?php echo $s_k ?>][field]">
<option value=""><?php _e('Select Field','crmperks-addons'); ?></option>
  <?php
  foreach($fields as $field_name=>$field){
  $sel=''; if(!empty($s_v['field']) && $field_name == $s_v['field'] ){$sel='selected="selected"';}      
   echo '<option value="'.$field_name.'" '.$sel.'>'.$field['label'].'</option>';    

     }      ?>
  </select>
  </div>
  <div class="vx_filter_field vx_filter_field2">
  <select data-name="op" name="assign[<?php echo $k ?>][filters][<?php echo $filter_k ?>][<?php echo $s_k ?>][op]" >
  <?php
                 foreach($vx_op as $k_op=>$v_op){
  $sel="";
  if($this->post('op',$s_v) == $k_op)
  $sel='selected="selected"';
                   echo "<option value='".$k_op."' $sel >".$v_op."</option>";
               } 
              ?>
  </select>
  </div>
  <div class="vx_filter_field vx_filter_field3">
  <input type="text" data-name="value"  class="vxc_filter_text" placeholder="<?php _e('Value','crmperks-addons') ?>" value="<?php echo $this->post('value',$s_v) ?>" name="assign[<?php echo $k ?>][filters][<?php echo $filter_k ?>][<?php echo $s_k ?>][value]">
  </div>
  <?php if( $sno_i>1){ ?>
  <div class="vx_filter_field vx_filter_field4"><i class="vx_icons-h vx_trash_and vxc_tips fa fa-trash-o" data-tip="Delete"></i></div>
  <?php } ?>
  <div style="clear: both;"></div>
  </div>
  <?php
  } }
                     ?>
  <div class="vx_btn_div">
  <button class="button button-default button-small vx_add_and" title="<?php _e('Add AND Filter','crmperks-addons'); ?>"><i class="vx_icons-s vx_trash_and fa fa-hand-o-right"></i>
  <?php _e('Add AND Filter','crmperks-addons') ?>
  </button>
  <?php if($sno>1){ ?>
  <a href="#" class="vx_trash_or">
  <?php _e('Trash','crmperks-addons') ?>
  </a>
  <?php } ?>
  </div>
  </div>
  </div>
  <?php    } ?>
  <div class="vx_btn_div">
  <button class="button button-default  vx_add_or" title="<?php _e('Add OR Filter','crmperks-addons'); ?>"><i class="vx_icons vx_trash_and fa fa-check"></i>
  <?php _e('Add OR Filter','crmperks-addons') ?>
  </button>
  </div>
  </div>

</div> 

  </div></div>
  <div class="clear"></div>
  </div>
<?php
}
    ?>
</div> 
<input type="hidden" name="vx_form_action" value="save">  
  <!--------- template------------>
  <div style="display: none;" id="vx_filter_temp">
  <div class="vx_filter_or">
  <div class="vx_filter_label">
  <?php _e('OR','crmperks-addons') ?>
  </div>
  <div class="vx_filter_div">
  <div class="vx_filter_and">
  <div class="vx_filter_label vx_filter_label_and">
  <?php _e('AND','crmperks-addons') ?>
  </div>
  <div class="vx_filter_field vx_filter_field1">
  <select id="crm_optin_field" data-name="field">
<option value=""><?php _e('Select Field','crmperks-addons'); ?></option>
  <?php
  foreach($fields as $k=>$v){    
   echo '<option value="'.$k.'">'.$v['label'].'</option>';    

     }      ?>
  </select>
  </div>
  <div class="vx_filter_field vx_filter_field2">
  <select data-name="op">
  <?php
                 foreach($vx_op as $k=>$v){
  
                   echo "<option value='".$k."' >".$v."</option>";
               } 
              ?>
  </select>
  </div>
  <div class="vx_filter_field vx_filter_field3">
  <input type="text" class="vxc_filter_text" placeholder="<?php _e('Value','crmperks-addons') ?>" data-name="value">
  </div>
  <div class="vx_filter_field vx_filter_field4"><i class="vx_icons vx_trash_and vxc_tips fa fa-trash-o"></i></div>
  <div style="clear: both;"></div>
  </div>
  <div class="vx_btn_div">
  <button class="button button-default button-small vx_add_and" title="<?php _e('Add AND Filter','crmperks-addons'); ?>"><i class="vx_icons vx_trash_and  fa fa-hand-o-right"></i>
  <?php _e('Add AND Filter','crmperks-addons') ?>
  </button>
  <a href="#" class="vx_trash_or">
  <?php _e('Trash','crmperks-addons') ?>
  </a> </div>
  </div>
  </div>
  </div>
  <!--------- template end ------------>
<button type="button" class="button" id="vx_add_user_btn"><?php _e('Add New Condition','crmperks-addons'); ?></button>
<button type="submit" class="button button-primary" name="save"><?php _e('Save Changes','crmperks-addons'); ?></button>
    <?php
}


public function get_filter_ops(){
           return array("is"=>"Exactly Matches","is_not"=>"Does Not Exactly Match","contains"=>"(Text) Contains","not_contains"=>"(Text) Does Not Contain","is_in"=>"(Text) Is In","not_in"=>"(Text) Is Not In","starts"=>"(Text) Starts With","not_starts"=>"(Text) Does Not Start With","ends"=>"(Text) Ends With","not_ends"=>"(Text) Does Not End With","less"=>"(Number) Less Than","greater"=>"(Number) Greater Than","less_date"=>"(Date/Time) Less Than","greater_date"=>"(Date/Time) Greater Than","equal_date"=>"(Date/Time) Equals","empty"=>"Is Empty","not_empty"=>"Is Not Empty"); 
  }
public function crm_feeds($data){
if(empty($data['form_id'])){ return; }
     //check if entry sent to crm 
 $no_record=true;
 //get feeds
$fields=$this->get_form_fields($data['form_id']);
$custom_field=$this->post('field_custom',$data);
if(count($fields)>0){
    $no_record=false;
   ?>
   <table class="form-table" style="margin: 0px;">
<tbody>
  
     <tr>
  <th><label><?php _e('Edit Type','crmperks-addons'); ?></label></th>
  <td>
   <select name="options[type]" class="crm_text" autocomplete="off" style="margin: 8px 0px;">
  <option value=""><?php _e('','crmperks-addons'); ?></option>
  <?php
  $ops=array(''=>'Modify field value while sending to CRM','save'=>'Permanently update field value in entry');
  if(!isset($data['type'])){ $data['type']=''; }
  
  foreach($ops as $k=>$v){
  $sel=''; if( $k == $data['type'] ){$sel='selected="selected"';}      
   echo '<option value="'.$k.'" '.$sel.'>'.$v.'</option>';    

     }      ?>
  </select>
  </td>

  </tr>
  
   <tr>
  <th><label for="vxa_account"><?php _e('Modify Field','crmperks-addons'); ?></label></th>
  <td>
   <select name="options[field]" class="crm_text vx_data" autocomplete="off" id="vxa_feed" style="margin: 8px 0px;">
  <option value=""><?php _e('Select Field','crmperks-addons'); ?></option>
  <?php
  foreach($fields as $k=>$v){
  $sel=''; if(!empty($data['field']) && $k == $data['field'] ){$sel='selected="selected"';}      
   echo '<option value="'.$k.'" '.$sel.'>'.$v['label'].'</option>';    

     }      ?>
  </select>
  </td>

  </tr>
 
    <tr>
  <th><label for="vxa_account1"><?php _e('Modify Custom Field','crmperks-addons'); ?></label></th>
  <td>
   <input type="text" name="options[field_custom]"  class="crm_text vx_data" autocomplete="off" value="<?php echo $custom_field;  ?>" >
  </td>

  </tr>
   
   </tbody>
   </table>

     <div id="vx_feed">
<?php  
if(!empty($data['form_id'])){   
     $this->add_users_page($data);
}
?>
  </div> 
   <?php

}    
  
}



public function admin_menu(){ 
     // $this->install();
            $page_title =__('Edit Field Value','crmperks-addons');
        $capability = 'vx_crmperks_view_addons'; 
$menu_id='vx-addons'; $addon_menu='vx-marketing-data';
        $menu_title = __('CRM Perks','contact-form-entries');
        
     //  add_menu_page( $page_title,$page_title,$capability,$this->page,array( $this,'section_html'));
        
      
                global $admin_page_hooks;
         if(!empty($admin_page_hooks[$menu_id])){
            $icon='data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAxNy4wLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+DQo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxheWVyXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4Ig0KCSB3aWR0aD0iMjRweCIgaGVpZ2h0PSIyNi45NzVweCIgdmlld0JveD0iMTA2LjQyMSAxMjIuNDAxIDI0IDI2Ljk3NSIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAxMDYuNDIxIDEyMi40MDEgMjQgMjYuOTc1Ig0KCSB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxwYXRoIGZpbGw9IiNGMkYyRjIiIGQ9Ik0xMjkuMzE0LDE0Mi4wMjFsLTEwLjc1Nyw2LjM2OGwtMTAuODkzLTYuMTMybC0wLjEzNi0xMi41bDEwLjc1Ny02LjM2OGwxMC44OTMsNi4xMzJMMTI5LjMxNCwxNDIuMDIxeg0KCSBNMTI2LjY3MSwxMzIuMzEyYzAtMC41MDgtMC4zOTEtMC45Mi0wLjg3NC0wLjkyaC0xNC44MWMtMC40ODMsMC0wLjg3NCwwLjQxMi0wLjg3NCwwLjkybDAsMGMwLDAuNTA4LDAuMzkxLDAuOTIsMC44NzQsMC45MmgxNC44MQ0KCUMxMjYuMjgsMTMzLjIzMiwxMjYuNjcxLDEzMi44MiwxMjYuNjcxLDEzMi4zMTJMMTI2LjY3MSwxMzIuMzEyeiBNMTIxLjIzMywxNDEuMDVjMC0wLjUwOC0wLjQxMi0wLjkyLTAuOTItMC45MmgtMy43NzENCgljLTAuNTA4LDAtMC45MiwwLjQxMi0wLjkyLDAuOTJsMCwwYzAsMC41MDgsMC40MTIsMC45MiwwLjkyLDAuOTJoMy43NzFDMTIwLjgyMSwxNDEuOTcsMTIxLjIzMywxNDEuNTU5LDEyMS4yMzMsMTQxLjA1DQoJTDEyMS4yMzMsMTQxLjA1eiBNMTIzLjQ1MSwxMzYuNjM1YzAtMC41MDgtMC40MzItMC45Mi0wLjk2Ni0wLjkyaC04LjE4N2MtMC41MzMsMC0wLjk2NiwwLjQxMi0wLjk2NiwwLjkybDAsMA0KCWMwLDAuNTA4LDAuNDMyLDAuOTIsMC45NjYsMC45Mmg4LjE4N0MxMjMuMDE5LDEzNy41NTUsMTIzLjQ1MSwxMzcuMTQzLDEyMy40NTEsMTM2LjYzNUwxMjMuNDUxLDEzNi42MzV6Ii8+DQo8L3N2Zz4NCg==';
//   $hook=add_menu_page($page_title,$menu_title,$capability,$menu_id,array( $this,'section_html'),$icon,999);     
 add_submenu_page($menu_id,$page_title,$page_title,$capability,$this->page,array( $this,'section_html'));
     }else{
 add_menu_page($page_title,$page_title,$capability,$this->page,array( $this,'section_html'));
     }


}
/**
* settings page
* 
*/
public function section_html(){
wp_enqueue_style( 'vx-fonts' ); 
$base_url=$this->get_base_url();
$page_link=$this->link_to_settings();
$new_account=$page_link.'&id=0';
$forms_arr=$this->get_forms();
$forms=array();
  foreach($forms_arr as $f_key=>$platform){
    if(isset($platform['forms']) && is_array($platform['forms'])){
    foreach($platform['forms'] as  $form_id_=>$form_title){  
  $form_id_arr=$f_key.'_'.$form_id_;
$forms[$form_id_arr]=$form_title;
    }      
  }
  }
 
global $wpdb; $table=$this->get_table_name();
?>
<div class="wrap"> 
<h2><?php _e('Edit Field Value ','crmperks-addons'); 
if(!isset($_GET['id']) || !empty($_GET['id'])){ ?>
<a href="<?php echo $new_account; ?>" class="page-title-action"><?php _e('Add Field','crmperks-addons'); ?></a><?php }
if(isset($_GET['id'])){?><a href="<?php echo $page_link; ?>" class="page-title-action"><?php _e('Back to Fields','crmperks-addons'); ?></a><?php } ?></h2>
<p><?php _e('Modify field value of Gravity Forms entry, Contact Form 7 entry and WooCommerce Order according to different conditions you set.','crmperks-addons'); ?> </p>
<?php
if(!empty($_GET['msg'])){
 if($_GET['msg'] == '1'){
     $this->screen_msg('',__('Successfully Saved','crmperks-addons'));
 } 
  if($_GET['msg'] == '2'){
     $this->screen_msg('',__('Successfully Deleted','crmperks-addons'));
 }  
}
if(isset($_GET['id'])){
$this->edit_user();        
}else{
     wp_enqueue_script('vx-sorter'); 
  $offset=$this->time_offset();   
?>
<style type="text/css">
.vx_red{
color: #E31230;
}
  .vx_green{
    color:rgb(0, 132, 0);  
  }
      .crm_fields_table input , .crm_fields_table select{
      margin: 0px;
  }
      .vx_accounts_table .vx_pointer{
      cursor: pointer;
  }
  .vx_accounts_table .fa-caret-up , .vx_accounts_table .fa-caret-down{
      display: none;
  }
  .vx_accounts_table th.headerSortUp .fa-caret-down{ 
display: inline; 
} 
  .vx_accounts_table th.headerSortDown .fa-caret-up{ 
display: inline; 
}
</style>
<table class="widefat fixed sort striped vx_accounts_table" style="margin: 20px 0 50px 0">
<thead>
<tr> <th class="manage-column column-cb vx_pointer" style="width: 30px" ><?php _e("#",'crmperks-addons'); ?> <i class="fa fa-caret-up"></i><i class="fa fa-caret-down"></i></th>  
<th class="manage-column"> <?php _e('Status','crmperks-addons'); ?> <i class="fa fa-caret-up"></i><i class="fa fa-caret-down"></i></th> 
<th class="manage-column vx_pointer"> <?php _e('Contact Form','crmperks-addons'); ?> <i class="fa fa-caret-up"></i><i class="fa fa-caret-down"></i></th> 
<th class="manage-column"> <?php _e('Field','crmperks-addons'); ?> </th> 
<th class="manage-column"> <?php _e('Custom Field','crmperks-addons'); ?> </th> 
<th class="manage-column vx_pointer"> <?php _e("Created",'crmperks-addons'); ?> <i class="fa fa-caret-up"></i><i class="fa fa-caret-down"></i></th> 
<th class="manage-column vx_pointer"> <?php _e('Updated','crmperks-addons'); ?> <i class="fa fa-caret-up"></i><i class="fa fa-caret-down"></i></th> 
<th class="manage-column"> <?php _e("Action",'crmperks-addons'); ?> </th> </tr>
</thead>
<tbody>
<?php
$nonce=wp_create_nonce("vx_nonce");
$assignments=$wpdb->get_results("Select * from $table limit 300",ARRAY_A);
if(is_array($assignments) && count($assignments) > 0){
 $sno=0;   
foreach($assignments as $id=>$v){
    $sno++; $id=$v['id'];
    $fields=$this->get_form_fields($v['form_id']);
    if(isset($fields[$v['field']]['label'])){
       $v['field']=$fields[$v['field']]['label'];
    }
 ?>
<tr> <td><?php echo $id ?></td> 
  <td class="vx_col"><img src="<?php echo $base_url ?>images/active<?php echo intval($v['status']) ?>.png" alt="<?php echo $v['status'] ? __('Active', 'crmperks-addons') : __('Inactive', 'crmperks-addons');?>" title="<?php echo $v['status'] ? __('Active', 'crmperks-addons') : __('Inactive', 'crmperks-addons');?>" data-id="<?php echo $v['id'] ?>" class="vx_toggle_status" /></td>
 <td> <?php if(isset($forms[$v['form_id']])){ echo $forms[$v['form_id']]; }else{ echo $v['form_id']; } ?></td> 
 <td> <?php echo $v['field'] ?></td> 
 <td> <?php echo $v['field_custom'] ?> </td>
 <td> <?php echo date('M-d-Y H:i:s', strtotime($v['created'])+$offset); ?> </td>
 <td> <?php echo date('M-d-Y H:i:s', strtotime($v['updated'])+$offset); ?> </td> 
<td><span class="row-actions visible"> <a href="<?php echo $page_link."&id=".$id ?>"><?php _e('Edit','crmperks-addons'); ?></a> | <span class="delete"><a href="<?php echo $page_link.'&tab_action=del_user&id='.$id.'&vx_nonce='.$nonce ?>" class="vx_del_account" > <?php _e("Delete",'crmperks-addons'); ?> </a></span></span> </td> </tr>
<?php
} }else{
?>
<tr><td colspan="8"><p><?php echo sprintf(__("No Field Edit Rule. %sAdd New Field Edit Rule%s",'crmperks-addons'),'<a href="'.$new_account.'">','</a>'); ?></p></td></tr>
<?php
}
?>
</tbody>
<tfoot>
<tr> <th class="manage-column column-cb vx_pointer" style="width: 30px" ><?php _e("#",'crmperks-addons'); ?> <i class="fa fa-caret-up"></i><i class="fa fa-caret-down"></i></th>  
<th class="manage-column"> <?php _e('Status','crmperks-addons'); ?> <i class="fa fa-caret-up"></i><i class="fa fa-caret-down"></i></th> 
<th class="manage-column vx_pointer"> <?php _e('Contact Form','crmperks-addons'); ?> <i class="fa fa-caret-up"></i><i class="fa fa-caret-down"></i></th> 
<th class="manage-column"> <?php _e('Field','crmperks-addons'); ?> </th> 
<th class="manage-column"> <?php _e('Custom Field','crmperks-addons'); ?> </th> 
<th class="manage-column vx_pointer"> <?php _e("Created",'crmperks-addons'); ?> <i class="fa fa-caret-up"></i><i class="fa fa-caret-down"></i></th> 
<th class="manage-column vx_pointer"> <?php _e('Updated','crmperks-addons'); ?> <i class="fa fa-caret-up"></i><i class="fa fa-caret-down"></i></th> 
<th class="manage-column"> <?php _e("Action",'crmperks-addons'); ?> </th> </tr>
</tfoot>
</table>
<script type="text/javascript">

jQuery(document).ready(function($){
var vx_crm_ajax='<?php echo wp_create_nonce("vx_crm_ajax") ?>';

   $(".vx_del_account").click(function(e){
     if(!confirm('<?php _e('Are you sure to delete Assignment ?','crmperks-addons') ?>')){
         e.preventDefault();
     }  
   });
     $(".vx_toggle_status").click(function(e){
      e.preventDefault();
    var feed_id;
    var img=this;
  var is_active = img.src.indexOf("active1.png") >=0
  var $img=$(this);
  
  if(is_active){
  img.src=img.src.replace("active1.png", "active0.png");
  $img.attr('title','<?php _e("Inactive", 'crmperks-addons') ?>').attr('alt', '<?php _e("Inactive", 'crmperks-addons') ?>');
  }
  else{
  img.src = img.src.replace("active0.png", "active1.png");
  $img.attr('title','<?php _e("Active", 'crmperks-addons') ?>').attr('alt', '<?php _e("Active", 'crmperks-addons') ?>');
  }
  
  if(feed_id = $(this).attr('data-id')) {
      $.post(ajaxurl,{action:"update_vxa_modify_field",vx_crm_ajax:vx_crm_ajax,id:feed_id,status:is_active ? 0 : 1})
  }
  }); 
})
</script>
<?php
}
?>
</div>
<?php
}
public function edit_user(){
 
$vx_nonce = wp_create_nonce( "vx_nonce");
$forms=$this->get_forms();

 global $wpdb; $table=$this->get_table_name();
 $sql=$wpdb->prepare("select * from $table where id=%d limit 1",array('id'=>$_GET['id']));
 $data=$wpdb->get_row($sql,ARRAY_A); 
 $data['assign']=json_decode($data['data'],true);
    ?>
<style type="text/css">
    .vx_tr{
      display: table; width: 100%;
  }
  .vx_td{
      display: table-cell; width: 90%;
      padding-right: 12px;
  }
  .vx_td2{
      display: table-cell; 
  }
 table .vx_td2 .vx_toggle_btn{
      margin: 0 0 0 10px; vertical-align: baseline; width: 80px;
  }
  .crm_text{
      width: 100%;
  }
    /*************panels*******************/
.crm_panel * {
  -webkit-box-sizing: border-box; /* Safari 3.0 - 5.0, Chrome 1 - 9, Android 2.1 - 3.x */
  -moz-box-sizing: border-box;    /* Firefox 1 - 28 */
  box-sizing: border-box;  
}
.crm_panel_100{
margin: 10px 0;
}
.crm_panel_head2{
    background: #f6f6f6;
    border: 1px solid #e8e8e8; 
      padding: 8px 20px;
      -moz-user-select: none;
  -webkit-user-select: none;
  -ms-user-select: none; 
}
.crm_panel_content{
    border: 1px dashed #e8e8e8;
    border-top: 0px;
    display: block;
    padding: 12px;
    background: #fff;
    overflow: auto;
}
.vx_label{
    font-weight: bold;
}
.crm_panel_head , .crm_head_text{
  font-size: 14px;  color:#666; font-weight: bold;
}
.crm_head_div{
 width: 80%;
 float: left; 
}.crm_btn_div{
 width: 20%;
 float: left; 
}
.crm_btn_div{
    text-align: right; font-size: 16px; cursor: pointer; color: #666;
}
.crm_btn_div .fa:hover{
    color: #999;
}
.crm_text_label{
    font-weight: bold;
}
 .entry_row {
 margin: 7px auto;   
}
.entry_col1 {
    float: left;
    width: 25%;
    padding: 0px 7px;
    text-align: left;
}
 .entry_col2 {
    float: left;
    width: 75%;
    padding-left: 7px;
}
.crm_clear{
    clear: both;
}
.vx_input_100{
    width: 100%;
}
 .vx_filter_div{
  border: 1px solid #eee;
  padding: 10px;
  background: #f3f3f3; 
  border-radius: 4px;  
  }
  .vx_filter_field{
  float: left; 
  }
  .vx_filter_field1{
  width: 32%;
  }
  .vx_filter_field2{
  width: 30%;
  }
  .vx_filter_field3{
  width: 30%;
  }
  .vx_filter_field4{
  width: 8%;
  }
  .vx_filter_field select{
  width:90%; display: block; 
  }
  .vx_btn_div{
  padding: 10px 0px;
  }
  .vx_filter_label{
  padding: 3px; 
  }
  .vxc_filter_text{
  max-width: 98%;
  width: 96%;
  }
  .vx_trash_or{
  color: #D20000;
  margin-left: 10px;
  }
  
  .vx_trash_or:hover{
  color: #C24B4B;
  }
  .vx_icons{
  font-size: 16px;
  vertical-align: middle;
  cursor: pointer;
    color: #999;
  }
  .vx_icons-s{
  font-size: 12px;
  vertical-align: middle;  
  }
  .vx_icons-h{
  font-size: 16px;
  line-height: 28px;
  vertical-align: middle; 
  cursor: pointer; 
  }
  .vx_icons:hover , .vx_icons-h:hover{
  color: #333;
  }
  .reg_proc{
      display: none;
  }
  .ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }
.ui-timepicker-div dl { text-align: left; }
.ui-timepicker-div dl dt { float: left; clear:left; padding: 0 0 0 5px; }
.ui-timepicker-div dl dd { margin: 0 10px 10px 40%; }
.ui-timepicker-div td { font-size: 90%; }
.ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
.ui-timepicker-div .ui_tpicker_unit_hide{ display: none; }

.ui-timepicker-div .ui_tpicker_time .ui_tpicker_time_input { background: none; color: inherit; border: none; outline: none; border-bottom: solid 1px #555; width: 95%; }
.ui-timepicker-div .ui_tpicker_time .ui_tpicker_time_input:focus { border-bottom-color: #aaa; }

.ui-timepicker-rtl{ direction: rtl; }
.ui-timepicker-rtl dl { text-align: right; padding: 0 5px 0 0; }
.ui-timepicker-rtl dl dt{ float: right; clear: right; }
.ui-timepicker-rtl dl dd { margin: 0 40% 10px 10px; }

/* Shortened version style */
.ui-timepicker-div.ui-timepicker-oneLine { padding-right: 2px; }
.ui-timepicker-div.ui-timepicker-oneLine .ui_tpicker_time, 
.ui-timepicker-div.ui-timepicker-oneLine dt { display: none; }
.ui-timepicker-div.ui-timepicker-oneLine .ui_tpicker_time_label { display: block; padding-top: 2px; }
.ui-timepicker-div.ui-timepicker-oneLine dl { text-align: right; }
.ui-timepicker-div.ui-timepicker-oneLine dl dd, 
.ui-timepicker-div.ui-timepicker-oneLine dl dd > div { display:inline-block; margin:0; }
.ui-timepicker-div.ui-timepicker-oneLine dl dd.ui_tpicker_minute:before,
.ui-timepicker-div.ui-timepicker-oneLine dl dd.ui_tpicker_second:before { content:':'; display:inline-block; }
.ui-timepicker-div.ui-timepicker-oneLine dl dd.ui_tpicker_millisec:before,
.ui-timepicker-div.ui-timepicker-oneLine dl dd.ui_tpicker_microsec:before { content:'.'; display:inline-block; }
.ui-timepicker-div.ui-timepicker-oneLine .ui_tpicker_unit_hide,
.ui-timepicker-div.ui-timepicker-oneLine .ui_tpicker_unit_hide:before{ display: none; }
  </style>    
<form method="post" id="vx_fields_form">
<input type="hidden" name="action" value="vxa_edit_field_get_html_section">  
<input type="hidden" name="vx_nonce" value="<?php echo $vx_nonce; ?>">   
<table class="form-table">
<tbody>
<tr>
  <th><label for="vx_form"><?php _e('Select Form ','crmperks-addons'); ?></label></th>
  <td>
<select id="vx_form_id" class="crm_text vx_get_data" name="options[form_id]" autocomplete="off" required>
  <option value=""><?php _e("Select a Form", 'crmperks-addons'); ?></option>
  <?php
   
  foreach($forms as $f_key=>$platform){
     if(isset($platform['label'])){
      ?>
      <optgroup label="<?php echo $platform['label'] ?>">
      <?php
    if(isset($platform['forms']) && is_array($platform['forms'])){
    foreach($platform['forms'] as  $form_id_=>$form_title){  
  $sel="";
  $form_id_arr=$f_key.'_'.$form_id_;
  if(!empty($data['form_id']) && $data['form_id'] == $form_id_arr)
  $sel="selected='selected'";
  echo "<option value='".$form_id_arr."' $sel>".$form_title."</option>"; 
    }      
  }
  ?>
  </optgroup>
  <?php
     } }
  ?>
  </select>
  </td>

  </tr>
</tbody>
</table>
  <div id="vx_crm">
  <?php
      $this->crm_feeds($data)
  ?>
  </div>   
     <div id="vx_ajax" style="display: none; text-align: center"> <p><i class="fa fa-circle-o-notch fa-spin"></i> <?php _e('Loading ...','crmperks-addons'); ?></p></div> 

  </form>
<script type="text/javascript">
var vx_crm_ajax='<?php echo wp_create_nonce("vx_crm_ajax") ?>';
jQuery(document).ready(function($){

 $(document).on('change','.crm_sel_tag',function(){
var panel=$(this).parents('.entry_row');
var text=panel.find('.vx_text');
var val=text.val()+'{'+$(this).val()+'}';
text.val( val );
 });
 $(document).on('click','.vx_remove_panel',function(){
var panel=$(this).parents('.crm_panel');
 panel.fadeOut('fast',function(){
  panel.remove();   check_trash_btn();
 })
 });

   $(document).on('click','#vx_add_user_btn',function(){
    var user=$('#vx_user_wrapper').find('.crm_panel').eq(0).clone(); 
    var id=rand();
    var id1=rand();
    var id2=rand();
    if(user.find('.vx_filter_or').length){ //:not(:first-child)
       user.find('.vx_filter_or:not(:first-child)').remove(); 
       user.find('.vx_filter_or').attr('data-id',id1); 
       user.find('.vx_filter_and:not(:first-child)').remove(); 
    }
    user.find(':input').each(function(){
        var name=$(this).attr('data-name');
        $(this).val('');
        if($.inArray(name,['field','value','op']) != -1){
    $(this).attr('name','assign['+id+'][filters]['+id1+']['+id2+']['+name+']');    }else{
    $(this).attr('name','assign['+id+']['+name+']');    }
    });
    user.attr('data-id',id);
    user.find('.vx_remove_panel').show();
 $('#vx_user_wrapper').append(user);

  check_trash_btn();    
 });
  
 $(document).on('change','.vx_get_data',function(){
  var ajax=$('#vx_ajax');

  var div=$('#vx_crm');
 div.html('');  
  ajax.show();
  var crm=$(this).val();
  $.post(ajaxurl,$("#vx_fields_form").serialize(),function(res){
   div.html(res); 
    if($('#vxa_type').val() == 'time'){
     start_date($('.vx_time_input'));
 }
 //  bottom.show();
   ajax.hide();   
  });  
});
  $(document).on("click",".vx_add_or",function(e){ 
  e.preventDefault(); 
  var par=$(this).parent(".vx_btn_div");   
  var panel=$(this).parents(".crm_panel");   
  var p_id=panel.attr('data-id');   
  var div=$("#vx_filter_temp");
  var temp=div.find(".vx_filter_or").clone();
  var par_id=rand();
  temp.attr('data-id',par_id);
  var id=rand();
  temp.find(":input").each(function(){
  var name=$(this).attr('data-name');
  if(name)
  $(this).attr('name','assign['+p_id+'][filters]['+par_id+']['+id+']['+name+']');   
  });
  temp.find(".vx_filter_label_and").remove();
  temp.find(".vx_filter_field4").remove();
  par.before(temp);
  });
  $(document).on("click",".vx_trash_or",function(e){ 
  e.preventDefault(); 
  var temp=$(this).parents(".vx_filter_or");
  mark_del(temp);
  });
  $(document).on("click",".vx_trash_and",function(e){ 
  e.preventDefault(); 
  var temp=$(this).parents(".vx_filter_and");
  mark_del(temp);
  });
  $(document).on("click",".vx_add_and",function(e){ 
  e.preventDefault(); 
  var par=$(this).parent(".vx_btn_div");  
    var panel=$(this).parents(".crm_panel");   
  var p_id=panel.attr('data-id');  
  var div=$("#vx_filter_temp");
  var temp=div.find(".vx_filter_and").clone();
  var par_id=$(this).parents(".vx_filter_or").attr('data-id');
  var id=rand();
  temp.find(":input").each(function(){
  var name=$(this).attr('data-name');
  if(name)
  $(this).attr('name','assign['+p_id+'][filters]['+par_id+']['+id+']['+name+']');   
  })
  par.before(temp);
  });

function mark_del(obj){
  obj.css({'opacity':'.5'});
  obj.fadeOut(500,function(){
  $(this).remove();
  });
}
function rand(){
  return Math.round(Math.random()*1000000000);
}
function check_trash_btn(){
var panels=$('.crm_panel').length;
    if(panels<2){
    $('.crm_panel').eq(0).find('.vx_remove_panel').hide();     
    }else if(panels == 2){
  $('.crm_panel').eq(0).find('.vx_remove_panel').show();       
    }
}
function button_state_vx(state,button){
var ok=button.find('.reg_ok');
var proc=button.find('.reg_proc');
     if(state == "ajax"){
          button.attr({'disabled':'disabled'});
ok.hide();
proc.show();
     }else{
         button.removeAttr('disabled');
   ok.show();
proc.hide();      
     }
}
    });
      
</script>  
    <?php
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
            array_unshift( $links, '<a href="' .$settings_link. '">' . __('Settings', 'crmperks-addons') . '</a>' );
        }
        return $links;
}
    /**
  * display screen notices
  * 
  * @param mixed $type
  * @param mixed $message
  */
public function screen_msg($type,$message){
      $type=$type == "" ? "updated" : $type;
  ?>
  <div class="<?php echo $type ?> fade notice is-dismissible"><p><?php echo $message;?></p></div>    
  <?php   
  }
}

endif;