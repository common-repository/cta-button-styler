<?php
/**
 * Description: Shortcodes for CTA button Styler
 * @package  ${NAMESPACE}
 */

/**
 * Create a test button on the page
 *  - not intended for use on the site rather attach the "cta101" class to existing elements on the site
 *  - this plugin is only intended to demonstrate the functionality and use
 *
 * @param $title - title on the button
 * @param string $link
 * @param string $tgt
 *
 * @return string
 */
function ctatbn_test_button($atts){
	$atts = shortcode_atts(
		array(
			'title'  => 'Test Button',
			'link'   => '#',
			'target' => '_self'
		),
		$atts
	);
	$out = '';
	$clsname = get_option('cta_button_name');
	$style1 = get_option('cta_button_options');
	$style2 = get_option('cta_button_hover_options');

	$atts['target'] = in_array($atts['target'], ['_blank', '_self','_parent','_top'])? $atts['target']: '_blank';
	$out .= '<div style="margin:30px">';
	if (isset($atts['title']) && strlen($atts['title'])>0) {
		$title = strlen($atts['title'])>16? substr(esc_html($atts['title']),0,14).'...': esc_html($atts['title']);
		$out .= '<span class="cta101">';
		$out .= '<a href="'.esc_url($atts['link']).'" title="'.$title.'" target="'.$atts['target'].'">'
		        .esc_html($atts['title']).'</a>';
		$out .= '</span>';
	} else {
		$out .= 'Nothing to show - button title missing';
	}
	$out .= '</div>';
	$out .= '<div style="padding:30px">';
	$out .= '<a class="cta101" href="#" target="_self">TEST</a>';
	$out .= '</div>';
//	$out .= '<h4>Class: '.$clsname.'</h4><hr />';
//	$out .= '<h4>Style</h4><pre>'.var_export($style1, true).'</pre><hr />';
//	$out .= '<h4>Hover Style</h4><pre>'.var_export($style2, true).'</pre><hr />';
	return $out;
}

add_shortcode('ctatbn_test_button', 'ctatbn_test_button');
