<!DOCTYPE html>
<?php

require '../vendor/autoload.php';

use LucianoTonet\CartesiaPHP\CartesiaClient;
use GuzzleHttp\Exception\ClientException;
use Dotenv\Dotenv;
use LucianoTonet\CartesiaPHP\CartesiaClientException;

// Carregando variáveis de ambiente
$dotenv = Dotenv::createImmutable(__DIR__, '../.env');
$dotenv->load();

// Verificando se a requisição é do tipo POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lendo o texto enviado
    $input = json_decode(file_get_contents('php://input'), true);
    $transcript = $input['text'] ?? '';

    // Criando cliente da API
    $client = new CartesiaClient($_ENV['CARTESIA_API_KEY']);

    try {
        // Enviando o texto para gerar áudio
        $response = $client->streamSpeechBytes([
            'context_id' => 'happy-monkeys-fly',
            'model_id' => 'sonic-multilingual',
            'transcript' => $transcript,
            'duration' => 10,
            'voice' => [
                'mode' => 'id',
                'id' => '2b568345-1d48-4047-b25f-7baccf842eb0',
            ],
            // 'output_format' => [
            //     'container' => 'mp3',
            //     'encoding' => 'pcm_f32le',
            //     'sample_rate' => 44100
            // ],
            'language' => 'pt', // Definindo o idioma como português
        ]);

        header('Content-Type: audio/mpeg');
        // Enviando o áudio gerado para o cliente em tempo real
        while (!$response->getBody()->eof()) {
            echo $response->getBody()->read(8192);
            flush();
            usleep(100000); // Pausa para simular transmissão em tempo real
        }
    } catch (CartesiaClientException $e) {
        echo "Erro: " . htmlspecialchars($e->getMessage());
    }
    exit;
}
?>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transmissão de Texto para Áudio</title>
</head>

<body>
    <h1>Transmissão de Texto para Áudio</h1>
    <textarea id="text" placeholder="Digite seu texto aqui..."></textarea>
    <button id="start">Gerar Áudio</button>
    <audio id="audio" controls></audio>
    <script>
        document.getElementById('start').addEventListener('click', async (event) => {
            const text = document.getElementById('text').value;
            const response = await fetch('/examples/stream_speech_ws.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ text })
            });

            if (!response.ok) {
                console.error('Erro ao enviar dados:', response.statusText);
            } else {
                const audio = document.getElementById('audio');
                const reader = response.body.getReader();
                const stream = new ReadableStream({
                    start(controller) {
                        function push() {
                            reader.read().then(({ done, value }) => {
                                if (done) {
                                    controller.close();
                                    return;
                                }
                                controller.enqueue(value);
                                push();
                            });
                        }
                        push();
                    }
                });

                const audioBlob = await new Response(stream).blob();
                audio.src = URL.createObjectURL(audioBlob);
                audio.play();
            }
        });
    </script>
</body>

</html>