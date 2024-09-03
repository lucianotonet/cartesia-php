<?php
use LucianoTonet\CartesiaPHP\CartesiaClient;
use LucianoTonet\CartesiaPHP\CartesiaClientException;
use Dotenv\Dotenv;

?>
<?php if ($_SERVER['REQUEST_METHOD'] !== 'POST') { ?>
    <?php require_once '_parts/header.php'; ?>
    <?php require_once '_parts/nav.php'; ?>

    <div class="flex flex-col flex-1 bg-green-400/5 min-h-full p-10">

        <form id="speechForm" aria-label="Formulário de Geração de Fala"
            class="max-w-lg mx-auto p-4 bg-white rounded-lg shadow-md flex flex-col my-auto">
            <textarea id="textInput" rows="4" cols="50" placeholder="Digite o texto aqui..." required
                aria-label="Texto para fala" class="w-full p-2 border border-gray-300 rounded-md"></textarea>

            <div>
                <label for="speedControl" class="flex items-center">
                    Velocidade: <div id="speedLabel" class="text-right ml-auto">Normal</div>
                </label>
                <input type="range" id="speedControl" min="0" max="4" value="2" step="1" aria-label="Controle de Velocidade"
                    class="w-full">
                <div class="flex justify-between">
                    <span>Muito devagar</span>
                    <span>Devagar</span>
                    <span>Normal</span>
                    <span>Rápido</span>
                    <span>Muito rápido</span>
                </div>
            </div>

            <div>
                <input type="checkbox" id="positivityToggle" aria-label="Habilitar Controle de Positividade"
                    class="float-left mt-1.5 mr-2">
                <label for="positivityControl" class="flex items-center">
                    Positividade: <div id="positivityLabel" class="text-right ml-auto"></div>
                </label>
                <input type="range" id="positivityControl" min="0" max="4" value="2" step="1"
                    aria-label="Controle de Positividade" class="w-full" disabled>
            </div>

            <div>
                <input type="checkbox" id="curiosityToggle" aria-label="Habilitar Controle de Curiosidade"
                    class="float-left mt-1.5 mr-2">
                <label for="curiosityControl" class="flex items-center">
                    Curiosidade: <div id="curiosityLabel" class="text-right ml-auto"></div>
                </label>
                <input type="range" id="curiosityControl" min="0" max="4" value="2" step="1"
                    aria-label="Controle de Curiosidade" class="w-full" disabled>
            </div>

            <div>
                <input type="checkbox" id="angerToggle" aria-label="Habilitar Controle de Raiva"
                    class="float-left mt-1.5 mr-2">
                <label for="angerControl" class="flex items-center">
                    Raiva: <div id="angerLabel" class="text-right ml-auto"></div>
                </label>
                <input type="range" id="angerControl" min="0" max="4" value="2" step="1" aria-label="Controle de Raiva"
                    class="w-full" disabled>
            </div>

            <div>
                <input type="checkbox" id="surpriseToggle" aria-label="Habilitar Controle de Surpresa"
                    class="float-left mt-1.5 mr-2">
                <label for="surpriseControl" class="flex items-center">
                    Surpresa: <div id="surpriseLabel" class="text-right ml-auto"></div>
                </label>
                <input type="range" id="surpriseControl" min="0" max="4" value="2" step="1"
                    aria-label="Controle de Surpresa" class="w-full" disabled>
            </div>

            <div>
                <input type="checkbox" id="sadnessToggle" aria-label="Habilitar Controle de Tristeza"
                    class="float-left mt-1.5 mr-2">
                <label for="sadnessControl" class="flex items-center">
                    Tristeza: <div id="sadnessLabel" class="text-right ml-auto"></div>
                </label>
                <input type="range" id="sadnessControl" min="0" max="4" value="2" step="1" aria-label="Controle de Tristeza"
                    class="w-full" disabled>
            </div>

            <button type="submit"
                class="mt-6 w-full bg-blue-500 text-white font-semibold py-2 rounded-md hover:bg-blue-600">Gerar
                Fala</button>
        </form>

        <audio id="audioPlayer" controls aria-label="Reprodutor de Áudio" class="mt-auto w-full max-w-xl mx-auto"></audio>
    </div>

    <script>
        const updateLabel = (range, label, options) => {
            label.textContent = options[range.value]; // Atualiza o label com a opção selecionada
        };

        const speedControl = document.getElementById('speedControl');
        speedControl.addEventListener('input', function () {
            updateLabel(this, document.getElementById('speedLabel'), ["slowest", "slow", "normal", "fast", "fastest"]);
        });

        const emotionOptions = {
            positivity: ["lowest", "low", "", "high", "highest"],
            curiosity: ["lowest", "low", "", "high", "highest"],
            anger: ["lowest", "low", "", "high", "highest"],
            surprise: ["lowest", "low", "", "high", "highest"],
            sadness: ["lowest", "low", "", "high", "highest"]
        };

        const emotionControls = [
            { control: 'positivityControl', label: 'positivityLabel', toggle: 'positivityToggle', options: emotionOptions.positivity },
            { control: 'curiosityControl', label: 'curiosityLabel', toggle: 'curiosityToggle', options: emotionOptions.curiosity },
            { control: 'angerControl', label: 'angerLabel', toggle: 'angerToggle', options: emotionOptions.anger },
            { control: 'surpriseControl', label: 'surpriseLabel', toggle: 'surpriseToggle', options: emotionOptions.surprise },
            { control: 'sadnessControl', label: 'sadnessLabel', toggle: 'sadnessToggle', options: emotionOptions.sadness }
        ];

        emotionControls.forEach(({ control, label, toggle, options }) => {
            document.getElementById(control).addEventListener('input', function () {
                updateLabel(this, document.getElementById(label), options);
            });

            document.getElementById(toggle).addEventListener('change', function () {
                const controlElement = document.getElementById(control);
                controlElement.disabled = !this.checked;
                if (!this.checked) {
                    controlElement.value = 0; // Reseta para neutro
                    updateLabel(controlElement, document.getElementById(label), options);
                } else {
                    updateLabel(controlElement, document.getElementById(label), options); // Atualiza o label ao habilitar
                }
            });
        });

        let audioContext = null;

        class Player {
            #startNextPlaybackAt = 0;
            #bufferDuration;

            constructor({ bufferDuration }) {
                this.#bufferDuration = bufferDuration;
            }

            async #playBuffer(buf, sampleRate) {
                if (!audioContext) {
                    throw new Error("AudioContext não inicializado.");
                }
                if (buf.length === 0) {
                    return;
                }

                const startAt = this.#startNextPlaybackAt;
                const duration = buf.length / sampleRate;
                this.#startNextPlaybackAt =
                    duration + Math.max(audioContext.currentTime, this.#startNextPlaybackAt);

                await this.playAudioBuffer(buf, startAt, sampleRate);
            }

            async playAudioBuffer(buf, startAt, sampleRate) {
                const audioBuffer = audioContext.createBuffer(1, buf.length, sampleRate);
                audioBuffer.copyToChannel(buf, 0);
                const source = audioContext.createBufferSource();
                source.buffer = audioBuffer;
                source.connect(audioContext.destination);
                source.start(startAt);
            }

            async play(source) {
                this.#startNextPlaybackAt = 0;
                const buffer = new Float32Array(source.durationToSampleCount(this.#bufferDuration));

                const plays = [];
                while (true) {
                    const read = await source.read(buffer);
                    const playableAudio = buffer.subarray(0, read);
                    const adjustedLength = Math.floor(playableAudio.length / 4) * 4; // Ajusta o tamanho do buffer para ser múltiplo de 4
                    const adjustedAudio = new Float32Array(adjustedLength);
                    adjustedAudio.set(playableAudio.subarray(0, adjustedLength));
                    plays.push(this.#playBuffer(adjustedAudio, source.sampleRate));

                    if (read < buffer.length) {
                        break;
                    }
                }
                await Promise.all(plays);
            }

            async pause() {
                if (!audioContext) {
                    throw new Error("AudioContext não inicializado.");
                }
                await audioContext.suspend();
            }

            async resume() {
                if (!audioContext) {
                    throw new Error("AudioContext não inicializado.");
                }
                await audioContext.resume();
            }

            async toggle() {
                if (!audioContext) {
                    throw new Error("AudioContext não inicializado.");
                }
                if (audioContext.state === "running") {
                    await this.pause();
                } else {
                    await this.resume();
                }
            }

            async stop() {
                if (!audioContext) {
                    throw new Error("AudioContext não inicializado.");
                }
                await audioContext.close();
            }

            async switchAudioFormat(format) {
                if (format === 'mp3') {
                    audioContext.close();
                    audioContext = new AudioContext(); // Reinicializa o AudioContext
                } else if (format === 'raw') {
                    audioContext.close();
                    audioContext = new AudioContext(); // Reinicializa o AudioContext
                }
                // Adicione mais formatos conforme necessário
            }
        }

        const form = document.getElementById('speechForm');
        const player = new Player({ bufferDuration: 2 });

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const text = document.getElementById('textInput').value.trim();
            if (!text) {
                alert("Por favor, insira um texto para gerar a fala. Não deixe o campo vazio!");
                return;
            }
            const speedOptions = ["slowest", "slow", "normal", "fast", "fastest"];
            const speed = speedOptions[document.getElementById('speedControl').value];

            const selectedEmotions = [];

            emotionControls.forEach(({ control, toggle, label }) => {
                const controlValue = document.getElementById(control).value;
                const isEnabled = document.getElementById(toggle).checked;
                if (isEnabled) {
                    const emotionName = label.replace('Label', ''); // Obtém o nome da emoção a partir do label
                    if (controlValue > 0) {
                        if (['lowest', 'low', 'high', 'highest'][controlValue] == undefined) {
                            selectedEmotions.push(`${emotionName}`);
                        } else {
                            selectedEmotions.push(`${emotionName}:${['lowest', 'low', 'high', 'highest'][controlValue]}`);
                        }
                    } else {
                        selectedEmotions.push(emotionName); // (omit level for moderate addition of the emotion)
                    }
                }
            });

            const response = await fetch('stream_speech_bytes.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    transcript: text,
                    voice: {
                        "__experimental_controls": {
                            "speed": speed,
                            "emotion": selectedEmotions
                        }
                    }
                })
            });

            if (!response.ok) {
                console.error('Erro na resposta da rede');
                alert('Ocorreu um erro ao gerar a fala. Tente novamente.');
                return;
            }

            const audioData = await response.arrayBuffer();
            const audioPlayer = document.getElementById('audioPlayer');
            audioPlayer.src = URL.createObjectURL(new Blob([audioData], { type: 'audio/mpeg' }));
            audioPlayer.play();

            const reader = new FileReader();
            reader.onload = async (event) => {
                const audioBuffer = new Float32Array(event.target.result);
                if (!audioContext) {
                    audioContext = new AudioContext();
                }
                await player.play({
                    read: async (buffer) => {
                        buffer.set(audioBuffer);
                        return audioBuffer.length;
                    }, sampleRate: 44100, durationToSampleCount: (duration) => duration * 44100
                });
            };
            reader.readAsArrayBuffer(new Blob([audioData])); // Passa o Blob para readAsArrayBuffer
        });
    </script>

    <?php require_once '_parts/footer.php'; ?>
