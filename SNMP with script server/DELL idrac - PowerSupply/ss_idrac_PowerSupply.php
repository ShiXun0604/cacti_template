<?php

error_reporting(0);
if (!isset($called_by_script_server)) {
	include_once(dirname(__FILE__) . '/../include/cli_check.php');
	include_once(dirname(__FILE__) . '/../lib/snmp.php');

	array_shift($_SERVER['argv']);

	print call_user_func_array('ss_idrac_PowerSupply', $_SERVER['argv']);
} else {
	include_once(dirname(__FILE__) . '/../lib/snmp.php');
}

function ss_idrac_PowerSupply($hostname = '', $host_id = 0, $snmp_auth = '', $cmd = 'index', $arg1 = '', $arg2 = ''){
	
	$oid = array(
		'index1'		=> '.1.3.6.1.4.1.674.10892.5.4.600.12.1.1',
		'index2' 		=> '.1.3.6.1.4.1.674.10892.5.4.600.12.1.2',
		'status'		=> '.1.3.6.1.4.1.674.10892.5.4.600.12.1.5',
		'type'			=> '.1.3.6.1.4.1.674.10892.5.4.600.12.1.7',
		'maximumInputVoltage'	=> '.1.3.6.1.4.1.674.10892.5.4.600.12.1.9',
		'fqdd'			=> '.1.3.6.1.4.1.674.10892.5.4.600.12.1.15',
		'currentInputVoltage' 	=> '.1.3.6.1.4.1.674.10892.5.4.600.12.1.16',

	);

	$mapping_arr = array(
		'status' => array(1 => "other", 2 => "unknown", 3 => "ok", 4 => "nonCritical", 5 => "critical", 6 => "nonRecoverable"),
		'type' => array(1 => "powerSupplyTypeIsOther", 2 => "powerSupplyTypeIsUnknown", 3 => "powerSupplyTypeIsLinear", 4 => "powerSupplyTypeIsSwitching", 5 => "powerSupplyTypeIsBattery", 6 => "powerSupplyTypeIsUPS", 7 => "powerSupplyTypeIsConverter", 8 => "powerSupplyTypeIsRegulator", 9 => "powerSupplyTypeIsAC", 10 => "powerSupplyTypeIsDC", 11 => "powerSupplyTypeIsVRM"),
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
		$arr1 = ss_idrac_PowerSupply_reindex(cacti_snmp_walk($hostname, $snmp_community, $oid['index1'], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids, SNMP_POLLER));
		$arr2 = ss_idrac_PowerSupply_reindex(cacti_snmp_walk($hostname, $snmp_community, $oid['index2'], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids, SNMP_POLLER));
		for ($i=0;($i<cacti_sizeof($arr1));$i++){
			print "." . $arr1[$i] . "." . $arr2[$i] . "\n";
		}
	}
	elseif (($cmd == 'num_indexes')) {
		# build index array
		$index_arr = Array();
		$arr1 = ss_idrac_PowerSupply_reindex(cacti_snmp_walk($hostname, $snmp_community, $oid['index1'], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids, SNMP_POLLER));
		$arr2 = ss_idrac_PowerSupply_reindex(cacti_snmp_walk($hostname, $snmp_community, $oid['index2'], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids, SNMP_POLLER));
		for ($i=0;($i<cacti_sizeof($arr1));$i++){
			$index_arr[$i]="." . $arr1[$i] . "." . $arr2[$i];
		}
		print cacti_sizeof($index_arr);
	}
	elseif ($cmd == 'query') {
		$arg = $arg1;

		# build index array
		$index_arr = Array();
		$arr1 = ss_idrac_PowerSupply_reindex(cacti_snmp_walk($hostname, $snmp_community, $oid['index1'], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids, SNMP_POLLER));
		$arr2 = ss_idrac_PowerSupply_reindex(cacti_snmp_walk($hostname, $snmp_community, $oid['index2'], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids, SNMP_POLLER));
		for ($i=0;($i<cacti_sizeof($arr1));$i++){
			$index_arr[$i]="." . $arr1[$i] . "." . $arr2[$i];
		}
		
		# query
		$arr = ss_idrac_PowerSupply_reindex(cacti_snmp_walk($hostname, $snmp_community, $oid[$arg], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids, SNMP_POLLER));
		if (array_key_exists($arg, $mapping_arr)){
			for ($i=0;($i<cacti_sizeof($arr));$i++){
				print $index_arr[$i] . "!" .  $mapping_arr[$arg][$arr[$i]] . "\n";
			}
		}
		else if ($arg=='index'){
			for ($i=0;($i<cacti_sizeof($index_arr));$i++){
				print $index_arr[$i] . "!" .  $index_arr[$i] . "\n";
			}
		}
		else{
			for ($i=0;($i<cacti_sizeof($arr));$i++){
				print $index_arr[$i] . "!" .  $arr[$i] . "\n";
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
function ss_idrac_PowerSupply_reindex($arr) {
	$return_arr = array();

	for ($i=0;($i<cacti_sizeof($arr));$i++) {
		$return_arr[$i] = $arr[$i]['value'];
	}
	return $return_arr;
}









