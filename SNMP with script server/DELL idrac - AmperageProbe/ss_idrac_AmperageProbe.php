<?php

#error_reporting(0);
if (!isset($called_by_script_server)) {
	include_once(dirname(__FILE__) . '/../include/cli_check.php');
	include_once(dirname(__FILE__) . '/../lib/snmp.php');

	array_shift($_SERVER['argv']);

	print call_user_func_array('ss_idrac_AmperageProbe', $_SERVER['argv']);
} else {
	include_once(dirname(__FILE__) . '/../lib/snmp.php');
}

function ss_idrac_AmperageProbe($hostname = '', $host_id = 0, $snmp_auth = '', $cmd = 'index', $arg1 = '', $arg2 = ''){
	
	$oid = array(
		'index1'		=> '.1.3.6.1.4.1.674.10892.5.4.600.30.1.1',
		'index2' 		=> '.1.3.6.1.4.1.674.10892.5.4.600.30.1.2',
		'status'		=> '.1.3.6.1.4.1.674.10892.5.4.600.30.1.5',
		'reading'		=> '.1.3.6.1.4.1.674.10892.5.4.600.30.1.6',
		'type'			=> '.1.3.6.1.4.1.674.10892.5.4.600.30.1.7',
		'locationName'		=> '.1.3.6.1.4.1.674.10892.5.4.600.30.1.8',

	);

	$mapping_arr = array(
		'status' => array(1 => "other", 2 => "unknown", 3 => "ok", 4 => "nonCriticalUpper", 5 => "criticalUpper", 6 => "nonRecoverableUpper", 7 => "nonCriticalLower", 8 => "criticalLower", 9 => "nonRecoverableLower", 10 => "failed"),
		'type' => array(1 => "amperageProbeTypeIsOther", 2 => "amperageProbeTypeIsUnknown", 3 => "amperageProbeTypeIs1Point5Volt", 4 => "amperageProbeTypeIs3Point3volt", 5 => "amperageProbeTypeIs5Volt", 6 => "amperageProbeTypeIsMinus5Volt", 7 => "amperageProbeTypeIs12Volt", 8 => "amperageProbeTypeIsMinus12Volt", 9 => "amperageProbeTypeIsIO", 10 => "amperageProbeTypeIsCore", 11 => "amperageProbeTypeIsFLEA", 12 => "amperageProbeTypeIsBattery", 13 => "amperageProbeTypeIsTerminator", 14 => "amperageProbeTypeIs2Point5Volt", 15 => "amperageProbeTypeIsGTL", 16 => "amperageProbeTypeIsDiscrete", 23 => "amperageProbeTypeIsPowerSupplyAmps", 24 => "amperageProbeTypeIsPowerSupplyWatts", 25 => "amperageProbeTypeIsSystemAmps", 26 => "amperageProbeTypeIsSystemWatts"),
	);
	$xml_delimiter = "!";

	/* support for SNMP V2 and SNMP V3 parameters */
	$snmp = explode(':', $snmp_auth);
	$snmp_version 	= $snmp[0];
	$snmp_port    	= $snmp[1];
	$snmp_timeout 	= $snmp[2];
	$ping_retries 	= $snmp[3];
	$max_oids	= $snmp[4];

	# for SNMP V3
	$snmp_auth_username   	= '';
	$snmp_auth_password   	= '';
	$snmp_auth_protocol  	= '';
	$snmp_priv_passphrase 	= '';
	$snmp_priv_protocol   	= '';
	$snmp_context         	= '';
	$snmp_community 		= '';
	
	if ($snmp_version == 3) {
		$snmp_auth_username   = $snmp[6];
		$snmp_auth_password   = $snmp[7];
		$snmp_auth_protocol   = $snmp[8];
		$snmp_priv_passphrase = $snmp[9];
		$snmp_priv_protocol   = $snmp[10];
		$snmp_context         = $snmp[11];
	} else {
		$snmp_community = $snmp[5];
	}

	if (($cmd == 'index')){
		# build index array
		$index_arr = Array();
		$arr1 = ss_idrac_AmperageProbe_reindex(cacti_snmp_walk($hostname, $snmp_community, $oid['index1'], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids, SNMP_POLLER));
		$arr2 = ss_idrac_AmperageProbe_reindex(cacti_snmp_walk($hostname, $snmp_community, $oid['index2'], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids, SNMP_POLLER));
		$type_arr = ss_idrac_AmperageProbe_reindex(cacti_snmp_walk($hostname, $snmp_community, $oid['type'], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids, SNMP_POLLER));

		for ($i=0;($i<cacti_sizeof($arr1));$i++){
			if ($type_arr[$i] != 16){
				
				print "." . $arr1[$i] . "." . $arr2[$i] . "\n";
			}
		}
	}
	elseif (($cmd == 'num_indexes')) {
		# build index array
		$index_arr = Array();
		$arr1 = ss_idrac_AmperageProbe_reindex(cacti_snmp_walk($hostname, $snmp_community, $oid['index1'], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids, SNMP_POLLER));
		$arr2 = ss_idrac_AmperageProbe_reindex(cacti_snmp_walk($hostname, $snmp_community, $oid['index2'], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids, SNMP_POLLER));
		$type_arr = ss_idrac_AmperageProbe_reindex(cacti_snmp_walk($hostname, $snmp_community, $oid['type'], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids, SNMP_POLLER));

		for ($i=0;($i<cacti_sizeof($arr1));$i++){
			if ($type_arr[$i] != 16){
				
				$index_arr[$i]="." . $arr1[$i] . "." . $arr2[$i];
			}
		}
		print_r(cacti_sizeof($index_arr));
	}
	elseif ($cmd == 'query') {
		$arg = $arg1;

		# build index array
		$index_arr = Array();
		$arr1 = ss_idrac_AmperageProbe_reindex(cacti_snmp_walk($hostname, $snmp_community, $oid['index1'], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids, SNMP_POLLER));
		$arr2 = ss_idrac_AmperageProbe_reindex(cacti_snmp_walk($hostname, $snmp_community, $oid['index2'], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids, SNMP_POLLER));
		$type_arr = ss_idrac_AmperageProbe_reindex(cacti_snmp_walk($hostname, $snmp_community, $oid['type'], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids, SNMP_POLLER));

		for ($i=0;($i<cacti_sizeof($arr1));$i++){
			if ($type_arr[$i] != 16){
				
				$index_arr[$i]="." . $arr1[$i] . "." . $arr2[$i];
			}
		}
		
		# query
		$arr = ss_idrac_AmperageProbe_reindex(cacti_snmp_walk($hostname, $snmp_community, $oid[$arg], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids, SNMP_POLLER));
		if (array_key_exists($arg, $mapping_arr)){
			foreach ($index_arr as $key => $index){
				print $index . "!" .  $mapping_arr[$arg][$arr[$key]] . "\n";
			}
		}
		else if ($arg=='index'){
			foreach ($index_arr as $key => $index){
				print $index . "!" .  $index . "\n";
			}
		}
		# Special case
		else if ($arg=='reading'){
			$i = 0;
			foreach ($index_arr as $key => $index){
				print $index . "!" .  $arr[$i] . "\n";
				$i+=1;
			}
		}
		else{
			foreach ($index_arr as $key => $index){
				print $index . "!" . $arr[$key] . "\n";
			}
		}
	}
	elseif ($cmd == 'get'){
		$arg = $arg1;
		$index = $arg2;
		

		if (array_key_exists($arg, $mapping_arr)){
            		$data = $mapping_arr[$arg][cacti_snmp_get($hostname, $snmp_community, $oid[$arg]."$index", $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol,$snmp_priv_passphrase,$snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, SNMP_POLLER)];
        	}
		else if ($arg=='index'){
			$data = $index;
		}
        	else{
            		$data = cacti_snmp_get($hostname, $snmp_community, $oid[$arg]."$index", $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol,$snmp_priv_passphrase,$snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, SNMP_POLLER);
        	}
		return $data;	
	}
	else{
		print 'ERROR: Invalid command given' . PHP_EOL;
	}
	
}
function ss_idrac_AmperageProbe_reindex($arr) {
	$return_arr = array();

	for ($i=0;($i<cacti_sizeof($arr));$i++) {
		$return_arr[$i] = $arr[$i]['value'];
	}
	return $return_arr;
}









