<?php 
/**
 * @package CodeFlavors floating menu
 * @author CodeFlavors ( http://www.codeflavors.com )
 * @version 1.0
 */

/*
Plugin Name: CodeFlavors floating menu
Plugin URI: 
Description: Displays a floating menu on the right or left side of your WordPress blog.
Author: CodeFlavors
Version: 1.0
Author URI: http://www.codeflavors.com
*/	

define('CFM_LOCATION', 'cfm_floating_menu');

/**
 * Admin menu page
 */
add_action('admin_menu', 'cfm_admin_menu');
// action callback
function cfm_admin_menu(){
	$page = add_submenu_page('themes.php', __('CodeFlavors Menu', 'cfm_menu'), __('CodeFlavors Menu', 'cfm_menu'), 'manage_options', 'cfm_menu_options', 'cfm_admin_page');
	add_action('load-'.$page, 'cfm_admin_page_load');
}

/**
 * Administration page display
 */
function cfm_admin_page(){
	// get options
	$options = cfm_get_options();
	// errors display
	global $cfm_errors;
	if( is_wp_error( $cfm_errors ) ){
		$options = $_POST;		
	}	
?>
<div class="wrap">
	<div class="icon32" id="icon-themes"><br></div>
	<h2><?php _e('CodeFlavors Floating Menu', 'cfm_menu');?></h2>
	<?php if( isset($_GET['message']) && 'success' == $_GET['message'] && !is_wp_error($cfm_errors) ):?>
	<div class="updated"><p><?php _e('Settings saved.', 'cfm_menu');?></p></div>
	<?php endif;//end of success message display?>
	<?php if( is_wp_error($cfm_errors) ):?>
	<div class="error">
		<p>
			<?php 
				$err_code = $cfm_errors->get_error_code();
				echo $cfm_errors->get_error_message($err_code);
			?>
		</p>
	</div>	
	<?php endif;?>	
	<p><?php printf( __('To assign a menu, navigate to %1$sMenus%2$s and select a menu for CodeFlavors floating menu location.', 'cfm_menu'), '<a href="nav-menus.php">', '</a>' );?></p>
	<form method="post" action="">
		<label for="animation"><?php _e('Menu animation', 'cfm_menu');?>: </label>
		<select name="animation" id="animation">
			<option value="fixed"><?php _e('Fixed - no animation', 'cfm_menu')?></option>
			<option value="animated"<?php if('animated'==$options['animation']):?> selected="selected"<?php endif;?>><?php _e('Animated', 'cfm_menu')?></option>
		</select><br />
		<label for="position"><?php _e('Menu position', 'cfm_menu')?>: </label>
		<select name="position" id="position">
			<option value="left"><?php _e('Left', 'cfm_menu');?></option>
			<option value="right"<?php if('right'==$options['position']):?> selected="selected"<?php endif;?>><?php _e('Right', 'cfm_menu');?></option>
		</select><br />
		<label for="top_distance"><?php _e('Top distance', 'cfm_menu');?>: </label>
		<input type="text" name="top_distance" value="<?php echo $options['top_distance'];?>" id="top_distance" size="2" /> px.<br />
		<label for="menu_title"><?php _e('Menu title');?>: </label>
		<input type="text" name="menu_title" id="menu_title" value="<?php echo $options['menu_title'];?>" size="60" />
		
		<?php wp_nonce_field('cfm_update_settings', 'cfm_nonce');?>
		<?php submit_button( __('Save settings', 'cfm_menu'), 'primary', 'submit' );?>
	</form>
</div>
<?php 
}

/**
 * Load action on administration page.
 * Saves options and sets errors to be displayed in case needed
 */
function cfm_admin_page_load(){
	// add styles to administration page
	wp_enqueue_style('cfm_admin_page', plugins_url('css/admin.css', __FILE__));
	// save options
	if( isset( $_POST['cfm_nonce'] ) ){
		if( wp_verify_nonce($_POST['cfm_nonce'], 'cfm_update_settings') ){
			$defaults = cfm_default_options();
			
			global $cfm_errors;
			
			foreach( $defaults as $name=>$value ){
				if( empty( $_POST[$name] ) ){
					if( !empty($value) ){
						$cfm_errors = new WP_Error();
						$cfm_errors->add('cfm_empty_field', __('Settings were not saved. Please try to fill all fields.'));
						break;
					}
				}
				
				if( is_numeric($value) && isset($_POST[$name]) ){
					$value = absint( $_POST[$name] );					
				}elseif (is_string($value)){
					$value = sanitize_text_field($_POST[$name]);
				}				
				$defaults[$name] = $value;
			}	
			// if no errors, do save
			if( !is_wp_error($cfm_errors) ){
				update_option('cfm_floating_menu', $defaults);
				$page = add_query_arg(array(
					'message' => 'success'
				), menu_page_url('cfm_menu_options', false));
				
				wp_redirect( $page, false );
				exit();
			}
		}
	}
}

