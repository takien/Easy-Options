<?php
/* Takien Plugin Options
@author: takien.com
@ver 0.4
@added: textarea, class  regular-text, dynamic menu location
- filter: before_form, after form
*/

if(!class_exists('takienPluginOptions')){
	class takienPluginOptions{
		var $option_group;
		var $menu_name;
		var $menu_slug;
		var $fields     = Array();
		var $settings 	= Array();
       

		/* menu_location
		 * add_theme_page,add_menu_page,add_submenu_page etc
		 * for more: see here http://codex.wordpress.org/Administration_Menus
		 */
		var $menu_location   = 'add_menu_page';
		var $capability 	 = 'edit_theme_options';
		var $parent_slug;
		var $icon_small;
		var $icon_big;
		var $menu_position;
		var $add_tab = false;
		var $tab_pos = 1;

		
		function __construct(){
			add_action('admin_init',array(&$this,'register_setting'));
			add_action('admin_menu',array(&$this,'add_page'));
			add_filter('takien_plugin_options_tabs',array(&$this,'tab'));
			
		}
		
		/**
		* Render form
		* @param array 
		*/	
		private function render_form($fields){
			$output ='<table class="form-table">';
			foreach($fields as $field){
				$field['rowclass'] = isset($field['rowclass']) ? $field['rowclass'] : false;
				$field['name'] = $this->option_group.'['.$field['name'].']';
				
				if($field['type']=='text'){
					$output .= '<tr '.($field['rowclass'] ? 'class="'.$field['rowclass'].'"': '').'><th><label for="'.$field['name'].'">'.$field['label'].'</label></th>';
					$output .= '<td><input type="text" id="'.$field['name'].'" name="'.$field['name'].'" value="'.$field['value'].'" />';
					$output .= ' <span class="description">'.$field['description'].'</span></td></tr>';
				}
				if($field['type']=='checkbox'){
					$output .= '<tr '.($field['rowclass'] ? 'class="'.$field['rowclass'].'"': '').'><th><label for="'.$field['name'].'">'.$field['label'].'</label></th>';
					$output .= '<td><input type="hidden" name="'.$field['name'].'" value="" /><input type="checkbox" id="'.$field['name'].'" name="'.$field['name'].'" value="'.$field['value'].'" '.$field['attr'].' />';
					$output .= ' <span class="description">'.$field['description'].'</span></td></tr>';
				}
				if($field['type']=='checkboxgroup'){
					$output .= '<tr '.($field['rowclass'] ? 'class="'.$field['rowclass'].'"': '').'><th><label>'.$field['grouplabel'].'</label></th>';
					$output .= '<td>';
					foreach($field['groupitem'] as $key=>$item){
						$output .= '<input type="hidden" name="'.$item['name'].'" value="" /><input type="checkbox" id="'.$item['name'].'" name="'.$item['name'].'" value="'.$item['value'].'" '.$item['attr'].' /> <label for="'.$item['name'].'">'.$item['label'].'</label><br />';
					}
					$output .= ' <span class="description">'.$field['description'].'</span></td></tr>';
				}
				if($field['type'] == 'select'){
					$output .= '<tr '.($field['rowclass'] ? 'class="'.$field['rowclass'].'"': '').'><th><label>'.$field['label'].'</label></th>';
					$output .= '<td>';
					$output .= '<select name="'.$field['name'].'">';
					foreach( (array)$field['values'] as $val=>$name ) {
						$output .= '<option '.(($val==$field['value']) ? 'selected="selected"' : '' ).' value="'.$val.'">'.$name.'</option>';
					}
					$output .= '</select>';
					$output .= ' <span class="description">'.$field['description'].'</span></td></tr>';
				}
				if($field['type'] == 'dropdown_roles'){
					$output .= '<tr '.($field['rowclass'] ? 'class="'.$field['rowclass'].'"': '').'><th><label>'.$field['label'].'</label></th>';
					$output .= '<td>';
					$output .= '<select name="'.$field['name'].'">';
					
					$p = $r = '';
					$editable_roles = get_editable_roles();
					
					foreach ( $editable_roles as $role => $details ) {
						$name = translate_user_role($details['name'] );
						if ( $field['value'] == $role ) // preselect specified role
						$p = "\n\t<option selected='selected' value='" . esc_attr($role) . "'>$name</option>";
						else
						$r .= "\n\t<option value='" . esc_attr($role) . "'>$name</option>";
					}
					$output .= $p . $r;
					$output .= '</select>';
					$output .= ' <span class="description">'.$field['description'].'</span></td></tr>';
				}
			}
			$output .= '</table>';
			return $output;
		}
		
		/*register setting*/
		function register_setting() {
			register_setting($this->menu_slug.'_option_field', $this->option_group);
		}
		
		/*option*/
		function option($key=''){
			$option = get_option($this->option_group) ? get_option($this->option_group) : Array();
			$option = array_merge($this->settings,$option);

			$return = false;
			if($key){
				if(isset($option[$key])){
				$return = $option[$key];
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
		
		if(in_array($this->menu_location,$top_menu)){
			call_user_func($this->menu_location, $this->menu_name, $this->menu_name, $this->capability, $this->menu_slug, array(&$this,'page'), $this->icon_small, $this->menu_position);
		}
		else if (in_array($this->menu_location,$specific_sub_menu)){
			call_user_func($this->menu_location,$this->menu_name, $this->menu_name, $this->capability, $this->menu_slug, array(&$this,'page'));
		}
		else if(strpos($this->menu_location,'post_type') === 0){
			$post_type = end(explode('=',$this->menu_location));
			add_submenu_page( "edit.php?post_type=$post_type", $this->menu_name, $this->menu_name, $this->capability, $this->menu_slug,  array(&$this,'page') );
		}
		else  {
			if($this->parent_slug) {
				add_submenu_page( $this->parent_slug, $this->menu_name, $this->menu_name, $this->capability, $this->menu_slug,  array(&$this,'page') );
			}
		}
			
	}
		function page(){ ?>
			<div class="wrap">
			<?php 
			$icon = $this->icon_big ? $this->icon_big : get_bloginfo('template_url').'/images/icon-setting.png';?>
			<div class="icon32"><img src="<?php echo $icon;?>" /></div>
				<?php 
				$navs = apply_filters('takien_plugin_options_tabs','');
				if(!empty($navs)) {
				echo '<h2 class="nav-tab-wrapper">';
				if(is_array($navs)){
					foreach($navs as $nav){
						$class = ( $nav['slug'] == $_GET['page'] ) ? ' nav-tab-active' : '';
						echo '<a class="nav-tab '.$class.'" href="?page='.$nav['slug'].'">'.$nav['name'].'</a>';
					}
				}
				echo '</h2>';
				}
				else {
				?>
				<h2><?php echo $this->menu_name;?></h2>
				<?php } ?>
				<?php
					echo apply_filters('takien_plugin_option_'.$this->menu_slug.'_before_form','');
				?>
				<form method="post" action="options.php">
					<?php 
					wp_nonce_field('update-options'); 
					settings_fields($this->menu_slug.'_option_field');?>
					<?php
						if(!empty($this->fields)){
							echo $this->render_form($this->fields);
						}

					?>
				<input type="hidden" name="action" value="update" />
				<input type="hidden" name="<?php echo $this->menu_slug.'_option_field';?>" value="<?php echo $this->option_group;?>" />
				<p><input type="submit" class="button-primary" value="Save" /> </p>
				</form>
				<?php 
					echo apply_filters('takien_plugin_option_'.$this->menu_slug.'_after_form','');
				?>
				<?php /*<p>
				To retrieve value in your theme, use <strong>&lt;?php echo theme_option('FIELD_NAME','<?php echo $this->option_group;?>');?&gt;</strong>, example: &lt;?php echo theme_option('facebook_page','<?php echo $this->option_group;?>');?&gt;</p>*/?>
				
			</div>
		<?php
		}
		/*tab*/
		function tab($tab){
			if($this->add_tab) {
				$tab[] = array(
					'slug'=>$this->menu_slug,
					'name'=>$this->menu_name
				);
			}
			return $tab;
		}
		
	}
}