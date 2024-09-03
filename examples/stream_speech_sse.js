class Player {
    // Contexto de áudio e propriedades...
    #context = null;
    #sourceNode = null;
    #isPlaying = false;
    #sampleRate = 44100;
    #bufferQueue = [];
    #chunkSize = 4096; // Tamanho de chunk usado para cada buffer
    #previousBuffer = null;
    // Contador de buffers a serem pré-carregados
    #preloadBufferCount = 3;
    // Contador de chunks processados
    #chunkCount = 0;
    // Largura total da forma de onda desenhada
    #totalWaveformWidth = 0;
    // Elemento de canvas para desenhar a forma de onda
    #canvas = null;
    // Estado de arrasto
    #isDragging = false;
    // Posição inicial do arrasto
    #startX = 0;
    // Posição de rolagem inicial
    #scrollStart = 0;
    // Nível de zoom da forma de onda
    #zoomLevel = 1;

    // Inicializa o contexto de áudio
    initializeAudioContext() {
        if (!this.#context || this.#context.state === 'closed') {
            this.#context = new AudioContext({ sampleRate: this.#sampleRate });
        }
    }


    // Decodificação A-law
    alawDecode(sample) {
        const ALAW_MAX = 32768; // Corrigido para 32768
        let sign = (sample & 0x80) ? -1 : 1;
        let mantissa = sample & 0x0F;
        let exponent = (sample >> 4) & 0x07;
        let decodedSample = sign * (mantissa === 0 ? 0 : (1 << (exponent + 1)) * (mantissa + 0.5) - 1); // Ajuste na fórmula para melhor precisão
        decodedSample *= 1.5; // Aumenta o ganho para reduzir distorção
        return Math.max(-1, Math.min(1, decodedSample / ALAW_MAX)); // Normaliza e limita o valor
    }

    // Decodificação Mu-law
    mulawDecode(sample) {
        const MULAW_MAX = 32768; // Corrigido para 32768
        const MULAW_BIAS = 132;
        sample = ~sample & 0xFF;
        let sign = (sample & 0x80) ? -1 : 1;
        let exponent = (sample >> 4) & 0x07;
        let mantissa = sample & 0x0F;
        let decodedSample = sign * ((1 << (exponent + 3)) * (mantissa + 0.5) - MULAW_BIAS);
        return decodedSample * 2 / MULAW_MAX; // Aumenta o volume
    }

    // Adiciona um novo buffer à fila
    async #appendBuffer(newBuffer, encoding) {
        if (newBuffer.length > 0) {
            this.#bufferQueue.push(newBuffer); // Armazena o buffer completo
            // Inicia a reprodução se a fila de buffers atingir o limite
            if (this.#bufferQueue.length >= this.#preloadBufferCount && !this.#isPlaying) {
                this.#playNextBuffer();
            }
        }
    }

    // Reproduz o áudio transmitido
    async playStreamedAudio(base64AudioData, encoding) {
        if (!this.#context) {
            throw new Error("AudioContext não inicializado.");
        }

        const binaryString = atob(base64AudioData);
        const len = binaryString.length;
        let bytes;

        switch (encoding) {
            case 'pcm_mulaw':
                bytes = new Float32Array(len);
                for (let i = 0; i < len; i++) {
                    const value = binaryString.charCodeAt(i);
                    bytes[i] = this.mulawDecode(value);
                }
                break;
            case 'pcm_s16le':
                bytes = new Float32Array(len / 2);
                for (let i = 0; i < len / 2; i++) {
                    const value = binaryString.charCodeAt(i * 2) | (binaryString.charCodeAt(i * 2 + 1) << 8);
                    bytes[i] = new Int16Array(new Uint16Array([value]).buffer)[0] / 32768.0;
                }
                break;

            case 'pcm_f32le':
                bytes = new Float32Array(len / 4);
                for (let i = 0; i < len / 4; i++) {
                    const value = binaryString.charCodeAt(i * 4) |
                        (binaryString.charCodeAt(i * 4 + 1) << 8) |
                        (binaryString.charCodeAt(i * 4 + 2) << 16) |
                        (binaryString.charCodeAt(i * 4 + 3) << 24);
                    bytes[i] = new Float32Array(new Uint32Array([value]).buffer)[0];
                }
                break;
            case 'pcm_alaw':
                bytes = new Float32Array(len);
                for (let i = 0; i < len; i++) {
                    const value = binaryString.charCodeAt(i);
                    bytes[i] = this.alawDecode(value);
                }
                break;
        }

        // Criação de um buffer de áudio a partir dos bytes decodificados
        const audioBuffer = this.#context.createBuffer(1, bytes.length, this.#sampleRate);
        audioBuffer.getChannelData(0).set(bytes);

        // Reprodução do buffer imediatamente
        await this.#appendBuffer(audioBuffer, encoding);
    }

    #playNextBuffer() {
        if (this.#bufferQueue.length === 0) {
            this.#isPlaying = false;
            return;
        }

        const buffer = this.#bufferQueue.shift();
        this.#sourceNode = this.#context.createBufferSource();
        this.#sourceNode.buffer = buffer; // O buffer deve ser um AudioBuffer

        this.#sourceNode.connect(this.#context.destination);
        this.#sourceNode.start(0); // Inicia a reprodução a partir do início
        this.#isPlaying = true;

        this.drawWaveform(buffer);

        // Ao terminar, começa o próximo buffer
        this.#sourceNode.onended = () => {
            this.#isPlaying = false;
            this.#playNextBuffer();
        };
    }

    // Para a reprodução de áudio
    stop() {
        if (this.#sourceNode) {
            this.#sourceNode.stop();
            this.#sourceNode.disconnect();
            this.#sourceNode = null;
        }
        this.#isPlaying = false;
        this.#bufferQueue = [];
        this.#chunkCount = 0;
        this.#canvas.scrollLeft = 0;
    }

    // Define a taxa de amostragem e inicializa o contexto de áudio
    setSampleRate(rate) {
        this.#sampleRate = rate;
        this.initializeAudioContext();
    }

    // Desenha a forma de onda do buffer de áudio
    drawWaveform(audioBuffer) {
        this.#canvas = document.getElementById('waveformCanvas');
        const ctx = this.#canvas.getContext('2d');
        const width = this.#canvas.width;
        const height = 200;
        const data = audioBuffer.getChannelData(0);
        const amp = height / 2;

        const chunkWidth = Math.min(width / this.#zoomLevel, width);
        const step = Math.max(1, Math.floor(data.length / chunkWidth));  // Evita step menor que 1

        ctx.clearRect(0, 0, width, height);
        ctx.beginPath();
        ctx.moveTo(0, amp);

        for (let i = 0; i < chunkWidth; i++) {
            let min = 1.0;
            let max = -1.0;
            for (let j = 0; j < step; j++) {
                const datum = data[(i * step) + j];
                if (datum < min) min = datum;
                if (datum > max) max = datum;
            }

            // Suaviza a linha
            const yMin = (1 + min) * amp;
            const yMax = (1 + max) * amp;

            ctx.lineTo(i, (yMin + yMax) / 2);  // Posição média entre min e max
        }

        ctx.strokeStyle = `hsl(${this.#chunkCount * 30 % 360}, 100%, 50%)`;
        ctx.stroke();

        this.#chunkCount++;
        this.#totalWaveformWidth += chunkWidth;

        if (this.#totalWaveformWidth >= width) {
            this.#totalWaveformWidth = 0;
        }
    }
}

