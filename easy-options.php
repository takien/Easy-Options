<?php
/**
	* @name   : Easy Options
	* @author : takien
	* @version: 1.0
	* @link   : http://takien.com
	* 
 */
defined('ABSPATH') or die();

if(!class_exists('EasyOptions')) {
	class EasyOptions {

	var $option_group;
	var $option_menu_name;
	var $option_menu_slug;
	var $option_fields          = Array();
	var $option_default         = Array();
	var $option_menu_location   = 'add_menu_page';
	var $option_capability 	    = 'edit_theme_options';
	var $option_parent_slug;
	var $option_icon_small;
	var $option_icon_big;
	var $option_menu_position;
	var $option_add_tab = false;

	public function __construct() {
		add_action('admin_init',array(&$this,'option_register_setting'));
		add_action('admin_menu',array(&$this,'option_add_page'));
		add_filter('takien_plugin_options_tabs',array(&$this,'option_tab'));
	}
	
	/* begin option stuff*/
	
	/*register setting*/
	function option_register_setting() {
		register_setting($this->option_menu_slug.'_option_field', $this->option_group);
	}
	
	/*option*/
	function option($key='',$group=''){
		$group = $group ? $group : $this->option_group;
		$option = get_option($group) ? get_option($group) : Array();
		//$option = array_merge($this->option_default,$option);
		$option = array_replace_recursive($option,$this->option_default,$option);

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
	function option_add_page(){
		
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
		
		if(in_array($this->option_menu_location,$top_menu)){
			call_user_func($this->option_menu_location, $this->option_menu_name, $this->option_menu_name, $this->option_capability, $this->option_menu_slug, array(&$this,'option_page'), $this->option_icon_small, $this->option_menu_position);
		}
		else if (in_array($this->option_menu_location,$specific_sub_menu)){
			call_user_func($this->option_menu_location,$this->option_menu_name, $this->option_menu_name, $this->option_capability, $this->option_menu_slug, array(&$this,'option_page'));
		}
		else if(strpos($this->option_menu_location,'post_type') === 0){
			$post_type = end(explode('=',$this->option_menu_location));
			add_submenu_page( "edit.php?post_type=$post_type", $this->option_menu_name, $this->option_menu_name, $this->option_capability, $this->option_menu_slug,  array(&$this,'option_page') );
		}
		else  {
			if($this->parent_slug) {
				add_submenu_page( $this->parent_slug, $this->option_menu_name, $this->option_menu_name, $this->option_capability, $this->option_menu_slug,  array(&$this,'option_page') );
			}
		}
		
	}
	function option_page(){ ?>
	<div class="wrap">
		<?php 
		$icon = $this->option_icon_big;?>
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
			<h2><?php echo $this->option_menu_name;?></h2>
		<?php } ?>
		<?php
			echo apply_filters('takien_plugin_option_'.$this->option_menu_slug.'_before_form','');
		?>
		<form method="post" action="options.php">
			<?php 
				wp_nonce_field('update-options'); 
				settings_fields($this->option_menu_slug.'_option_field');?>
			<?php
				if(!empty($this->option_fields)){
					echo $this->option_form($this->option_fields);
				}
				
			?>
			<input type="hidden" name="action" value="update" />
			<input type="hidden" name="<?php echo $this->option_menu_slug.'_option_field';?>" value="<?php echo $this->option_group;?>" />
			<p><input type="submit" class="button-primary" value="Save" /> </p>
		</form>
		<?php 
			echo apply_filters('takien_plugin_option_'.$this->option_menu_slug.'_after_form','');
		?>
		<?php /*<p>
		To retrieve value in your theme, use <strong>&lt;?php echo theme_option('FIELD_NAME','<?php echo $this->option_group;?>');?&gt;</strong>, example: &lt;?php echo theme_option('facebook_page','<?php echo $this->option_group;?>');?&gt;</p>*/?>
		
	</div>
	<?php
	}
	/*tab*/
	function option_tab($tab){
		if($this->option_add_tab) {
			$tab[] = array(
			'slug'=>$this->option_menu_slug,
			'name'=>$this->option_menu_name
			);
		}
		return $tab;
	}
	
	/**
	 * Render form
	 * @param array 
	 */	
	private function option_form($fields){
		$output ='<table class="form-table">';
		foreach($fields as $field){
			$field['rowclass'] = isset($field['rowclass']) ? $field['rowclass'] : false;
			$field['name'] = $this->option_group.'['.$field['name'].']';
			
			if($field['type']=='text'){
				$output .= '<tr '.($field['rowclass'] ? 'class="'.$field['rowclass'].'"': '').'><th><label for="'.$field['name'].'">'.$field['label'].'</label></th>';
				$output .= '<td><input class="regular-text" type="text" id="'.$field['name'].'" name="'.$field['name'].'" value="'.$field['value'].'" />';
				$output .= ' <p class="description">'.$field['description'].'</p></td></tr>';
			}
			if($field['type']=='checkbox'){
				$output .= '<tr '.($field['rowclass'] ? 'class="'.$field['rowclass'].'"': '').'><th><label for="'.$field['name'].'">'.$field['label'].'</label></th>';
				$output .= '<td><input type="hidden" name="'.$field['name'].'" value="" /><input type="checkbox" id="'.$field['name'].'" name="'.$field['name'].'" value="'.$field['value'].'" '.$field['attr'].' />';
				$output .= ' <p class="description">'.$field['description'].'</p></td></tr>';
			}
			if($field['type']=='checkboxgroup'){
				$output .= '<tr '.($field['rowclass'] ? 'class="'.$field['rowclass'].'"': '').'><th><label>'.$field['grouplabel'].'</label></th>';
				$output .= '<td>';
				foreach($field['groupitem'] as $key=>$item){
					$output .= '<input type="hidden" name="'.$item['name'].'" value="" /><input type="checkbox" id="'.$item['name'].'" name="'.$item['name'].'" value="'.$item['value'].'" '.$item['attr'].' /> <label for="'.$item['name'].'">'.$item['label'].'</label><br />';
				}
				$output .= ' <p class="description">'.$field['description'].'</p></td></tr>';
			}
			if($field['type'] == 'select'){
				$output .= '<tr '.($field['rowclass'] ? 'class="'.$field['rowclass'].'"': '').'><th><label>'.$field['label'].'</label></th>';
				$output .= '<td>';
				$output .= '<select style="min-width:200px" name="'.$field['name'].'">';
				foreach( (array)$field['values'] as $val=>$name ) {
					$output .= '<option '.(($val==$field['value']) ? 'selected="selected"' : '' ).' value="'.$val.'">'.$name.'</option>';
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
					if ( $field['value'] == $role ) // preselect specified role
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