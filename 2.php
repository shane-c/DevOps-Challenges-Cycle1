<?php
require 'vendor/autoload.php';

use OpenCloud\Rackspace;
use OpenCloud\Compute\Constants\Network;
use OpenCloud\Compute\Constants\ServerState;

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

//$numServers = 1;
do {
  $numServers = getInput("Enter how many servers to build 1-3");
}
while (!($numServers <= 3 and $numServers > 0));

$nameServers = getInput("Enter name of servers");
$sshkey = getInput("Enter location of SSH key");
$compute = $client->computeService('cloudServersOpenStack', 'IAD');
$fivetwelveFlavor = $compute->flavor('2');
$ubuntu = $compute->image('80fbcb55-b206-41f9-9bc2-2dd7aac6c061');
$server = $compute->server();

for ($i=1; $i <= $numServers; $i++) {
	$name = $nameServers . $i;
	try {
    		$response = $server->create(array(
        	'name'     => $name,
        	'image'    => $ubuntu,
        	'flavor'   => $fivetwelveFlavor,
		'keypair'  => array(
          	  'name'      => 'testssh.pub',
          	  'publicKey' => file_get_contents($sshkey)
		),
        	'networks' => array(
            		$compute->network(Network::RAX_PUBLIC),
            		$compute->network(Network::RAX_PRIVATE)
        	)
    	));
	} catch (\Guzzle\Http\Exception\BadResponseException $e) {

    		$responseBody = (string) $e->getResponse()->getBody();
    		$statusCode   = $e->getResponse()->getStatusCode();
    		$headers      = $e->getResponse()->getHeaderLines();

    		echo sprintf('Status: %s\nBody: %s\nHeaders: %s', $statusCode, $responseBody, implode(', ', $headers));
	}

	$callback = function($server) {
    	if (!empty($server->error)) {
        	var_dump($server->error);
        	exit;
    	} else {
        	echo sprintf(
            	"Waiting on %s/%-12s %4s%%\n",
            	$server->name(),
            	$server->status(),
            	isset($server->progress) ? $server->progress : 0
        	);
    	}
	};

	$server->waitFor(ServerState::ACTIVE, 600, $callback);

	printf("IP is %s, root password is %s\n",
    	$server->accessIPv4, $server->adminPass);	
}

?>
