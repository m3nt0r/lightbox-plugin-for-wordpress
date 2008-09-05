<?php
/*
Plugin Name: Lightbox 2 Plugin
Plugin URI: http://www.m3nt0r.de/blog/lightbox-wordpress-plugin/
Feed URI:
Description: Auto-converting all linked images in a post, excerpt and even comments to lightboxed links. (see options menu). -- Using Lightbox v2.04 and compressed Prototype Javascript. 
Version: 0.7a
Author: Kjell Bublitz
Author URI: http://www.m3nt0r.de/blog/
*/

define("LIGHTBOX_EXTENSIONS",      'jpg|jpeg|png|gif');   	// checking for extension, divided by pipes (no leading or trailing pipe!!)
define("LIGHTBOX_DISABLE_WORD",    '##NOLIGHTBOX##');   		// disables the lightbox converter on the whole post/page

/*******************************************************************
*	DON'T EDIT BELOW THIS LINE UNLESS YOU KNOW WHAT YOU ARE DOING
*******************************************************************/

	// for version control and installation
	define('LIGHTBOX_VERSION', '0.7/2.04');
	
	// Default Options Setup
	$lightbox_defaults = array(
		'speed' =>  '7',
		'border' => '10',
		'opacity' => '0.8',
		'animate' => 'true',		
		'posts' => 'true',
		'excerpt' => 'false',
		'comments' => 'false',
		'version' => LIGHTBOX_VERSION
	);
		
	// detect the plugin path
	$lightbox_path = get_settings('siteurl').'/wp-content/plugins/lightbox-plugin-for-wordpress';
	
	// try to always get the values from the database
	if ($wp_version == "2.6") {
	   $lightbox_settings = get_option("lightbox_settings");
	} else {
	   $lightbox_settings = unserialize(get_option("lightbox_settings"));
	}
	
	// if the database value returns empty use defaults
	if($lightbox_settings['version'] != LIGHTBOX_VERSION) 
	{
		add_option('lightbox_settings', serialize($lightbox_defaults));
	}

	/**
	 * Creates the necessary HTML for the wp_head() action. 
	 * 
	 * @package Lightbox Plugin
	 */
     function lightbox_header()
     {
     	global $lightbox_settings, $lightbox_path;

		// prepare header and print
		$lightboxHead = "\n\t<!-- Lightbox Plugin {$lightbox_settings['version']} -->\n";
		$lightboxHead.= "\t<link rel=\"stylesheet\" href=\"{$lightbox_path}/css/lightbox.css\" type=\"text/css\" media=\"screen\" />\n";
		$lightboxHead.= "\t<script type=\"text/javascript\"><!--\n";
		$lightboxHead.= "\t\tvar fileLoadingImage = '{$lightbox_path}/images/loading.gif';\n";
		$lightboxHead.= "\t\tvar fileBottomNavCloseImage = '{$lightbox_path}/images/closelabel.gif';\n";
		$lightboxHead.= "\t\tvar resizeSpeed = {$lightbox_settings['speed']};\n";
		$lightboxHead.= "\t\tvar borderSize = {$lightbox_settings['border']};\n";
		$lightboxHead.= "\t\tvar animate = {$lightbox_settings['animate']};\n";
		$lightboxHead.= "\t\tvar overlayOpacity = {$lightbox_settings['opacity']};\n";
		$lightboxHead.= "\t//--></script>\n";
		$lightboxHead.= "\t<script type=\"text/javascript\" src=\"{$lightbox_path}/js/prototype.js\"></script>\n";
		$lightboxHead.= "\t<script type=\"text/javascript\" src=\"{$lightbox_path}/js/scriptaculous.js\"></script>\n";
		$lightboxHead.= "\t<script type=\"text/javascript\" src=\"{$lightbox_path}/js/lightbox.js\"></script>\n";
		$lightboxHead.= "\t<!-- /Lightbox Plugin -->\n";
		
		print($lightboxHead);
     }

	/**
	 * Scans the whole content passed by the filter hook and 
	 * looks for links, then for images, then for rel-tags, then
	 * for a optional title tag and finally adds the lightbox bit.
	 * 
	 * @param string $content The content from the tag filter
	 * @return string Modified content, if any imagelinks where found
	 * @package Lightbox Plugin
	 */
	function lightbox_converter($content)
	{
		if(strpos($content, LIGHTBOX_DISABLE_WORD) === false)
		{			
			// actually i tested that those support single and double quotes.
			// yes, i really suck at regex, but it works. feel free to submit patches :)
			$link_reg = "/<a\s*.*?href\s*=\s*['\"]([^\"'>]*).*?>(.*?)<\/a>/i";
			$title_reg = '#title\s*=\s*[\'|"]*([^("|\')\s>]*)#';
			$rel_reg = '#rel\s*=\s*[\'|"]*([^("|\')\s>]*)#';
			$image_reg = '#^(.*?\.('.LIGHTBOX_EXTENSIONS.')$)#is';
			
			if(preg_match_all($link_reg, $content, $links)) // find all links, if any
			{
				foreach($links[1] as $num => $link) // check all URLs
				{
					if(preg_match($image_reg, $link)) // if the URL leads to an image
					{
						$link_html = $links[0][$num];	
						
						// remove nofollows, to avoid false positives in image links (comments, actually)
						$link_html = str_replace('rel="nofollow"', '', $link_html);
						$link_html = str_replace("rel='nofollow'", '', $link_html);
								
						// no rel-tag yet?
						if(!preg_match($rel_reg, $link_html)) 
						{
							// any title-tag ?
							if(preg_match($title_reg, $link_html, $title)) 
							{
								// series-title tag maybe ?
								if(preg_match('/{(.*)}/', $title[1], $series)) 
								{								
									$new_link_html = str_replace('<a ', '<a rel="lightbox['.$series[1].']" ', $link_html);
									$new_link_html = str_replace($series[0], '', $new_link_html);
								}
								else // single lightbox
								{
									$new_link_html = str_replace('<a ', '<a rel="lightbox" ', $link_html);
								}								
							} 
							else // single lightbox
							{
								$new_link_html = str_replace('<a ', '<a rel="lightbox" ', $link_html);
							}
												
							// replace old <a href..> with new one		
							$content = str_replace($link_html, $new_link_html, $content);				
						}
					}
				} // loop
			}
		}
		return $content;
	}

	/**
	 * Creates the Option Page
	 * 
	 * @package Lightbox Plugin
	 * @see lightbox_options()
	 */
	function lightbox_pages() {
	    add_options_page('Lightbox Options', 'Lightbox Options', 5, 'lightboxoptions', 'lightbox_options');
	}

	/**
	 * Outputs the HTML for the options page
	 * 
	 * @package Lightbox Plugin
	 * @see lightbox_pages()
	 */
	function lightbox_options() 
	{
     	global $lightbox_settings, $lightbox_defaults;

		// if settings are updated
		if (isset($_POST['update_lightbox'])) 
		{
			if (is_numeric($_POST['lightbox_speed'])) {
				$lightbox_settings['speed'] = intval($_POST['lightbox_speed']);
			}
			if (is_numeric($_POST['lightbox_border'])) {
				$lightbox_settings['border'] = intval($_POST['lightbox_border']);
			}
			if (!empty($_POST['lightbox_opacity'])) {
				$lightbox_settings['opacity'] = floatval($_POST['lightbox_opacity']);
			}	
			if ($_POST['lightbox_animate'] == 'true' || $_POST['lightbox_animate'] == 'false') {
				$lightbox_settings['animate'] = $_POST['lightbox_animate'];
			}
			if ($_POST['lightbox_posts'] == 'true' || $_POST['lightbox_posts'] == 'false') {
				$lightbox_settings['posts'] = $_POST['lightbox_posts'];
			}
			if ($_POST['lightbox_excerpt'] == 'true' || $_POST['lightbox_excerpt'] == 'false') {
				$lightbox_settings['excerpt'] = $_POST['lightbox_excerpt'];
			}
			if ($_POST['lightbox_comments'] == 'true' || $_POST['lightbox_comments'] == 'false') {
				$lightbox_settings['comments'] = $_POST['lightbox_comments'];
			}
			
			update_option('lightbox_settings', serialize($lightbox_settings));
		}
		
		// if the user clicks the uninstall button, clean all options and show good-bye message
		if (isset($_POST['uninstall_lightbox'])) 
		{
			delete_option('lightbox_settings');
			
			// deprecated fields ... will be removed in later versions
			delete_option('lightbox_speed');
			delete_option('lightbox_border');
			delete_option('lightbox_opacity');
			delete_option('lightbox_animate');
			delete_option('lightbox_version');
			delete_option('lightbox_posts');
			delete_option('lightbox_excerpt');
			delete_option('lightbox_comments');
			
			// bye bye page
			echo '<div class="wrap">';
			echo '<h2>Good Bye!</h2>';
			echo '<p>All lightbox settings were removed and you can now go to the <a href="plugins.php">plugin menu</a> and deactivate it.</p>';
			echo '<h3>Thank you for using Lightbox '.$lightbox_version.'!</h3>'; 
			echo '<p style="text-align:right"><small>if this happend by accident, <a href="options-general.php?page=lightboxoptions">click here</a> to reinstall</small></p>';
			echo '</div>';
						
		} 
		else // show the menu
		{		
			
			// if the lightbox_version is empty or unequal, 
			// write the defaults to the database
			if($lightbox_settings['version'] != LIGHTBOX_VERSION)
			{
				echo '<div class="updated"><p><strong>New Lightbox Version detected.</strong><br/>Settings were reset to defaults in order to avoid conflicts.</p></div>';
				update_option('lightbox_settings', serialize($lightbox_defaults));
			}
			
			echo '<div class="wrap"><h2>Lightbox Options</h2><small style="display:block;text-align:right">Version: '.LIGHTBOX_VERSION.'</small>';
			echo '<form method="post" action="options-general.php?page=lightboxoptions">';		
			echo '<input type="hidden" name="update_lightbox" value="true" />';
			
			echo '<table class="form-table">';
			
			echo '<tr valign="top">';
			echo '<th scope="row">Resizing-Animation:</th>';
			echo '<td><select name="lightbox_animate" />';
			echo '<option value="true" '.($lightbox_settings['animate']=="true"?'selected':'').'>Animate resizing</option>';
			echo '<option value="false" '.($lightbox_settings['animate']=="false"?'selected':'').'>Don\'t animate</option>';
			echo '</select><br/>Control resizing animation</td>';
			echo '</tr>';		
			
			echo '<tr valign="top">';
			echo '<th scope="row">Resizing-Animation Speed:</th>';
			echo '<td><input name="lightbox_speed" type="text" id="blogname" value="'.$lightbox_settings['speed'].'" size="40" /><br/>1=slowest and 10=fastest, 7 is default</td>';
			echo '</tr>';
	
			echo '<tr valign="top">';
			echo '<th scope="row">Background Opacity:</th>';
			echo '<td><input name="lightbox_opacity" type="text" id="blogname" value="'.$lightbox_settings['opacity'].'" size="40" /><br/>Controls transparency of shadow overlay</td>';
			echo '</tr>';		
	
	
			echo '<tr valign="top">';
			echo '<th scope="row">Border Size:</th>';
			echo '<td><input name="lightbox_border" type="text" id="blogname" value="'.$lightbox_settings['border'].'" size="40" /><br/>If you adjust the padding in the CSS, you will need to update this</td>';
			echo '</tr>';
			
			
			echo '</table>';
			echo '<p class="submit"><input type="submit" name="Submit" value="Update Options &raquo;" /></p>';
			echo '<br/>';
			
			echo '<h2>Lightbox Filter</h2>';
			echo '<table class="form-table">';
			
			echo '<tr valign="top">';
			echo '<th scope="row">Filter Images in Posts:</th>';
			echo '<td><select name="lightbox_posts" /><option value="true" '.($lightbox_settings['posts']=="true"?'selected':'').'>Yes</option><option value="false" '.($lightbox_settings['posts']=="false"?'selected':'').'>No</option></select></td>';
			echo '</tr>';	
			
			echo '<tr valign="top">';
			echo '<th scope="row">Filter Images in Excerpt:</th>';
			echo '<td><select name="lightbox_excerpt" /><option value="true" '.($lightbox_settings['excerpt']=="true"?'selected':'').'>Yes</option><option value="false" '.($lightbox_settings['excerpt']=="false"?'selected':'').'>No</option></select></td>';
			echo '</tr>';		
			
			echo '<tr valign="top">';
			echo '<th scope="row">Filter Images in Comments:</th>';
			echo '<td><select name="lightbox_comments" /><option value="true" '.($lightbox_settings['comments']=="true"?'selected':'').'>Yes</option><option value="false" '.($lightbox_settings['comments']=="false"?'selected':'').'>No</option></select></td>';
			echo '</tr>';		
			
			echo '</table>';
			echo '<p class="submit"><input type="submit" name="Submit" value="Update Options &raquo;" /></p>';		
			
			echo '</form>';
			echo '<br/>';
			
			echo '<h2>Uninstall</h2><form method="post" action="options-general.php?page=lightboxoptions">';
			echo '<input type="hidden" name="uninstall_lightbox" value="true" />';
			echo '<p class="submit"><input type="submit" name="Submit" value="Clear Settings &raquo;" /></p>';		
			echo '</form>';
			
			
			echo '<p>The plugin assumes all files are installed at:<br />'.$lightbox_path.'/</p></div>';
			
		}
	}
	
	
/*******************************************************************
*	LINK THE FUNCTIONS TO THE FILTERS AND ACTIONS
*******************************************************************/	
	
// add the options page to admin menu
add_action('admin_menu', 'lightbox_pages');
	
// add lightbox header to theme
add_action('wp_head', 'lightbox_header');

// add the content filter
if ($lightbox_settings['posts'] == 'true') {
	add_filter('the_content', 'lightbox_converter', 5);
}
if ($lightbox_settings['excerpt'] == 'true') {
	add_filter('the_excerpt', 'lightbox_converter', 5);
}
if ($lightbox_settings['comments'] == 'true') {
	add_filter('comment_text', 'lightbox_converter', 50);
}

?>