// Lógica para inicializar o Player e conectar ao formulário
const form = document.getElementById('speechForm');
form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const sampleRateSelect = document.getElementById('sampleRate');
    const encodingSelect = document.getElementById('encoding');
    const player = new Player();
    const sampleRate = parseInt(sampleRateSelect.value);
    const encoding = encodingSelect.value;
    player.setSampleRate(sampleRate);

    const text = document.getElementById('textInput').value.trim();
    const eventData = {
        transcript: text,
        sample_rate: sampleRate,
        encoding: encoding,
        add_timestamps: true
    };

    const eventSource = new EventSource(`stream_speech_sse.php?transcript=${encodeURIComponent(text)}&sample_rate=${sampleRate}&encoding=${encoding}&add_timestamps=true`);

    eventSource.onerror = (error) => {
        console.error("Erro na conexão:", error);
        eventSource.close();
    };

    eventSource.addEventListener("connected", () => {
        console.log("Conexão estabelecida com o servidor.");
    });

    eventSource.addEventListener("chunk", async (e) => {
        const audioData = JSON.parse(e.data);
        if (audioData.type === "chunk" && audioData.data) {
            const base64AudioData = audioData.data;
            await player.playStreamedAudio(base64AudioData, encoding);
        }
    });

    eventSource.addEventListener("timestamps", () => {
        // Processamento dos timestamps
    });

    eventSource.addEventListener("done", () => {
        console.log("Transcrição concluída.");
        eventSource.close();
    });

    eventSource.addEventListener("close", () => {
        console.log("Conexão encerrada.");
        eventSource.close();
    });
});
