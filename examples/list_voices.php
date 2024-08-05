<?php

require '../vendor/autoload.php';

use LucianoTonet\CartesiaPHP\CartesiaClient;
use LucianoTonet\CartesiaPHP\CartesiaClientException;
use Dotenv\Dotenv;

// Load environment variables from the .env file
$dotenv = Dotenv::createImmutable(__DIR__, '../.env');
$dotenv->load();

try {
    // Create a new instance of the CartesiaClient using the API key from the environment variables
    $client = new CartesiaClient($_ENV['CARTESIA_API_KEY']);
    
    // Call the listVoices method to retrieve the available voices
    $response = $client->listVoices();
    
    // Decode the JSON response into an associative array
    $voices = json_decode($response->getBody(), true);

    // Check if the API response contains an error
    if (isset($voices['error'])) {
        echo "Error listing voices: " . $voices['error'];
    } else {
        // Display the list of voices in an HTML format
        echo "<!DOCTYPE html>";
        echo "<html>";
        echo "<head>";
        echo "<title>List of Voices</title>";
        echo "</head>";
        echo "<body>";
        echo "<h1>Available Voices:</h1>";
        echo "<ul>";
        
        // Iterate through each voice and display its details
        foreach ($voices as $voice) {
            echo "<li>";
            echo "<strong>Name:</strong> " . htmlspecialchars($voice['name']) . "<br>";
            echo "<strong>ID:</strong> " . htmlspecialchars($voice['id']) . "<br>";
            echo "<strong>Description:</strong> " . htmlspecialchars($voice['description']) . "<br>";
            echo "<strong>Created At:</strong> " . htmlspecialchars($voice['created_at']) . "<br>";
            echo "<strong>Public:</strong> " . ($voice['is_public'] ? 'Yes' : 'No') . "<br>";
            echo "</li>";
        }
        
        echo "</ul>";
        echo "</body>";
        echo "</html>";
    }
} catch (CartesiaClientException $e) {
    echo "Error listing voices: " . $e->getMessage();
}
