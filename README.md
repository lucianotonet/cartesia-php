# Cartesia PHP

[![Latest Stable Version](https://poser.pugx.org/lucianotonet/cartesia-php/v/stable)](https://packagist.org/packages/lucianotonet/cartesia-php)
[![Total Downloads](https://poser.pugx.org/lucianotonet/cartesia-php/downloads)](https://packagist.org/packages/lucianotonet/cartesia-php)
[![License](https://poser.pugx.org/lucianotonet/cartesia-php/license)](https://packagist.org/packages/lucianotonet/cartesia-php)

## Description

This PHP library provides a robust interface to interact with the Cartesia REST API, allowing you to seamlessly integrate Cartesia's advanced voice cloning and speech synthesis capabilities into your PHP applications. **Please note that this library is not officially affiliated with Cartesia AI.** 

## Key Features

- **High-Quality Speech Synthesis:** Convert text into natural and expressive speech using Cartesia's state-of-the-art models.
- **Custom Voice Cloning:** Create personalized synthetic voices that capture the unique essence and tone of your desired speakers.
- **Simplified Voice Management:** Easily manage your cloned voices, including listing, retrieving, updating, and deleting.
- **Intuitive and User-Friendly Interface:** The library offers a straightforward and well-documented interface, making integration with your PHP projects quick and easy.

## Getting Started

### Installation

The recommended way to install the Cartesia PHP library is via Composer:

```bash
composer require lucianotonet/cartesia-php
```

### Usage

Here's a basic example of how to use the library to generate speech from text:

```php
require 'vendor/autoload.php';

use LucianoTonet\CartesiaPHP\CartesiaClient;

// Initialize the Cartesia client with your API key
$client = new CartesiaClient('your_api_key');

// Define the speech synthesis parameters
$modelId = 'sonic-english'; // Voice model ID
$transcript = 'Hello, world!'; // Text to be converted to speech
$voiceId = 'a0e99841-438c-4a64-b679-ae501e7d6091'; // Cloned voice ID

// Make a speech synthesis request
try {
    $response = $client->streamSpeechBytes([
        'model_id' => $modelId,
        'transcript' => $transcript,
        'voice' => [
            'mode' => 'id',
            'id' => $voiceId
        ],
        'output_format' => [
            'container' => 'raw',
            'encoding' => 'pcm_f32le',
            'sample_rate' => 44100
        ]
    ]);

    // Save the generated audio to a file
    file_put_contents('hello_world.wav', $response->getBody());

    echo "Audio successfully generated at hello_world.wav\n";
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
```

## Complete Documentation

For detailed information on all available features and options, please refer to the complete library documentation: [https://cartesia.ai/docs/php-sdk](https://cartesia.ai/docs/php-sdk).

For the full source code and examples, visit the GitHub repository: [https://github.com/lucianotonet/cartesia-php](https://github.com/lucianotonet/cartesia-php)

## Support

If you encounter any issues or have any questions about the library, please contact our support team at [support@cartesia.ai](mailto:support@cartesia.ai).
