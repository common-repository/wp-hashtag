<?php
/*
Plugin Name: WP Hashtag
Plugin URI: http://borneolab.org
Description: Enable Wordpress engine to create hashtags like twitter. It's not grab popular hash from twitter, This plugin allow you to create your own hashtag just by adding <code>#yourtagname</code> to post editor.
Author: <a href="http://gandamanurung.com">Ganda Manurung</a>, <a href="http://planet-orange.org">Aan Afdi</a>
Version: 1.0.3
Author URI: http://borneolab.org
Credits : Original idea is by Aan Afdi(http://planet-orange.org)
*/

#
#  Copyright (c) 2010 Borneo Lab
#
#  WP-Hashtag is free software; you can redistribute it and/or modify it under
#  the terms of the GNU General Public License as published by the Free
#  Software Foundation; either version 2 of the License, or (at your option)
#  any later version.
#
#  WP-Hashtag is distributed in the hope that it will be useful, but WITHOUT ANY
#  WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
#  FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
#  details. http://www.gnu.org/licenses/license-list.html#GPLCompatibleLicenses
#

$use_prefix = 'true';
$saved_prefix = get_option('wpht_url_prefix');

/* default hashtag anchor styles */
$da_color = '585858'; #default hashtag's anchor color
$da_bgcolor = 'd0d0d0'; #default hashtag's anchor background color
$da_colorhover = 'd0d0d0'; #default hashtag's anchor hover color
$da_bgcolorhover = '383838'; #default hashtag's anchor hover background color

if (!defined("WP_CONTENT_URL")) define("WP_CONTENT_URL", get_option("siteurl") . "/wp-content");
if (!defined("WP_PLUGIN_URL"))  define("WP_PLUGIN_URL",  WP_CONTENT_URL        . "/plugins");

function wp_hashtag_style()
{
  $hashtag_css_url = WP_PLUGIN_URL . "/wp_hashtag/css/wp_hashtag.css";
  echo "\n".'<link rel="stylesheet" href="' . $hashtag_css_url . '" type="text/css" media="screen" />'."\n";
}

function wp_hashtag_option_style()
{
  $colorpicker_css_url = WP_PLUGIN_URL . "/wp_hashtag/css/colorpicker.css";
  echo "\n".'<link rel="stylesheet" href="' . $colorpicker_css_url . '" type="text/css" media="screen" />'."\n";
  $hashtag_css_url = WP_PLUGIN_URL . "/wp_hashtag/css/wp_hashtag.css";
  echo "\n".'<link rel="stylesheet" href="' . $hashtag_css_url . '" type="text/css" media="screen" />'."\n";
}

function wp_hashtag_script()
{
    echo "\n".'<script type="text/javascript" src="'.WP_PLUGIN_URL.'/wp_hashtag/js/colorpicker.js"></script>'."\n";
    echo "\n".'<script type="text/javascript" src="'.WP_PLUGIN_URL.'/wp_hashtag/js/eye.js"></script>'."\n";
    echo "\n".'<script type="text/javascript" src="'.WP_PLUGIN_URL.'/wp_hashtag/js/utils.js"></script>'."\n";
}

function wp_hashtag_colorpicker_script()
{
    echo "\n".'<script type="text/javascript" src="'.WP_PLUGIN_URL.'/wp_hashtag/js/wp-hashtag-colorpicker.js"></script>'."\n";
}


