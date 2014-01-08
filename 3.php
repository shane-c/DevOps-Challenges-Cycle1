<?php
require 'vendor/autoload.php';

use OpenCloud\Rackspace;

$ini = parse_ini_file(".rackspace_cloud_credentials", TRUE);
$client = new Rackspace(Rackspace::US_IDENTITY_ENDPOINT, array(
    'username' => $ini['username'],
    'apiKey'   => $ini['apikey']
));

function getInput($msg){
  fwrite(STDOUT, "$msg: ");
  $varin = trim(fgets(STDIN));
  return $varin;
}

$dns = $client->dnsService('cloudDNS');
$domainlist = $dns->DomainList();

foreach ($domainlist as $key=>$domain) {
    echo "$key. $domain->name\n";
}

$input = getInput("Select domain to add record to");
$ip = getInput("Enter IP for A record");
$ttl = getInput("Enter TTL for A record");
$hostname = getInput("Enter hostname for A record");

foreach ($domainlist as $key=>$domain) {
    if ($input == $key) {
	$record = $domain->record();
        $response = $record->create(array(
            'type' => 'A', 
            'data' => $ip,
	    'ttl' => $ttl,
	    'name' => $hostname . "." . $domain->name
        ));
     } 
}

?>
