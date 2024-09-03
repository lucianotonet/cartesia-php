<?php
use Dotenv\Dotenv;
use LucianoTonet\CartesiaPHP\CartesiaClient;

require '../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__, '/../.env');
$dotenv->load();

$client = new CartesiaClient();
$mockResponseFile = '_mocks/FILENAME_SAMPLE_RATE_ENCODING.txt'; // Arquivo para salvar a resposta mockada
$useApi = true; // Alterar para true para usar a API real

ini_set('log_errors', 1); // Habilita logs de erro
ini_set('error_log', '../error.log');

if (strpos($_SERVER['HTTP_ACCEPT'], 'text/event-stream') !== false) {

    // Obtendo dados da query string
    $transcript = $_GET['transcript'] ?? 'Hello! My name is Sonic. I am a multilingual voice model. How can I help you today?';
    
    if (!isset($_GET['sample_rate'])) {
        echo "event: done\n";
        echo "data: " . json_encode(["message" => "A taxa de amostragem não foi fornecida. Conexão encerrada."]) . "\n\n";
        flush();
        exit;
    }

    $sampleRate = (int)$_GET['sample_rate'];    
    $encoding = $_GET['encoding'] ?? 'pcm_s16le'; // Agora permite alternar entre pcm_s16le, pcm_f32le, pcm_mulaw e pcm_alaw

    $mockResponseFile = str_replace('SAMPLE_RATE', (string)$sampleRate, $mockResponseFile);
    $mockResponseFile = str_replace('ENCODING', (string)$encoding, $mockResponseFile);
    $mockResponseFile = str_replace('FILENAME', 'stream_speech_sse', $mockResponseFile);
    
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    echo "event: connected\n";
    echo "data: " . json_encode(["message" => "Conexão estabelecida com o servidor.", "using_api" => $useApi && !file_exists($mockResponseFile)]) . "\n\n";
    flush();

    // Inicializa o AudioAssembler aqui, salvando o arquivo no mesmo diretório de $mockResponseFile
    $audioAssembler = new AudioAssembler($sampleRate, $encoding, dirname($mockResponseFile) . '/' . pathinfo($mockResponseFile, PATHINFO_FILENAME) . '.wav');

    // Lógica para alternar entre resposta mockada e real
    if ($useApi == false && file_exists($mockResponseFile)) {
        $mockData = file_get_contents($mockResponseFile);
        $chunks = explode("\n\n", trim($mockData)); // Dividir os chunks pelo separador "\n\n"

        foreach ($chunks as $chunk) {
            if (!empty($chunk)) {
                usleep(25000);
                
                echo $chunk . "\n\n";
                flush();

                $lines = explode("\n\n", trim($chunk)); // Dividir os chunks pelo separador "\n\n"
                foreach ($lines as $line) {
                    if (strpos($line, "data: ") !== false) {
                        $jsonString = substr($line, strpos($line, "data: ") + 6);
                        $jsonString = trim($jsonString);
                        
                        // Encontrar o primeiro '{' e o último '}'
                        $start = strpos($jsonString, '{');
                        $end = strrpos($jsonString, '}');
                        
                        if ($start !== false && $end !== false && $start < $end) {
                            $validJson = substr($jsonString, $start, $end - $start + 1);
                            $chunkData = json_decode($validJson, true);

                            if(isset($chunkData['data']) && $chunkData['type'] === 'chunk') {
                                $audioAssembler->appendChunk($chunkData['data']);
                            }
                        }
                    }
                }

                // Verifica se o chunk contém o tipo "done" para finalizar a conexão
                if (strpos($chunk, 'event: done') !== false) {
                    echo "event: done\n";
                    echo "data: " . json_encode(["message" => "Conexão encerrada."]) . "\n\n";
                    flush();
                    break; // Finaliza o loop
                }
            }
        }
    } else {
        // Lógica para resposta real
        $response = $client->streamSpeechSSE([
            'context_id' => 'happy-monkeys-fly',
            'model_id' => 'sonic-multilingual',
            'transcript' => $transcript,
            'duration' => 120,
            'voice' => [
                'mode' => 'id',
                'id' => 'e3827ec5-697a-4b7c-9704-1a23041bbc51',
                "__experimental_controls" => [
                    "speed" => "normal",
                    "emotion" => [
                        "positivity:highest",
                        "curiosity:low"
                    ]
                ]
            ],
            'output_format' => [
                'container' => 'raw',
                'encoding' => $encoding,
                'sample_rate' => (int)$sampleRate
            ],
            'language' => 'en',
            'add_timestamps' => true // não suportado para a língua pt
        ]);

        ob_start();

        // Criar uma string para salvar os dados recebidos
        $savedData = '';
        $startTime = time(); // Armazena o tempo de início
        while (!$response->getBody()->eof()) {
            $chunk = $response->getBody()->read(8192);
            $savedData .= $chunk; // Concatenar os chunks em uma única string

            // Enviando o chunk como recebido, sem decodificação
            echo $chunk;
            flush(); // Removido ob_flush() para evitar erro de buffer

            // Divide o chunk em linhas
            $lines = explode("\n", $chunk);

            // Itera sobre as linhas
            foreach ($lines as $line) {
                // Verifica se a linha começa com "data: "
                if (strpos($line, "data: ") === 0) {
                    // Extrai os dados JSON da linha
                    $jsonData = substr($line, 6);
                    // Decodifica o JSON
                    $chunkData = json_decode($jsonData, true);

                    // Verifica se o chunk contém dados de áudio
                    if (isset($chunkData['data'])) {
                        $audioAssembler->appendChunk($chunkData['data']);
                    }
                }
            }

            // Verifica se o chunk contém o tipo "done" para finalizar a conexão
            if (strpos($chunk, '"type":"done"') !== false) {
                break; // Finaliza o loop
            }

            // Interrompe o loop se o cliente abortou a conexão (fechou a página)
            if (connection_aborted()) {
                break;
            }

            // Verifica se o tempo de execução excedeu 30 segundos
            if (time() - $startTime > 30) {
                echo "event: timeout\n";
                echo "data: " . json_encode(["message" => "Tempo de execução excedido."]) . "\n\n";
                flush();
                break; // Finaliza o loop se o tempo de execução for excedido
            }
        }

        echo "\n\nevent: close\n";
        echo "data: " . json_encode(["message" => "Conexão encerrada."]) . "\n\n";
        flush();

        // Salvar os dados recebidos em um arquivo local
        file_put_contents($mockResponseFile, $savedData); // Salvar a string completa
    }

    // Salva o arquivo de áudio completo
    $audioAssembler->saveToFile();

} else {
    // Código HTML e JavaScript...
    require_once '_parts/header.php';
    require_once '_parts/nav.php';
    ?>
    <div class="container max-w-2xl flex flex-col p-4 mx-auto">
        <form id="speechForm" class="space-y-4 mb-4">
            <textarea id="textInput" rows="4" cols="50" placeholder="Digite o texto aqui..."
                class="w-full p-2 border border-gray-300 rounded">Hello! My name is Sonic. I am a multilingual voice model. How can I help you today?</textarea>
            <label for="sampleRate">Escolha a taxa de amostragem:</label>
            <select id="sampleRate" name="sampleRate" class="w-full p-2 border border-gray-300 rounded">
                <option value="8000">8000 Hz</option>
                <option value="16000">16000 Hz</option>
                <option value="22050">22050 Hz</option>
                <option value="24000">24000 Hz</option>
                <option value="44100" selected>44100 Hz</option>
                <option value="48000">48000 Hz</option>
            </select>
            <label for="encoding">Escolha o formato de codificação:</label>
            <select id="encoding" name="encoding" class="w-full p-2 border border-gray-300 rounded">
                <option value="pcm_s16le" selected>pcm_s16le</option>
                <option value="pcm_f32le">pcm_f32le</option>
                <option value="pcm_mulaw">pcm_mulaw</option> <!-- Adicionado suporte para pcm_mulaw -->
                <option value="pcm_alaw">pcm_alaw</option> <!-- Adicionado suporte para pcm_alaw -->
            </select>
            <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded hover:bg-blue-600">Gerar Fala</button>
        </form>
        <div class="flex flex-col">
            <div id="display"></div>
            <div id="displayText"></div>
            <canvas id="waveformCanvas" class="border" width="400" height="200"></canvas>
        </div>
    </div>
    <script src="./stream_speech_sse.js"></script>
    <?php
    require_once '_parts/footer.php';
}

