<?php
if ( ! defined( 'ABSPATH' ) ) {
     exit;
 }                                            
 ?>
 
 <div  class="vx_div">
   <div class="vx_head">
<div class="crm_head_div"> <?php esc_html_e('4. Map Form Fields to Netsuite Fields.', 'gravity-forms-netsuite-crm'); ?></div>
<div class="crm_btn_div" title="<?php esc_html_e('Expand / Collapse','gravity-forms-netsuite-crm') ?>"><i class="fa crm_toggle_btn vx_action_btn fa-minus"></i></div>
<div class="crm_clear"></div> 
  </div>
  <div class="vx_group" style="padding: 10px 0px; border-width: 0px; background-color: transparent;">


  <div id="vx_fields_div">
  <?php 
   $req_span=" <span class='vx_red vx_required'>(".__('Required','gravity-forms-netsuite-crm').")</span>";
 $req_span2=" <span class='vx_red vx_required vx_req_parent'>(".__('Required','gravity-forms-netsuite-crm').")</span>";
 $module_single=substr($module,0,-1);
 $show_account=false;
$show_vender=false; 
  foreach($map_fields as $k=>$v){
        if(isset($v['name_c'])){
  $v['name']=$v['name_c'];      
  $v['label']=__('Custom Field','gravity-forms-netsuite-crm');      
  } 

  $sel_val=isset($map[$k]['field']) ? $map[$k]['field'] : ""; 
  $val_type=isset($map[$k]['type']) && !empty($map[$k]['type']) ? $map[$k]['type'] : "field"; 

  $options=$this->gf_fields_options($form_id,$sel_val); 
    $display="none"; $btn_icon="fa-plus";
  if(isset($map[$k][$val_type]) && !empty($map[$k][$val_type])){
    $display="block"; 
    $btn_icon="fa-minus";   
  }
  $required=isset($v['req']) && $v['req'] == "true" ? true : false;
   $req_html=$required ? $req_span : ""; $k=esc_attr($k);
  ?>
<div class="crm_panel crm_panel_100">
<div class="crm_panel_head2">
<div class="crm_head_div"><span class="crm_head_text crm_text_label">  <?php echo esc_html($v['label']);?></span> <?php echo wp_kses_post($req_html) ?></div>
<div class="crm_btn_div">
<?php
 if(! $required){   
?>
<i class="vx_remove_btn vx_remove_btn vx_action_btn fa fa-trash-o" title="<?php esc_html_e('Delete','gravity-forms-netsuite-crm'); ?>"></i>
<?php } ?>
<i class="fa crm_toggle_btn vx_action_btn vx_btn_inner <?php echo $btn_icon ?>" title="<?php esc_html_e('Expand / Collapse','gravity-forms-netsuite-crm') ?>"></i>
</div>
<div class="crm_clear"></div> </div>
<div class="more_options crm_panel_content" style="display: <?php echo $display ?>;">
  <?php if(!isset($v['name_c'])){ ?>

  <div class="crm-panel-description">
  <span class="crm-desc-name-div"><?php echo esc_html__('Name:','gravity-forms-netsuite-crm')." ";?><span class="crm-desc-name"><?php echo esc_html($v['name']); ?></span> </span>
  <?php if($this->post('type',$v) !=""){ ?>
    <span class="crm-desc-type-div">, <?php echo esc_html__('Type:','gravity-forms-netsuite-crm')." ";?><span class="crm-desc-type"><?php echo esc_html($v['type']) ?></span> </span>
<?php
   }
  if($this->post('maxlength',$v) !=""){ 
   ?>
   <span class="crm-desc-len-div">, <?php echo esc_html__('Max Length:','gravity-forms-netsuite-crm')." ";?><span class="crm-desc-len"><?php echo esc_html($v['maxlength']); ?></span> </span>
  <?php 
  }
    if($this->post('eg',$v) !=""){ 
   ?>
   <span class="crm-eg-div">, <?php echo esc_html__('e.g:','gravity-forms-netsuite-crm')." ";?><span class="crm-eg"><?php echo $v['eg']; ?></span> </span>
  <?php 
  }
  ?>
   </div> 
  <?php
  }
  ?>

<div class="vx_margin">

<?php
    if(isset($v['name_c'])){
?>
<div class="entry_row">
<div class="entry_col1 vx_label"><?php esc_html_e('Field API Name','gravity-forms-netsuite-crm') ?></div>
<div class="entry_col2">
<input type="text" name="meta[map][<?php echo $k ?>][name_c]" value="<?php echo esc_attr($v['name_c']) ?>" placeholder="<?php esc_html_e('Field API Name','gravity-forms-netsuite-crm') ?>" class="vx_input_100">
</div>
<div class="crm_clear"></div>
</div> 
<?php             
    }
?>
<div class="entry_row">
<div class="entry_col1 vx_label"><label  for="vx_type_<?php echo $k ?>"><?php esc_html_e('Field Type','gravity-forms-netsuite-crm') ?></label></div>
<div class="entry_col2">
<select name='meta[map][<?php echo $k ?>][type]'  id="vx_type_<?php echo $k ?>" class='vxc_field_type vx_input_100'>
<?php
  foreach($sel_fields as $f_key=>$f_val){
  $select="";
  if($this->post2($k,'type',$map) == $f_key)
  $select='selected="selected"';
  ?>
  <option value="<?php echo esc_attr($f_key) ?>" <?php echo $select ?>><?php echo esc_html($f_val)?></option>   
  <?php } ?> 
</select>
</div>
<div class="crm_clear"></div>
</div>  
<div class="entry_row entry_row2">
<div class="entry_col1 vx_label">
<label for="vx_field_<?php echo $k ?>" style="<?php if($this->post2($k,'type',$map) != ''){echo 'display:none';} ?>" class="vxc_fields vxc_field_"><?php esc_html_e('Select Field','%dd%') ?></label>

<label for="vx_value_<?php echo $k ?>" style="<?php if($this->post2($k,'type',$map) != 'value'){echo 'display:none';} ?>" class="vxc_fields vxc_field_value"> <?php esc_html_e('Custom Value','%dd%') ?></label>
</div>
<div class="entry_col2">
<div class="vxc_fields vxc_field_value" style="<?php if($this->post2($k,'type',$map) != 'value'){echo 'display:none';} ?>">
<input type="text" name='meta[map][<?php echo $k?>][value]'  id="vx_value_<?php echo $k ?>" value='<?php echo $this->post2($k,'value',$map)?>' placeholder='<?php esc_html_e("Custom Value",'%dd%')?>' class='vx_input_100 vxc_field_input'>
<div class="howto"><?php echo sprintf(__('You can add a form field %s in custom value from following form fields','%dd%'),'<code>{field_id}</code>')?></div>
</div>


<select name="meta[map][<?php echo $k ?>][field]"  id="vx_field_<?php echo $k ?>" class="vxc_field_option vx_input_100">
<?php echo $options ?>
</select>


</div>
<div class="crm_clear"></div>
</div>  

  </div></div>
  <div class="clear"></div>
  </div>
<?php
  }
  ?> 
 
 <div id="vx_field_temp" style="display:none"> 
  <div class="crm_panel crm_panel_100 vx_fields">
<div class="crm_panel_head2">
<div class="crm_head_div"><span class="crm_head_text crm_text_label">  <?php esc_html_e('Custom Field', 'gravity-forms-netsuite-crm');?></span> </div>
<div class="crm_btn_div">
<i class="vx_remove_btn vx_action_btn fa fa-trash-o" title="<?php esc_html_e('Delete','gravity-forms-netsuite-crm'); ?>"></i>
<i class="fa crm_toggle_btn vx_action_btn vx_btn_inner fa-minus" title="<?php esc_html_e('Expand / Collapse','gravity-forms-netsuite-crm') ?>"></i>
</div>
<div class="crm_clear"></div> </div>
<div class="more_options crm_panel_content" style="display: block;">