function wp_hashtag_filter($content)
{
    global $post, $wpdb, $saved_prefix;
    $saved_prefix = ($saved_prefix=='')? 'true' : $saved_prefix;
    $the_prefix = ($saved_prefix=='true')? '#' : '';
    $pattern = '/\s*#[a-zA-Z0-9-_]+([\s|\.|,+|\?|!|\)]+)/i';
    preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE);
    $hashtag_array = $matches[0];
    $numoftag = count($hashtag_array);
    $the_tag = array();
    
    for($i=0;$i<$numoftag;$i++)
    {
        $hash_tag[] = trim(trim($hashtag_array[$i][0]), ".,?!)");
        $the_tag[] = substr($hash_tag[$i], 1);
        $hash_tag_pattern[$i] = '/'.$hash_tag[$i].'/';
    }
    if(!empty($hash_tag_pattern))
      $hash_tag_pattern = array_values(array_unique($hash_tag_pattern));
    
    $replacement = array();
    foreach($the_tag as $value)
    {
        if(!is_term($value, 'post_tag'))
        {
            $term = wp_insert_term($value, 'post_tag');
            $wpdb->query($wpdb->prepare("INSERT INTO $wpdb->term_relationships SET object_id = %d, term_taxonomy_id = %d, term_order=0", $post->ID, $term['term_id']));
        }

        $exist_term = get_term_by('name', $value, 'post_tag');
        $exists_term_id = $exist_term->term_id;
        $sql = sprintf("SELECT object_id FROM $wpdb->term_relationships WHERE object_id = %d AND term_taxonomy_id = %d", $post->ID, $exists_term_id);
        $exists = $wpdb->get_var($sql);
        if(empty($exists))
        {
            $wpdb->query($wpdb->prepare("INSERT INTO $wpdb->term_relationships SET object_id = %d, term_taxonomy_id = %d, term_order=0", $post->ID, $exists_term_id));
        }
        $replacement[] = "<a href='" . esc_attr(get_term_link($value, 'post_tag')) . "' rel='tag' class=\"hashtag\">".$the_prefix.$value."</a>";
        if(!empty($replacement))
          $replacement = array_values(array_unique($replacement));
    }
    if(count($the_tag)>0)
        $content = preg_replace($hash_tag_pattern, $replacement, $content);
    return $content;
}

function wp_hashtag_config_page()
{
    if ( function_exists('add_submenu_page') )
        add_submenu_page('plugins.php', __('WP Hashtag'), __('WP Hashtag'), 'manage_options', 'wp-hashtag-config', 'wp_hashmap_conf');
}

