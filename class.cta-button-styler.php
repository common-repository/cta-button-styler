<?php
/* CTA Button Styler */

namespace ctabtn;

class cta_button_styler {
	private $cta_name = "cta101";
	private $options = array(
		'margin-left'=>'5px',
		'margin-right'=>'5px',
		'margin-top'=>'5px',
		'margin-bottom'=>'5px',
		'padding-left'=>'15px',
		'padding-right'=>'15px',
		'padding-top'=>'15px',
		'padding-bottom'=>'15px',
		'background-color'=>'#ffffff',
		'color'=>'#f47721',
		'font-weight'=>'bold',
		'border-width'=>'3px',
		'border-style'=>'solid',
		'border-color'=>'#f47721',
		'border-radius'=>'20px',
		'cursor'=>'hand'
	);
	private $hover_options = array(
		'background-color'=>'#f47721',
		'color'=>'#ffffff',
		'font-weight'=>'bold',
		'border-color'=>'#ffffff',
	);
	private $button_effects = array(
		'shake'=>'OFF',
		'buzz'=>'OFF',
		'blink'=>'OFF',
	);
	private $pluginloc = '';
	//var $temp = 'Info...';

	/**
	 * Initialise the plugin class
	 * @param string $loc the full directory and filename for the plugin
	 */
	public function __construct($loc){
		$this->pluginloc = strlen($loc)? $loc: __FILE__;
		$basename = plugin_basename($this->pluginloc);

		if (is_admin()){
			add_action('admin_enqueue_scripts', array($this, 'ctabtn_enqueue_admin_scripts'));
			add_action('admin_init',array($this, 'ctabtn_register_settings'));
			add_action('admin_menu', array($this, 'ctabtn_menu'));
			add_filter('plugin_action_links_'.$basename, array($this, 'ctabtn_settings_link'));
			register_activation_hook($loc, array($this, 'ctabtn_load_options'));
			register_uninstall_hook ($loc, array($this, 'ctabtn_unset_options'));
			//register_deactivation_hook($loc, array($this, 'ctabtn_unset_options'));
		} else {
			add_action('wp_enqueue_scripts', array($this, 'ctabtn_enqueue_scripts'));
		}
		add_action('wp_ajax_ctabtn_fetch_styles', 'ctabtn_fetch_styles');
		add_action('wp_ajax_nopriv_ctabtn_fetch_styles', 'ctabtn_fetch_styles');
		//add_action('wp_head', 'ctabtn_style_in_header');
	}

// -------------------- Add styles and scripts --------------------
	function ctabtn_enqueue_admin_scripts(){
		wp_enqueue_style('cta-button-css', plugins_url('css/cta-button-styler.css', __FILE__));
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script(
			'cta-menu-button-js',
			plugins_url('js/ctabtn-scripts.js', __FILE__),
			['jquery', 'jquery-ui-core', 'wp-color-picker'], false, true
		);
		wp_localize_script( 'cta-menu-button-js', 'ctabtn', array('ajax_url' => admin_url('admin-ajax.php')));
		wp_enqueue_script("jquery-effects-pulsate");
	}

	function ctabtn_enqueue_scripts(){
		wp_enqueue_style('cta-button-css', plugins_url('css/cta-button-styler.css', __FILE__));
		wp_enqueue_script(
			'cta-menu-button-js',
			plugins_url('js/ctabtn-scripts.js', __FILE__),
			['jquery', 'jquery-ui-core'], false, true
		);
		wp_localize_script( 'cta-menu-button-js', 'ctabtn', array('ajax_url' => admin_url('admin-ajax.php')));
		wp_enqueue_script("jquery-effects-pulsate");
	}

// -------------------- Add a menu item to the settings (themes) menu --------------------
	function ctabtn_menu() {
		add_theme_page('Call to Action (CTA) button Styler', 'CTA Button Styler',
			'edit_theme_options', 'cta_button_styler', array($this,'ctabtn_options_page'));
	}

// -------------------- Add a link to the plugin to point to the settings location --------------------

