<?php
/**
	* @name   : Easy Options
	* @author : takien
	* @version: 1.3
	* @link   : http://takien.com
	* 
 */
defined('ABSPATH') or die();

if(!class_exists('EasyOptions')) {
	class EasyOptions {


	var $group           = '';
	var $menu_name       = '';
	var $menu_slug       = '';
	var $fields          = Array();
	var $default         = Array();
	var $menu_location   = 'add_menu_page';
	var $capability 	 = 'edit_theme_options';
	var $parent_slug     = '';
	var $icon_small      = '';
	var $icon_big        = '';
	var $menu_position   = 82;
	var $add_tab = false;


	public function __construct($args=array()) {
		add_action('admin_init',array(&$this,'register_setting'));
		add_action('admin_menu',array(&$this,'add_page'));
		
		foreach($args as $key=>$val) {
			
			if(isset($this->$key)) {
				$this->$key = $val;
			}
		}
		add_filter($this->tab_nav(),array(&$this,'tab'));
		
	}
	

	/*register setting*/
	function register_setting() {
		register_setting($this->menu_slug.'_option_field', $this->group);
	}
	
	/*option*/
	function option($key='',$group=''){
		$group = $group ? $group : $this->group;
		$option = get_option($group) ? get_option($group) : Array();
		//$option = array_merge($this->default,$option);
		$option = array_replace_recursive($option,$this->default,$option);

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
		$icon = $this->icon_big;?>
		<div class="icon32"><img src="<?php echo $icon;?>" /></div>
		<?php 

			$navs = apply_filters($this->tab_nav(),'');
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
		<?php } 
			if(isset($_GET['settings-updated'])) { ?>
			<div id="setting-error-settings_updated" class="updated settings-error"> 
				<p><strong>Settings saved.</strong></p>
			</div>
			<?php }
			echo apply_filters('easy_option_'.$this->menu_slug.'_before_form','');
		?>
		
		<form method="post" action="options.php">
			<?php 
				wp_nonce_field('update-options'); 
				settings_fields($this->menu_slug.'_option_field');?>
			<?php
				if(!empty($this->fields)){
					echo $this->form($this->fields);
				}
				
			?>
			<input type="hidden" name="action" value="update" />
			<input type="hidden" name="<?php echo $this->menu_slug.'_option_field';?>" value="<?php echo $this->group;?>" />
			<p><input type="submit" class="button-primary" value="Save" /> </p>
		</form>
		<?php 
			echo apply_filters('easy_option_'.$this->menu_slug.'_after_form','');
		?>
		<p>To retrieve value in your theme or plugin, use <code>&lt;?php echo easy_options('FIELD_NAME','<?php echo $this->group;?>');?&gt;</code>, example: <code>&lt;?php echo easy_options('facebook_page','<?php echo $this->group;?>');?&gt;</code></p>
		
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
	
	/*
	* Unique tab group name
	*/
	function tab_nav() {
		return 'easy_options_tabs_'.md5($this->parent_slug ? $this->parent_slug : $this->menu_slug);
	}
	
	/**
	 * Render form
	 * @param array 
	 */	
	private function form($fields){
		$output ='<table class="form-table">';
		foreach($fields as $field){
			$field['rowclass'] = isset($field['rowclass']) ? $field['rowclass'] : false;
			$value = $this->option($field['name']) ? $this->option($field['name']) : (isset($field['value']) ? $field['value'] : null);
			$field['attr'] = isset($field['attr']) ? $field['attr'] : '';
			
			if ( $field['type']=='checkbox' ) {
				$field['attr'] = $field['attr']. ' '.(($value) ? 'checked="checked"' : '');
			}
			
			$field['name'] = $this->group.'['.$field['name'].']';
			
			//dropdown pages
			if($field['type'] == 'dropdown_pages') {
				$field['values'] = $this->dropdown_pages();
			}
			
			if($field['type']=='textarea'){
					$output .= '<tr><th><label for="'.$field['name'].'">'.$field['label'].'</label></th>';
					$output .= '<td style="vertical-align:top"><textarea style="width:400px;height:150px" id="'.$field['name'].'" name="'.$field['name'].'">'.esc_textarea($value).'</textarea>';
					$output .= ' <p class="description">'.$field['description'].'</p></td></tr>';
			}
			if($field['type']=='text'){
				$output .= '<tr '.($field['rowclass'] ? 'class="'.$field['rowclass'].'"': '').'><th><label for="'.$field['name'].'">'.$field['label'].'</label></th>';
				$output .= '<td><input class="regular-text" type="text" id="'.$field['name'].'" name="'.$field['name'].'" value="'.$value.'" />';
				$output .= ' <p class="description">'.$field['description'].'</p></td></tr>';
			}
			if($field['type']=='checkbox'){
				$output .= '<tr '.($field['rowclass'] ? 'class="'.$field['rowclass'].'"': '').'><th><label for="'.$field['name'].'">'.$field['label'].'</label></th>';
				$output .= '<td><input type="hidden" name="'.$field['name'].'" value="" /><input type="checkbox" id="'.$field['name'].'" name="'.$field['name'].'" value="1" '.$field['attr'].' />';
				$output .= ' <p class="description">'.$field['description'].'</p></td></tr>';
			}
			if($field['type']=='checkboxgroup'){
				$output .= '<tr '.($field['rowclass'] ? 'class="'.$field['rowclass'].'"': '').'><th><label>'.$field['grouplabel'].'</label></th>';
				$output .= '<td>';
				foreach($field['groupitem'] as $key=>$item){
					$output .= '<input type="hidden" name="'.$item['name'].'" value="" /><input type="checkbox" id="'.$item['name'].'" name="'.$item['name'].'" value="1" '.$item['attr'].' /> <label for="'.$item['name'].'">'.$item['label'].'</label><br />';
				}
				$output .= ' <p class="description">'.$field['description'].'</p></td></tr>';
			}
			if(($field['type'] == 'select') OR $field['type'] == 'dropdown_pages') {
				$output .= '<tr '.($field['rowclass'] ? 'class="'.$field['rowclass'].'"': '').'><th><label>'.$field['label'].'</label></th>';
				$output .= '<td>';
				$output .= '<select style="min-width:200px" name="'.$field['name'].'">';
				foreach( (array)$field['values'] as $val=>$name ) {
					$output .= '<option '.(($val==$value) ? ' selected="selected" ' : '' ).' value="'.$val.'">'.$name.'</option>';
				}
				$output .= '</select>';
				$output .= ' <p class="description">'.$field['description'].'</p></td></tr>';
			}
			if($field['type'] == 'dropdown_roles'){
				$output .= '<tr '.($field['rowclass'] ? 'class="'.$field['rowclass'].'"': '').'><th><label>'.$field['label'].'</label></th>';
				$output .= '<td>';
				$output .= '<select name="'.$field['name'].'">';
				
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
				$output .= ' <p class="description">'.$field['description'].'</p></td></tr>';
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
	
	}
}

if (!function_exists('array_replace_recursive'))
{
    function rrecurse($array, $array1)
    {
      foreach ($array1 as $key => $value)
      {
        // create new key in $array, if it is empty or not an array
        if (!isset($array[$key]) || (isset($array[$key]) && !is_array($array[$key])))
        {
          $array[$key] = array();
        }

        // overwrite the value in the base array
        if (is_array($value))
        {
          $value = rrecurse($array[$key], $value);
        }
        $array[$key] = $value;
      }
      return $array;
    }
  function array_replace_recursive($array, $array1)
  {


    // handle the arguments, merge one by one
    $args = func_get_args();
    $array = $args[0];
    if (!is_array($array))
    {
      return $array;
    }
    for ($i = 1; $i < count($args); $i++)
    {
      if (is_array($args[$i]))
      {
        $array = rrecurse($array, $args[$i]);
      }
    }
    return $array;
  }
}

if( !function_exists('easy_options') ) {
	function easy_options( $option='', $group = '' ) {
		if( $option ) {
			$opt = new EasyOptions;
			return $opt->option( $option, $group );
		}
	}
}