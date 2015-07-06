<?php
	use Neoxygen\NeoClient\ClientBuilder;

	require "vendor/autoload.php";

	$connection = ClientBuilder::create()
		    ->addConnection('default','http','localhost',7474)
		    ->setAutoFormatResponse(true)
		    ->build();


	
?>