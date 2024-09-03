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
        'context_id' => 'happy-monkeys-fly',
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

## Roadmap

- [x] API Status and Version
- [x] Create Voice
- [x] Delete Voice
- [x] Update Voice
- [x] Get Voice
- [x] List Voices
- [x] Clone Voice (Clip)
- [x] Mix Voices
- [x] Stream Speech (Bytes)
- [x] Stream Speech (Server-Sent Events)
- [ ] Stream Speech (WebSocket)
- [ ] Localize Voice

## Running the examples

To run the examples provided in the `examples` directory, copy the `.env.example` file to `.env` and update the `CARTESIA_API_KEY` constant with your Cartesia API key:

```bash
# .env
CARTESIA_API_KEY=your_api_key
```

Then, start the PHP server in the package root directory:

```bash
php -S 127.0.0.1:80
```

Now you can access the examples in your browser at `http://127.0.0.1/examples/`.


## Complete Documentation

For detailed information on all available features and options, please refer to the original REST API documentation: [https://docs.cartesia.ai](https://docs.cartesia.ai/api-reference/endpoints/).

For the full source code and examples, visit the GitHub repository: [https://github.com/lucianotonet/cartesia-php](https://github.com/lucianotonet/cartesia-php)

## Support

If you encounter any issues or have any questions about the library, please feel free to open an issue on GitHub. Contributions to the project are also welcome!
