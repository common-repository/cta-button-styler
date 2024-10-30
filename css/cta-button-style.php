<?php
/* CTA Button Styler v.0.9.4 [OBSOLETE] */
header('Content-type: text/css');

$keyframes = ["webkit". "moz". "ms". ""];
$clsname = get_option('cta_button_name');
$clsname = strlen($clsname)>=5? $clsname: "cta101";
$opt = get_option('cta_button_options');
$opt1 = get_option('cta_button_hover_options');
$color0 = "#000000";
$color1 = "#000000";

$css = '@CHARSET "ISO-8859-1";'."\r\n";
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

//remove any default styling around links as these will interfere with the button style

echo $css;
