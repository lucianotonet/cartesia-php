<?php

require '../vendor/autoload.php';

use LucianoTonet\CartesiaPHP\CartesiaClient;
use GuzzleHttp\Exception\ClientException;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__, '../.env');
$dotenv->load();

$client = new CartesiaClient();

$transcript = 'Hello, this is a test of audio generation, where we are exploring the ability to transform text into speech efficiently and clearly. This example demonstrates how we can use the API to generate audio from a specific text, allowing voice synthesis technology to be applied in various situations, such as in virtual assistants and accessibility applications.';

try {
    $response = $client->streamSpeechBytes([
        'context_id' => 'happy-monkeys-fly',
        'model_id' => 'sonic-multilingual',
        'transcript' => $transcript,
        'duration' => 123,
        'voice' => [
            'mode' => 'id',
            'id' => '03496517-369a-4db1-8236-3d3ae459ddf7',
            "__experimental_controls" => [
                "speed" => "normal",
                "emotion" => [
                    "positivity:high",
                    "curiosity:high"
                ]
            ]
        ],
        'output_format' => [
            'container' => 'mp3',
            'encoding' => 'pcm_f32le',
            'sample_rate' => 44100
        ],
        'language' => 'en', // pt, ...
    ]);

    header('Content-Type: audio/mpeg');
    header('Cache-Control: no-cache');
    header('Content-Disposition: inline; filename="audio.mp3"');

    // Modification for audio streaming
    while (!$response->getBody()->eof()) {
        echo $response->getBody()->read(8192); // Increased to read more data at once
        flush();
    }
} catch (ClientException $e) {
    echo "Error: " . htmlspecialchars($e->getResponse()->getBody()->getContents()); // Displays the error message from the response
}