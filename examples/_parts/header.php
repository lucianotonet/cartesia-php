<!DOCTYPE html>
<?php

require '../vendor/autoload.php';

use LucianoTonet\CartesiaPHP\CartesiaClient;
use LucianoTonet\CartesiaPHP\CartesiaClientException;
use Dotenv\Dotenv;

// Load environment variables from the .env file
$dotenv = Dotenv::createImmutable(__DIR__, '../../.env');
$dotenv->load();

$client = new CartesiaClient();
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Cartesia PHP</title>
</head>

<body class="flex flex-row w-full min-h-dvh max-h-screen">