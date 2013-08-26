<?php
/**
	* @name   : Easy Options
	* @author : takien
	* @version: 1.5
	* @link   : http://takien.com
	* @url    : https://github.com/takien/Easy-Options
	* 
 */
defined('ABSPATH') or die();

if(!class_exists('EasyOptions_2')) {
	class EasyOptions_2 {
		var $defaults = Array(
				'group'           => '',
				'menu_name'       => '',
				'page_title'      => '',
				'page_callback'   => false,
				'menu_slug'       => '',
				'fields'          => Array(),
				'menu_location'   => 'add_menu_page',
				'capability'      => 'edit_theme_options',
				'parent_slug'     => '',
				'icon_small'      => '',
				'icon_big'        => '',
				'menu_position'   => 85,
				'add_tab'         => false,
			);
		var $admin_menu     = Array();
		var $add_menu       = Array();
		var $fields         = Array();
		
		//costruct
		public function __construct($args=array()) {
			
			add_action( 'admin_init',array(&$this,'register_setting') );
			add_action( 'admin_menu',array(&$this,'add_page') );
					
			
			add_filter($this->tab_nav(),array(&$this,'tab'),200);
			
			/* foreach($args as $key=>$val) {
				if(isset($this->$key)) {
					$this->$key = $val;
				}
			} */

			//$this->settings
			//$this->add_settings = $this->add_settings();
			//array_push($this->settings, $this->add_settings());
			/* $this->fields = $this->fields();
			
			if(!$this->parent_slug) {
				$this->init();
			}
			$this->page_title = $this->page_title ? $this->page_title : $this->menu_name;
			add_filter($this->tab_nav(),array(&$this,'tab')); */
		}
		
		function add_admin_menu($menu) {
			$menu = $this->merge($this->defaults,$menu);
			array_push($this->admin_menu, $menu);
		}
		
		function add_fields($group,$fields) {
			
			$this->fields[$group] = $fields;
			
     	}
		
		/*register setting*/
		function register_setting() {
			foreach($this->admin_menu as $menu) {
				extract ( $menu );
				//only register if fields exists
				if(!empty( $this->fields[$group] ) )
					register_setting( $menu_slug.'_option_field', $group );
			}
			
		}
		
		/*option*/
		function option($key='',$group='',$default=''){
			$option = get_option($group);
			$return = '';
			if($key){
				if(isset($option[$key]) AND !empty($option[$key])){
					$return = $option[$key];
				}
				else {
					$return = $default;
				}
			}
			else{
				$return = $option;
			}
			return $return;
		}
		
		/*add page*/
		function add_page(){
			
			$top_menu = Array(
				'add_menu_page', //$page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position 
				'add_object_page', 
				'add_utility_page' //$page_title, $menu_title, $capability, $menu_slug, $function, $icon_url
			);
			$specific_sub_menu = Array(
				'add_dashboard_page', //$page_title, $menu_title, $capability, $menu_slug, $function
				'add_posts_page', 
				'add_media_page', 
				'add_links_page',  
				'add_pages_page', 
				'add_comments_page', 
				'add_theme_page', 
				'add_plugins_page', 
				'add_users_page', 
				'add_management_page', 
				'add_options_page'  
			);
			foreach( $this->admin_menu as $menu ) {
			
				extract ( $menu );
				if(in_array($menu_location,$top_menu)){
					call_user_func($menu_location, $page_title, $menu_name, $capability, $menu_slug, array(&$this,'page'), $icon_small, $menu_position);
				}
				else if (in_array($menu_location, $specific_sub_menu)){
					call_user_func($menu_location, $page_title, $menu_name, $capability, $menu_slug, array(&$this,'page'));
				}
				else if(strpos($menu_location,'post_type') === 0){
					$post_type = end(explode('=',$menu_location));
					add_submenu_page( "edit.php?post_type=$post_type", $page_title, $menu_name, $capability, $menu_slug,  array(&$this,'page') );
				}
				else  {
					if($parent_slug) {
						add_submenu_page( $parent_slug, $page_title, $menu_name, $capability, $menu_slug,  array(&$this,'page') );
					}
				}
				
			}
			
		}
		
		//page
		function page(){
			global $plugin_page;
/* 			echo $this->tab_nav();
				echo '<hr/>'; */
		foreach( $this->admin_menu as $menu ) {
			extract ( $menu );
			
			if($menu_slug !== $plugin_page) continue;
			?>
			<div class="wrap">
				<div class="icon32"><img src="<?php echo $icon_big;?>" /></div>
				<?php 
					$navs = apply_filters($this->tab_nav(),'');
					if(!empty($navs)) {
						echo '<h2 class="nav-tab-wrapper">';
						if(is_array($navs)){
							foreach($navs as $nav){
								$class = ( $nav['slug'] == $plugin_page ) ? ' nav-tab-active' : '';
								echo '<a class="nav-tab '.$class.'" href="?page='.$nav['slug'].'">'.$nav['name'].'</a>';
							}
						}
						echo '</h2>';
					}
					else {
					?>
					<h2><?php echo $page_title;?></h2>
				<?php } 
					if(isset($_GET['settings-updated']) AND ('add_options_page' !== $menu_location)) { ?>
					<div id="setting-error-settings_updated" class="updated settings-error"> 
						<p><strong>Settings saved.</strong></p>
					</div>
					<?php }
					echo apply_filters('easy_option_'.$menu_slug.'_before_form','');
					if( $page_callback AND is_callable( $this->$page_callback() ) ) {
						call_user_func ( $this->$page_callback() );
					}
					//Don't create form if no fields.
					else if(!empty( $this->fields[$group] ) ){
					?>
					
					<form method="post" action="options.php">
						<?php 
							wp_nonce_field('update-options'); 
							settings_fields($menu_slug.'_option_field');?>
						<?php
							
								echo $this->form( $this->fields[$group], $group );
							
							
						?>
						<input type="hidden" name="action" value="update" />
						<input type="hidden" name="<?php echo $menu_slug.'_option_field';?>" value="<?php echo $group;?>" />
						<p><input type="submit" class="button-primary" value="Save" /> </p>
					</form>
					<?php 
					}
					echo apply_filters('easy_option_'.$menu_slug.'_after_form','');
				?>
				
			</div>
			<?php
			}
		}
		
		/*
		* Unique tab group name
		*/
		function tab_nav() {
			return 'easy_options_tabs_plugin-demo-slug';
		}
		
		/*tab*/
		function tab( $tab ){
		
		$tab = Array();
			foreach( $this->admin_menu as $menu ) {
				extract ( $menu );
				if($add_tab) {
					$tab[] = array(
					   'slug' => $menu_slug,
					   'name' => $page_title
					);
				}
			}
			return $tab;
		}

		/**
		 * Render form
		 * @param array 
		 */	
		function form( $fields, $group){
			$output ='<table class="form-table">';
			foreach($fields as $field){
			
				$pairs = Array(
					'name'        =>'',
					'attr'        =>'',
					'value'       =>'',
					'label'       =>'',
					'rowclass'    =>'',
					'description' =>'',
					'groupitem'   =>'',
					'type'        =>'text',
					'grouplabel'  =>'',
					'values'      =>Array(),
					'style'       =>'',
				);

				extract ( $this->merge($pairs, $field) );
			
				if ( $type == 'checkbox' ) {
					$attr = $attr. ' '.(($value) ? 'checked="checked"' : '');
				}
				
				$value = $this->option( $name,$group,$value );
				
				$name  = $group.'['.$name.']';
				
				
				//dropdown pages
				if($type == 'dropdown_pages') {
					$values = $this->dropdown_pages();
				}
				
				if($type=='textarea'){
						$output .= '<tr><th><label for="'.$name.'">'.$label.'</label></th>';
						$output .= '<td style="vertical-align:top"><textarea '.($style ? $style : 'style="width:400px;height:150px"').' id="'.$name.'" name="'.$name.'">'.esc_textarea($value).'</textarea>';
						$output .= ' <p class="description">'.$description.'</p></td></tr>';
				}
				if($type=='text'){
					$output .= '<tr '.($rowclass ? 'class="'.$rowclass.'"': '').'><th><label for="'.$name.'">'.$label.'</label></th>';
					$output .= '<td><input class="regular-text" type="text" id="'.$name.'" name="'.$name.'" value="'.$value.'" />';
					$output .= ' <p class="description">'.$description.'</p></td></tr>';
				}
				if($type=='checkbox'){
					$output .= '<tr '.($rowclass ? 'class="'.$rowclass.'"': '').'><th><label for="'.$name.'">'.$label.'</label></th>';
					$output .= '<td><input type="hidden" name="'.$name.'" value="" /><input type="checkbox" id="'.$name.'" name="'.$name.'" value="1" '.$attr.' />';
					$output .= ' <p class="description">'.$description.'</p></td></tr>';
				}
				if($type=='checkboxgroup'){
					$output .= '<tr '.($rowclass ? 'class="'.$rowclass.'"': '').'><th><label>'.$field['grouplabel'].'</label></th>';
					$output .= '<td>';
					foreach($groupitem as $key=>$item){
						$output .= '<input type="hidden" name="'.$item['name'].'" value="" /><input type="checkbox" id="'.$item['name'].'" name="'.$item['name'].'" value="1" '.$item['attr'].' /> <label for="'.$item['name'].'">'.$item['label'].'</label><br />';
					}
					$output .= ' <p class="description">'.$description.'</p></td></tr>';
				}
				if(($type == 'select') OR $type == 'dropdown_pages') {
					$output .= '<tr '.($rowclass ? 'class="'.$rowclass.'"': '').'><th><label>'.$label.'</label></th>';
					$output .= '<td>';
					$output .= '<select style="min-width:200px" name="'.$name.'">';
					foreach( $values as $val=>$name_ ) {
						$output .= '<option '.(($val==$value) ? ' selected="selected" ' : '' ).' value="'.$val.'">'.$name_.'</option>';
					}
					$output .= '</select>';
					$output .= ' <p class="description">'.$description.'</p></td></tr>';
				}
				if($type == 'dropdown_roles'){
					$output .= '<tr '.($rowclass ? 'class="'.$rowclass.'"': '').'><th><label>'.$label.'</label></th>';
					$output .= '<td>';
					$output .= '<select name="'.$name.'">';
					
					$p = $r = '';
					$editable_roles = get_editable_roles();
					
					foreach ( $editable_roles as $role => $details ) {
						$name = translate_user_role($details['name'] );
						if ( $value == $role ) // preselect specified role
						$p = "\n\t<option selected='selected' value='" . esc_attr($role) . "'>$name</option>";
						else
						$r .= "\n\t<option value='" . esc_attr($role) . "'>$name</option>";
					}
					$output .= $p . $r;
					$output .= '</select>';
					$output .= ' <p class="description">'.$description.'</p></td></tr>';
				}
			}
			$output .= '</table>';
			return $output;
		}	
		
		/**
		 * Dropdown pages select
		 * Since 1.3
		 */
		function dropdown_pages(){
			$args = Array();
			$return = Array();
			$pages = get_pages( $args );
			foreach($pages as $k=>$v){
				$return[$v->ID] = $v->post_title;
			}
			return $return;
		}
		
				
		private function merge($arr1,$arr2) {
			$arr2   = (array)$arr2;
			$return = Array();
			foreach($arr1 as $name => $default) {
				if ( array_key_exists($name, $arr2) ) {
					$return[$name] = $arr2[$name];
				}
				else {
					$return[$name] = $default;
				}
			}
			return $return;
		}
	}
}