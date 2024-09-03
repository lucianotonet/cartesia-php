<?php require_once '_parts/header.php'; ?>
<?php require_once '_parts/nav.php'; ?>
<?php if ($_SERVER['REQUEST_METHOD'] !== 'POST') { ?>
    <div class="flex flex-col flex-1 bg-green-400/5 min-h-full overflow-y-scroll">
        <?php
        // Chama o método listVoices para recuperar as vozes disponíveis
        $response = $client->listVoices();

        // Decodifica a resposta JSON em um array associativo
        $voices = json_decode($response->getBody(), true);

        // Verifica se a resposta da API contém um erro
        if (isset($voices['error'])) {
            echo "<div class='max-w-lg mx-auto mt-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg' role='alert'>";
            echo "Erro ao listar vozes: " . htmlspecialchars($voices['error']);
            echo "</div>";
        } else {
            // Exibe a lista de vozes em um formato HTML
            echo "<div class='max-w-2xl mx-auto mt-6'>";
            echo "<h1 class='text-2xl font-bold mb-4'>Vozes Disponíveis:</h1>";
            echo "<ul class='space-y-4'>";

            // Itera por cada voz e exibe seus detalhes
            foreach ($voices as $voice) {
                echo "<li class='p-4 bg-white shadow rounded-lg'>";
                echo "<strong class='text-lg'>Nome:</strong> " . htmlspecialchars($voice['name']) . "<br>";
                echo "<strong>ID:</strong> " . htmlspecialchars($voice['id']) . "<br>";
                echo "<strong>Descrição:</strong> " . htmlspecialchars($voice['description']) . "<br>";
                echo "<strong>Criado Em:</strong> " . htmlspecialchars($voice['created_at']) . "<br>";
                echo "<strong>Público:</strong> " . ($voice['is_public'] ? 'Sim' : 'Não') . "<br>";
                echo "</li>";
            }

            echo "</ul>";
            echo "</div>";
        }
        ?>
    </div>
<?php } ?>