class AudioAssembler {
    private $chunks = [];
    private $sampleRate;
    private $encoding;
    private $outputFilename;

    public function __construct(int $sampleRate, string $encoding, string $outputFilename) {
        $this->sampleRate = $sampleRate;
        $this->encoding = $encoding;
        $this->outputFilename = $outputFilename;
        // error_log("AudioAssembler inicializado com taxa de amostragem: $sampleRate, codificação: $encoding, arquivo de saída: $outputFilename");
    }

    public function appendChunk(string $chunk): void {
        $this->chunks[] = $chunk;
        // error_log("Chunk adicionado. Total de chunks: " . count($this->chunks));
    }

    public function saveToFile(): void {
        $combinedAudio = implode('', array_map('base64_decode', $this->chunks));
        // error_log("Salvando arquivo de áudio. Total de dados: " . strlen($combinedAudio) . " bytes");

        // Criando um arquivo WAVE manualmente
        $this->saveWaveFile($combinedAudio);
    }

    private function saveWaveFile(string $audioData): void {
        // error_log($audioData);

        // Sobrescreve o arquivo se já existir
        $fileHandle = fopen($this->outputFilename, 'wb');
        if (!$fileHandle) {
            throw new \RuntimeException('Erro ao abrir arquivo para escrita.');
        }

        // Cabeçalho WAVE
        $chunkID = "RIFF";
        $chunkSize = 36 + strlen($audioData);
        $format = "WAVE";
        $subChunk1ID = "fmt ";
        $subChunk1Size = 16;
        $audioFormat = $this->getAudioFormat(); // Torna o formato de áudio dinâmico
        $numChannels = 1;
        $bitsPerSample = $this->getBitsPerSample($this->encoding); 
        $byteRate = $this->sampleRate * $numChannels * ($bitsPerSample / 8);
        $blockAlign = $numChannels * ($bitsPerSample / 8);

        fwrite($fileHandle, $chunkID);
        fwrite($fileHandle, pack('V', $chunkSize));
        fwrite($fileHandle, $format);
        fwrite($fileHandle, $subChunk1ID);
        fwrite($fileHandle, pack('V', $subChunk1Size));
        fwrite($fileHandle, pack('v', $audioFormat));
        fwrite($fileHandle, pack('v', $numChannels));
        fwrite($fileHandle, pack('V', $this->sampleRate));
        fwrite($fileHandle, pack('V', $byteRate));
        fwrite($fileHandle, pack('v', $blockAlign));
        fwrite($fileHandle, pack('v', $bitsPerSample));
        fwrite($fileHandle, "data");
        fwrite($fileHandle, pack('V', strlen($audioData)));

        // Aplica a codificação antes de escrever os dados de áudio
        $encodedAudioData = $this->encodeAudioData($audioData);
        fwrite($fileHandle, $encodedAudioData);

        fclose($fileHandle);
        // error_log("Arquivo WAVE salvo com sucesso: " . $this->outputFilename);
    }

