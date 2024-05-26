<?php
/*
Plugin Name: Net Neutrality FR
Version: 0.0.2
Description: Plugin spécifiquement développé pour illustrer une certaine vision du net aux personnes adéquates.
Author: Lucas Fernandez <contact@kasey.fr>
*/

function ip_in_network($ip, $net_addr, $net_mask){
    if($net_mask <= 0) return false;
    $ip_binary_string  = sprintf('%032b',ip2long($ip));
    $net_binary_string = sprintf('%032b',ip2long($net_addr));
    return (substr_compare($ip_binary_string,$net_binary_string,0,$net_mask) === 0);
}

add_action('init','net_sanitize');

/* *** Main function ******************************************* */

function net_sanitize() {
	$ips;
	/* options */
	$lang 		 = 'fr' ;		/* change the lang and translate the page.XX.html template */
	$IP_banneds  = array (
		'62.160.71.0' 	=> 24, //Assemblée Nationale
		'84.233.174.48' => 28, //Assemblée Nationale
		'80.118.39.160' => 27, //Assemblée Nationale
		//'78.235.120.208'  => 32 /* uncomment and adapt */
	);

	/* get various paths */
	$plugin_root     = ABSPATH.PLUGINDIR.'/'.str_replace(basename( __FILE__),'',plugin_basename(__FILE__));
	$url_plugin_root = PLUGINDIR.'/'.str_replace(basename( __FILE__),'',plugin_basename(__FILE__));
	
	/* check if user use a proxy */
	if($_SERVER['REMOTE_ADDR'] === '127.0.0.1')
		$client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	else
		$client_ip = $_SERVER['REMOTE_ADDR'];

	/* generate the ip list for the template */
	foreach ($IP_banneds as $ip => $mask)
		$ips .= "<li>$ip/$mask</li>\n";

	/* check if client have a suitable ip, if yes load template and stop process */
	foreach ($IP_banneds as $ip => $mask)
		if(ip_in_network($client_ip,$ip,$mask)) {
			header ("HTTP/1.1 451 Unavailable For Legal Reasons",true, 451 );
			echo str_replace(array('{IP_LIST}','{PLUGIN_PATH}'),array($ips,$url_plugin_root),file_get_contents($plugin_root.'page.'.$lang.'.html'));
			die();
		}
}
?>
