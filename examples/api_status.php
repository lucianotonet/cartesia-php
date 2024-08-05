
<?php

require '../vendor/autoload.php';

use LucianoTonet\CartesiaPHP\CartesiaClient;
use Dotenv\Dotenv;

// Load environment variables from the .env file
$dotenv = Dotenv::createImmutable(__DIR__, '../.env');
$dotenv->load();

$client = new CartesiaClient();

$response = $client->apiStatus();

echo $response->getBody();
