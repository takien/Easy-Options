<?php
/**
	* @name   : Easy Options
	* @author : takien
	* @version: 1.6.1
	* @link   : http://takien.com
	* @url    : https://github.com/takien/Easy-Options
	* 
 */
defined('ABSPATH') or die();

if(!class_exists('EasyOptions_1_6_1')) {
	class EasyOptions_1_6_1 {
		var $plugin_name  = '';
		var $plugin_slug  = '';
		var $defaults = Array(
				'group'           => '',
				'menu_name'       => '',
				'page_title'      => '',
				'page_callback'   => false,
				'menu_slug'       => '',
				'fields'          => Array(),
				'menu_location'   => 'add_menu_page',
				'capability'      => 'edit_theme_options',
				'existing_page'   => false,
				'parent_slug'     => '',
				'icon_small'      => '',
				'icon_big'        => '',
				'menu_position'   => 85,
				'add_tab'         => false,
				'image_field'     => false,
				'actions'         => Array(),
			);
		var $admin_menu     = Array();
		var $add_menu       = Array();
		var $fields         = Array();
		var $page = '';
		
		//costruct
		public function __construct($args=array()) {
			
			add_action( 'admin_init',array(&$this,'register_setting') );
			add_action( 'admin_menu',array(&$this,'add_page') );
			
			$page = $this->page;

			add_filter($this->tab_nav(),array(&$this,'tab'),200);
			$this->init();
		}
		

		function init() {
			
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
				$page     = $actions = '';
				extract ( $menu );
				
				$unique = preg_replace('/[^0-9]+/','',md5($menu_slug));
				$menu_position = strpos($menu_position,'.') ? $menu_position.$unique : $menu_position.'.'.$unique ;

				$callback = Array('priority','function');
				
				if( $existing_page ) {
					$menu_callback = false;
				}
				else {
					$menu_callback = array(&$this,'page');
				}
				
				if(in_array($menu_location,$top_menu)){
					$page = call_user_func($menu_location, $page_title, $menu_name, $capability, $menu_slug, $menu_callback, $icon_small, $menu_position);
				}
				else if (in_array($menu_location, $specific_sub_menu)){
					$page = call_user_func($menu_location, $page_title, $menu_name, $capability, $menu_slug, $menu_callback);
				}
				else if(strpos($menu_location,'post_type') === 0){
					$post_type = end(explode('=',$menu_location));
					$page = add_submenu_page( "edit.php?post_type=$post_type", $page_title, $menu_name, $capability, $menu_slug,  $menu_callback );
				}
				else  {
					if($parent_slug) {
						$page = add_submenu_page( $parent_slug, $page_title, $menu_name, $capability, $menu_slug,  $menu_callback );
					}
				}
				if( $image_field ) {
					add_action( "admin_print_scripts-$page", 'wp_enqueue_media');
					add_action( "admin_head-$page", array(&$this, 'scripts_upload_head'));
				}
				foreach ( (array)$actions as $action_name => $callbacks ) {
					foreach( (array)$callbacks as $callback ) {
						if( !empty ($callback['function']) AND is_callable(array(&$this, $callback['function'])) ) {
							add_action( "$action_name-$page", array(&$this, $callback['function']), isset($callback['priority']) ? $callback['priority'] : false );
						}
					}
				}
			}
			
		}
		
		function scripts_upload_head() {
			?>
			<script type="text/javascript">
			/* <![CDATA[ */	
			(function($) {
			var imageframe,
			loading = '<?php echo admin_url('images/loading.gif');?>';
			$( function() {
				$('.easy-options-choose-file').each(function() {
				var $el = $(this);
				$el.click( function( event ) {
					
					
					event.preventDefault();
					imageframe = wp.media.frames.customHeader = wp.media({
						title: $el.data('choose'),
						library: {
							type: 'image'
						},

						button: {
							text: $el.data('update'),
							close: true
						}
					});
					imageframe.on( 'select', function() {
						var attachment = imageframe.state().get('selection').first().toJSON(),
							image_container = $el.data('image-container'),
							image_url_val   = $el.data('image-url-to-value');
							$($el.data('image-container')).attr('src',loading);
							$(image_container).attr('src',attachment.url);
							$(image_url_val).val(attachment.url);
					});
					
					if ( imageframe ) {
						imageframe.open();
						return;
					}
					imageframe.open();
				});
				});
			});
			}(jQuery));
			/* ]]> */
			</script>
				<?php
		}
		
		//page
		function page(){
			global $plugin_page;
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
								//*added  20,11,2013
								if(strpos($nav['slug'],'.php')) {
									echo '<a class="nav-tab '.$class.'" href="'.$nav['slug'].'">'.$nav['name'].'</a>';
								}
								else {
									echo '<a class="nav-tab '.$class.'" href="?page='.$nav['slug'].'">'.$nav['name'].'</a>';
								}
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
					do_action('easy_option_'.$menu_slug.'_before_form');
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
					do_action('easy_option_'.$menu_slug.'_after_form');
				?>
				
			</div>
			<?php
			}
		}
		
		/*
		* Unique tab group name
		*/
		function tab_nav() {
			return 'easy_options_tabs_'.$this->plugin_slug;
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
					if( $this->option($name,$group) ) {
						$attr = ' checked="checked" ';
					}
				}
				
				
				/* check if name cotain array [] */
				$names = explode('[', $name, 2);
				if(isset ($names[1])) {
					$value = $this->option( $names[0],$group,$value );
					
					$name  = $group.'['.$names[0].']['.$names[1];
					
				}
				else {
					$value = $this->option( $name,$group,$value );
					
					$name  = $group.'['.$name.']';
					
				}
				
				if (is_array($value )) {
					$index = intval(end(explode('[',$name)));
					$value = isset($value[$index]) ? $value[$index] : '';
				}
				
				$id = str_replace( Array('[',']'),Array('-','-'),$name );
				//dropdown pages
				if($type == 'dropdown_pages') {
					$values = $this->dropdown_pages();
				}
				
				if($type=='textarea'){
						$output .= '<tr><th><label for="'.$name.'">'.$label.'</label></th>';
						$output .= '<td style="vertical-align:top"><textarea '.$attr.' '.($style ? $style : 'style="width:400px;height:150px"').' id="'.$id.'" name="'.$name.'">'.esc_textarea($value).'</textarea>';
						$output .= ' <p class="description">'.$description.'</p></td></tr>';
				}
				if($type=='text'){
					$output .= '<tr '.($rowclass ? 'class="'.$rowclass.'"': '').'><th><label for="'.$name.'">'.$label.'</label></th>';
					$output .= '<td><input class="regular-text" type="text" id="'.$id.'" name="'.$name.'" value="'.$value.'" '.$attr.'/>';
					$output .= ' <p class="description">'.$description.'</p></td></tr>';
				}
				if($type=='checkbox'){
					$output .= '<tr '.($rowclass ? 'class="'.$rowclass.'"': '').'><th><label for="'.$name.'">'.$label.'</label></th>';
					$output .= '<td><input type="hidden" name="'.$name.'" value="" /><input type="checkbox" id="'.$id.'" name="'.$name.'" value="1" '.$attr.' />';
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
				if( $type=='image' ){
					$output .= '<tr '.($rowclass ? 'class="'.$rowclass.'"': '').'><th><label for="'.$name.'">'.$label.'</label></th>';
					$output .= '<td><img style="display:block;max-width:300px;height:auto" src="'.($value ? $value : 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==').'" id="'.$id.'-image-container" /><input class="regular-text" type="text" id="'.$id.'" placeholder="image URL here" name="'.$name.'" value="'.$value.'" />';
					$output .= ' or <a class="easy-options-choose-file button"
						data-image-container="#'.$id.'-image-container"
						data-image-url-to-value="#'.$id.'"
						data-choose="Choose Image"
						data-update="Set Image">Choose image</a>';
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