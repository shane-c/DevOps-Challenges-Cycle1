<?php

//ran into this bug: https://github.com/rackspace/php-opencloud/issues/249
//fixed with removing "callable" from line 217 in Common/Collection/ResourceIterator.php
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

$service = $client->objectStoreService('cloudFiles');
$containerList = $service->listContainers();

$contain = getInput("Enter container name");

foreach ($containerList as $key=>$container) {
    if ($container->name == $contain) {
	die("The $contain container already exists, exiting.\n");
    }
}

$container = $service->createContainer($contain);
$container->uploadDirectory('files/');
$container->enableCdn();
$cdncontainer = $service->getContainer($contain);
$cdn = $cdncontainer->getCdn();
printf("%s\n", $cdn->getCdnUri());

?>