    private function getAudioFormat(): int {
        switch ($this->encoding) {
            case 'pcm_f32le':
                return 3; // PCM float
            case 'pcm_s16le':
                return 1; // PCM 16 bits
            case 'pcm_mulaw':
                return 7; // PCM mu-law
            case 'pcm_alaw':
                return 6; // PCM A-law
            default:
                throw new \InvalidArgumentException("Codificação de áudio não suportada: $this->encoding");
        }
    }

    private function getBitsPerSample(string $encoding): int {
        switch ($encoding) {
            case 'pcm_f32le':
                return 32;
            case 'pcm_s16le':
                return 16;
            case 'pcm_mulaw':
                return 8; // PCM mu-law
            case 'pcm_alaw':
                return 8; // PCM A-law
            default:
                throw new \InvalidArgumentException("Codificação de áudio não suportada: $encoding");
        }
    }

    private function encodeAudioData(string $audioData): string {
        switch ($this->encoding) {
            case 'pcm_f32le':
                return $this->encodePcmFloat32($audioData);
            case 'pcm_s16le':
                return $this->encodePcmInt16($audioData);
            case 'pcm_mulaw':
                return $this->encodeMuLaw($audioData);
            case 'pcm_alaw':
                return $this->encodeALaw($audioData);
            default:
                throw new \InvalidArgumentException("Codificação de áudio não suportada: $this->encoding");
        }
    }

    // Funções de codificação para cada formato

    private function encodePcmFloat32(string $audioData): string {
        // Lógica para codificar em PCM float 32 bits little-endian
        $samples = unpack('f*', $audioData);
        // Normaliza os samples para evitar distorções
        $normalizedSamples = array_map(function($sample) {
            return max(-1.0, min(1.0, $sample)); // Garante que os valores estejam entre -1.0 e 1.0
        }, $samples);
        return implode('', array_map('pack', array_fill(0, count($normalizedSamples), 'f'), $normalizedSamples));
    }

    private function encodePcmInt16(string $audioData): string {
        // Lógica para codificar em PCM inteiro 16 bits little-endian
        return $audioData; // Placeholder
    }

    private function encodeMuLaw(string $audioData): string {
        // Lógica para codificar em μ-law
        // Implementação simplificada
        return $audioData; // Placeholder
    }

    private function encodeALaw(string $audioData): string {
        $encodedData = '';
        $samples = unpack('f*', $audioData); // Desempacota os dados de áudio em samples de ponto flutuante

        foreach ($samples as $sample) {
            // Normaliza o sample para o intervalo de -1.0 a 1.0
            $sample = max(-1.0, min(1.0, $sample));
            // Converte o sample normalizado para A-law
            if ($sample < 0) {
                $sample = 0x7F; // Define o valor para o máximo negativo
            } else {
                $sample = 0x80; // Define o valor para o máximo positivo
            }
            $encodedData .= pack('C', $sample); // Empacota o sample em um byte
        }

        return $encodedData; // Retorna os dados codificados em A-law
    }
}