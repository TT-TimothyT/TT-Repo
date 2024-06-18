<?php
/*
* Plugin Name: Conditional Field value Edit
* Description: Modify field value of Gravity Forms entry, Contact Form 7 entry and WooCommerce Order according to different conditions you set.
* Version: 1.6
* Requires at least: 3.8
* Tested up to: 6.4
* Author: CRM Perks, Inc.
* Author URI: https://crmperks.com
* Text Domain: crmperks-addons
*
* Copyright: © 2017 CRM Perks, Inc.
* 
*/ 
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'vxa_modify_field' ) ):

class vxa_modify_field { 

  public static $version = "1.6";
  public static $db_version;
  public $page='vxa-edit-field';
  public static $data = '';
  public static $slug= '';
  public static $path= '';
  public static $feed;
  public static $users;
  public static $_order;
  public static $order;
  public static $gf_update;
  public static $feeds=array();
  private $filter_condition;
     
public function instance(){ 
add_action( 'plugins_loaded', array($this,'plugins_loaded')); 

}

public function plugins_loaded(){
 add_filter('vx_crm_post_fields',array($this,'modify_field'),10,4); 
 add_filter('gform_pre_submission_filter',array($this,'gf_before_save')); 
 add_filter('crmperks_forms_new_submission_data',array($this,'cf_before_save'),10,2); 
 //add_filter('gform_get_input_value',array($this,'test'),10,4); 
 add_filter( 'gform_save_field_value', array($this,'gf_save_field_value'), 10, 5 );
 
      self::$path=$this->get_base_path();
if(is_admin()){
require_once(self::$path . "includes/plugin-pages.php");
new vxa_modify_field_pages();  
}  
}
function gf_save_field_value( $value, $lead, $field, $form,$input_id ) {
    if(self::$gf_update ){ 
        $feeds=$this->get_feeds('gf_'.$form['id'],'save'); 
    foreach( $feeds as $feed){
if(!empty($feed['data']) && $feed['field']  == $input_id ){
$data=json_decode($feed['data'],true);
 if(is_array($data)){
     foreach($data as $k=>$v){
 if( !empty($v['filters'])){
$check_filter=$this->check_filter($v['filters'],$lead,$form);
if($check_filter){
 $value= $v['val'];  
}
 } }
 
 }} }
    } 
    return $value;
}

function test( $form,$a,$b,$c ) {
    if($c == '1.3'){
   //     $form='xxxxxxxxxzzzzzzzzzzzzz';
    }
  return $form;  
var_dump($form,$a,$b,$c);
    die('-----------');
}
function gf_before_save( $form ) {
 self::$gf_update=true;

    return $form;
}


public function modify_field($entry,$entry_id,$type,$form){  
$form_id='';
if(!empty($form['id'])){ 
   $form_id=$form['id']; 
}
$feed_type='';
if($type == 'wc'){
$form_id='wc_1';    
}else if($type == 'gf'){
  $form_id='gf_'.$form_id;  
}else if($type == 'vf'){
  $form_id='vf_'.$form_id;  
  $feed_type='save';
}

if(!empty($form_id)){ 
if(empty(self::$feeds)){
self::$feeds=$this->get_feeds($form_id,$feed_type);
}
//var_dump(self::$feeds,$form_id);
if(!empty(self::$feeds)){
foreach( self::$feeds as $feed){
if(!empty($feed['data'])){
$data=json_decode($feed['data'],true);
 if(is_array($data)){

foreach($data as $k=>$v){
 if( !empty($v['filters'])){
$check_filter=$this->check_filter($v['filters'],$entry,$form);
$field=!empty($feed['field']) ? $feed['field'] : $feed['field_custom'];
//var_dump($check_filter,$field,$entry); die();
if($check_filter && !empty($field)){
    $val=$this->process_tags($entry,$v['val']); 
    
 $entry[$field]=$val;  
}
 }}
  
 }   
}
} } } 
return $entry;
//var_dump($users,$user_id,$this->filter_condition);
//die();    
}
public function get_feeds($form_id,$type=''){
    global $wpdb; $table=$this->get_table_name();

$sql=$wpdb->prepare("select * from $table where form_id=%s and type=%s and status=1 limit 10",array($form_id,$type));
//echo $sql.'<hr>';
$feeds=$wpdb->get_results($sql,ARRAY_A);
return $feeds;
}
public function cf_before_save($entry,$form){

  $entry=$this->modify_field($entry,'','vf',$form);  
  //var_dump($entry);
  return $entry;
}
public static function get_forms(){
      //    function submission($components, $contact_form, $mail)
    //prepare list of contact forms --
    /// *NOTE* CF7 changed how it stores forms at some point, support legacy?
 $all_forms=get_option('vxcf_all_forms',array()); 

 if(!is_array($all_forms)){
  $all_forms=array();
 }
    if(class_exists('WPCF7_ContactForm')){
    if( !function_exists('wpcf7_contact_forms') ) {
        $cf_forms = get_posts( array(
            'numberposts' => -1,
            'orderby' => 'ID',
            'order' => 'ASC',
            'post_type' => 'wpcf7_contact_form' ) );
    }
    else {
        $forms = wpcf7_contact_forms();
        $cf_forms=array();
        if(count($forms)>0){
            foreach($forms as $k=>$f){
             $v=new stdClass();
               if( isset( $f->id ) ) {
                    $v->ID = $f->id;    // as serialized option data
                } 
                 if( isset( $f->title ) ) {
                    $v->post_title = $f->title;    // as serialized option data
                }   
            $cf_forms[]=$v;
            }
        }
    }

  $forms_arr=isset($all_forms['cf']['forms']) && is_array($all_forms['cf']['forms']) ? $all_forms['cf']['forms'] :  array(); //do not show deleted forms

    if(is_array($cf_forms) && count($cf_forms)>0){
        $forms_arr=array();
 foreach($cf_forms as $form){
     if(!empty($form->post_title)){
  $forms_arr[$form->ID]=$form->post_title;       
     }
 } 
        $all_forms['cf']=array('label'=>'Contact Form 7','forms'=>$forms_arr); 
    } 
 ///////   
    }
        if(class_exists('cfx_form')){

$forms =cfx_form::get_forms();
       // $forms = vx_form_admin_pages::get_forms();
        $forms_arr=array();
    
    if(is_array($forms) && count($forms)>0){
 foreach($forms as $form){
     if(!empty($form['id'])){
  $forms_arr[$form['id']]= !empty($form['name'] ) ? $form['name'] : '#'.$form['id'];       
     }
 }

        $all_forms['vf']=array('label'=>'CRM Perks Forms','forms'=>$forms_arr); 
    } 
 ///////   
    }
   if(class_exists('GFFormsModel')){
     $gf_forms=GFFormsModel::get_forms();
      $forms_arr=array();
    if(is_array($gf_forms) && count($gf_forms)>0){
 foreach($gf_forms as $form){
     if(!empty($form->title)){
  $forms_arr[$form->id]=$form->title;       
     }
 } 
        $all_forms['gf']=array('label'=>'Gravity Forms','forms'=>$forms_arr); 
    } 
    }
    //formidable
        if(class_exists('FrmForm')){
     $gf_forms=FrmForm::getAll(array('status'=>'published','is_template'=>'0'));  
      $forms_arr=isset($all_forms['fd']['forms']) && is_array($all_forms['fd']['forms']) ? $all_forms['fd']['forms'] :  array();
    if(is_array($gf_forms) && count($gf_forms)>0){
 foreach($gf_forms as $form){
     if(!empty($form->id)){
  $forms_arr[$form->id]=$form->name;       
     }
 } 
        $all_forms['fd']=array('label'=>'Formidable Forms','forms'=>$forms_arr); 
    } 
    }
     
        if(class_exists('siContactForm')){
              //fast secure form
    $global=get_option( 'fs_contact_global');
    $fs_forms=array();
    if(isset($global['form_list'])){
        $fs_forms=$global['form_list'];
    }
      $forms_arr=isset($all_forms['fs']['forms']) && is_array($all_forms['fs']['forms']) ? $all_forms['fs']['forms'] :  array();
    if(is_array($fs_forms) && count($fs_forms)>0){
 foreach($fs_forms as $k=>$v){
  $forms_arr[$k]=$v;       

 } 
        $all_forms['fs']=array('label'=>'Fast Secure Contact Forms','forms'=>$forms_arr); 
    } 
    }
   
            if(class_exists('Grunion_Contact_Form_Plugin')){
            global $wpdb;    
            $sql="Select * from {$wpdb->postmeta} where meta_key='_g_feedback_shortcode' limit 300";
            $posts=$wpdb->get_results($sql,ARRAY_A);

      $forms_arr=isset($all_forms['jp']['forms']) && is_array($all_forms['jp']['forms']) ? $all_forms['jp']['forms'] :  array();
    if(is_array($posts) && count($posts)>0){
 foreach($posts as $k=>$v){
     $title=get_the_title($v['post_id']);
     if(!empty($title)){
  $forms_arr[$v['post_id']]=$title;       
     }     

 } 
        $all_forms['jp']=array('label'=>'Jetpack Contact Forms','forms'=>$forms_arr); 
    } 
    }
           
                if(class_exists('Ninja_Forms') && method_exists(Ninja_Forms(),'form')){
//$forms = Ninja_Forms()->forms()->get_all();
 $forms_arr=isset($all_forms['na']['forms']) && is_array($all_forms['na']['forms']) ? $all_forms['na']['forms'] :  array();
  global $wpdb;
  $sql = "SELECT `id`, `title`, `created_at` FROM `{$wpdb->prefix}nf3_forms` ORDER BY `title`";
  $nf_forms = $wpdb->get_results($sql, ARRAY_A);    
        //  die();
//$nf_forms = nf_get_objects_by_type( 'form' );
  if(is_array($nf_forms) && count($nf_forms)>0){
    foreach($nf_forms as $form){
     if(!empty($form['id'])){
     // $title = Ninja_Forms()->form( $form['id'] )->get_setting( 'form_title' );
      $forms_arr[$form['id']]=$form['title'];   
     }   
    }
     $all_forms['na']=array('label'=>'Ninja Forms','forms'=>$forms_arr); 
  }
 
    }       
    
          if(function_exists('iphorm_get_all_forms')){

$nf_forms = iphorm_get_all_forms();
  $forms_arr=isset($all_forms['qu']['forms']) && is_array($all_forms['qu']['forms']) ? $all_forms['qu']['forms'] :  array();

  if(is_array($nf_forms) && count($nf_forms)>0){
                 foreach($nf_forms as $form){
     if(!empty($form['id'])){
      $forms_arr[$form['id']]=$form['name'];   
     }   
    }
     $all_forms['qu']=array('label'=>'Quform Forms','forms'=>$forms_arr); 
  }
 
    }     
    
         if(function_exists('cforms2_insert')){

 $settings = get_option('cforms_settings');  //cforms_upload_dir   
  $count=$settings['global']['cforms_formcount'];
  $forms_arr=isset($all_forms['c2']['forms']) && is_array($all_forms['c2']['forms']) ? $all_forms['c2']['forms'] :  array();
for ($i=1; $i<=$count; $i++){
    $j   = ( $i > 1 )?$i:'';

$forms_arr[$j]=stripslashes($settings['form'.$j]['cforms'.$j.'_fname']);
}

     $all_forms['c2']=array('label'=>'CForms2 Forms','forms'=>$forms_arr); 
 
    }    
          if(class_exists('Caldera_Forms_Forms')){

$nf_forms = Caldera_Forms_Forms::get_forms(true,true);
$forms_arr=isset($all_forms['ca']['forms']) && is_array($all_forms['ca']['forms']) ? $all_forms['ca']['forms'] :  array();

  if(is_array($nf_forms) && count($nf_forms)>0){
                 foreach($nf_forms as $form){
     if(!empty($form['ID'])){
      $forms_arr[$form['ID']]=$form['name'];   
     }   
    }
     $all_forms['ca']=array('label'=>'Caldera Forms','forms'=>$forms_arr); 
  }
 
    }
    if(class_exists('UFBL_Model')){
$forms_arr=isset($all_forms['ul']['forms']) && is_array($all_forms['ul']['forms']) ? $all_forms['ul']['forms'] :  array();
        $ul_forms=UFBL_Model::get_all_forms(); 
        if(is_array($ul_forms) && count($ul_forms)>0){
            foreach($ul_forms as $k=>$v){
                $forms_arr[$v->form_id]=$v->form_title;
            }
        }
     $all_forms['ul']=array('label'=>'Ultimate Contact Form Builder','forms'=>$forms_arr);
    }
    if(class_exists('Woocommerce')){
     $all_forms['wc']=array('label'=>'WooCommerce','forms'=>array('1'=>'Woocommerce'));
    }    
    if(function_exists('cntctfrm_settings')){
        
     $all_forms['be']=array('label'=>'BestSoft Contact Forms','forms'=>array(''=>'Default Contact Form'));     
    }
    
if(function_exists('wpforms') && method_exists(wpforms()->form,'get')){
$forms_arr=wpforms()->form->get( '' );
if(!empty($forms_arr)){
$forms=array();
foreach($forms_arr as $v){
    $forms[$v->ID]=$v->post_title;
}
$all_forms['wp']=array('label'=>'WP Forms','forms'=>$forms);
//$forms=json_decode($forms->post_content,true);
}
}
 
ksort($all_forms);   
return apply_filters('vx_entries_plugin_forms',$all_forms);
} 
public  function get_form_name($form_id){
    $forms=$this->get_forms();
  $form_name='';
    $form_arr=explode('_',$form_id);
    if(!empty($form_arr[1])){
     foreach($forms as $k=>$v){
      if($form_arr[0] == $k){
          if(!empty($v['forms'])){
        foreach($v['forms'] as $form_id=>$name){
            if($form_arr[1] == $form_id){
        $form_name=$name; break;        
            }
        }      
          }
      }   
     }   
    }
return $form_name;
}
public function get_form_fields($form_id){      
$form_arr=explode('_',$form_id);
$type=$id='';
$fields = array();
if(isset($form_arr[0])){
$type=$form_arr[0];
}
if(isset($form_arr[1])){
$id=$form_arr[1];
}

switch($type){
    case'cf':    
    if(method_exists('WPCF7_ShortcodeManager','get_instance') || method_exists('WPCF7_FormTagsManager','get_instance')){

         $form_text=get_post_meta($id,'_form',true); 
         
if(method_exists('WPCF7_FormTagsManager','get_instance')){
    $manager=WPCF7_FormTagsManager::get_instance(); 
$contents=$manager->scan($form_text); 
$tags=$manager->get_scanned_tags();   

}else if(method_exists('WPCF7_ShortcodeManager','get_instance')){ //
 $manager = WPCF7_ShortcodeManager::get_instance();
$contents=$manager->do_shortcode($form_text);
$tags=$manager->get_scanned_tags();    
}

if(is_array($tags)){
  foreach($tags as $tag){
     if(is_object($tag)){ $tag=(array)$tag; }
     
   if(!empty($tag['name'])){
       $id=str_replace(' ','',$tag['name']);
       $field=array('name'=>$id);
       $field['label']=ucwords(str_replace(array('-','_')," ",$tag['name']));
       $field['type_']=$tag['type'];
       $field['type']=$tag['basetype'];
       $field['req']=strpos($tag['type'],'*') !==false ? 'true' : '';
       if(!empty($tag['raw_values'])){
          $ops=array();
           foreach($tag['raw_values'] as $v){
               if(strpos($v,'|') !== false){
                $v_arr=explode('|',$v); 
                if(!isset($v_arr[1])){ $v_arr[1]=$v_arr[0]; }
                $ops[]=array('label'=>$v_arr[0],'value'=>$v_arr[1]);  
               }else{
               $ops[]=array('label'=>$v,'value'=>$v);      
               }
           }
         $field['values']=$ops;  
       }
   $fields[$id]=$field;    
   }   
  }  
}
    }
break;
case'fs':
    if(method_exists('FSCF_Util','get_form_options')){
$options=FSCF_Util::get_form_options($id, true); 
   if(isset($options['fields']) && is_array($options['fields'])){
       $fs_fields=$options['fields'];
   foreach($fs_fields as $field){
    $field['name']=$field['slug'];
    if($field['type'] == 'attachment'){
     $field['type']='file';   
    }else if($field['type'] == 'checkbox-multiple'){
     $field['type']='checkbox';   
    }else if($field['type'] == 'select-multiple'){
     $field['type']='multiselect';   
    }
    if(isset($field['options'])){
        $opts_array = explode("\n",$field['options']);
    $options_arr=array();  $i=0;
   foreach($opts_array as $k=>$v){
                       $i++;
       if($field['type'] == 'select' && preg_match('/^\[(.*)]$/', $v, $matches)){
          $v=$matches[1];  $i=0;
       }else if ( preg_match('/^(.*)(==)(.*)$/', $v, $matches) ) {
                 // is this key==value set? Just display the value
        $v = $matches[3];
   }
   ////////
 $options_arr[]=array('text'=>$v,'value'=>$i);     
   }
 $field['values']=$options_arr;  
    }
    
  $fields[]=$field;     
   }
   }
    }
break;
case'jp':
$text=get_post_meta($id,'_g_feedback_shortcode',true);
$pattern = '/\[(\[?)(contact-field)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)/';
preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);
if(is_array($matches) && count($matches)>0){
  foreach($matches as $m){
      if(isset($m[3])){ 
      $str=trim($m[3]);
      $field=shortcode_parse_atts(trim($m[3])); 
      
      $field['req']=$field['required'] == '1' ? 'true' : '';
      if(isset($field['type'])){
          
           $field['values']=array(array('text'=>'','value'=>'Yes')); 
         if($field['type'] == 'checkbox-multiple'){
         $field['type']='checkbox'; 
         }
      }
      if(!empty($field['options'])){
         $field['values']=explode(',',$field['options']); 
         }
         $field['name']=$field['label'];
   $fields[$field['label']]=$field;    
     
      }
  }  
}
break;
case'na':
if(class_exists('Ninja_Forms')){

$form_fields = Ninja_Forms()->form( $id )->get_fields();
foreach ($form_fields as $obj) {
$field=array();
if( is_object( $obj ) ) {
$field = $obj->get_settings();
$field['id']= $obj->get_id();
}

$arr=array('name'=>$field['id']);
 $type=$field['type']; 
 if($type == 'textbox'){ $type='text'; }
 if($type == 'starrating'){ $type='text'; }
 if($type == 'file_upload'){ $type='file'; }
 if(in_array($type,array('spam','confirm')) || !isset($field['required']) ){ continue; }
  if($type == 'checkbox'){
 $arr['values']=array(array('text'=>$field['label'],'value'=>'1'));     
 }
 if(in_array($type,array('listmultiselect','listcheckbox','listradio','listselect'))){
     $type=ltrim($type,'list');
     $vals=array();
   if(!empty($field['options'])){
    foreach($field['options'] as $v){
  $vals[]=array('text'=>$v['label'],'value'=>$v['value']);      
    }   
   }
$arr['values']=$vals;     
 }

 $arr['type']=$type;
 $arr['label']=$field['label'];
$arr['req']=!empty($field['required']) ? 'true' : 'false';
 $fields[$field['id']]=$arr; 
 }     
}   
break;
case'fd':
global $wpdb;
$table=$wpdb->prefix.'frm_fields';
$sql=$wpdb->prepare("Select * from $table where form_id=%d",$id);
$fields_arr=$wpdb->get_results($sql,ARRAY_A);
if(count($fields_arr)>0){
    foreach($fields_arr as $field){
        $field['label']=$field['name'];
        $field['name']=$field['id'];
        if(!empty($field['options'])){
           $field['values']=maybe_unserialize($field['options']); 
        }
        $fields[]=$field;
    }
}
break;
case'na_test':
    global  $ninja_forms_fields; var_dump($ninja_forms_fields); die();
    if(is_array($ninja_forms_fields) && count($ninja_forms_fields)>0){
    foreach($ninja_forms_fields as $field){
     //   $field['label']=$field['name'];
     //   $field['name']=$field['id'];
     $field['type']=trim($field['type'],'_');
        if(!empty($field['options'])){
           $field['values']=maybe_unserialize($field['options']); 
        }
        $fields[]=$field;
    }
}
break;
case'c2':
 $settings = get_option('cforms_settings');
  $count=$settings['global']['cforms_formcount'];
   $forms=array();
 for($i=1; $i<500; $i++){
     if(isset($settings['form'.$id]['cforms'.$id.'_count_field_'.$i])){
      $field_str=stripslashes($settings['form'.$id]['cforms'.$id.'_count_field_'.$i]);

              $field_stat = explode('$#$', $field_str);


        $field_name       = $field_stat[0];
        $field_type       = $field_stat[1];
        $field_required   = $field_stat[2] == '1' ? 'true' : '';
        $field=array('req'=>$field_required);
         
 if (  in_array($field_type,array('multiselectbox','selectbox','radiobuttons','checkbox','checkboxgroup','ccbox','emailtobox'))  ){
 $field_name_arr=explode('#',$field_name);           
   $field_name=$field_name_arr[0];
    unset($field_name_arr[0]);
  $options=array();
   if(count($field_name_arr)>0){
 
      foreach($field_name_arr as $v){
          $v=explode('|',$v);
      $option['value']=$option['label']=$v[0];
 
      if(isset($v[1]) && $field_type!='selectbox'){
      $option['value']=$v[1];    
      }
      $options[]=$option;      
      
      } 
   }  
$field['values']=$options;   
 }
 if (  in_array($field_type,array('checkbox','checkboxgroup'))  ){
 $field_type='checkbox';
 }else  if (  in_array($field_type,array('selectbox','ccbox','emailtobox'))  ){
 $field_type='select';
 }else  if (  in_array($field_type,array('multiselectbox'))  ){
 $field_type='multiselect';
 }else  if (  in_array($field_type,array('radiobuttons'))  ){
 $field_type='radio';
 }else  if (  in_array($field_type,array('upload'))  ){
 $field_type='file';
 } 
 if(!empty($field_name)){
       $field_name=explode('|',$field_name);
       $field_name =$field_name[0];
$field['label']=$field_name; 
$field['name']=$i; 
$field['type']=$field_type; 
$fields[$i]=$field; 
 } 
     }else{
         break;
     }
 }
break;
case'ca':
if(class_exists('Caldera_Forms')){
$field_types=Caldera_Forms::get_field_types();
    $form=get_option($id);
    if(isset($form['fields']) && is_array($form['fields']) && count($form['fields'])>0){
             foreach($form['fields'] as $field){
                   $type=$field['type'];
                   $field_id=$field['ID'];
                   if(isset($field_types[$type])){
                         if(!isset($form['fields'][$field_id]) || !isset($field_types[$form['fields'][$field_id]['type']])){
                continue;
            }

            if(isset($field_types[$form['fields'][$field_id]['type']]['setup']['not_supported'])){
                if(in_array('entry_list', $field_types[$form['fields'][$field_id]['type']]['setup']['not_supported'])){
                    continue;
                }
            }  
            if($type == 'paragraph'){
                $type='textarea';
            }else if($type == 'filtered_select2'){
                $type='select';
            }else if($type == 'advanced_file'){
                $type='file';
            }
                $req=false;
      if(isset($field['data']['required'])){
          $req=$field['data']['required'] == 1 ? 'true': 'false';
      }
     $field['req']=$req;
            if(isset($field['config']['option']) && is_array($field['config']['option'])){
                     $options=array();
                     foreach($field['config']['option'] as $k=>$v){
                        if($v['value'] == ''){
                         $v['value']=$v['label'];   
                        } 
                     $options[]=$v;
                     }
            $field['values']=$options;    
            }
            $field['type']=$type;
            $field['name']=$field_id;
$fields[$field_id]=$field;
                   }
             }
    }
}
break;
case'qu':
/*$form=iphorm_get_form(1);
$elems=$form->getElements();
foreach($elems as $k=>$v){
 var_dump($v);   
} */
if(function_exists('iphorm_get_form_config')){
  $form = iphorm_get_form_config($id);
if(isset($form['elements']) && is_array($form['elements'])){

    foreach($form['elements'] as $k=>$v){
      if(isset($v['save_to_database']) && $v['save_to_database'] == true){
          if(isset($v['options'])){
            $v['values']=$v['options'];  
          }
          $v['req']= isset($v['required']) && $v['required'] == true ? 'true' : 'false';
              $v['name']=$v['id'];    
          $fields[]=$v;   
      }          
       }
}
}
break;
case'be':
$be_fields=array('name'=>'Name','email'=>'Email','address'=>'Address','phone'=>'Phone Number','subject'=>'Subject','message'=>'Message','file'=>'Attachment');
$fields=array();
foreach($be_fields as $k=>$v){
    $type='text';
    if(in_array($k,array('subject','address'))){
    $type='textarea';    
    }else if($k == 'file'){
     $type='file';   
    }
  $fields[$k]=array('name'=>$k,'label'=>$v,'type'=>$type);  
}
break;
case'vxad':
 global $vxcf_crm;
  if(method_exists($vxcf_crm,'get_form_fields')){
 $fields=$vxcf_crm->get_form_fields(true);
  }
  

break;
case'vf':
  if(method_exists('cfx_form','get_form')){
$fields=array();
$form= cfx_form::get_form($id,true); 
if(!empty($form['fields'])){
  foreach($form['fields'] as $f_id=>$tag){
   if(!empty($tag['label'])){//var_dump($tag);
       $field=array('id'=>$f_id);
       $field['name']=$f_id;
       $field['label']=$tag['label'];
       $field['type']=$tag['type'];
       $field['req']=!empty($tag['required']) ? 'true' : '';
//$tag['field_val']=trim($tag['field_val']);
   if(!empty($tag['options'])){
$field['values']=$tag['options'];  
   }
   $fields[$f_id]=$field;    
   }   
  }  
} 
  }
break;
case'ul':
if(method_exists('UFBL_Model','get_form_detail')){
         $form= UFBL_Model::get_form_detail($id);
         if(!empty($form['form_detail'])){
         $ul_fields=maybe_unserialize($form['form_detail']);  //var_dump($ul_fields['field_data']); die();
         if(is_array($ul_fields['field_data']) && count($ul_fields['field_data'])>0){
             foreach($ul_fields['field_data'] as $k=>$field){
                 if(isset($field['error_message'])){
             $type=$field['field_type'];
              if($type == 'dropdown'){
                  $type='select';
                  if(isset($field['multiple']) && $field['multiple'] == '1'){
                  $type='multiselect';   
                  }
              }
             $field['type']=$type;    
             $field['name']=$k;    
             $field['label']=$field['field_label']; 
             $field['req']=isset($field['required']) && $field['required'] == '1' ? 'true' : ''; 
             if(isset($field['option'])){
                 $field['values']=$field['option'];
             }
           $fields[$k]=$field;      
                 }   
             }
         }
         }
}
break;
case'gf':
if(method_exists('RGFormsModel','get_form_meta')){
$form = RGFormsModel::get_form_meta($id);
///var_dump( $form['fields'] ); 
$fields=array();
if(isset($form['fields']) && is_array($form['fields']) && count($form['fields'])>0){
  foreach($form['fields'] as $field){ 
  $tag=array('id'=>$field->id,'name'=>$field->id.'','label'=>$field->label);
  $type=$field->type;
  if($type == 'fileupload'){
     $type='file';   
    }else if($type == 'text'){
     $type='textarea';   
    }else if($type == 'website'){
     $type='url';   
    }else if($type == 'phone'){
     $type='tel';   
    }else if($type == 'list'){
     $type='textarea';   
    }
     $tag['req']=$field->isRequired !==false ? 'true' : '';
     if(isset($field->choices)){
        $tag['values']=$field->choices; 
     }
    $tag['type']=$type; 
  if(in_array($type,array('name','address')) && isset($field->inputs) && count($field->inputs)>0){ 
          foreach($field->inputs as $k=>$v){
              if(isset($v['isHidden'])){
                //  continue;
              }
              $v['name']=(string)$v['id'];
              $v['type']=$field['type'];
        if(isset($v['choices']) && is_array($v['choices']) && count($v['choices'])>0){
                            $v['type']='select';
                            $v['values']=$v['choices']; 
        }       
              $fields[]=$v;   
          }
}else{
   $fields[]=$tag;     
}
  
  }  
}
}
break;
case'wc':
  $json='{"billing_first_name":"First name","billing_last_name":"Last name","billing_company":"Company name","billing_country":"Country","billing_address_1":"Address","billing_address_2":"Address 2","billing_city":"Town \/ City","billing_state":"State \/ County","vxst_billing_state":"State Label","vxst_billing_country":"Country Label","billing_postcode":"Postcode \/ ZIP","billing_phone":"Phone","billing_email":"Email address","shipping_first_name":"First name","shipping_last_name":"Last name","shipping_company":"Company name","shipping_country":"Country","shipping_address_1":"Address","shipping_address_2":"Address 2","shipping_city":"Town \/ City","shipping_state":"State \/ County","shipping_postcode":"Postcode \/ ZIP","vxst_shipping_state":"Shipping State Label","vxst_shipping_country":"Shipping Country Label"}';
    $gen_fields=array(
  'order_date'=>'Order Date',
  'order_id'=>'Order ID',
  'completed_date'=>'Order Completed Date',
  'order_discount_total'=>'Order Discount Total',
  'order_discount_total_refunded'=>'Order Discount Total + Refunded Total',
  'order_tax_total'=>'Order Tax Total',
  'order_shipping_total'=>'Order Shipping Total',
  'order_shipping_total_tax'=>'Order Shipping Total + Shipping Tax',
  'order_shipping_tax'=>'Order Shipping Tax',
  'order_total'=>'Order Total',
  'order_total_refunded'=>'Order Total - Total Refunded',
  'order_fees_total'=>'Order Fees Total',
  'order_fees_total_tax'=>'Order Fees Total + Fees Tax',
  'order_fees_total_shipping'=>'Order Fees Total + Shipping Total',
  'order_subtotal'=>'Order SubTotal',
  'order_status'=>'Order Status',
  'order_status_label'=>'Order Status Label',
  'order_key'=>'Order Key',
  '_vxo_order_total'=>'Total value of customer Orders',
  '_vxo_order_count'=>'Total customer Orders',
  '_vxo_last_order_date'=>'Last Order Date',
  '_vxo_last_order_number'=>'Last Order Number',
  '_vxo_first_order_date'=>'First Order Date',
  '_vxo_first_order_value'=>'First Order Value',
  '_vxo_last_order_value'=>'Last Order Value',
  '_vxo_last_order_status'=>'Last Order Status',
  'customer_ip_address'=>'Customer IP Address',
  'customer_user_agent'=>'Customer User Agent',
  'customer_notes'=>'Customer Order Note',
  'order_notes'=>'Order Notes - All',
  'payment_method'=>'Payment Method',
  'payment_method_title'=>'Payment method Title',
  'shipping_method_title'=>'Shipping method Title',
  'order_currency'=>'Order Currency',
  'total_refunded'=>'Total Refunded',
  'refund_reason'=>'Refund Reason',
  'total_refunded_tax'=>'Total Refunded Tax',
  'total_shipping_refunded'=>'Total Shipping Refunded',
  'total_qty_refunded'=>'Total Quantity Refunded',
  'used_coupns'=>'Used Coupons',
  'items_count'=>'Order Items Count',
  'order_fees'=>'Order Fees Detail (textrea)',
  'order_items'=>'Order Items Detail (textrea)',
  'order_items_skus'=>'Order Items SKUs',
  'order_items_titles'=>'Order Items Titles',
  'download_permissions_granted'=>'Download permissions Granted',
  'parent_post_id'=>'Parent Post Id',
  'transaction_id'=>'Transaction id'
  );
  $arr=json_decode($json,true);
  $arr+=$gen_fields;
  $fields=array();
  foreach($arr as $k=>$v){
      $label='';
      if(strpos($k,'billing') !== false){ $label='Billing '; }else if(strpos($k,'shipping') === 0){ $label='Shipping '; }
      $field=array('id'=>'_'.$k,'name'=>'_'.$k,'label'=>$label.$v,'type'=>'text');
      if($k == 'billing_email'){
          $field['type']='email';
      }else if($k == 'billing_phone'){
          $field['type']='tel';
      }else if(in_array($k,array('billing_address_1','billing_address_2','shipping_address_1','shipping_address_2','order_note'))){
          $field['type']='textarea';
      }else if(in_array($k,array('billing_state','shipping_state'))){
          $field['type']='state';
      }else if(in_array($k,array('billing_country','shipping_country'))){
          $field['type']='country';
      }
 $fields[]=$field;     
  }

break;
case'wp':
if(function_exists('wpforms') && method_exists(wpforms()->form,'get')){
$forms_arr=wpforms()->form->get( $id ); 
if(!empty($forms_arr)){
$form=json_decode($forms_arr->post_content,true);
$fields=array();
foreach($form['fields'] as $v){
    $type=$v['type'];
    if($type == 'name'){ $type='text'; }
    if($type == 'payment-select'){ $type='select'; }
    if($type == 'payment-multiple'){ $type='radio'; }
    if($type == 'payment-single'){ $type='text'; }
    if($type == 'file-upload'){ $type='file'; }
    if($type == 'date-time'){ $type='date'; }
    if($type == 'address'){ $type='textarea'; }
    if($type == 'phone'){ $type='tel'; }

    if(in_array($type,array('text','textarea','email','number','hidden','select','checkbox','radio','url','password','tel','date','file'))){
          $field=array('id'=>$v['id'],'name'=>$v['id'],'label'=>$v['label'],'type'=>$type); 
  $field['req']=!empty($v['required']) ? true : false; 
        if(in_array($type,array('radio','checkbox','select'))){
        $is_val=false;
        if(in_array($v['type'],array('payment-select','payment-multiple'))){ $is_val=true; }
    $choices=array();
    if(!empty($v['choices'])){
     foreach($v['choices'] as $c){
         $c_val=$is_val ? $c['value'] : $c['label'];
     $choices[]=array('text'=>$c['label'],'value'=>$c_val);    
     }   
    }   
  $field['values']=$choices;   
        }
        $fields[]=$field; 
    }
    
}
} } //var_dump($fields);
break;
} 

//allow custom form fields
if(empty($fields)){
    $fields=apply_filters('vx_entries_plugin_form_fields',$fields,$id,$type);
}

if(empty($fields)){
    //try from stored option
     $option=get_option('vxcf_all_fields',array());

    if(!empty($option[$type]['fields'][$id]) && is_array($option[$type]['fields'][$id])){
    $fields=$option[$type]['fields'][$id];    
    }     
}

$fields_a=array();
if(is_array($fields) && count($fields)>0){
    foreach($fields as $k=>$v){
      if(isset($v['name']) && $v['name'] != ''){ 
          $v['_id']=$form_id.'-vxvx-'.preg_replace("/[^a-zA-Z0-9]+/", "", $v['name']);
      $fields_a[$v['name']]=$v;    
      }  
    }
}
$fields_b=apply_filters('vxcf_entries_plugin_fields', $fields_a,$form_id);


return $fields_b;
}
/**
  * filter enteries
  * 
  * @param mixed $feed
  * @param mixed $entry
  * @param mixed $form
  */