<?php } else {
    require '../vendor/autoload.php'; // Mover para fora do bloco condicional

    // Carregando variáveis de ambiente do arquivo .env
    $dotenv = Dotenv::createImmutable(__DIR__, '/../.env');
    $dotenv->load();

    $client = new CartesiaClient();


    $transcript = json_decode(file_get_contents('php://input'))->transcript;

    // Obtendo os controles de velocidade e emoção do corpo da requisição
    $speed = json_decode(file_get_contents('php://input'))->voice->__experimental_controls->speed;
    $emotion = json_decode(file_get_contents('php://input'))->voice->__experimental_controls->emotion;

    $response = $client->streamSpeechBytes([
        'context_id' => 'happy-monkeys-fly',
        'model_id' => 'sonic-multilingual',
        'transcript' => $transcript,
        'duration' => 180,
        'voice' => [
            'mode' => 'id',
            'id' => '700d1ee3-a641-4018-ba6e-899dcadc9e2b',
            "__experimental_controls" => [
                "speed" => $speed,
                "emotion" => $emotion
            ]
        ],
        'output_format' => [
            'container' => 'mp3',
            'encoding' => 'mp3',
            "sample_rate" => 44100
        ],
        'language' => 'pt',
    ]);

    header('Content-Type: audio/mpeg');
    header('Cache-Control: no-cache');
    header('Content-Disposition: inline; filename="audio.mp3"');

    while (!$response->getBody()->eof()) {
        echo $response->getBody()->read(1);
        flush();
    }
} ?>