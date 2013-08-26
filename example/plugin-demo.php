<?php
/*
Plugin Name: Plugin Demo
Plugin URI: http://takien.com/
Description: Just Demo 
Author: Takien
Version: 0.1
Author URI: http://takien.com/
*/

/*
* This is only sample plugin, you can edit it, comments each line will help you.
* this plugin allows you to insert some text after post content.
* The text can be configured via options page.
* yeah just like that. but at least it's good place to start to write your first plugin.
*/

//include the class
require_once(dirname(__FILE__).'/options/easy-options.php');

//make your plugin class, extends EasyOptions_2

class PluginDemo extends EasyOptions_2 {

	
	//some setting must be in start() method.
	function start() {
		//create admin menu named Plugin Demo
		$admin_menu = Array(
			'group'           => 'plugin-demo-settings', //lowercase, no space. this must be unique accross site, the options in the database will be saved using this name
			'menu_name'       => 'Plugin Demo', //this is menu name that will be shown in the menu list on admin page. required.
			'page_title'      => 'Plugin Demo Page Title', //this is Page title, in case you want it to be different from menu_name. required.
			'menu_slug'       => 'plugin-demo-slug',  //this is menu slug (will be shown in the URL if you click on the menu), required, must be unique.
			'fields'          => Array(),             //fields array, you can set it here or SEE BELOW to set it later.
			'menu_location'   => 'add_menu_page', //menu location, `add_menu_page` is top menu, others are mostly sub menu, see here http://codex.wordpress.org/Administration_Menus  
			'capability'      => 'edit_theme_options', //who can access your menu? `edit_theme_options` means who can edit theme options more here: http://codex.wordpress.org/Roles_and_Capabilities
			
			//'parent_slug'   => '',       //if this is first/top menu, you can skip it.
			'icon_small'      => '',       //URL to image/icon to be displayed on the menu, size 16x16px. if not set, default gear icon will be used.
			'icon_big'        => plugins_url( 'options/images/icon-setting-large.png' , __FILE__  ),       //URL to image/icon to be displayed on the page, size 32x32px.
			'menu_position'   => 85,       //position/order, only for top menu. see here for more. http://codex.wordpress.org/Function_Reference/add_menu_page#Menu_Positions
			'add_tab'         => true,    //set true if you want to tabbed menu. must be true if your menu more than one (contains sub menu).
			);
		$this->add_admin_menu($admin_menu);
		
		//lets create some fields
		$fields = Array(
			Array(
				'name'         => 'name',  //whatever you want.
				'label'        => 'Your name please', //label
				'type'         => 'text',  //input type, can be text, select, radio, etc
				'value'        => '',      //default value if nothing set.
				//'values'     => Array(),  //just like value, but values is for `select` type.
				'description' => 'This is just demo for text field' //say something so you understand what should be entered.
				),
			Array(
				'name'         => 'gender', 
				'label'        => 'Gender', 
				'type'         => 'select',  
				'value'        => 'female', //if you set value here, it will be default selected on the option select.
				'values'     => Array(
					'male'  => 'Male',
					'female'=> 'Female'
				), 
				'description' => 'Your sex' 
				),
				Array(
				'name'         => 'About you',  
				'label'        => 'about', 
				'type'         => 'textarea',  
				'value'        => '',
				'description' => 'About yourself'
				),
			
			);
		//add fields, first param is 'group', must be matched to the group name we have created above..
		$this->add_fields('plugin-demo-settings',$fields);
		
		//ADD MENU 2, SUBS OF MENU 1, MAKE IT NO FIELDS
		$admin_menu2 = Array(
			'group'           => 'plugin-demo-about', //lowercase, no space. this must be unique accross site, the options in the database will be saved using this name
			'menu_name'       => 'About', //this is menu name that will be shown in the menu list on admin page. required.
			'page_title'      => 'About', //this is Page title, in case you want it to be different from menu_name. required.
			'menu_slug'       => 'plugin-demo-about',  //this is menu slug (will be shown in the URL if you click on the menu), required, must be unique.
			//'fields'          => Array(),             //fields array, you can set it here or SEE BELOW to set it later.
			'menu_location'   => 'add_sub_menu_page', //menu location, `add_menu_page` is top menu, others are mostly sub menu, see here http://codex.wordpress.org/Administration_Menus  
			'capability'      => 'edit_theme_options', //who can access your menu? `edit_theme_options` means who can edit theme options more here: http://codex.wordpress.org/Roles_and_Capabilities
			'page_callback'   => 'about_plugin_demo', //because this tab contain no fields, we use function name instead to display text.
			'parent_slug'     => 'plugin-demo-slug',       //if this is first/top menu, you can skip it.
			'icon_small'      => '',       //URL to image/icon to be displayed on the menu, size 16x16px. if not set, default gear icon will be used.
			'icon_big'        => plugins_url( 'options/images/icon-setting-large.png' , __FILE__  ),       //URL to image/icon to be displayed on the page, size 32x32px.
			//'menu_position'   => 85,       //position/order, only for top menu. see here for more. http://codex.wordpress.org/Function_Reference/add_menu_page#Menu_Positions
			'add_tab'         => true,    //set true if you want to tabbed menu. must be true if your menu more than one (contains sub menu).
			);
		$this->add_admin_menu( $admin_menu2 );		
	}
	
	function about_plugin_demo() { //this callback function will be called in second tab
		?>	
			<p>
			Plugin name: <strong>Plugin Demo</strong><br>
			Version: <strong>0.1</strong><br>
			Author: <strong>takien</strong>
			Author URL: <a href="http://takien.com">http://takien.com</a>
			</p>
			<p>
				This tab contains arbritrary text instead of form fields. It's ideal to put information about the plugin or other information here.
			</p>
		<?php
	}
}

$plgn = new PluginDemo;
$plgn->start();

