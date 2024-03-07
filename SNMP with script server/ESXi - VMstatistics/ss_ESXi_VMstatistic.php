<?php

error_reporting(0);
if (!isset($called_by_script_server)) {
	include_once(dirname(__FILE__) . '/../include/cli_check.php');
	include_once(dirname(__FILE__) . '/../lib/snmp.php');

	array_shift($_SERVER['argv']);

	print call_user_func_array('ss_ESXi_VMstatistic', $_SERVER['argv']);
} else {
	include_once(dirname(__FILE__) . '/../lib/snmp.php');
}

function ss_ESXi_VMstatistic($hostname = '', $host_id = 0, $snmp_auth = '', $cmd = 'index', $arg1 = '', $arg2 = ''){
	
	$oid = array(
		'index'			=> '.1.3.6.1.4.1.6876.2.1.1.2',
		'vmCount'		=> '.1.3.6.1.4.1.6876.2.1.1.2',
		'vmPwrOnCount'		=> '.1.3.6.1.4.1.6876.2.1.1.6',
		'vmRunningCount'	=> '.1.3.6.1.4.1.6876.2.1.1.8',
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
		print "1\n";
	
	}
	elseif (($cmd == 'num_indexes')) {
		print "1\n";
	}
	elseif ($cmd == 'query') {
		$arg = $arg1;

		# query
		if ($arg=='index'){
			print "1!1\n";
		}
		else if ($arg=='information'){
			print "1!Please select the item you want to monitor in the drop-down menu.\n";
		}
		else{
			$arr = ss_ESXi_VMstatistic_reindex(cacti_snmp_walk($hostname, $snmp_community, $oid[$arg], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids, SNMP_POLLER));
			if ($arg=='vmCount'){
				print "1!" . cacti_sizeof($arr) . "\n";
			}
			else if ($arg=='vmPwrOnCount'){
				$valueCounts = array_count_values(array_values($arr));
				print "1!" . $valueCounts['powered on'] . "\n";
			}
			else if ($arg=='vmRunningCount'){
				$valueCounts = array_count_values(array_values($arr));
				print "1!" . $valueCounts['running'] . "\n";
			}
		}
	}
	elseif ($cmd == 'get'){
		$arg = $arg1;
		$index = $arg2;
		

		if ($arg=='index'){
			print "1";
		}
		else if ($arg=='information'){
			print "Please select the item you want to monitor in the drop-down menu.";
		}
		else{
			$arr = ss_ESXi_VMstatistic_reindex(cacti_snmp_walk($hostname, $snmp_community, $oid[$arg], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids, SNMP_POLLER));
			if ($arg=='vmCount'){
				return cacti_sizeof($arr);
			}
			else if ($arg=='vmPwrOnCount'){
				$valueCounts = array_count_values(array_values($arr));
				return $valueCounts['powered on'];
			}
			else if ($arg=='vmRunningCount'){
				$valueCounts = array_count_values(array_values($arr));
				return $valueCounts['running'];
			}
		}	
	}
	else{
		print 'ERROR: Invalid command given' . PHP_EOL;
	}
	
}
function ss_ESXi_VMstatistic_reindex($arr) {
	$return_arr = array();

	for ($i=0;($i<cacti_sizeof($arr));$i++) {
		$return_arr[$i] = $arr[$i]['value'];
	}
	return $return_arr;
}