/**
 * Register menu location
 */
add_action('init', 'cfm_floating_menu');
// action callback
function cfm_floating_menu(){	
	register_nav_menu(CFM_LOCATION, 'CodeFlavors floating menu');
}

/**
 * Display menu on front-end
 */
add_action('wp_footer', 'cfm_show_menu');
// action callback
function cfm_show_menu(){
	
	if( !cfm_has_menu() ){
		return;
	}
	
	$container_class = 'cfn_menu_floating_menu_container';
	$options = cfm_get_options();
	if( 'animated' == $options['animation'] ){
		$container_class.= ' animated';
	}
	if( 'right' == $options['position'] ){
		$container_class.= ' right';
	}
	
	
	// get the menu
    $args = array(
		'theme_location' 	=> CFM_LOCATION,
		'menu' 				=> '',
		'container' 		=> 'div',
		'container_class' 	=> $container_class,
		'container_id' 		=> 'cfn_floating_menu',
		'echo' 				=> true
	);		
	wp_nav_menu($args);

	$opt = array(
		'top_distance' => $options['top_distance'],
		'animate' => 'animated' == $options['animation'] ? 1 : 0,
		'position' => $options['position']
	);
	
	// plugin JavaScript params
	$params = "\n".'<script language="javascript" type="text/javascript">'."\n";
	$params.= "\tvar CFM_MENU_PARAMS='".json_encode($opt)."';\n";
	$params.= '</script>'."\n";
	echo $params;
}

/**
 * Filter on navigation menu to put the menu title on CFM menu
 * @param array $sorted_menu_items - menu items
 * @param array $args - menu arguments
 */
add_filter('wp_nav_menu_objects', 'cfm_nav_menu_filter', 10, 2);
// filter callback
function cfm_nav_menu_filter( $sorted_menu_items, $args ){
	if( CFM_LOCATION != $args->theme_location ){
		return $sorted_menu_items;
	}
	// get options
	$options = cfm_get_options();
	// if menu title isn't set, return only the pages list
	if( !isset($options['menu_title']) || empty($options['menu_title']) ){
		return $sorted_menu_items;
	}
	
	// add menu title to menu
	$item = new stdClass();
	$item->title = $options['menu_title'];
	$item->guid = '#';
	$item->url = '#';
	$item->menu_item_parent = false;
	$item->ID = -1;
	$item->db_id = -1;
	$item->is_menu_title = true;
	$item->classes = array('cfm_menu_title_li');
	
	add_filter('nav_menu_item_id', 'cfm_menu_title_filter', 10, 2);
	
	// put menu title in menu elements	
	array_unshift($sorted_menu_items, $item);
	return $sorted_menu_items;
}

/**
 * Filter callback to put a unique ID on CFM menu title list element.
 * Gets called only if menu title is on and only for menu title li element.
 */
function cfm_menu_title_filter( $id, $item ){
	if( !isset( $item->is_menu_title ) ){
		return $id;
	}	
	return 'cfm_menu_title_li';
}

/**
 * Add styles top front-end
 */
add_action('wp_print_styles', 'cfm_frontend_styles');
// action callback
function cfm_frontend_styles(){
	if( !cfm_has_menu() ){
		return;
	}	
	wp_enqueue_style('cfm_frontend_menu', plugins_url('css/cfm_menu.css', __FILE__));
} 

/**
 * Add scripts to front-end
 */
add_action('wp_print_scripts', 'cfm_frontend_scripts');
// action callback
function cfm_frontend_scripts(){
	if( !cfm_has_menu() ){
		return;
	}
	wp_enqueue_script('cfm_frontend_menu', plugins_url('js/cfm_menu.js', __FILE__), array('jquery'));	
}

/**************************************************
 * Helper functions
 **************************************************/

/**
 * Verifies if a menu is assigned to plugin menu position. Useful to check if
 * styles and scripts should load into pages.
 */
function cfm_has_menu(){
	if ( ( $locations = get_nav_menu_locations() ) && isset( $locations[ CFM_LOCATION ] ) ) {
		// get menu object
    	$menu = wp_get_nav_menu_object( $locations[ CFM_LOCATION ] );
    	if( $menu ){
    		return true;
    	}
	}
	return false;
}
/**
 * Default options
 */
function cfm_default_options(){
	// menu options defaults
	$defaults = array(
		'animation' => 'fixed',
		'position' => 'left',
		'top_distance' => '50',
		'menu_title' => ''
	);
	return $defaults;
}

/**
 * Database plugin options
 */
function cfm_get_options(){
	$defaults = cfm_default_options();
	return get_option('cfm_floating_menu', $defaults);
}