<?php
    if($api_type  != 'web'){
?>

  <div class="crm-panel-description">
  <span class="crm-desc-name-div"><?php echo esc_html__('Name:','gravity-forms-netsuite-crm')." ";?><span class="crm-desc-name"></span> </span>
  <span class="crm-desc-type-div">, <?php echo esc_html__('Type:','gravity-forms-netsuite-crm')." ";?><span class="crm-desc-type"></span> </span>
  <span class="crm-desc-len-div">, <?php echo esc_html__('Max Length:','gravity-forms-netsuite-crm')." ";?><span class="crm-desc-len"></span> </span>
   <span class="crm-eg-div">, <?php echo esc_html__('e.g:','gravity-forms-netsuite-crm')." ";?><span class="crm-eg"></span> </span>


   </div> 

<?php
    }
?>
<div class="vx_margin">

<div class="entry_row">
<div class="entry_col1 vx_label"><label  for="vx_type"><?php esc_html_e('Field Type','gravity-forms-netsuite-crm') ?></label></div>
<div class="entry_col2">
<select name='type' class='vxc_field_type vx_input_100'>
<?php
  foreach($sel_fields as $f_key=>$f_val){
  ?>
  <option value="<?php echo esc_attr($f_key) ?>"><?php echo esc_html($f_val)?></option>   
  <?php } ?> 
</select>
</div>
<div class="crm_clear"></div>
</div>  
<div class="entry_row entry_row2">
<div class="entry_col1 vx_label">
<label for="vx_field" class="vxc_fields vxc_field_"><?php esc_html_e('Select Field','%dd%') ?></label>

<label for="vx_value" class="vxc_fields vxc_field_value" style="display: none;"> <?php esc_html_e('Custom Value','%dd%') ?></label>
</div>
<div class="entry_col2">
<div class="vxc_fields vxc_field_value" style="display: none;">
<input type="text" name='value'  placeholder='<?php esc_html_e("Custom Value",'%dd%')?>' class='vx_input_100 vxc_field_input'>
<div class="howto"><?php echo sprintf(__('You can add a form field %s in custom value from following form fields','%dd%'),'<code>{field_id}</code>')?></div>
</div>

<select name="field"  class="vxc_field_option vx_input_100">
<?php echo $options ?>
</select>


</div>
<div class="crm_clear"></div>
</div>  

  </div></div>
  <div class="clear"></div>
  </div>
   </div>
   <!--end field box template--->
   <div class="crm_panel crm_panel_100">
<div class="crm_panel_head2">
<div class="crm_head_div"><span class="crm_head_text ">  <?php esc_html_e("Add New Field", 'gravity-forms-netsuite-crm');?></span> </div>
<div class="crm_btn_div"><i class="fa crm_toggle_btn vx_btn_inner fa-minus" style="display: none;" title="<?php esc_html_e('Expand / Collapse','gravity-forms-netsuite-crm') ?>"></i></div>
<div class="crm_clear"></div> </div>
<div class="more_options crm_panel_content" style="display: block;">

<div class="vx_margin">
<div style="display: table">
  <div style="display: table-cell; width: 85%; padding-right: 14px;">
<select id="vx_add_fields_select" class="vx_input_100" autocomplete="off">
<option value=""></option>
<?php
$json_fields=array();
 foreach($fields as $k=>$v){
     $v['type']=ucfirst($v['type']);
     if(!empty($v['options'])){
     $ops=array();
     foreach($v['options'] as $vv){
    $ops[$vv['value']]=$vv['name'];     
     }    
    $v['options']=$ops; 
     }
     $json_fields[$k]=$v;
   $disable='';
   if(isset($map_fields[$k])){
    $disable='disabled="disabled"';   
   } 
echo '<option value="'.esc_attr($k).'" '.$disable.'>'.esc_html($v['label']).'</option>';  
} ?>
</select>
  </div><div style="display: table-cell;">
 <button type="button" class="button button-default" style="vertical-align: middle;" id="xv_add_custom_field"><i class="fa fa-plus-circle" ></i> <?php esc_html_e('Add Field','gravity-forms-netsuite-crm')?></button>
  
  </div></div>
 

  </div></div>
  <div class="clear"></div>
  </div>
  <!--add new field box template--->
  <script type="text/javascript">
var crm_fields=<?php echo json_encode($json_fields); ?>;

</script> 
 
  </div>

  <div class="clear"></div>
  </div>
  </div>
  <div class="vx_div">
   <div class="vx_head">
