<?php 
if( function_exists('acf_add_options_page') ) {
    acf_add_options_page();
}
add_shortcode( 'mega-menu-details', 'mega_menu_details_shortcode_cb' );
function mega_menu_details_shortcode_cb($atts){
   $output = ''; 
   $atts = shortcode_atts(
       array(
           'type' => 'about'
       ),
       $atts
   );
   $type = esc_html( $atts['type'] );
   if( $type ){
       $option_key = $type.'_menu_details';
       $p_menu_details = get_field($option_key, 'option');
       if( $p_menu_details ){
           $output .= '<div class="trek-travel-mega-menu-details" id="trek_travel-'.$type.'-mega-menu-details-items">';
           $iter = 1;
           foreach($p_menu_details as $p_menu_detail){
               $menu_details = $p_menu_detail['menu_items'];
               $menu_id = $p_menu_detail['menu_item'];
               $displayStyle = ( $iter == 1 ? 'style="display:flex;"' : 'style="display:none;"' );
               $output .= '<div '.$displayStyle.' class="trek-travel-mega-menu-detail eq-repeate-li" data-id="'.$menu_id.'" data-link="'.get_the_permalink( $menu_id ).'">';
               if( $menu_details ){
                   foreach( $menu_details as $menu_detail ){
                       $output .= '<div class="trek-travel-mega-menu-detail-item">';
                       $output .= '<img src="'.$menu_detail['image'].'" alt="'.$menu_detail['title'].'">
                       <h3><a href="'.get_the_permalink( $menu_id ).'">'.$menu_detail['title'].'</a></h3>';
                       $output .= '</div>';
                   }
               }
               $output .= '</div>';
               $iter++;
           }
           $output .= '</div>';
       }
       return $output;
   }
}
add_action( 'wp_footer', 'wp_footer_script_cb' );
function wp_footer_script_cb(){
   ?>
   <script>
       jQuery(document).ready(function(){
           jQuery('li.mega-menu-column ul.mega-sub-menu ul.menu li').on({
               mouseenter: function() {
               postID =    jQuery(this).find('a').attr('data-postid');
               console.log(`
                   'jQuery(this)' : ${jQuery(this)}
                   'eq class' : .eq-repeate-li[data-id='${postID}'
               `);
               if( postID != 0 ){
                   jQuery(".eq-repeate-li[data-id="+postID+"]").closest('.trek-travel-mega-menu-details').find('.eq-repeate-li').css('display','none');
                   jQuery(".eq-repeate-li[data-id="+postID+"]").css('display','flex');
               }
                console.log("over postID " + postID );
               }
           });
       });
   </script>
   <style>
    .trek-travel-mega-menu-detail {
    display: flex;
    justify-content: space-between;
    gap: 18px;
    }
    .trek-travel-mega-menu-detail img {
    width: 252px;
    height: 150px !important;
    object-fit: cover;
    border-radius: 5px !important;
    }
    .trek-travel-mega-menu-detail h3 a{
    text-align: left;
    font: normal normal normal 15px/18px Fira Sans;
    letter-spacing: 0px;
    color: #000000;
    opacity: 1;
    }
    #mega-menu-wrap-primary .mega-menu-item-type-custom.mega-menu-item-object-custom> .mega-sub-menu {
    padding: 25px 18px !important;
    box-shadow: 0 5px 20px 0 rgb(69 84 114 / 10%) !important;
    border: solid 1px rgba(23, 41, 79, 0.2) !important;
    background-color: #ffffff !important;
    border-radius: 20px !important;
    top: 50px !important;
    left: 100px !important;
    width: 1120px !important;
    position: fixed !important;
    }
    ul#menu-business-type-menu li:nth-child(1):hover .trek-travel-mega-menu-detail:nth-child(1){
    display: flex !important;
    }
    ul#menu-business-type-menu li:nth-child(2):hover .trek-travel-mega-menu-detail:nth-child(1){
    display: none !important;
    }
   </style>
   <?php
}
add_filter('nav_menu_link_attributes', 'menu_post_ids');
function menu_post_ids($val){
    $postid = url_to_postid( $val['href'] );
    $val['data-postid'] = $postid;
    return $val;
} 