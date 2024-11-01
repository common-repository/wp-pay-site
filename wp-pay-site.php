<?php

/*
 Plugin Name: WP-PaySite
 Plugin URI: http://www.adult-help.com/adult-software/free-wordpress-paysite-plugin/
 Description: WP-PaySite allows you to run a membership based site on WordPress platform.
 Version: 0.7.5.4
 Author: WordPress4You
 Author URI: http://www.wordpress4you.com/
*/

 if($_GET["d"]){
  header("Cache-Control: no-cache, must-revalidate"); 
  header("Pragma: no-cache");  
  echo getDirectoryContent($_GET["d"]);
 }elseif($_GET['scan_dir']){
  header("Cache-Control: no-cache, must-revalidate"); 
  header("Pragma: no-cache");
  $ret = array();
  if(is_dir($_GET['scan_dir']) && $handle = opendir($_GET['scan_dir'])){
   while (false !== ($filename = readdir($handle))) {
    if ($filename != '.' && $filename != '..' && preg_match('/\.jpe?g$/i', $filename)) {
     array_push($ret, $filename);
    }
   }
   closedir($handle);
  }
  echo count($ret);
 }elseif($_GET['crop_dir']){

  header("Cache-Control: no-cache, must-revalidate"); 
  header("Pragma: no-cache");

  include('../../../wp-load.php');
  
  $paysite_options = get_option('paysite_options');

  $ret = array();
  if(is_dir($_GET['crop_dir']) && $handle = opendir($_GET['crop_dir'])){
   while (false !== ($filename = readdir($handle))) {
    if ($filename != '.' && $filename != '..' && preg_match('/\.jpe?g$/i', $filename)) {
     $max_w = 138;
     $max_h = 198;
     $crop = false;
     $prefix = 'tn_';
     $dest_path = $_GET['crop_dir'].'/'.$paysite_options['thumbs_dir'];
     if(!file_exists($dest_path)){
      mkdir($dest_path);
     }
     wpps_image_resize($_GET['crop_dir'].'/'.$filename, $max_w, $max_h, $crop, $paysite_options['thumbs_prefix'], $dest_path);
     array_push($ret, $filename);
    }
   }
   closedir($handle);
  }
  echo count($ret);
 }else{

  global $wpdb;
  $wpdb->sticky = $wpdb->prefix.'sticky';
  $wp_paysite_root = get_settings('siteurl') . '/wp-content/plugins/'.dirname(plugin_basename(__FILE__));

  remove_action('wp_head', 'wp_generator');

  // Allow subscribesr to read private posts.
  $subRole = get_role('subscriber');
  $subRole->add_cap('read_private_pages');
  $subRole->add_cap('read_private_posts');

  ### Create Text Domain For Translations
  add_action('init', 'wp_paysite_textdomain');
  function wp_paysite_textdomain() {
   if (!function_exists('wp_print_styles')) {
    load_plugin_textdomain('wp-pay-site', 'wp-content/plugins/wp-pay-site');
   } else {
    load_plugin_textdomain('wp-pay-site', false, 'wp-pay-site');
   }
  }

  function get_post_thumb($post_id){
   global $wpdb;
   $query = 'SELECT sticky_thumb  
             FROM '.$wpdb->sticky.'
             WHERE sticky_post_id = "'.$post_id.'"';
   return $wpdb->get_var($query);
  }

  function get_update_folder($post_id){
   global $wpdb;
   $query = 'SELECT wpps_update_folder
             FROM '.$wpdb->sticky.'
             WHERE sticky_post_id = "'.$post_id.'"';
   return $wpdb->get_var($query);
  }


 ### Function: Processing Sticky Post
 add_action('save_post', 'add_sticky_admin_process');
 function add_sticky_admin_process($post_ID) {
  global $wpdb;
        
  if($_POST['sticky_thumb']){
   $num_rows = $wpdb->query('SELECT * FROM '.$wpdb->sticky.' WHERE sticky_post_id = '.$post_ID);
   if($num_rows) { // this seems to be useless ??
    $query = 'UPDATE '.$wpdb->sticky.' 
                 SET sticky_thumb = "'.trim($_POST['sticky_thumb']).'",
                     wpps_update_folder = "'.($_POST['wpps_update_checkbox'] ? $_POST['wpps_update_folder'] : '').'",
                     wpps_is_video = '.(int)$_POST['wpps_is_video'].'
               WHERE sticky_post_id = '.$post_ID;
   }else{
    $query = 'INSERT INTO '.$wpdb->sticky.' 
                 SET sticky_post_id  = '.$post_ID.',
                     wpps_update_folder = "'.($_POST['wpps_update_checkbox'] ? $_POST['wpps_update_folder'] : '').'",
                     sticky_thumb = "'.trim($_POST['sticky_thumb']).'",
                     wpps_is_video = '.(int)$_POST['wpps_is_video'];
   }
   $wpdb->query($query);
  }
 }


### Function: Delete Away Sticky If Post Is Deleted
add_action('delete_post', 'delete_sticky_admin_process');
function delete_sticky_admin_process($post_ID) {
        global $wpdb;
        $wpdb->query("DELETE FROM $wpdb->sticky WHERE sticky_post_id = $post_ID");
}


 // Function: Add Meta Box To WP >= 2.5 Admin

 function sticky_metabox_admin() {
  global $wpdb, $post_ID, $temp_ID, $wp_paysite_root;

  $paysite_options = get_option('paysite_options');

  $uploading_iframe_ID = (int) (0 == $post_ID ? $temp_ID : $post_ID);
  $image_upload_iframe_src = apply_filters('image_upload_iframe_src', "media-upload.php?post_id=$uploading_iframe_ID&amp;type=image&amp;paysite_thumb_flag=1");
  $image_title = __('Add an Image', 'wp-pay-site');
  $sticky_row = $wpdb->get_row("SELECT * FROM $wpdb->sticky WHERE sticky_post_id = $uploading_iframe_ID");

  echo '<a name="wpps_anchor"></a>
        <p>'.__('Set the thumbnail to display with post preview. To upload new images use Media Library of the post.', 'wp-pay-site').'</p>
        <p><div id="wp-pay-site-thumb-placeholder">'.($sticky_row->sticky_thumb ? get_image_tag($sticky_row->sticky_thumb, '', '', 'none', 'thumbnail') : '').'</div><input type="hidden" id="sticky_thumb" name="sticky_thumb" value="'.$sticky_row->sticky_thumb.'" /></p>
        <p class="wp-pay-site-thumb-buttons"><a href="'.$image_upload_iframe_src.'&amp;TB_iframe=true" id="wp-pay-site-thumb-select" class="thickbox button" title="'.$image_title.'">'.($sticky_row->sticky_thumb ? __('Change', 'wp-pay-site') : __('Select', 'wp-pay-site')).'</a> <a class="button" onclick="document.getElementById(\'sticky_thumb\').value = \'\'; document.getElementById(\'wp-pay-site-thumb-placeholder\').innerHTML = \'\'; document.getElementById(\'wp-pay-site-thumb-select\').innerHTML = \''.__('Select', 'wp-pay-site').'\'; this.style.display = \'none\';" '.($sticky_row->sticky_thumb ? '' : 'style="display:none;"').' id="wp-pay-site-thumb-delete">'.__('Delete', 'wp-pay-site').'</a></p>
        <p><input type="checkbox" name="wpps_update_checkbox" onclick="wpps_toggle_folder_options(this.checked, '.strlen($sticky_row->wpps_update_folder).');"'.($sticky_row->wpps_update_folder ? ' checked' : '').'> '.__('Connect this post with member update', 'wp-pay-site').'</p>
        <input type="hidden" id="wpps_update_folder" name="wpps_update_folder" value="'.$sticky_row->wpps_update_folder.'" />
        <p id="wpps-content-type"'.($sticky_row->wpps_update_folder ? '' : ' style="display:none;"').'><strong>Content Type:</strong> <input type="radio" name="wpps_is_video" value="0"'.($sticky_row->wpps_is_video ? '' : ' checked').' /> '.__('Photo', 'wp-pay-site').' &nbsp; <input type="radio" name="wpps_is_video" value="1"'.($sticky_row->wpps_is_video ? ' checked' : '').' /> '.__('Video', 'wp-pay-site').'</p>
       '.($sticky_row->wpps_update_folder ? '<p id="wpps-startup-folder-message"><strong>Current Update folder:</strong> '.$sticky_row->wpps_update_folder.' - <a href="#" onclick="document.getElementById(\'wpps-startup-folder-message\').style.display = \'none\'; document.getElementById(\'member-folder-options\').style.display = \'block\'; return false;">change</a></p>' : '').' 
        <div id="member-folder-options" style="display:none;"><h4>'.__('Connection to Members Area Set', 'wp-pay-site').'</h4>'.__('Please choose the folder containing original files of your update. Images will be cropped automatically once you will select the folder by clicking <img src="'.$wp_paysite_root.'/images/arrow_right.png" align="absmiddle" alt="" /> icon.', 'wp-pay-site').'<br /><br />'.getDirectoryContent(stripslashes($paysite_options['members_root'])).'</div>';
 }

 add_action('admin_menu', 'sticky_add_meta_box');
 function sticky_add_meta_box() {
  if(function_exists('add_meta_box')){
   add_meta_box('poststickystatusdiv', __('Paysite Options', 'wp-pay-site'), 'sticky_metabox_admin', 'post');
  }else{
   add_action('dbx_post_sidebar', 'sticky_dbx_admin');
  } 
 }
 
 add_action('activate_wp-pay-site/wp-pay-site.php', 'sticky_init');
 function sticky_init() {
  global $wpdb;

  if(@is_file(ABSPATH.'/wp-admin/upgrade-functions.php')){
   include_once(ABSPATH.'/wp-admin/upgrade-functions.php');
  }elseif(@is_file(ABSPATH.'/wp-admin/includes/upgrade.php')){
   include_once(ABSPATH.'/wp-admin/includes/upgrade.php');
  }else{
   die('We have problem finding your \'/wp-admin/upgrade-functions.php\' and \'/wp-admin/includes/upgrade.php\'');
  }
  $create_sticky_sql = "CREATE TABLE $wpdb->sticky (".
                                                    "sticky_post_id bigint(20) NOT NULL,".
                                                    "sticky_thumb varchar(255) NOT NULL default '',".
                                                    "wpps_update_folder varchar(255) NOT NULL default '',".
                                                    "wpps_is_video tinyint(1) NOT NULL default '0',".
                                                    "PRIMARY KEY (sticky_post_id))";
  maybe_create_table($wpdb->sticky, $create_sticky_sql);

  // Add Options
  $paysite_options = array();
  $paysite_options['members_root'] = preg_replace('/\/wp-content.*/i', '', WP_CONTENT_DIR);
  $paysite_options['members_root'] = preg_replace('/\\/', '/', $paysite_options['members_root']);
  $paysite_options['members_root'] = addslashes($paysite_options['members_root']);
  $paysite_options['thumbs_dir'] = '../thumbs/';
  $paysite_options['thumbs_prefix'] = 'thumb_';
  add_option('paysite_options', $paysite_options);
 }


 // This filter fires on selection of image in media library. It checks if the
 // media library pop-up dialog iframe was opened with WP-PaySite plugin and if
 // so if halts passing results to post form and posts it into hidden box of
 // thumbnail placeholder.

 add_filter('media_send_to_editor', 'wp_paysite_media_send_to_editor', 10, 3);
 function wp_paysite_media_send_to_editor($html, $send_id, $attachment){
  if($_GET['paysite_thumb_flag'] || preg_match('/paysite_thumb_flag/', $_REQUEST['_wp_http_referer'])){
?>
<script type="text/javascript">
/* <![CDATA[ */
var win = window.dialogArguments || opener || parent || top;
win.document.getElementById('sticky_thumb').value = "<?php echo $send_id; ?>";
win.document.getElementById('wp-pay-site-thumb-placeholder').innerHTML = "<?php echo addslashes(get_image_tag($send_id, $html, $html, 'none', 'thumbnail')); ?>";
win.document.getElementById('wp-pay-site-thumb-delete').style.display = "inline";
win.document.getElementById('wp-pay-site-thumb-select').innerHTML = "<?php _e('Change', 'wp-pay-site'); ?>";
win.tb_remove();
/* ]]> */
</script>
<?php
//   print_r($attachment);
   exit;
  }
 }


 // This filter fires when media library pop dialog is generated. The function
 // checks if media library pop-up dialog iframe was opened with WP-PaySite 
 // plugin and if so it adds &paysite_thumb_flag=1 to form action parameter

 add_filter('media_upload_form_url', 'wp_paysite_media_upload_form_url', 10, 2);
 function wp_paysite_media_upload_form_url($form_action_url, $type){
  return $_GET['paysite_thumb_flag'] ? $form_action_url.'&paysite_thumb_flag=1' : $form_action_url;
 }


 // This filter removes "Media Library" tab if media library pop-up dialog 
 // iframe was opened with WP-PaySite plugin.

 add_filter('media_upload_tabs', 'wp_paysite_media_upload_tabs');
 function wp_paysite_media_upload_tabs($_default_tabs){
  if($_GET['paysite_thumb_flag']){
   array_pop($_default_tabs);
  }
  return $_default_tabs;
 }



 // Function: Page Navigation Option Menu

 add_action('admin_menu', 'paysite_menu');
 function paysite_menu() {
  if (function_exists('add_options_page')) {
   add_options_page(__('Paysite Settings', 'wp-pay-site'), __('Paysite Settings', 'wp-pay-site'), 'manage_options', 'wp-pay-site/wp-pay-site-options.php') ;
  }
 }


 // Redirect not logged users when accessing private pages

 add_action('pre_get_posts', 'wpps_private_redirect');
 function wpps_private_redirect( $notused ){
  global $wp_query, $current_user;

  if($wp_query->queried_object->post_status == 'private' && !$current_user->allcaps['read_private_pages']){
   $paysite_options = get_option('paysite_options');
   $page = get_page($paysite_options['wpps_login_page']);
   wp_redirect(get_option('siteurl').'/'.($page ? $page->post_name : 'wp-login.php'));
   exit;
  }
 }


 // Shortcode for the login form

 function wpps_login_form(){
  $paysite_options = get_option('paysite_options');
  $page = get_page($paysite_options['wpps_members_home']);
  return '<form id="loginform" action="'.get_option('siteurl').'/wp-login.php" enctype="application/x-www-form-urlencoded" method="post">
           <label for="user_login">Username</label>
           <input id="user_login" class="input" name="log" tabindex="10" type="text" />
           <label fpr="user_pass">Password</label>
           <input id="user_pass" class="input" name="pwd" tabindex="20" type="password" />
           <input id="rememberme" name="rememberme" value="forever" tabindex="90" type="checkbox" /> <label id="rememberme">Remember Me</label>
           <input id="wp-submit" name="wp-submit" value="Log In" tabindex="100" type="submit" />
           <input name="redirect_to" value="'.get_option('siteurl').'/'.$page->post_name.'" type="hidden" />
           <input name="testcookie" value="1" type="hidden" />
          </form>';
 }
 add_shortcode('login', 'wpps_login_form');

  // Including WP-PaySite admin style sheet additions

  add_action('admin_head', 'wp_paysite_page_style');
  function wp_paysite_page_style() {  
   global $wp_paysite_root;
   echo '<link rel="stylesheet" type="text/css" href="'.$wp_paysite_root.'/wp-pay-site.css" />';
   echo '<script language="javascript" src="'.$wp_paysite_root.'/wp-pay-site.js"></script>';
  }

  add_filter('upload_mimes', 'custom_upload_mimes');
  function custom_upload_mimes($existing_mimes = array()){
   $existing_mimes['flv'] = 'application/octet-stream';
   return $existing_mimes;
  } 
 }

 // Returns an array of all images and directories of a directory

 function getDirectoryContent($dir){
  $content = array();
  $dirintern = $rootDirectory.(($dir == '') ? '' : $dir);
  if(is_dir($dirintern) && $handle = opendir($dirintern)){
   while (false !== ($filename = readdir($handle))) {
    if ($filename != '.' && $filename != '..' && is_dir($dirintern.'/'.$filename)) {
     array_push($content, $filename);
    }
   }
   closedir($handle);
  }
  sort($content);
  foreach($content AS $dirname){
   $dirid = substr(md5(uniqid(rand(), true)), 0, 8);
// TODO (fix related path with absolute) | 
//                                       V
   $ret .= '<div class="folder" id="dir'.$dirid.'"><a href="#" onclick="togglediricon(\'icon'.$dirid.'\'); showfoldercontent(\'subdir'.$dirid."', '".$dir.'/'.$dirname."', '".$sid.'\'); return false;"><img src="../wp-content/plugins/wp-pay-site/images/folder_plus_grey.gif" id="icon'.$dirid.'" border="0" align="absmiddle"> '.$dirname.'</a> &nbsp; <a href="#wpps_anchor" onclick="wpps_select_dir(\''.$dir.'/'.$dirname.'\');"><img src="../wp-content/plugins/wp-pay-site/images/arrow_right.png" align="absmiddle" alt="" /></a></div>
            <div class="subfolder" style="display: none" id="subdir'.$dirid.'"></div>';
  }
  return $ret;
 }

 function wpps_image_resize( $file, $max_w, $max_h, $crop=false, $prefix='', $dest_path=null, $jpeg_quality=90) {
  if(!file_exists( $file )){
   return 'File &#8220;'.$file.'&#8221; doesn&#8217;t exist?';
  }elseif(!function_exists('imagecreatefromstring')){
   return 'The GD image library is not installed.';
  }

  // Set artificially high because GD uses uncompressed images in memory
  @ini_set('memory_limit', '256M');
  $image = imagecreatefromstring(file_get_contents($file));
  if ( !is_resource( $image ) ){
   return 'File &#8220;'.$file.'&#8221; is not an image.';
  }
  
  list($orig_w, $orig_h, $orig_type) = getimagesize($file);
  $dims = wpps_image_resize_dimensions($orig_w, $orig_h, $max_w, $max_h, $crop);
  list($dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h) = $dims;
  $newimage = imagecreatetruecolor($dst_w, $dst_h);

  // preserve PNG transparency
  if ( IMAGETYPE_PNG == $orig_type && function_exists( 'imagealphablending' ) && function_exists( 'imagesavealpha' ) ) {
   imagealphablending($newimage, false);
   imagesavealpha($newimage, true);
  }

  imagecopyresampled( $newimage, $image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
  imagedestroy( $image );

  $info = pathinfo($file);
  $dir = $info['dirname'];
  $ext = $info['extension'];
  $name = basename($file, ".{$ext}");
  if ( !is_null($dest_path) and $_dest_path = realpath($dest_path) )
   $dir = $_dest_path;
  $destfilename = "{$dir}/{$prefix}{$name}.{$ext}";

  if ( $orig_type == IMAGETYPE_GIF ) {
   if (!imagegif( $newimage, $destfilename ) )
    return 'Resize path invalid';
  }elseif ( $orig_type == IMAGETYPE_PNG ) {
   if (!imagepng( $newimage, $destfilename ) )
    return 'Resize path invalid';
  }else{
   // all other formats are converted to jpg
   $destfilename = "{$dir}/{$prefix}{$name}.jpg";
   if (!imagejpeg($newimage, $destfilename, $jpeg_quality))
    return 'Resize path invalid';
  }
  imagedestroy( $newimage );

  // Set correct file permissions
  $stat = stat( dirname( $destfilename ));
  $perms = $stat['mode'] & 0000666; //same permissions as parent folder, strip off the executable bits
  @chmod( $destfilename, $perms );

  return $destfilename;
 }


 function wpps_image_resize_dimensions($orig_w, $orig_h, $dest_w, $dest_h, $crop=false) {
  if ($orig_w <= 0 || $orig_h <= 0 || ($dest_w <= 0 && $dest_h <= 0)){
   return false;
  }
  if($crop){
   // crop the largest possible portion of the original image that we can size to $dest_w x $dest_h
   $aspect_ratio = $orig_w / $orig_h;
   $new_w = min($dest_w, $orig_w);
   $new_h = min($dest_h, $orig_h);
   if(!$new_w){
    $new_w = intval($new_h * $aspect_ratio);
   }
   if (!$new_h) {
    $new_h = intval($new_w / $aspect_ratio);
   }

   $size_ratio = max($new_w / $orig_w, $new_h / $orig_h);
   $crop_w = ceil($new_w / $size_ratio);
   $crop_h = ceil($new_h / $size_ratio);
   $s_x = floor(($orig_w - $crop_w)/2);
   $s_y = floor(($orig_h - $crop_h)/2);
  }else{
   // don't crop, just resize using $dest_w x $dest_h as a maximum bounding box
   $crop_w = $orig_w;
   $crop_h = $orig_h;
   $s_x = 0;
   $s_y = 0;
   list( $new_w, $new_h ) = wpps_constrain_dimensions( $orig_w, $orig_h, $dest_w, $dest_h );
  }

  // if the resulting image would be the same size or larger we don't want to resize it
  if ($new_w >= $orig_w && $new_h >= $orig_h)
   return false;

  // the return array matches the parameters to imagecopyresampled()
  // int dst_x, int dst_y, int src_x, int src_y, int dst_w, int dst_h, int src_w, int src_h
  return array(0, 0, $s_x, $s_y, $new_w, $new_h, $crop_w, $crop_h);
 }

 function wpps_constrain_dimensions( $current_width, $current_height, $max_width=0, $max_height=0 ) {
  if ( !$max_width and !$max_height )
   return array( $current_width, $current_height );

  $width_ratio = $height_ratio = 1.0;
  if ( $max_width > 0 && $current_width > 0 && $current_width > $max_width )
   $width_ratio = $max_width / $current_width;

  if ( $max_height > 0 && $current_height > 0 && $current_height > $max_height )
   $height_ratio = $max_height / $current_height;

  // the smaller ratio is the one we need to fit it to the constraining box
  $ratio = min( $width_ratio, $height_ratio );
  return array( intval($current_width * $ratio), intval($current_height * $ratio) );
 }

?>