<div class="crm_head_div"> <?php esc_html_e('5. When to Send Entry to Netsuite.', 'gravity-forms-netsuite-crm'); ?></div>
<div class="crm_btn_div" title="<?php esc_html_e('Expand / Collapse','gravity-forms-netsuite-crm') ?>"><i class="fa crm_toggle_btn vx_action_btn fa-minus"></i></div>
<div class="crm_clear"></div> 
  </div>

  <div class="vx_group">
  <div class="vx_row">
  <div class="vx_col1">
  <label for="crm_manual_export">
  <?php esc_html_e('Disable Automatic Export', 'gravity-forms-netsuite-crm'); ?>
  <?php gform_tooltip("vx_manual_export") ?>
  </label>
  </div>
  <div class="vx_col2">
  <fieldset>
  <legend class="screen-reader-text"><span>
  <?php esc_html_e('Disable Automatic Export', 'gravity-forms-netsuite-crm'); ?>
  </span></legend>
  <label for="crm_manual_export">
  <input name="meta[manual_export]" id="crm_manual_export" type="checkbox" value="1" <?php echo isset($meta['manual_export'] ) ? 'checked="checked"' : ''; ?>>
  <?php esc_html_e( 'Manually send the entries to Netsuite.', 'gravity-forms-netsuite-crm'); ?> </label>
  </fieldset>
  </div>
  <div style="clear: both;"></div>
  </div>
  <div class="vx_row">
  <div class="vx_col1">
  <label for="crm_optin">
  <?php esc_html_e("Opt-In Condition", 'gravity-forms-netsuite-crm'); ?>
  <?php gform_tooltip("vx_optin_condition") ?>
  </label>
  </div>
  <div class="vx_col2">
  <div>
  <input type="checkbox" style="margin-top: 0px;" id="crm_optin" class="crm_toggle_check" name="meta[optin_enabled]" value="1" <?php echo !empty($meta["optin_enabled"]) ? "checked='checked'" : ""?>/>
  <label for="crm_optin">
  <?php esc_html_e("Enable", 'gravity-forms-netsuite-crm'); ?>
  </label>
  </div>
  <div style="clear: both;"></div>
  <div id="crm_optin_div"  style="margin-top: 16px; <?php echo empty($meta["optin_enabled"]) ? "display:none" : ""?>">
  <div>
  <?php
  $sno=0;
  foreach($filters as $filter_k=>$filter_v){ $filter_k=esc_attr($filter_k);
  $sno++;
                              ?>
  <div class="vx_filter_or" data-id="<?php echo $filter_k ?>">
  <?php if($sno>1){ ?>
  <div class="vx_filter_label">
  <?php esc_html_e('OR','gravity-forms-netsuite-crm') ?>
  </div>
  <?php } ?>
  <div class="vx_filter_div">
  <?php
  if(is_array($filter_v)){
  $sno_i=0;
  foreach($filter_v as $s_k=>$s_v){ $s_k=esc_attr($s_k);   
  $sno_i++;
  
  ?>
  <div class="vx_filter_and">
  <?php if($sno_i>1){ ?>
  <div class="vx_filter_label">
  <?php esc_html_e('AND','gravity-forms-netsuite-crm') ?>
  </div>
  <?php } ?>
  <div class="vx_filter_field vx_filter_field1">
  <select id="crm_optin_field" name="meta[filters][<?php echo $filter_k ?>][<?php echo $s_k ?>][field]" class='optin_selecta'>
  <?php 
  echo $this->gf_fields_options($form_id,$this->post('field',$s_v));
                ?>
  </select>
  </div>
  <div class="vx_filter_field vx_filter_field2">
  <select name="meta[filters][<?php echo $filter_k ?>][<?php echo $s_k ?>][op]" >
  <?php
                 foreach($vx_op as $k=>$v){
  $sel="";
  if($this->post('op',$s_v) == $k)
  $sel='selected="selected"';
                   echo "<option value='".esc_attr($k)."' $sel >".esc_html($v)."</option>";
               } 
              ?>
  </select>
  </div>
  <div class="vx_filter_field vx_filter_field3">
  <input type="text" class="vxc_filter_text" placeholder="<?php esc_html_e('Value','gravity-forms-netsuite-crm') ?>" value="<?php echo $this->post('value',$s_v) ?>" name="meta[filters][<?php echo $filter_k ?>][<?php echo $s_k ?>][value]">
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
  <button class="button button-default button-small vx_add_and" title="<?php esc_html_e('Add AND Filter','gravity-forms-netsuite-crm'); ?>"><i class="vx_icons-s vx_trash_and fa fa-hand-o-right"></i>
  <?php esc_html_e('Add AND Filter','gravity-forms-netsuite-crm') ?>
  </button>
  <?php if($sno>1){ ?>
  <a href="#" class="vx_trash_or">
  <?php esc_html_e('Trash','gravity-forms-netsuite-crm') ?>
  </a>
  <?php } ?>
  </div>
  </div>
  </div>
  <?php
                          }
                      ?>
  <div class="vx_btn_div">
  <button class="button button-default  vx_add_or" title="<?php esc_html_e('Add OR Filter','gravity-forms-netsuite-crm'); ?>"><i class="vx_icons vx_trash_and fa fa-check"></i>
  <?php esc_html_e('Add OR Filter','gravity-forms-netsuite-crm') ?>
  </button>
  </div>
  </div>
  <!--------- template------------>
  <div style="display: none;" id="vx_filter_temp">
  <div class="vx_filter_or">
  <div class="vx_filter_label">
  <?php esc_html_e('OR','gravity-forms-netsuite-crm') ?>
  </div>
  <div class="vx_filter_div">
  <div class="vx_filter_and">
  <div class="vx_filter_label vx_filter_label_and">
  <?php esc_html_e('AND','gravity-forms-netsuite-crm') ?>
  </div>
  <div class="vx_filter_field vx_filter_field1">
  <select id="crm_optin_field" name="field">
  <?php 
  echo $this->gf_fields_options($form_id);
                ?>
  </select>
  </div>
  <div class="vx_filter_field vx_filter_field2">
  <select name="op" >
  <?php
                 foreach($vx_op as $k=>$v){
  
                   echo '<option value="'.esc_attr($k).'" >'.esc_html($v)."</option>";
               } 
              ?>
  </select>
  </div>
  <div class="vx_filter_field vx_filter_field3">
  <input type="text" class="vxc_filter_text" placeholder="<?php esc_html_e('Value','gravity-forms-netsuite-crm') ?>" name="value">
  </div>
  <div class="vx_filter_field vx_filter_field4"><i class="vx_icons vx_trash_and vxc_tips fa fa-trash-o"></i></div>
  <div style="clear: both;"></div>
  </div>
  <div class="vx_btn_div">
  <button class="button button-default button-small vx_add_and" title="<?php esc_html_e('Add AND Filter','gravity-forms-netsuite-crm'); ?>"><i class="vx_icons vx_trash_and  fa fa-hand-o-right"></i>
  <?php esc_html_e('Add AND Filter','gravity-forms-netsuite-crm') ?>
  </button>
  <a href="#" class="vx_trash_or">
  <?php esc_html_e('Trash','gravity-forms-netsuite-crm') ?>
  </a> </div>
  </div>
  </div>
  </div>
  <!--------- template end ------------>
  </div>
  </div>
  <div style="clear: both;"></div>
  </div>


  </div>    
   </div>
<?php
$panel_count=5;
if(!in_array($module,array('SalesOrder'))){  
?>   
<div class="vx_div"> 
  <div class="vx_head">
<div class="crm_head_div"> <?php  echo sprintf(__('%s. Choose Primary Key.',  'gravity-forms-netsuite-crm' ),$panel_count); ?></div>
<div class="crm_btn_div"><i class="fa crm_toggle_btn fa-minus" title="<?php esc_html_e('Expand / Collapse','gravity-forms-netsuite-crm') ?>"></i></div>
<div class="crm_clear"></div> 
  </div>                    
    <div class="vx_group">
  <div class="vx_row">
  <div class="vx_col1">
  <label for="crm_primary_field"><?php esc_html_e('Select Primary Key','gravity-forms-netsuite-crm') ?></label>
  </div><div class="vx_col2">
  <select id="crm_primary_field" name="meta[primary_key]" class="vx_sel vx_input_100" autocomplete="off">
  <?php echo $this->crm_select($search_fields,$this->post('primary_key',$meta)); ?>
  </select> 
  <div class="description" style="float: none; width: 90%"><?php esc_html_e('If you want to update a pre-existing object, select what should be used as a unique identifier ("Primary Key"). For example, this may be an email address, lead ID, or address. When a new entry comes in with the same "Primary Key" you select, a new object will not be created, instead the pre-existing object will be updated.', 'gravity-forms-netsuite-crm'); ?></div>
  </div>
  <div class="clear"></div>
  </div>
 <div class="vx_row">
  <div class="vx_col1">
  <label for="vx_update"><?php esc_html_e('Update Entry ', 'gravity-forms-netsuite-crm');?></label>
  </div>
  <div class="vx_col2">
  <input type="checkbox" style="margin-top: 0px;" id="vx_update" class="crm_toggle_check" name="meta[update]" value="1" <?php echo !empty($meta['update']) ? 'checked="checked"' : ''?> autocomplete="off"/>
    <label for="vx_update"><?php esc_html_e('Do not update entry, if already exists', 'gravity-forms-netsuite-crm'); ?></label>
  
  </div>
  <div class="clear"></div>
  </div>
    
  </div>
</div>
<?php
}
       $account=$this->account;    
           $id=$this->post('id',$meta);

$status_list=$this->post('status_list',$info_meta); 
$folders=$this->post('folders',$info_meta); 
$type_list=$this->post('note_types',$info_meta); 
$emp_list=$this->post('emp_list',$info_meta); 
$loc_list=$this->post('loc_list',$info_meta); 
$source_list=$this->post('source_list',$info_meta); 
$ship_list=$this->post('ship_list',$info_meta); 
$pay_list=$this->post('pay_list',$info_meta); 
$class_list=$this->post('class_list',$info_meta); 

           $meta_status=isset($meta['status']) ? $meta['status'] : '';
           $meta_folder=isset($meta['folder']) ? $meta['folder'] : '';
           $meta_emp=isset($meta['emp']) ? $meta['emp'] : '';
           $meta_loc=isset($meta['loc']) ? $meta['loc'] : '';
           $meta_source=isset($meta['lead_source']) ? $meta['lead_source'] : '';
           $meta_ship=isset($meta['ship_method']) ? $meta['ship_method'] : '';
           $meta_pay=isset($meta['pay_method']) ? $meta['pay_method'] : '';
           $meta_class=isset($meta['class']) ? $meta['class'] : '';
           
           $note_type=isset($meta['note_type']) ? $meta['note_type'] : '';
           $status_lists=array('_completed'=>'Completed', '_inProgress'=>'In Progress', '_notStarted'=>'Not Started');
           if($module == 'PhoneCall'){
    $status_lists=array('_completed'=>'Completed', '_scheduled'=>'Scheduled');
}
$company_feeds=$this->get_object_feeds($form_id,$account,'Customer');
$contact_feeds=$this->get_object_feeds($form_id,$account,'Contact');

if( in_array($module,array('Customer','SupportCase'))){
?> 
<div class="vx_div vx_refresh_panel ">    
      <div class="vx_head ">
<div class="crm_head_div"> <?php echo sprintf(__('%s. Post Data to Netsuite Forms ',  'gravity-forms-netsuite-crm' ),++$panel_count); ?></div>
<div class="crm_btn_div"><i class="fa crm_toggle_btn fa-minus" title="<?php esc_html_e('Expand / Collapse','gravity-forms-netsuite-crm') ?>"></i></div>
<div class="crm_clear"></div> 
  </div>                 
    <div class="vx_group">
       <div class="vx_row"> 
   <div class="vx_col1"> 
  <label for="crm_forms"><?php esc_html_e('Use Forms ', 'gravity-forms-netsuite-crm'); ?></label>
  </div>
  <div class="vx_col2">
  <input type="checkbox" style="margin-top: 0px;" id="crm_forms" class="crm_toggle_check" name="meta[forms_check]" value="1" <?php echo !empty($meta['forms_check']) ? "checked='checked'" : ""?> autocomplete="off"/>
    <label for="owner"><?php esc_html_e("Yes, Send Form data as Netsuite Customer forms ", 'gravity-forms-netsuite-crm'); ?></label>
  </div>
<div class="clear"></div>
</div>
<div id="crm_forms_div" style="<?php echo empty($meta['forms_check']) ? "display:none" : ""?>">
   <div class="vx_row"> 
   <div class="vx_col1"> 
  <label><?php esc_html_e("Online Form URL ", 'gravity-forms-netsuite-crm');?></label>
  </div>
  <div class="vx_col2">
  <input type="text"  class="vx_input_100" name="meta[form_url]" value="<?php echo $this->post('form_url',$meta) ?>" autocomplete="off"/>
    <div class="howto"><?php esc_html_e('Go to Setup->Marketing->Online Customer Forms-> open any form -> select "External" tab and copy "PUBLISHABLE FORM URL"', 'gravity-forms-netsuite-crm'); ?></div>
  </div>
<div class="clear"></div>
</div>
  
  </div>

  </div>
</div>
<?php
}
if( in_array($module,array('Customer'))){        
    ?>    
<div class="vx_div vx_refresh_panel ">    
      <div class="vx_head ">
<div class="crm_head_div"> <?php echo sprintf(__('%s. Customer Status(Stage)',  'gravity-forms-netsuite-crm' ),++$panel_count); ?></div>
<div class="crm_btn_div"><i class="fa crm_toggle_btn fa-minus" title="<?php esc_html_e('Expand / Collapse','gravity-forms-netsuite-crm') ?>"></i></div>
<div class="crm_clear"></div> 
  </div>                 
    <div class="vx_group ">
   <div class="vx_row"> 
   <div class="vx_col1"> 
  <label for="crm_owner"><?php esc_html_e("Status ", 'gravity-forms-netsuite-crm'); gform_tooltip('vx_status_check');?></label>
  </div>
  <div class="vx_col2">
  <input type="checkbox" style="margin-top: 0px;" id="crm_owner" class="crm_toggle_check <?php if(empty($status_list)){echo 'vx_refresh_btn';} ?>" name="meta[status_check]" value="1" <?php echo !empty($meta['status_check']) ? "checked='checked'" : ""?> autocomplete="off"/>
    <label for="owner"><?php esc_html_e("Enable", 'gravity-forms-netsuite-crm'); ?></label>
  </div>
<div class="clear"></div>
</div>
    <div id="crm_owner_div" style="<?php echo empty($meta['status_check']) ? "display:none" : ""?>">
  <div class="vx_row">
  <div class="vx_col1">
  <label><?php esc_html_e('Status List ','gravity-forms-netsuite-crm'); gform_tooltip('vx_status_list'); ?></label>
  </div>
  <div class="vx_col2">
  <button class="button vx_refresh_data" data-id="refresh_status" type="button" autocomplete="off" style="vertical-align: baseline;">
  <span class="reg_ok"><i class="fa fa-refresh"></i> <?php esc_html_e('Refresh Data','gravity-forms-netsuite-crm') ?></span>
  <span class="reg_proc"><i class="fa fa-refresh fa-spin"></i> <?php esc_html_e('Refreshing...','gravity-forms-netsuite-crm') ?></span>
  </button>
  </div> 
   <div class="clear"></div>
  </div> 

  <div class="vx_row">
   <div class="vx_col1">
  <label for="crm_sel_user"><?php esc_html_e('Select Status ','gravity-forms-netsuite-crm'); gform_tooltip('vx_sel_status'); ?></label>
</div> 
<div class="vx_col2">

  <select id="crm_sel_user" name="meta[status]" style="width: 100%;" autocomplete="off">
  <?php echo $this->gen_select($status_list,$meta_status,__('Select Status','gravity-forms-netsuite-crm')); ?>
  </select>

   </div>

   <div class="clear"></div>
   </div>
 
  
  </div>
  

  </div>
  </div>
  
<div class="vx_div vx_refresh_panel ">    
      <div class="vx_head ">
<div class="crm_head_div"> <?php echo sprintf(__('%s. Default Folder for Files (Required for File Fields)',  'gravity-forms-netsuite-crm' ),++$panel_count); ?></div>
<div class="crm_btn_div"><i class="fa crm_toggle_btn fa-minus" title="<?php esc_html_e('Expand / Collapse','gravity-forms-netsuite-crm') ?>"></i></div>
<div class="crm_clear"></div> 
  </div>                 
    <div class="vx_group ">


  <div class="vx_row">
  <div class="vx_col1">
  <label><?php esc_html_e('Folders ','gravity-forms-netsuite-crm'); gform_tooltip('vx_folders_list'); ?></label>
  </div>
  <div class="vx_col2">
  <button class="button vx_refresh_data" data-id="refresh_folders" type="button" autocomplete="off" style="vertical-align: baseline;">
  <span class="reg_ok"><i class="fa fa-refresh"></i> <?php esc_html_e('Refresh Data','gravity-forms-netsuite-crm') ?></span>
  <span class="reg_proc"><i class="fa fa-refresh fa-spin"></i> <?php esc_html_e('Refreshing...','gravity-forms-netsuite-crm') ?></span>
  </button>
  </div> 
   <div class="clear"></div>
  </div> 

  <div class="vx_row">
   <div class="vx_col1">
  <label for="crm_sel_folder"><?php esc_html_e('Select Folder ','gravity-forms-netsuite-crm'); gform_tooltip('vx_sel_folder'); ?></label>
</div> 
<div class="vx_col2">

  <select id="crm_sel_folder" name="meta[folder]" style="width: 100%;" autocomplete="off">
  <?php echo $this->gen_select($folders,$meta_folder,__('Select Folder','gravity-forms-netsuite-crm')); ?>
  </select>

   </div>

   <div class="clear"></div>
   </div>
 
  
  
  

  </div>
  </div>  
<?php
}
if($module == 'SalesOrder'){
$status_list=array('_pendingApproval'=>'Pending Approval','_pendingFulfillment'=>'Pending Fulfillment');
//,'_cancelled'=>'Cancelled','_partiallyFulfilled'=>'Partially Fulfilled','_pendingBillingPartFulfilled'=>'Pending Billing Part Fulfilled','_pendingBilling'=>'Pending Billing','_fullyBilled'=>'Fully Billed','_closed'=>'Closed','_undefined'=>'Undefined'
?>
<div class="vx_div vx_refresh_panel ">    
      <div class="vx_head ">
<div class="crm_head_div"> <?php echo sprintf(__('%s. Assign Customer ',  'gravity-forms-netsuite-crm' ),++$panel_count).$req_span2; 
?></div>
<div class="crm_btn_div"><i class="fa crm_toggle_btn fa-minus" title="<?php esc_html_e('Expand / Collapse','gravity-forms-netsuite-crm') ?>"></i></div>
<div class="crm_clear"></div> 
  </div>                 
    <div class="vx_group ">

        <div class="vx_row"> 
   <div class="vx_col1"> 
  <label for="customer_check"><?php esc_html_e("Assign Customer", 'gravity-forms-netsuite-crm');?></label>
  </div>
  <div class="vx_col2">
  <input type="checkbox" style="margin-top: 0px;" id="customer_check" class="crm_toggle_check" name="meta[customer_check]" value="1" <?php echo !empty($meta["customer_check"]) ? "checked='checked'" : ""?> autocomplete="off"/>
    <label for="customer_check"><?php esc_html_e("Enable", 'gravity-forms-netsuite-crm'); ?></label>
  </div>
<div class="clear"></div>
</div>
    <div id="customer_check_div" style="<?php echo empty($meta["customer_check"]) ? "display:none" : ""?>">
         <div class="vx_row">
   <div class="vx_col1">
  <label for="object_customer"><?php esc_html_e('Select Customer Feed','gravity-forms-netsuite-crm'); ?></label>
</div> 
<div class="vx_col2">

  <select id="object_customer" name="meta[object_customer]" style="width: 100%;" autocomplete="off">
  <?php echo $this->gen_select($company_feeds ,$meta['object_customer'],__('Select Customer Feed','gravity-forms-netsuite-crm')); ?>
  </select>

   </div>

   <div class="clear"></div>
   </div>
    </div>

  </div>
  </div>
  
<div class="vx_div vx_refresh_panel ">    
      <div class="vx_head ">
<div class="crm_head_div"> <?php echo sprintf(__('%s. Store Location',  'gravity-forms-netsuite-crm' ),++$panel_count).$req_span2; ?></div>
<div class="crm_btn_div"><i class="fa crm_toggle_btn fa-minus" title="<?php esc_html_e('Expand / Collapse','gravity-forms-netsuite-crm') ?>"></i></div>
<div class="crm_clear"></div> 
  </div>             
    <div class="vx_group ">
   <div class="vx_row"> 
   <div class="vx_col1"> 
  <label for="crm_loc"><?php esc_html_e("Location ", 'gravity-forms-netsuite-crm'); ?></label>
  </div>
  <div class="vx_col2">
  <input type="checkbox" style="margin-top: 0px;" id="crm_loc" data-id="refresh_locs" class="crm_toggle_check <?php if(empty($loc_list)){echo 'vx_refresh_btn';} ?>" name="meta[loc_check]" value="1" <?php echo !empty($meta['loc_check']) ? "checked='checked'" : ""?> autocomplete="off"/>
    <label for="crm_emp"><?php esc_html_e("Enable", 'gravity-forms-netsuite-crm'); ?></label>
  </div>
<div class="clear"></div>
</div>
    <div id="crm_loc_div" style="<?php echo empty($meta['loc_check']) ? "display:none" : ""?>">
  <div class="vx_row">
  <div class="vx_col1">
  <label><?php esc_html_e('Locations List ','gravity-forms-netsuite-crm'); ?></label>
  </div>
  <div class="vx_col2">
  <button class="button vx_refresh_data" data-id="refresh_locs" type="button" autocomplete="off" style="vertical-align: baseline;">
  <span class="reg_ok"><i class="fa fa-refresh"></i> <?php esc_html_e('Refresh Data','gravity-forms-netsuite-crm') ?></span>
  <span class="reg_proc"><i class="fa fa-refresh fa-spin"></i> <?php esc_html_e('Refreshing...','gravity-forms-netsuite-crm') ?></span>
  </button>
  </div> 
   <div class="clear"></div>
  </div> 

  <div class="vx_row">
   <div class="vx_col1">
  <label for="crm_sel_loc"><?php esc_html_e('Select Location ','gravity-forms-netsuite-crm'); ?></label>
</div> 
<div class="vx_col2">
  <select id="crm_sel_loc" name="meta[loc]" style="width: 100%;" autocomplete="off">
  <?php echo $this->gen_select($loc_list,$meta_loc,__('Select Location','gravity-forms-netsuite-crm')); ?>
  </select>

   </div>

   <div class="clear"></div>
   </div>
 
  
  </div>
  

  </div>
  </div>
  
<div class="vx_div vx_refresh_panel ">    
      <div class="vx_head ">
<div class="crm_head_div"> <?php echo sprintf(__('%s. Shipping Method',  'gravity-forms-netsuite-crm' ),++$panel_count); ?></div>
<div class="crm_btn_div"><i class="fa crm_toggle_btn fa-minus" title="<?php esc_html_e('Expand / Collapse','gravity-forms-netsuite-crm') ?>"></i></div>
<div class="crm_clear"></div> 
  </div>             
    <div class="vx_group ">
   <div class="vx_row"> 
   <div class="vx_col1"> 
  <label for="crm_lead_source"><?php esc_html_e("Shipping Method ", 'gravity-forms-netsuite-crm'); ?></label>
  </div>
  <div class="vx_col2">
  <input type="checkbox" style="margin-top: 0px;" id="crm_ship_method" data-id="refresh_ship_method" class="crm_toggle_check <?php if(empty($ship_list)){echo 'vx_refresh_btn';} ?>" name="meta[ship_method_check]" value="1" <?php echo !empty($meta['ship_method_check']) ? "checked='checked'" : ""?> autocomplete="off"/>
    <label for="crm_lead_source"><?php esc_html_e("Enable", 'gravity-forms-netsuite-crm'); ?></label>
  </div>
<div class="clear"></div>
</div>
    <div id="crm_ship_method_div" style="<?php echo empty($meta['ship_method_check']) ? "display:none" : ""?>">
  <div class="vx_row">
  <div class="vx_col1">
  <label><?php esc_html_e('Methods ','gravity-forms-netsuite-crm'); ?></label>
  </div>
  <div class="vx_col2">
  <button class="button vx_refresh_data" data-id="refresh_ship_method" type="button" autocomplete="off" style="vertical-align: baseline;">
  <span class="reg_ok"><i class="fa fa-refresh"></i> <?php esc_html_e('Refresh Data','gravity-forms-netsuite-crm') ?></span>
  <span class="reg_proc"><i class="fa fa-refresh fa-spin"></i> <?php esc_html_e('Refreshing...','gravity-forms-netsuite-crm') ?></span>
  </button>
  </div> 
   <div class="clear"></div>
  </div> 

  <div class="vx_row">
   <div class="vx_col1">
  <label for="crm_sel_ship"><?php esc_html_e('Select Method ','gravity-forms-netsuite-crm'); ?></label>
</div> 
<div class="vx_col2">
  <select id="crm_sel_ship" name="meta[ship_method]" style="width: 100%;" autocomplete="off">
  <?php echo $this->gen_select($ship_list,$meta_ship,__('Select Shipping Method','gravity-forms-netsuite-crm')); ?>
  </select>

   </div>

   <div class="clear"></div>
   </div>
 
  
  </div>
  

  </div>
  </div>
  
<div class="vx_div vx_refresh_panel ">    
      <div class="vx_head ">
<div class="crm_head_div"> <?php echo sprintf(__('%s. Payment Method',  'gravity-forms-netsuite-crm' ),++$panel_count); ?></div>
<div class="crm_btn_div"><i class="fa crm_toggle_btn fa-minus" title="<?php esc_html_e('Expand / Collapse','gravity-forms-netsuite-crm') ?>"></i></div>
<div class="crm_clear"></div> 
  </div>             
    <div class="vx_group ">
   <div class="vx_row"> 
   <div class="vx_col1"> 
  <label for="crm_lead_source"><?php esc_html_e("Payment Method ", 'gravity-forms-netsuite-crm'); ?></label>
  </div>
  <div class="vx_col2">
  <input type="checkbox" style="margin-top: 0px;" id="crm_pay_method" data-id="refresh_pay_method" class="crm_toggle_check <?php if(empty($pay_list)){echo 'vx_refresh_btn';} ?>" name="meta[pay_method_check]" value="1" <?php echo !empty($meta['pay_method_check']) ? "checked='checked'" : ""?> autocomplete="off"/>
    <label for="crm_lead_source"><?php esc_html_e("Enable", 'gravity-forms-netsuite-crm'); ?></label>
  </div>
<div class="clear"></div>
</div>
    <div id="crm_pay_method_div" style="<?php echo empty($meta['pay_method_check']) ? "display:none" : ""?>">
  <div class="vx_row">
  <div class="vx_col1">
  <label><?php esc_html_e('Methods ','gravity-forms-netsuite-crm'); ?></label>
  </div>
  <div class="vx_col2">
  <button class="button vx_refresh_data" data-id="refresh_pay_method" type="button" autocomplete="off" style="vertical-align: baseline;">
  <span class="reg_ok"><i class="fa fa-refresh"></i> <?php esc_html_e('Refresh Data','gravity-forms-netsuite-crm') ?></span>
  <span class="reg_proc"><i class="fa fa-refresh fa-spin"></i> <?php esc_html_e('Refreshing...','gravity-forms-netsuite-crm') ?></span>
  </button>
  </div> 
   <div class="clear"></div>
  </div> 

  <div class="vx_row">
   <div class="vx_col1">
  <label for="crm_sel_pay"><?php esc_html_e('Select Method ','gravity-forms-netsuite-crm'); ?></label>
</div> 
<div class="vx_col2">
  <select id="crm_sel_pay" name="meta[pay_method]" style="width: 100%;" autocomplete="off">
  <?php echo $this->gen_select($pay_list,$meta_pay,__('Select Payment Method','gravity-forms-netsuite-crm')); ?>
  </select>

   </div>

   <div class="clear"></div>
   </div>
 
  
  </div>
  

  </div>
  </div>
  
<div class="vx_div vx_refresh_panel ">    
      <div class="vx_head ">
<div class="crm_head_div"> <?php echo sprintf(__('%s. Order Class',  'gravity-forms-netsuite-crm' ),++$panel_count); ?></div>
<div class="crm_btn_div"><i class="fa crm_toggle_btn fa-minus" title="<?php esc_html_e('Expand / Collapse','gravity-forms-netsuite-crm') ?>"></i></div>
<div class="crm_clear"></div> 
  </div>             
    <div class="vx_group ">
   <div class="vx_row"> 
   <div class="vx_col1"> 
  <label for="crm_lead_source"><?php esc_html_e("Order Class ", 'gravity-forms-netsuite-crm'); ?></label>
  </div>
  <div class="vx_col2">
  <input type="checkbox" style="margin-top: 0px;" id="crm_class" data-id="refresh_class" class="crm_toggle_check <?php if(empty($class_list)){echo 'vx_refresh_btn';} ?>" name="meta[class_check]" value="1" <?php echo !empty($meta['class_check']) ? "checked='checked'" : ""?> autocomplete="off"/>
    <label for="crm_lead_source"><?php esc_html_e("Enable", 'gravity-forms-netsuite-crm'); ?></label>
  </div>
<div class="clear"></div>
</div>
    <div id="crm_class_div" style="<?php echo empty($meta['class_check']) ? "display:none" : ""?>">
  <div class="vx_row">
  <div class="vx_col1">
  <label><?php esc_html_e('Classes ','gravity-forms-netsuite-crm'); ?></label>
  </div>
  <div class="vx_col2">
  <button class="button vx_refresh_data" data-id="refresh_class" type="button" autocomplete="off" style="vertical-align: baseline;">
  <span class="reg_ok"><i class="fa fa-refresh"></i> <?php esc_html_e('Refresh Data','gravity-forms-netsuite-crm') ?></span>
  <span class="reg_proc"><i class="fa fa-refresh fa-spin"></i> <?php esc_html_e('Refreshing...','gravity-forms-netsuite-crm') ?></span>
  </button>
  </div> 
   <div class="clear"></div>
  </div> 

  <div class="vx_row">
   <div class="vx_col1">
  <label for="crm_sel_class"><?php esc_html_e('Select Class ','gravity-forms-netsuite-crm'); ?></label>
</div> 
<div class="vx_col2">
  <select id="crm_sel_class" name="meta[class]" style="width: 100%;" autocomplete="off">
  <?php echo $this->gen_select($class_list,$meta_class,__('Select Order Class','gravity-forms-netsuite-crm')); ?>
  </select>

   </div>

   <div class="clear"></div>
   </div>
 
  
  </div>
  

  </div>
  </div>
<div class="vx_div">    
      <div class="vx_head ">
<div class="crm_head_div"> <?php $panel_count++;  echo sprintf(__('%s. Order Status',  'gravity-forms-netsuite-crm' ),$panel_count); ?></div>
<div class="crm_btn_div"><i class="fa crm_toggle_btn fa-minus" title="<?php esc_html_e('Expand / Collapse','gravity-forms-netsuite-crm') ?>"></i></div>
<div class="crm_clear"></div> 
  </div>                 
    <div class="vx_group ">

<div class="vx_row">
   <div class="vx_col1">
  <label for="crm_order_status"><?php esc_html_e('Order Status ','gravity-forms-netsuite-crm'); ?></label>
</div> <div class="vx_col2">

  <select id="crm_order_status" class="vx_input_100" name="meta[order_status]" style="width: 100%;" autocomplete="off">
  <?php echo $this->gen_select($status_list,$meta['order_status'],__('Select Order Status','gravity-forms-netsuite-crm')); ?>
  </select>

  </div>
   <div class="clear"></div>
   </div>  

  </div>
</div>
<?php    
}
  
$priority_lists=array('_high'=>'High', '_medium'=>'Medium', '_low'=>'Low');
if(in_array($module,array('Task','PhoneCall'))){
?>                
<div class="vx_div vx_refresh_panel ">    
      <div class="vx_head ">
<div class="crm_head_div"> <?php $panel_count++;  echo sprintf(__('%s. Object Priority',  'gravity-forms-netsuite-crm' ),$panel_count); ?></div>
<div class="crm_btn_div"><i class="fa crm_toggle_btn fa-minus" title="<?php esc_html_e('Expand / Collapse','gravity-forms-netsuite-crm') ?>"></i></div>
<div class="crm_clear"></div> 
  </div>                 
    <div class="vx_group ">

<div class="vx_row">
   <div class="vx_col1">
  <label for="crm_sel_priority"><?php esc_html_e('Select Priority ','gravity-forms-netsuite-crm'); gform_tooltip('vx_priority'); ?></label>
</div> <div class="vx_col2">

  <select id="crm_sel_priority" class="vx_input_100" name="meta[priority_sel]" style="width: 100%;" autocomplete="off">
  <?php echo $this->gen_select($priority_lists,$meta['priority_sel'],__('Select Priority','gravity-forms-netsuite-crm')); ?>
  </select>

  </div>
   <div class="clear"></div>
   </div>  

  </div>
</div>

<div class="vx_div vx_refresh_panel ">    
      <div class="vx_head ">
<div class="crm_head_div"> <?php $panel_count++;  echo sprintf(__('%s. Object Status',  'gravity-forms-netsuite-crm' ),$panel_count); ?></div>
<div class="crm_btn_div"><i class="fa crm_toggle_btn fa-minus" title="<?php esc_html_e('Expand / Collapse','gravity-forms-netsuite-crm') ?>"></i></div>
<div class="crm_clear"></div> 
  </div>                 
    <div class="vx_group ">

<div class="vx_row">
   <div class="vx_col1">
  <label for="crm_sel_status"><?php esc_html_e('Select Status ','gravity-forms-netsuite-crm'); gform_tooltip('vx_status'); ?></label>
</div> <div class="vx_col2">

  <select id="crm_sel_status" class="vx_input_100" name="meta[status_sel]" style="width: 100%;" autocomplete="off">
  <?php echo $this->gen_select($status_lists,$meta['status_sel'],__('Select Status','gravity-forms-netsuite-crm')); ?>
  </select>

  </div>
   <div class="clear"></div>
   </div>  

  </div>
</div>
<?php
}
if( in_array($module,array('Customer','SupportCase','Contact','Task'))){
?>
<div class="vx_div vx_refresh_panel ">    
      <div class="vx_head ">
<div class="crm_head_div"> <?php echo sprintf(__('%s. Assign Company/Customer',  'gravity-forms-netsuite-crm' ),++$panel_count); 
?></div>
<div class="crm_btn_div"><i class="fa crm_toggle_btn fa-minus" title="<?php esc_html_e('Expand / Collapse','gravity-forms-netsuite-crm') ?>"></i></div>
<div class="crm_clear"></div> 
  </div>                 
    <div class="vx_group ">

        <div class="vx_row"> 
   <div class="vx_col1"> 
  <label for="company_check"><?php esc_html_e("Assign Company/Customer ", 'gravity-forms-netsuite-crm'); gform_tooltip('vx_assign_company');?></label>
  </div>
  <div class="vx_col2">
  <input type="checkbox" style="margin-top: 0px;" id="company_check" class="crm_toggle_check" name="meta[company_check]" value="1" <?php echo !empty($meta["company_check"]) ? "checked='checked'" : ""?> autocomplete="off"/>
    <label for="contact_check"><?php esc_html_e("Enable", 'gravity-forms-netsuite-crm'); ?></label>
  </div>
<div class="clear"></div>
</div>
    <div id="company_check_div" style="<?php echo empty($meta["company_check"]) ? "display:none" : ""?>">
         <div class="vx_row">
   <div class="vx_col1">
  <label for="object_company"><?php esc_html_e('Select Company/Customer Feed','gravity-forms-netsuite-crm'); gform_tooltip('vx_sel_company'); ?></label>
</div> 
<div class="vx_col2">

  <select id="object_company" name="meta[object_company]" style="width: 100%;" autocomplete="off">
  <?php echo $this->gen_select($company_feeds ,$meta['object_company'],__('Select Company/Contact Feed','gravity-forms-netsuite-crm')); ?>
  </select>

   </div>

   <div class="clear"></div>
   </div>
    </div>

  </div>
  </div>
 <?php
}
if( in_array($module,array('SupportCase','Task'))){
?>
<div class="vx_div vx_refresh_panel ">    
      <div class="vx_head ">
<div class="crm_head_div"> <?php echo sprintf(__('%s. Assign Contact',  'gravity-forms-netsuite-crm' ),++$panel_count); 
?></div>
<div class="crm_btn_div"><i class="fa crm_toggle_btn fa-minus" title="<?php esc_html_e('Expand / Collapse','gravity-forms-netsuite-crm') ?>"></i></div>
<div class="crm_clear"></div> 
  </div>                 
    <div class="vx_group ">

        <div class="vx_row"> 
   <div class="vx_col1"> 
  <label for="contact_check"><?php esc_html_e("Assign Contact ", 'gravity-forms-netsuite-crm'); ?></label>
  </div>
  <div class="vx_col2">
  <input type="checkbox" style="margin-top: 0px;" id="contact_check" class="crm_toggle_check" name="meta[contact_check]" value="1" <?php echo !empty($meta["contact_check"]) ? "checked='checked'" : ""?> autocomplete="off"/>
    <label for="contact_check"><?php esc_html_e("Enable", 'gravity-forms-netsuite-crm'); ?></label>
  </div>
<div class="clear"></div>
</div>
    <div id="contact_check_div" style="<?php echo empty($meta["contact_check"]) ? "display:none" : ""?>">
         <div class="vx_row">
   <div class="vx_col1">
  <label for="object_contact"><?php esc_html_e('Select Contact Feed','gravity-forms-netsuite-crm');  ?></label>
</div> 
<div class="vx_col2">

  <select id="object_company" name="meta[object_contact]" style="width: 100%;" autocomplete="off">
  <?php echo $this->gen_select($contact_feeds ,$meta['object_contact'],__('Select Contact Feed','gravity-forms-netsuite-crm')); ?>
  </select>

   </div>

   <div class="clear"></div>
   </div>
    </div>

  </div>
  </div>
 <?php
}

 if( in_array($module,array('SalesOrder','Customer')) ){
?>
<div class="vx_div vx_refresh_panel ">    
      <div class="vx_head ">
<div class="crm_head_div"> <?php echo sprintf(__('%s. Lead Source',  'gravity-forms-netsuite-crm' ),++$panel_count); ?></div>
<div class="crm_btn_div"><i class="fa crm_toggle_btn fa-minus" title="<?php esc_html_e('Expand / Collapse','gravity-forms-netsuite-crm') ?>"></i></div>
<div class="crm_clear"></div> 
  </div>             
    <div class="vx_group ">
   <div class="vx_row"> 
   <div class="vx_col1"> 
  <label for="crm_lead_source"><?php esc_html_e("Lead Source ", 'gravity-forms-netsuite-crm'); ?></label>
  </div>
  <div class="vx_col2">
  <input type="checkbox" style="margin-top: 0px;" id="crm_lead_source" data-id="refresh_lead_source" class="crm_toggle_check <?php if(empty($source_list)){echo 'vx_refresh_btn';} ?>" name="meta[lead_source_check]" value="1" <?php echo !empty($meta['lead_source_check']) ? "checked='checked'" : ""?> autocomplete="off"/>
    <label for="crm_lead_source"><?php esc_html_e("Enable", 'gravity-forms-netsuite-crm'); ?></label>
  </div>
<div class="clear"></div>
</div>
    <div id="crm_lead_source_div" style="<?php echo empty($meta['lead_source_check']) ? "display:none" : ""?>">
  <div class="vx_row">
  <div class="vx_col1">
  <label><?php esc_html_e('Lead Sources ','gravity-forms-netsuite-crm'); ?></label>
  </div>
  <div class="vx_col2">
  <button class="button vx_refresh_data" data-id="refresh_lead_source" type="button" autocomplete="off" style="vertical-align: baseline;">
  <span class="reg_ok"><i class="fa fa-refresh"></i> <?php esc_html_e('Refresh Data','gravity-forms-netsuite-crm') ?></span>
  <span class="reg_proc"><i class="fa fa-refresh fa-spin"></i> <?php esc_html_e('Refreshing...','gravity-forms-netsuite-crm') ?></span>
  </button>
  </div> 
   <div class="clear"></div>
  </div> 

  <div class="vx_row">
   <div class="vx_col1">
  <label for="crm_sel_source"><?php esc_html_e('Select Source ','gravity-forms-netsuite-crm'); ?></label>
</div> 
<div class="vx_col2">
  <select id="crm_sel_source" name="meta[lead_source]" style="width: 100%;" autocomplete="off">
  <?php echo $this->gen_select($source_list,$meta_source,__('Select Lead Source','gravity-forms-netsuite-crm')); ?>
  </select>

   </div>

   <div class="clear"></div>
   </div>
 
  
  </div>
  

  </div>
  </div>
  <?php
 }
  ?>
<div class="vx_div">
     <div class="vx_head">
<div class="crm_head_div"> <?php echo sprintf(__('%s. Add Note.', 'gravity-forms-netsuite-crm'),$panel_count+=1); ?></div>
<div class="crm_btn_div" title="<?php esc_html_e('Expand / Collapse','gravity-forms-netsuite-crm') ?>"><i class="fa crm_toggle_btn fa-minus"></i></div>
<div class="crm_clear"></div> 
  </div>


  <div class="vx_group">

    <div class="vx_row">
  <div class="vx_col1">
  <label for="crm_note">
  <?php esc_html_e("Add Note", 'gravity-forms-netsuite-crm'); ?>
  <?php gform_tooltip("vx_entry_note") ?>
  </label>
  </div>
  <div class="vx_col2">
  <input type="checkbox" style="margin-top: 0px;" id="crm_note" class="crm_toggle_check" name="meta[note_check]" value="1" <?php echo !empty($meta['note_check']) ? "checked='checked'" : ""?>/>
  <label for="crm_note_div">
  <?php esc_html_e("Enable", 'gravity-forms-netsuite-crm'); ?>
  </label>
  </div>
  <div style="clear: both;"></div>
  </div>
  <div id="crm_note_div" style="margin-top: 16px; <?php echo empty($meta["note_check"]) ? "display:none" : ""?>">
  <div class="vx_row">
  <div class="vx_col1">
  <label for="crm_note_fields">
  <?php esc_html_e( 'Note Fields', 'gravity-forms-netsuite-crm' ); gform_tooltip("vx_note_fields") ?>
  </label>
  </div>
  <div class="vx_col2">
  <select name="meta[note_fields][]" id="crm_note_fields" multiple="multiple" class="crm_sel crm_note_sel crm_sel2 vx_input_100"  autocomplete="off">

  <?php echo $this->gf_fields_options($form_id,$this->post('note_fields',$meta)); ?>
  </select>
    <span class="howto">
  <?php esc_html_e('You can select multiple fields.', 'gravity-forms-netsuite-crm'); ?>
  </span>
   </div>
  <div style="clear: both;"></div>
  </div>
  
  <div class="vx_row">
  <div class="vx_col1">
  <label for="crm_note_dir">
  <?php esc_html_e( 'Note Direction', 'gravity-forms-netsuite-crm' ); ?>
  </label>
  </div>
  <div class="vx_col2">
  <select name="meta[note_dir]" id="crm_note_dir" class="crm_sel crm_note_sel crm_sel2 vx_input_100"  autocomplete="off">
  <?php $note_dirs=array('_incoming'=>'Incoming','_outgoing'=>'Outgoing'); 
echo $this->gen_select($note_dirs,$this->post('note_dir',$meta),__('Select Direction','gravity-forms-netsuite-crm'));
   ?>
  </select>

   </div>
  <div style="clear: both;"></div>
  </div>
  
 <div class="vx_row">
  <div class="vx_col1">
  <label><?php esc_html_e('Note Type ','gravity-forms-netsuite-crm'); ?></label>
  </div>
  <div class="vx_col2">
  <button class="button vx_refresh_data" data-id="refresh_note_types" type="button" autocomplete="off" style="vertical-align: baseline;">
  <span class="reg_ok"><i class="fa fa-refresh"></i> <?php esc_html_e('Refresh Data','gravity-forms-netsuite-crm') ?></span>
  <span class="reg_proc"><i class="fa fa-refresh fa-spin"></i> <?php esc_html_e('Refreshing...','gravity-forms-netsuite-crm') ?></span>
  </button>
  </div> 
   <div class="clear"></div>
  </div> 

  <div class="vx_row">
   <div class="vx_col1">
  <label for="crm_note_type"><?php esc_html_e('Select Type ','gravity-forms-netsuite-crm'); ?></label>
</div> 
<div class="vx_col2">

  <select id="crm_note_type" name="meta[note_type]" style="width: 100%;" autocomplete="off">
  <?php echo $this->gen_select($type_list,$note_type,__('Select Type','gravity-forms-netsuite-crm')); ?>
  </select>

   </div>

   <div class="clear"></div>
   </div>
  
  
  <div class="vx_row">
  <div class="vx_col1">
  <label for="crm_disable_note">
  <?php esc_html_e( 'Disable Note', 'gravity-forms-netsuite-crm' ); gform_tooltip("vx_disable_note") ?>
  </label>
  </div>
  <div class="vx_col2">
  
  <input type="checkbox" style="margin-top: 0px;" id="crm_disable_note" class="crm_toggle_check" name="meta[disable_entry_note]" value="1" <?php echo !empty($meta['disable_entry_note']) ? "checked='checked'" : ""?>/>
  <label for="crm_disable_note">
  <?php esc_html_e('Do not Add Note if entry already exists in Nesuite', 'gravity-forms-netsuite-crm'); ?>
  </label>
    
   </div>
  <div style="clear: both;"></div>
  </div>
  
  </div>
  
  </div>
  </div> 
    
<div class="vx_div vx_refresh_panel ">    
      <div class="vx_head ">
<div class="crm_head_div"> <?php echo sprintf(__('%s. Assigned To / SalesRep',  'gravity-forms-netsuite-crm' ),++$panel_count); ?></div>
<div class="crm_btn_div"><i class="fa crm_toggle_btn fa-minus" title="<?php esc_html_e('Expand / Collapse','gravity-forms-netsuite-crm') ?>"></i></div>
<div class="crm_clear"></div> 
  </div>                 
    <div class="vx_group ">
   <div class="vx_row"> 
   <div class="vx_col1"> 
  <label for="crm_emp"><?php esc_html_e("Assign Person ", 'gravity-forms-netsuite-crm'); gform_tooltip('vx_assign_person');?></label>
  </div>
  <div class="vx_col2">
  <input type="checkbox" style="margin-top: 0px;" id="crm_emp" class="crm_toggle_check <?php if(empty($emp_list)){echo 'vx_refresh_btn';} ?>" name="meta[emp_check]" value="1" <?php echo !empty($meta['emp_check']) ? "checked='checked'" : ""?> autocomplete="off"/>
    <label for="crm_emp"><?php esc_html_e("Enable", 'gravity-forms-netsuite-crm'); ?></label>
  </div>
<div class="clear"></div>
</div>
    <div id="crm_emp_div" style="<?php echo empty($meta['emp_check']) ? "display:none" : ""?>">
  <div class="vx_row">
  <div class="vx_col1">
  <label><?php esc_html_e('Persons List ','gravity-forms-netsuite-crm'); gform_tooltip('vx_owners'); ?></label>
  </div>
  <div class="vx_col2">
  <button class="button vx_refresh_data" data-id="refresh_emp" type="button" autocomplete="off" style="vertical-align: baseline;">
  <span class="reg_ok"><i class="fa fa-refresh"></i> <?php esc_html_e('Refresh Data','gravity-forms-netsuite-crm') ?></span>
  <span class="reg_proc"><i class="fa fa-refresh fa-spin"></i> <?php esc_html_e('Refreshing...','gravity-forms-netsuite-crm') ?></span>
  </button>
  </div> 
   <div class="clear"></div>
  </div> 

  <div class="vx_row">
   <div class="vx_col1">
  <label for="crm_sel_emp"><?php esc_html_e('Select Person ','gravity-forms-netsuite-crm'); gform_tooltip('vx_sel_owner'); ?></label>
</div> 
<div class="vx_col2">

  <select id="crm_sel_emp" name="meta[emp]" style="width: 100%;" autocomplete="off">
  <?php echo $this->gen_select($emp_list,$meta_emp,__('Select Person','gravity-forms-netsuite-crm')); ?>
  </select>

   </div>

   <div class="clear"></div>
   </div>
 
  
  </div>
  

  </div>
  </div> 

  <div class="button-controls submit" style="padding-left: 5px;">
  <input type="hidden" name="form_id" value="<?php echo esc_attr($form_id) ?>">
  <button type="submit" title="<?php esc_html_e('Save Feed','gravity-forms-netsuite-crm'); ?>" name="<?php echo esc_attr($this->id) ?>_submit" class="button button-primary button-hero"> <i class="vx_icons vx vx-arrow-50"></i> <?php echo empty($fid) ? esc_html__("Save Feed", 'gravity-forms-netsuite-crm') : esc_html__("Update Feed", 'gravity-forms-netsuite-crm'); ?> </button>
  </div>