function wp_hashmap_conf()
{
    global $use_prefix, $saved_prefix, $da_color, $da_bgcolor, $da_colorhover, $da_bgcolorhover;
    wp_enqueue_script("jquery");
    if(get_option('wpht_url_prefix')=='')
        $use_prefix = 'true';
    else
        $use_prefix = get_option('wpht_url_prefix');
    
    if (!empty($_POST['submit']) )
    {
        $prefix = $_POST['wpht_url_prefix'];
        $message = __("WP Hashtag options updated",'wp_hashtag');
            
        if ($prefix !== $use_prefix)
        {
            
            if(!update_option("wpht_url_prefix",$prefix))
                $message = "Update Failed";
            $use_prefix = $prefix;
        }
    }
    
    $hashtag_css_file = realpath(dirname(__FILE__)."/css/wp_hashtag.css");
    
    if(!is_writable($hashtag_css_file))
    {
      _e('<div id="message" class="error"><p>'.$hashtag_css_file.' file <strong>is not writeable</strong>. </p><p>Make sure you are already permit the file to be modified by <code>chmod 766 wp_hashtag.css</code></p></div>');
      $disable_form = 'disabled="true"';
    }
    
    if(!empty($_POST['changeWPHashtagStyle']))
    {
      $message = __("WP Hashtag options updated",'wp_hashtag');
      
      $wpht_anchor_color            = $_POST['wpht_anchor_color'];
      $wpht_anchor_background       = $_POST['wpht_anchor_background'];
      $wpht_anchor_color_hover      = $_POST['wpht_anchor_color_hover'];
      $wpht_anchor_background_hover = $_POST['wpht_anchor_background_hover'];
      
        if(!update_option("wpht_anchor_color",$wpht_anchor_color)
           && !update_option("wpht_anchor_background",$wpht_anchor_background)
           && !update_option("wpht_anchor_color_hover",$wpht_anchor_color_hover)
           && !update_option("wpht_anchor_background_hover",$wpht_anchor_background_hover))
          $message = "Update Failed";
        else
        {
          $new_style = sprintf(".hashtag{
  text-decoration:none;
  color:#%s !important;
  background:#%s;
  }
.hashtag:hover {
  text-decoration:none;
  color:#%s !important;
  background:#%s;
  }
  ", $wpht_anchor_color, $wpht_anchor_background, $wpht_anchor_color_hover, $wpht_anchor_background_hover);
          file_put_contents($hashtag_css_file, trim($new_style));
        }
    }
    
    $saved_a_color         = get_option('wpht_anchor_color');
    $saved_a_bgcolor       = get_option('wpht_anchor_background');
    $saved_a_colorhover    = get_option('wpht_anchor_color_hover');
    $saved_a_bgcolorhover  = get_option('wpht_anchor_background_hover');
    
    $da_color         = empty($saved_a_color)? $da_color : $saved_a_color; 
    $da_bgcolor       = empty($saved_a_colorhover)? $da_bgcolor : $saved_a_bgcolor; 
    $da_colorhover    = empty($saved_a_colorhover)? $da_colorhover : $saved_a_colorhover; 
    $da_bgcolorhover  = empty($saved_a_bgcolorhover)? $da_bgcolorhover : $saved_a_bgcolorhover;   
    

?>
<?php if ( !empty($_POST['submit'] ) ||  !empty($_POST['changeWPHashtagStyle'])) : ?>
    <div id="message" class="updated fade"><p><strong><?php _e($message) ?></strong></p></div>
<?php endif; ?>

        <div class="wrap">
        <h2><?php _e('WP Hashtag Configuration'); ?></h2>
        <p><?php _e("<a href=\"http://borneolab.org\">WP Hashtag</a> is a plugin to enable Wordpress engine to create hashtags like twitter. It's not grab popular hash from twitter, This plugin allow you to create your own hashtag.
        ");?> </p>
        <h3>WP Hashtag URL Prefix</h3>
        <form action="" method="post">
        <p><label><input type='radio' value="true" name='wpht_url_prefix'<?php if ( get_option('wpht_url_prefix')=='' || get_option('wpht_url_prefix') == 'true') echo ' checked="checked" '; ?> >Use <strong>#</strong> as prefix. <span>Your URL would be &lt;a href="..."&gt;#hashtag&lt;/a&gt; </span></label></p>
        <p><label><input type='radio' value="false" name='wpht_url_prefix' <?php if ( get_option('wpht_url_prefix') == 'false' ) echo ' checked="checked" '; ?>><strong>None</strong> <span>Your URL would be &lt;a href="..."&gt;hashtag&lt;/a&gt; </span></label></p>
        <p class="submit"><input type="submit" name="submit" value="<?php _e('Save changes'); ?>" /></p>
        </form>
        
        <h3>WP Hashtag style</h3>
        Example : <a href="javascript:void(0)" rel='tag' class="hashtag" id="wpht_hashtag_preview">#hashtag</a>
        <form action="" method="post">
        <div id="wpht_anchor_colorSelector"><div style="background-color: #<?php _e($da_color);?>"></div></div>
        <label>Hashtag anchor foreground color </label> <input type='text' value="<?php _e($da_color);?>" name='wpht_anchor_color' id='wpht_anchor_color' size="6" readonly="readonly">
        <div id="wpht_anchor_backgroundSelector"><div style="background-color: #<?php _e($da_bgcolor);?>"></div></div>
        <label>Hashtag anchor background color <input type='text' value="<?php _e($da_bgcolor);?>" name='wpht_anchor_background' id='wpht_anchor_background' size="6" readonly="readonly"></label>
        <div id="wpht_anchor_color_hoverSelector"><div style="background-color: #<?php _e($da_colorhover);?>"></div></div>
        <label>Hashtag anchor foreground hover color <input type='text' value="<?php _e($da_colorhover);?>" name='wpht_anchor_color_hover' id='wpht_anchor_color_hover' size="6" readonly="readonly"></label>
        <div id="wpht_anchor_background_hoverSelector"><div style="background-color: #<?php _e($da_bgcolorhover);?>"></div></div>
        <label>Hashtag anchor background hover color <input type='text' value="<?php _e($da_bgcolorhover);?>" name='wpht_anchor_background_hover' id='wpht_anchor_background_hover' size="6" readonly="readonly"></label>
        <p class="submit"><input type="submit" name="changeWPHashtagStyle" value="<?php _e('Save changes'); ?>" <?php _e($disable_form);?>/></p>
        </form>
        </div>
<?php
}
add_action('admin_head', 'wp_hashtag_option_style');
add_action('admin_head', 'wp_hashtag_script');
add_action('wp_head', 'wp_hashtag_style');
add_action('admin_footer', 'wp_hashtag_colorpicker_script');
add_action('admin_menu', 'wp_hashtag_config_page');

add_filter('the_content', 'wp_hashtag_filter', 0);
?>