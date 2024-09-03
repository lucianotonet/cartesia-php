---
title: 'Documentação do Cartesia PHP SDK'
---

# Documentação do Cartesia PHP SDK

Bem-vindo à documentação oficial do Cartesia PHP SDK. Esta biblioteca permite que você integre facilmente os serviços de síntese de fala e gerenciamento de vozes da Cartesia em seus projetos PHP.

## Conteúdo

1. [Introdução](./getting-started/installation.md)
   - [Instalação](./getting-started/installation.md)
   - [Configuração](./getting-started/installation.md#configuração)
   - [Início Rápido](./getting-started/quick-start.md)

2. [Guias](./guides/)
   - [Gerenciamento de Vozes](./guides/voice-management.md)
   - [Síntese de Fala](./guides/speech-synthesis.md)
     - Formatos de codificação suportados
     - Controles experimentais (velocidade e emoção)

3. [Referência da API](./api-reference/cartesia-client.md)

4. [Exemplos](./examples/)
   - [Listar Vozes](./examples/list-voices.md)
   - [Stream de Fala (Bytes)](./examples/stream-speech-bytes.md)
   - [Stream de Fala (SSE)](./examples/stream-speech-sse.md)
   - [Stream de Fala (WebSocket)](./examples/stream-speech-ws.md)

5. [Testes](./tests.md)

## Suporte

Se você encontrar algum problema ou tiver dúvidas sobre o uso da biblioteca, abra uma issue no [repositório GitHub](https://github.com/lucianotonet/cartesia-php). Também aceitamos pull requests para melhorias e novas funcionalidades.

## Licença

Este projeto está licenciado sob a [Licença MIT](../LICENSE).

## Notas

- Este SDK faz uso de um arquivo `.env` para configurações. Certifique-se de configurá-lo corretamente antes de usar a biblioteca.