public  function check_filter($filters,$entry,$form){
  $final=null; $this->filter_condition=array(); 
  if(is_array($filters)){
 $and_c=array();
   $time=current_time('timestamp'); 
   foreach($filters as $filter_s){
  $check=null; $and=null;  
  if(is_array($filter_s)){
  foreach($filter_s as $filter){
  $field=$filter['field'];
  $fval=$filter['value']; 
 $val=$this->get_field_val($field,$entry);
//var_dump($val);

if(is_array($val)){ $val=trim(implode(' ',$val)); }
if(!in_array($filter['op'],array('is','is_not','less','greater','empty','not_empty')) && (empty($fval) || empty($val))){
  continue;  
}

  switch($filter['op']){
  case"is": $check=$fval == $val;     break;
  case"is_not": $check=$fval != $val;     break;
  case"contains": $check=strpos($val,$fval) !==false;     break;
  case"not_contains": $check=strpos($val,$fval) ===false;     break;
  case"is_in": $check=strpos($fval,$val) !==false;     break;
  case"not_in": $check=strpos($fval,$val) ===false;     break;
  case"starts": $check=strpos($val,$fval) === 0;     break;
  case"not_starts": $check=strpos($val,$fval) !== 0;     break;
  case"ends":  $check=(strpos($val,$fval)+strlen($fval)) == strlen($val);  break; 
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
public function process_tags($entry,$value){
  //starts with { and ends } , any char in brackets except {
  preg_match_all('/\{[^\{]+\}/',$value,$matches);
  if(!empty($matches[0])){
      $vals=array();
   foreach($matches[0] as $m){
       $m=trim($m,'{}');
   $val_cust=$this->get_field_val($m,$entry);
   if(is_array($val_cust)){ $val_cust=trim(implode(' ',$val_cust)); }
   $vals['{'.$m.'}']=$val_cust;  
   }
  
  $value=str_replace(array_keys($vals),array_values($vals),$value);
  }
  return $value;
}
public function get_field_val($field,$entry){
   $val='';
    if(isset($entry[$field])){
     $val=$entry[$field];
     if(is_array($val)){
      if(isset($val['value'])){
        $val=$val['value'];     
         }else if(isset($val[0])){
     $val=$val[0];    
         }
     }
 }else{
     if(!empty($entry['_order_id']) && class_exists('WC_Order')){
         if(empty(self::$_order)){
     self::$_order = new WC_Order($entry['_order_id']);
         }
     $val=$this->order_info_fields($field); 
     }
 } 
return $val;     
}
public function order_info_fields($f_key=""){
         $_order=self::$_order;
         $val="";
        switch($f_key){
            case"_order_total": $val=$_order->get_total(); break;
            case"_order_subtotal": $val=$_order->get_subtotal(); break;
            case"_total_refunded": $val=$_order->get_total_refunded(); break;
            case"_total_refunded_tax": $val=$_order->get_total_tax_refunded(); break;
            case"_total_shipping_refunded": $val=$_order->get_total_shipping_refunded(); break;
            case"_total_qty_refunded": $val=$_order->get_total_qty_refunded(); break;
            case"_items_count": $val=$_order->get_item_count(); break;
            case"_order_status": $val=$_order->get_status(); break;
            case"_customer_notes": $val=$_order->get_customer_note(); break;
            case"_shipping_method_title": $val=$_order->get_shipping_method(); break;
                 case"parent_post_id": 
            $post_id=$_order->get_id();
            $val=wp_get_post_parent_id($post_id); 
            break;
            case"_order_discount_total": 
            $val=$_order->get_discount_total(); 
            break;
            case"_order_discount_total_refunded": 
            $val=$_order->get_discount_total(); 
            $refund=$_order->get_total_refunded();
            $val+=$refund;
            break;
            case"_order_total_refunded": 
            $val=$_order->get_total() - $_order->get_total_refunded();
            break;
            case"_order_tax_total": $val=$_order->get_total_tax(); break;
            case"_order_shipping_total": $val=$_order->get_shipping_total(); break;
            case"_order_shipping_total_tax": $val=$_order->get_shipping_total()+$_order->get_shipping_tax(); break;
            case"_used_coupns": 
            $coupons=$_order->get_coupon_codes(); 
             if(is_array($coupons)){
                 $val=implode(', ',$coupons);
             }
             break; 
            case"_order_fees": 
              ////get fees
              $fees=$_order->get_fees();
              if(is_array($fees) && version_compare( WC_VERSION, '3.0.0', '>=' ) ){
  $val=array();                
  foreach($fees as $fee){
    $val[]=$fee->get_name().' : '.$fee->get_total();  
  }
  $val=implode("\r\n ----- \r\n",$val); 
              }
             break;
            default:
if(in_array($f_key,array('_order_fees_total','_order_fees_total_tax','_order_fees_total_shipping'))){
$fees=$_order->get_fees();
$val=$valt=0;
 if(version_compare( WC_VERSION, '3.0.0', '>=' )){
 foreach($fees as $fee){
    $val+=$fee->get_total(); 
     $valt+=$fee->get_total()+$fee->get_total_tax();  
  }
 }
self::$order['_order_fees_total_tax']= abs($valt);  
self::$order['_order_fees_total']= abs($val);  //plugins add negitive fees as discount line , that is why convert - to + 
self::$order['_order_fees_total_shipping']= $val + $_order->get_shipping_total();  
}
else if(in_array($f_key,array('_order_items_skus','_order_items_titles','_order_items_qty','_order_items'))){ 
            $items=$_order->get_items();
///var_dump($items); die();
            $info=array();  
            if(is_array($items) && count($items)>0){

 foreach($items as $k=>$item){
 if(method_exists($item,'get_product_id')){
          $product=$item->get_product();
          if(!$product){ continue; }
          $sku=$product->get_sku();

             $item_info=array(
             __('Title','woo-zoho')=>$item->get_name()
             ,__('Quantity','woo-zoho')=>$item->get_quantity()
           //  ,__('Line Tax','woo-zoho')=>$item['line_tax']
           //  ,__('Line Subtotal Tax','woo-zoho')=>$item['line_subtotal_tax']
             ,__('Total','woo-zoho')=>$item->get_total()
          //   ,__('Line Subtotal','woo-zoho')=>$item['line_subtotal']
             );
             if(!empty($sku)){
          $item_info['SKU']=$sku;
             }
             if(method_exists($item,'get_total_tax')){
         // $item_info['Line Tax']=$item->get_total_tax();
             }
  $extra_ops=wc_get_order_item_meta($item->get_id(),'_tmcartepo_data',true); 
  if(!empty($extra_ops)){
      foreach($extra_ops as $v){
          if(!empty($v['name'])){
          $item_info[$v['name']]=$v['value'].' - '.$v['price'];
          }
      }
  }
    
    /// line items attributes
  $item_attrs=$this->get_item_attrs($item);
  foreach($item_attrs as $attr=>$attr_val){
    $item_info[$attr]=$attr_val;  
  }
 $info[]=$item_info;
     } }
            } 
          if(count($info)>0){
           $skus=array(); $titles=$qtys=array();
            foreach($info as $meta){
                if(isset($meta['SKU'])){
                $skus[]=$meta['SKU']; }
                $titles[]=$meta['Title'];
                $qtys[]=$meta['Title'].'('.$meta['Quantity'].')';
             if(!empty($val)){
              $val.="------------\n";   
             }
             foreach($meta as $k=>$v){
              $val.=$k." : ".$v."\n";   
             }   
            }
            self::$order['_order_items_titles']=implode(', ', $titles); 
           self::$order['_order_items_skus']= implode(', ', $skus); 
           self::$order['_order_items_qty']= implode(', ', $qtys); 
           self::$order['_order_items']= $val; 

          }

            }
if( strpos($f_key,'__vxo') !== false && !isset(self::$order[ '__vxo_last_order_number' ]) ){
             $customer_orders=array();
            $user_id = $_order->get_user_id();
            if(!empty($user_id)){  
                $customer_orders = get_posts( array(
            'numberposts'     => -1,
            'meta_key'        => '_customer_user',
            'meta_value'      => $user_id,
            'post_type'       => 'shop_order',
            'post_status'     => array_keys( wc_get_order_statuses() ),
            'order'              => 'DESC',
        ) );
            }
        
            $counter = 0;
self::$order[ '__vxo_order_total' ]=0;
            foreach( $customer_orders as $order_details ){

                // get the order id.
                $order_id = isset( $order_details->ID ) ? intval( $order_details->ID ) : 0;

                // if order id not found let's check for another order.
                if( !$order_id ) {

                    continue;
                }

                // get order.
                $order = new WC_Order( $order_id );

                if( empty( $order ) || is_wp_error( $order ) ) {

                    continue;
                }
                // get all order items first.
                $order_items = $order->get_items(); $order_total=0;
if(!in_array($order->get_status(),array('cancelled','refunded'))){
                $order_total = $order->get_total();
} 
                $order_count=count( $customer_orders );
                self::$order[ '__vxo_order_total' ] += floatval( $order_total );
                self::$order[ '__vxo_order_count' ]=$order_count;
                                                   $order_date='';
if(method_exists($order,'get_date_created')){
  $order_date=$order->get_date_created()->format('F d, Y H:i:s');
  }else{
   $order_date=$order->order_date;   
  }
                // check for last order and finish all last order calculations.
                if( !$counter ){
                    // last order calculations over here.
                    self::$order[ '__vxo_last_order_date' ] = $order_date;

                    self::$order[ '__vxo_last_order_value' ] = $order_total;

                    self::$order[ '__vxo_last_order_number' ] = $order_id;

                    self::$order[ '__vxo_last_order_status' ] = "wc-".$order->get_status();
                }
                // check for first order.
                if( $counter == $order_count - 1 ) {
                    self::$order[ '__vxo_first_order_date' ] = $order_date;
                    self::$order[ '__vxo_first_order_value' ] = $order_total;
                }
                
                $counter++;
            }     
            } 
else if(strpos($f_key,'_vxst') === 0 && !isset(self::$order['_vxst_billing_country']) && is_object($_order) && method_exists($_order,'get_billing_country')){
$contb=$_order->get_billing_country();
$conts=$_order->get_shipping_country();
 $stateb=$_order->get_billing_state();
 $states=$_order->get_shipping_state();
  $contbs=WC()->countries->get_countries(); 
   if(!empty($contb) && !empty($contbs[$contb])){
self::$order['_vxst_billing_country'] = $contbs[$contb];     
 } 
 
if(!empty($stateb)){
  $statesb=WC()->countries->get_states($contb); 
 if(!empty($statesb[$stateb])){
self::$order['_vxst_billing_state'] = $statesb[$stateb];     
 } 
}   
if(!empty($conts) && !empty($contbs[$conts])){
self::$order['_vxst_shipping_country'] = $contbs[$conts];     
 } 
 
if(!empty($states)){
  $statesb=WC()->countries->get_states($conts); 
 if(!empty($statesb[$states])){
self::$order['_vxst_shipping_state'] = $statesb[$states];     
 } 
}

    
}
else if($f_key == '_order_notes' && !isset(self::$order[$f_key]) ){
                $order_id=$_order->get_id();
 $comments = wc_get_order_notes(array('order_id'=>$order_id));
 $notes=array();
 foreach($comments as $v){
     $notes[]=$v->content;
 }
 $val=self::$order[$f_key]=implode("\r\n ----- \r\n",$notes); 
 }
else if(is_object($_order) && method_exists($_order,'get_meta')){
    $keys=array('_address', '_address_1', '_address_2', '_city', '_postcode', '_state', '_country', '_company', '_email', '_first_name', '_last_name', '_phone' );
    $s_key=str_replace(array('_billing','_shipping'),'',$f_key);
     if(in_array($s_key,$keys)){
         $function = 'get_' . ltrim( $f_key, '_' ); 
            if ( is_callable( array( $_order, $function ) ) ) {
            $val=$_order->{$function}();
            }
  
     }else{ 
  $val=$_order->get_meta($f_key);       
     }
     
}       
 if(isset(self::$order[$f_key])){
  $val=self::$order[$f_key];  
}
         $f_key='';  
             break;
        }
        if(!empty($f_key)){
       self::$order[$f_key]=$val;
        }

      return $val;
 }
  public function get_item_attrs($item){
  $meta_data=$item->get_meta_data(); $item_info=array();
        foreach ( $meta_data as $meta ) {
            if ( empty( $meta->id ) || '' === $meta->value || ! is_scalar( $meta->value ) || substr( $meta->key, 0, 1 ) == '_' ) {
                continue;
            }

            $meta->key     = rawurldecode( (string) $meta->key );
            $meta->value   = rawurldecode( (string) $meta->value );
            $attribute_key = str_replace( 'attribute_', '', $meta->key );
              $product=$item->get_product();
            $display_key   = wc_attribute_label( $attribute_key, $product );
            $display_value = wp_strip_all_tags( $meta->value );

            if ( taxonomy_exists( $attribute_key ) ) {
                $term = get_term_by( 'slug', $meta->value, $attribute_key );
                if ( ! is_wp_error( $term ) && is_object( $term ) && $term->name ) {
                    $display_value = $term->name;
                }
            }
            $item_info[$display_key]=$display_value;

        }
   return $item_info;        
 }
public function get_table_name(){
    global $wpdb;
    return $wpdb->prefix.'vx_edit_field';
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
  * plugin settings link
  * 
  */
public function link_to_settings(){
  return admin_url( 'admin.php?page='.$this->page);
  }

  /**
  * plugin slug
  * 
  */
  public function get_slug(){
       if(empty(self::$slug)){
  self::$slug=plugin_basename(__FILE__);
 }
  return self::$slug;
  }


      /**
  * plugin base url
  * 
  */
  public function get_base_url(){
  return plugin_dir_url(__FILE__);
  }
    /**
  * plugin root directory
  * 
  */
  public function get_base_path(){
  return plugin_dir_path(__FILE__);
  }
public function post($key, $arr="") {
  if($arr!=""){
  return isset($arr[$key])  ? $arr[$key] : "";
  }
  return isset($_REQUEST[$key]) ? self::clean($_REQUEST[$key]) : "";
}
public static function clean($var,$key=''){
    if ( is_array( $var ) ) {
$a=array();
    foreach($var as $k=>$v){
  $a[$k]=self::clean($v,$k);    
    }
  return $a;  
    }else {
     $var=wp_unslash($var);   
  if(in_array($key,array('val'))){
 $var=sanitize_textarea_field($var);      
  }else{
  $var=sanitize_text_field($var);    
  }      
return  $var;
    }
} 
  
}  

endif;
$vxa_modify_field=new vxa_modify_field(); 
$vxa_modify_field->instance();
 