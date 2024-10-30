<?php
/**
 * Color picker for form
 * @param string $name name of the color picker field
 * @param unknown $color - chosen color
 * @param unknown $default - default color
 * @param string $class optional class name for the option field
 * @param string $display - echo or return the html
 * @return string - return sting if $display is false
 */
function cta_button_color_picker($name,$color,$default='#efefef',$class='',$display=true){
	$out = "";
	$cls = $class? " ".$class."": "";
	$color = preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $color )? $color: null;
	$default = preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $default )? $default: "#ff0000";
	if ($color){
		$out .= "<div class='cta-color-picker'>";
		$out .= "<input type='text' class='cta-button-color-pick".$cls."' name='".esc_attr($name)."' value='".esc_attr($color)."' data-default-color='".$default."'>";
		$out .= "</div>";
	} else {
		$out = "<p>ERROR: Color not recognized</p>";
	}
	if ($display) echo $out;
	else return $out;
}

/**
 * Create a dynamic option list based on an option name, an array of elements and a default element
 * @param array $elements elements to display in the drop-down 
 * @param string $name name of the option field
 * @param string $selected default or selected option
 * @param string $class optional class name for the option field
 * @param string $display - echo or return the html
 * @return string - return sting if $display is false
 */
function cta_button_dynamic_options($elements,$name,$selected,$class='',$display=true){
	$out = "";
	$cls = $class? " class='".esc_attr($class)."'": "";
	if (is_array($elements)){
		$out = "<select".$cls." name='".esc_attr($name)."' class='".esc_attr($name)."'>\n";
		$out .= ($selected=="")? "<option selected='selected'>-- Select --</option>\n": "";
		foreach ($elements as $emt){
			$chk=($emt==$selected)? "selected='selected'": "";
			$out .= "<option value='".esc_attr($emt)."' ".$chk.">".esc_html($emt)."</option>\n";
		}
		$out .= "</select>\n";
	} else {
		$out = "<p>ERROR: Elements not defined for list</p>";
	}
	if ($display) echo $out;
	else return $out;
}


/**
 * Fetch the styles defined by the plugin and prepare these for a call from JS
 * - Called by ctabtn-scripts via AJAX
 * @return void
 */

function ctabtn_fetch_styles(){
	$opt = []; $opt1 = []; $opt2 = [];
	$def = array(
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
	$def1 = array(
		'background-color'=>'#f47721',
		'color'=>'#ffffff',
		'font-weight'=>'bold',
		'border-color'=>'#ffffff',
	);
	$def2 = array(
		'shake'=>'ON',
		'buzz'=>'OFF',
		'blink'=>'OFF',
	);
	$styles = [];
//	$clsname = get_option('cta_button_name');
	$opt = get_option('cta_button_options');
	$opt1 = get_option('cta_button_hover_options');
	$opt2 = get_option('cta_button_effects');
	$opt = is_array($opt)? array_merge($def,$opt): $def;
	$opt1 = is_array($opt1)? array_merge($def1,$opt1): $def1;
	$opt2 = is_array($opt2)? array_merge($def2,$opt2): $def2;
	foreach ( $opt as $att => $val ) {
		$val1 = isset($opt1[$att])? $opt1[$att]: $val;
		$styles[$att] = [$val, $val1];
	}
	foreach ( $opt2 as $att => $val ) {
		$styles['zz_'.$att] = ($val=='ON'? 1: 0);
	}
	echo json_encode($styles);
	wp_die(); //NB!! to prevent the whitespace error
}

/**
 * Generate the styles as part of the header for use with the wp_head hook.
 * This approach is not used as it is too inefficient as the code is loaded on every page and not
 * only when the styling is needed on a particular page.
 *
 * @return void
 */
function ctabtn_style_in_header(){
	$keyframes = ["webkit", "moz", "ms", ""];
	$clsname = get_option('cta_button_name');
	$clsname = strlen($clsname)>=5? $clsname: "cta101";
	$opt = get_option('cta_button_options');
	$opt1 = get_option('cta_button_hover_options');
	$color0 = "#000000";
	$color1 = "#000000";
	$css = "<style>\r\n";
	$css .= ".".$clsname." {\r\n";
	foreach ($opt as $att => $value) {
		$nb = stristr($att,'border')? " !important": (stristr($att,'padding')? " !important": "");
		$css .= "\t".$att.": ".$value.$nb.";\r\n";
		$color0 = $att=='color'? $value: $color0;
	}
	foreach ( $keyframes as $keyframe ) {
		$kf = (strlen($keyframe)? ("-".$keyframe."-"): "")."animation";
		$css .= "\t".$kf.": pulsing 7s 5 cubic-bezier(0.1, 0.7, 0.1, 1);\r\n";
	}
	$css .= "}\r\n\r\n";
	$css .= ".".$clsname.":hover {\r\n";
	foreach ($opt1 as $att => $value) {
		$nb = stristr($att,'border')? " !important": "";
		$css .= "\t".$att.": ".$value.$nb.";\r\n";
		$color1 = $att=='color'? $value: $color1;
	}
	foreach ( $keyframes as $keyframe ) {
		$kf = (strlen($keyframe)? ("-".$keyframe."-"): "")."animation";
		$css .= "\t".$kf.": none;\r\n";
	}
	$css .= "}\r\n\r\n";
	$css .= ".".$clsname.">a {padding: 0 !important; background: transparent !important; color: ".$color0." !important;}\r\n\r\n";
	$css .= ".".$clsname.":hover>a {padding: 0 !important; background: transparent !important; color: ".$color1." !important;}\r\n\r\n";
	$css .= "/* Animation */\r\n";
	foreach ( $keyframes as $keyframe ) {
		$kf = "@".(strlen($keyframe)? ("-".$keyframe."-"): "")."keyframes";
		$css .= $kf." pulsing {\r\n";
		$css .= "\tto {\r\n";
		$css .= "\t\tbox-shadow: 0 0 0 50px #fea92c00;\r\n";
		$css .= "\t}\r\n";
		$css .= "}\r\n";
		$css .= "\r\n";
	}
	$css .= "</style>\r\n";
	echo $css;
}
