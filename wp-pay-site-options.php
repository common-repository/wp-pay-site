<?php

### Variables Variables Variables
$base_name = plugin_basename('wp-pay-site/pay-site-options.php');
$base_page = 'admin.php?page='.$base_name;
$mode = trim($_GET['mode']);
$paysite_settings = array('paysite_options');

if(!empty($_POST['Submit'])) {
        $paysite_options = array();
        $paysite_options['members_root'] = addslashes($_POST['paysite_members_root']);
        $paysite_options['thumbs_dir'] = addslashes($_POST['paysite_thumbs_dir']);
        $paysite_options['thumbs_prefix'] = addslashes($_POST['paysite_thumbs_prefix']);
        $paysite_options['wpps_login_page'] = $_POST['wpps_login_page'];
        $paysite_options['wpps_members_home'] = $_POST['wpps_members_home'];
        $update_paysite_queries = array();
        $update_paysite_text = array();
        $update_paysite_queries[] = update_option('paysite_options', $paysite_options);
        $i=0;
        $text = '';
        foreach($update_paysite_queries as $update_paysite_query) {
                if($update_paysite_query) {
                        $text .= '<font color="green">'.$update_paysite_text[$i].' '.__('Updated', 'wp-pay-site').'</font><br />';
                }
                $i++;
        }
        if(empty($text)) {
                $text = '<font color="red">'.__('No Option Updated', 'wp-pay-site').'</font>';
        }
}


 $paysite_options = get_option('paysite_options');

?>
<?php if(!empty($text)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$text.'</p></div>'; } ?>
<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo plugin_basename(__FILE__); ?>">
<div class="wrap">
        <?php screen_icon(); ?>
        <h2><?php _e('Paysite Settings', 'wp-pay-site'); ?></h2>
        <table class="form-table">
                <tr><th scope="row" valign="top"><?php _e('<strong>Server Path to Member Area Root</strong><br /><small>Full path is required.</small>', 'wp-pay-site'); ?></th>
                    <td><input type="text" name="paysite_members_root" value="<?php echo stripslashes($paysite_options['members_root']); ?>" size="50" /><br />
                        <?php _e('e.g. /home/usr/sitename/public_html/members', 'wp-pay-site'); ?>
                    </td>
                </tr>
                <tr><th scope="row" valign="top"><?php _e('<strong>Thumbnails Directory Path</strong><br /><small>Relative to original set folder.</small>', 'wp-pay-site'); ?></th>
                    <td><input type="text" name="paysite_thumbs_dir" value="<?php echo stripslashes($paysite_options['thumbs_dir']); ?>" size="50" /><br />
                        <?php _e('e.g. ../thumbnails', 'wp-pay-site'); ?>
                    </td>
                </tr>
                <tr><th scope="row" valign="top"><?php _e('<strong>Thumbnails Prefix</strong><br /><small>Blank for same name.</small>', 'wp-pay-site'); ?></th>
                    <td><input type="text" name="paysite_thumbs_prefix" value="<?php echo stripslashes($paysite_options['thumbs_prefix']); ?>" size="50" /><br />
                        <?php _e('e.g. thumb_', 'wp-pay-site'); ?>
                    </td>
                </tr>
                <tr><th scope="row" valign="top"><?php _e('<strong>Members Login Page</strong><br /><small>Or default login page if none.</small>', 'wp-pay-site'); ?></th>
                    <td><?php wp_dropdown_pages("name=wpps_login_page&show_option_none=".__('- none -')."&selected=".$paysite_options['wpps_login_page']); ?><br />
                        <?php _e('don\'t forget to put [login] shorcode onto your login page', 'wp-pay-site'); ?>
                    </td>
                </tr>
                <tr><th scope="row" valign="top"><?php _e('<strong>Members Home Page</strong><br /><small>To redirect members to after login.</small>', 'wp-pay-site'); ?></th>
                    <td>
<?php

 $query = 'SELECT * FROM '.$wpdb->posts.' WHERE post_status = "private" AND post_type = "page"';
 $pages = $wpdb->get_results($query);
 echo "<select name=\"wpps_members_home\" id=\"wpps_members_home\"><option value=\"\">- none -</option>\n";
 foreach($pages as $page){
  echo '<option value="'.$page->ID.'"'.($paysite_options['wpps_members_home'] == $page->ID ? ' selected' : '').'>'.$page->post_title.'</option>';
 }
 echo "</select>\n";

?>
                        <br />
                        <?php _e('this list shows only privat pages of your site', 'wp-pay-site'); ?>
                    </td>
                </tr>
        </table>        
        <p class="submit">
                <input type="submit" name="Submit" class="button" value="<?php _e('Save Changes', 'wp-pay-site'); ?>" />
        </p>
</div>
</form> 
<p>&nbsp;</p>