	/**
	 * Add links to Settings page
	 */
	function ctabtn_settings_link($links) {
		$url = get_admin_url().'themes.php?page=cta_button_styler';
		$settings_link = '<a href="'.$url.'">' . __("Settings") . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

// -------------------- Define and create the options update page --------------------

	/**
	 * Register the plugin settings that will be stored to the option table and displayed on the menu page
	 */
	function ctabtn_register_settings(){
		//$this->temp .= 'Settings Registered...';
		register_setting('cta_button_template_group', 'cta_button_name', array($this, 'ctabtn_validate'));
		register_setting('cta_button_template_group', 'cta_button_options', array($this, 'ctabtn_validate'));
		register_setting('cta_button_template_group', 'cta_button_hover_options', array($this, 'ctabtn_validate'));
		register_setting('cta_button_template_group', 'cta_button_effects', array($this, 'ctabtn_validate'));
	}

	/**
	 * Validate and transform the values submitted to the options form
	 * @param array $input - options results from the form submission
	 * @return array|false - validated and transformed options results
	 */
	function ctabtn_validate($input){
		$checkArr = [
			'margin-left'=>['0px', '5px', '10px', '15px', '20px', '40px', '60px', '80px', '110px', '150px'],
			'margin-right'=>['0px', '5px', '10px', '15px', '20px', '40px', '60px', '80px', '110px', '150px'],
			'margin-top'=>['0px', '5px', '10px', '15px', '20px', '40px', '60px', '80px', '110px', '150px'],
			'margin-bottom'=>['0px', '5px', '10px', '15px', '20px', '40px', '60px', '80px', '110px', '150px'],
			'padding-left'=>['2px', '5px', '7px', '10px', '15px', '20px', '30px', '40px', '50px'],
			'padding-right'=>['2px', '5px', '7px', '10px', '15px', '20px', '30px', '40px', '50px'],
			'padding-top'=>['2px', '5px', '7px', '10px', '15px', '20px', '30px', '40px', '50px'],
			'padding-bottom'=>['2px', '5px', '7px', '10px', '15px', '20px', '30px', '40px', '50px'],
			'background-color'=>[],
			'color'=>[],
			'font-weight'=>['normal', 'bold', 'bolder', 'lighter'],
			'border-width'=>['thin', 'medium', 'thick', '1px', '2px', '10px'],
			'border-style'=>['none', 'solid', 'dotted', 'groove', 'ridge', 'inset', 'outset', 'double'],
			'border-color'=>[],
			'border-radius'=>['0px', '5px', '10px', '15px', '20px', '25px', '30px'],
			'cursor'=>['crosshair', 'default', 'hand', 'help', 'pointer'],
			'switch'=>['ON', 'OFF']
		];
		if ( is_array($input) ){
			foreach ($input as $att=>$val) {
				if (in_array($att, ['color','background-color','border-color'])){
					//sanitize color values
					if(preg_match('/^#[a-f0-9]{6}$/i', $val)){
						$output[$att] = $val;
					}
				} elseif ( isset($checkArr[$att]) ){
					if (in_array($val,$checkArr[$att])){
						$output[$att] = sanitize_text_field($val);
					}
				} else {
					if (in_array($val,$checkArr['switch'])){
						$output[$att] = sanitize_text_field($val);
					}
				}
			}
		} else {
			$output = preg_replace("/[^A-Za-z0-9 ]/", '', sanitize_text_field($input));
		}
		return $output;
	}

	/**
	 * Draw the data (styles) manipulation page
	 */
	function ctabtn_options_page() {
		$opt = get_option('cta_button_options');
		$opt1 = get_option('cta_button_hover_options');
		$opt2 = get_option('cta_button_effects');
		$opt = is_array($opt)? array_merge($this->options,$opt): $this->options;
		$opt1 = is_array($opt1)? array_merge($this->hover_options,$opt1): $this->hover_options;
		$opt2 = is_array($opt2)? array_merge($this->button_effects,$opt2): $this->button_effects;
		$name = get_option('cta_button_name');
		$name = strlen($name)>=5? $name: "cta101";

		if(current_user_can('manage_options')) {
			echo "<div class='wrap'>";
				echo "<h2>Call To Action Button Styling</h2>";
				//echo "<h3>Temp</h3><div style='padding: 10px 0'>".$this->temp."</div>";
				echo "<div style='padding: 10px 0'><button class='".$name."'>Sample Call to Action Button</button>";
				echo "<span class='cta_message'><em> <==== Give me a few seconds - watch here</em></span>";
				echo "<p><em>Save changes to the options to see the style changes in the button above (after a few seconds).</em></p></div>";
				settings_errors(); //turn on settings notification

					echo "<form action='options.php' method='post'>";
					settings_fields('cta_button_template_group'); //This line must be inside the form tags
					//do_settings_fields('cta_button_template_group');

					echo "<table class='cta-form-table'>";
					echo "<tr class='cta-input-hdr'><th colspan='4'>GENERAL SETTINGS</th></tr>";
					$txt = "Use the Button Class Label below (i.e. '".$name."') to style any menus or text buttons on ".
					       "your site (case-sensitive). Either wrap the text in a &lt;span&gt...&lt;/span&gt element to ".
					       "create a button around any text or assign the class to a menu element ".
					       "(as described in the help text).";
					echo "<tr><td colspan='4'><em>".$txt."</em></td></tr>";

					echo "<tr class='cta-hdr'><td colspan='4'>Button Class Label</td></tr>";
					echo "<tr><td colspan='4'>".$name."<input type='hidden' name='cta_button_name' size='17' value='".$name."' /></td></tr>";

					echo "<tr class='cta-input-hdr'><th colspan='4'>BUTTON SETTINGS</th></tr>";
					echo "<tr class='cta-hdr'><td colspan='4'>Margin Space</td></tr>";
					$ets = array('0px', '5px', '10px', '15px', '20px', '40px', '60px', '80px', '110px', '150px');
					echo "<tr class='cta-bod'><td>Left</td><td>Right</td><td>Top</td><td>Bottom</td></tr>";
					echo "<tr class='cta-bod'><td>".cta_button_dynamic_options($ets,'cta_button_options[margin-left]',esc_attr($opt['margin-left']),'',false)."</td>";
					echo "<td>".cta_button_dynamic_options($ets,'cta_button_options[margin-right]',esc_attr($opt['margin-right']),'',false)."</td>";
					echo "<td>".cta_button_dynamic_options($ets,'cta_button_options[margin-top]',esc_attr($opt['margin-top']),'',false)."</td>";
					echo "<td>".cta_button_dynamic_options($ets,'cta_button_options[margin-bottom]',esc_attr($opt['margin-bottom']),'',false)."</td></tr>";
					//echo "<tr class='cta-bod'><td>".."</td>";

					echo "<tr class='cta-hdr'><td colspan='4'>Text Padding</td></tr>";
					$ets = array('2px', '5px', '7px', '10px', '15px', '20px', '30px', '40px', '50px');
					echo "<tr class='cta-bod'><td>Left</td><td>Right</td><td>Top</td><td>Bottom</td></tr>";
					echo "<tr class='cta-bod'><td>".cta_button_dynamic_options($ets,'cta_button_options[padding-left]',esc_attr($opt['padding-left']),'',false)."</td>";
					echo "<td>".cta_button_dynamic_options($ets,'cta_button_options[padding-right]',esc_attr($opt['padding-right']),'',false)."</td>";
					echo "<td>".cta_button_dynamic_options($ets,'cta_button_options[padding-top]',esc_attr($opt['padding-top']),'',false)."</td>";
					echo "<td>".cta_button_dynamic_options($ets,'cta_button_options[padding-bottom]',esc_attr($opt['padding-bottom']),'',false)."</td></tr>";

					echo "<tr class='cta-hdr'><td colspan='4'>Button Colors</td></tr>";
					echo "<tr class='cta-bod'><td>Background</td><td colspan='3'>Label Text</td></tr>";
					$val = esc_attr($opt['background-color']);
					echo "<tr class='cta-bod'><td>".cta_button_color_picker('cta_button_options[background-color]',$val,$val,'',false)."</td>";
					$val = esc_attr($opt['color']);
					echo "<td colspan='2'>".cta_button_color_picker('cta_button_options[color]',$val,$val,'',false)."</td></tr>";

					echo "<tr class='cta-hdr'><td colspan='4'>Button Border</td></tr>";
					echo "<tr class='cta-bod'><td>Color</td><td>Width</td><td>Style</td><td>Radius</td></tr>";
					$val = esc_attr($opt['border-color']);
					echo "<tr class='cta-bod'><td>".cta_button_color_picker('cta_button_options[border-color]',$val,$val,'',false)."</td>";
					$ets = array('thin', 'medium', 'thick', '1px', '2px', '10px');
					echo "<td>".cta_button_dynamic_options($ets,'cta_button_options[border-width]',esc_attr($opt['border-width']),'',false)."</td>";
					$ets = array('none', 'solid', 'dotted', 'groove', 'ridge', 'inset', 'outset', 'double');
					echo "<td>".cta_button_dynamic_options($ets,'cta_button_options[border-style]',esc_attr($opt['border-style']),'',false)."</td>";
					$ets = array('0px', '5px', '10px', '15px', '20px', '25px', '30px');
					echo "<td>".cta_button_dynamic_options($ets,'cta_button_options[border-radius]',esc_attr($opt['border-radius']),'',false)."</td></tr>";

					echo "<tr class='cta-hdr'><td colspan='4'>Other</td></tr>";
					echo "<tr class='cta-bod'><td>Font Weight</td><td colspan='3'>Cursor</td></tr>";
					$ets = array('normal', 'bold', 'bolder', 'lighter');
					echo "<tr class='cta-bod'><td>".cta_button_dynamic_options($ets,'cta_button_options[font-weight]',esc_attr($opt['font-weight']),'',false)."</td>";
					$ets = array('crosshair', 'default', 'hand', 'help', 'pointer');
					echo "<td colspan='3'>".cta_button_dynamic_options($ets,'cta_button_options[cursor]',esc_attr($opt['cursor']),'',false)."</td></tr>";

					//-----------------------------------------------------------------
					echo "<tr class='cta-input-hdr'><th colspan='4'>BUTTON HOVER STYLES</th></tr>";
					$txt = "These are the styles that will display when the mouse is hovered over the button.";
					echo "<tr><td colspan='4'><em>".$txt."</em></td></tr>";

					echo "<tr class='cta-hdr'><td colspan='4'>Button Styles</td></tr>";
					echo "<tr class='cta-bod'><td>Color</td><td>Label Color</td><td>Font Weight</td><td>Border Color</td></tr>";
					$val = esc_attr($opt1['background-color']);
					echo "<tr class='cta-bod'><td>".cta_button_color_picker('cta_button_hover_options[background-color]',$val,$val,'',false)."</td>";
					$val = esc_attr($opt1['color']);
					echo "<td>".cta_button_color_picker('cta_button_hover_options[color]',$val,$val,'',false)."</td>";
					$ets = array('normal', 'bold', 'bolder', 'lighter');
					echo "<td>".cta_button_dynamic_options($ets,'cta_button_hover_options[font-weight]',esc_attr($opt1['font-weight']),'',false)."</td>";
					$val = esc_attr($opt1['border-color']);
					echo "<td>".cta_button_color_picker('cta_button_hover_options[border-color]',$val,$val,'',false)."</td></tr>";

					//-----------------------------------------------------------------
					echo "<tr class='cta-input-hdr'><th colspan='4'>BUTTON EFFECTS</th></tr>";
					$txt = "There are 3 different animation effects that can be switched on or off. These are meant to ".
					       "highlight the Call To Action (CTA) button. If more than one effect is switched on, the ".
					       "effects will run sequentially in the order listed. Usually only one effect is switched on.";
					echo "<tr><td colspan='4'><em>".$txt."</em></td></tr>";

					echo "<tr class='cta-hdr'><td colspan='4'>Effects</td></tr>";
					$ets = array('ON', 'OFF');
					$choice = cta_button_dynamic_options($ets,'cta_button_effects[shake]',esc_attr($opt2['shake']),'',false);
					echo "<tr class='cta-bod'><td>Shake</td><td colspan='3'>".$choice."</td></tr>";
					$choice = cta_button_dynamic_options($ets,'cta_button_effects[buzz]',esc_attr($opt2['buzz']),'',false);
					echo "<tr class='cta-bod'><td>Buzz</td><td colspan='3'>".$choice."</td></tr>";
					$choice = cta_button_dynamic_options($ets,'cta_button_effects[blink]',esc_attr($opt2['blink']),'',false);
					echo "<tr class='cta-bod'><td>Blink</td><td colspan='3'>".$choice."</td></tr>";

					echo "</table>";
					submit_button();
				echo "</form>";
			echo "</div>";

		} else {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
	}

// -------------------- Other functions --------------------

// -------------------- Define actions to be taken when installing and uninstalling the Plugin --------------------
	function ctabtn_load_options() {
		//$value = serialize($options);
		add_option('cta_button_name', $this->cta_name);
		add_option('cta_button_options', $this->options);
		add_option('cta_button_hover_options', $this->hover_options);
		add_option('cta_button_effects', $this->button_effects);
	}

	function ctabtn_unset_options() {
		delete_option('cta_button_name');
		delete_option('cta_button_options');
		delete_option('cta_button_hover_options');
		delete_option('cta_button_effects');
	}

}
