<?php

function enviar_mensagem_para_ia($request) {
    // 1. Recebe a mensagem do usuário via JSON
    $mensagem_usuario = sanitize_text_field($request['mensagem']);
    
    // 2. O NOVO PROMPT DO SISTEMA (Baseado no seu novo JSON Padrão)
    // Usamos regras estritas para evitar que a IA alucine dados que o usuário não pediu.
    $system_prompt = 'Você é um assistente especialista em redes e segurança, focado em configurar regras de firewall IPTables.
    SUA ÚNICA SAÍDA DEVE SER UM JSON VÁLIDO. NÃO ESCREVA TEXTO FORA DO JSON.
    
    Seu objetivo é extrair as configurações solicitadas pelo usuário e preencher o objeto "configuracao".
    Para os campos que o usuário AINDA NÃO informou, deixe como null, false ou array vazio [].
    NÃO INVENTE IPs, portas ou serviços que não foram solicitados.
    
    O seu JSON DEVE OBRIGATORIAMENTE ter a estrutura exata abaixo:
    
    {
      "configuracao": {
        "interfaces": {
          "wan": null,
          "lan": null
        },
        "lan_network": null,
        "policies": {
          "input": null,
          "forward": null,
          "output": null
        },
        "nat": false,
        "lan_free_internet": false,
        "connection_states": [],
        "drop_invalid": false,
        "services": [
            // Crie objetos aqui APENAS quando o usuário pedir para adicionar um serviço, exemplo:
            // { "name": "SSH Roteador", "port": 22, "protocol": "tcp", "chain": "INPUT", "internal_ip": null, "allowed_from": ["lan"] }
        ],
        "blocked_ips": [
            // Adicione strings de IPs ou CIDR bloqueados aqui APENAS quando solicitado, ex: "198.51.100.23" ou "185.220.101.0/24"
        ]
      },
      "resposta_amigavel": "Escreva aqui a sua resposta para o usuário. Confirme o que você alterou, explique de forma simples e pergunte qual é o próximo passo ou a próxima informação necessária."
    }';

    // 3. Monta o corpo da requisição para o Ollama
    $payload = array(
        'model' => 'llama3', 
        'format' => 'json',  // Trava o hardware da IA para gerar apenas JSON
        'stream' => false,   // Pede a resposta inteira de uma vez
        'messages' => array(
            array('role' => 'system', 'content' => $system_prompt),
            array('role' => 'user', 'content' => $mensagem_usuario)
        )
    );

    // 4. Configurações da requisição (Timeout estendido para a IA pensar)
    $args = array(
        'body'    => json_encode($payload),
        'headers' => array('Content-Type' => 'application/json'),
        'timeout' => 120 
    );

    // 5. Envia para o servidor Ollama no túnel SSH
    $resposta = wp_remote_post('http://localhost:11434/api/chat', $args);

    // 6. Proteção contra queda do túnel SSH ou timeout
    if (is_wp_error($resposta)) {
        return new WP_Error('erro_conexao_ia', 'Erro ao conectar com o servidor Ollama: ' . $resposta->get_error_message(), array('status' => 500));
    }

    // 7. Extrai o corpo da resposta
    $corpo_bruto = wp_remote_retrieve_body($resposta);
    $dados_ollama = json_decode($corpo_bruto, true);

    // 8. Proteção contra erros internos do próprio Ollama (ex: falta de RAM, modelo errado)
    if (isset($dados_ollama['error'])) {
        return rest_ensure_response(array(
            'status' => 'erro_interno_ollama',
            'mensagem' => 'O Ollama retornou um erro.',
            'detalhes' => $dados_ollama['error']
        ));
    }

    // 9. Verifica se a estrutura de resposta veio como esperado
    if (!isset($dados_ollama['message']['content'])) {
        return rest_ensure_response(array(
            'status' => 'erro_estrutura',
            'mensagem' => 'A resposta do Ollama veio vazia ou em formato desconhecido.',
            'corpo_bruto' => $corpo_bruto
        ));
    }

    $conteudo_ia = $dados_ollama['message']['content'];

    // 10. Limpeza contra formatação Markdown (IAs costumam usar ```json ... ```)
    $conteudo_limpo = preg_replace('/```json|```/i', '', $conteudo_ia);
    $conteudo_limpo = trim($conteudo_limpo);

    // 11. Converte a string final para um array PHP manipulável
    $json_extraido = json_decode($conteudo_limpo, true);

    // 12. Debug em caso de erro na geração do JSON
    if ($json_extraido === null) {
        return rest_ensure_response(array(
            'status' => 'erro_parse',
            'mensagem' => 'A IA não devolveu um JSON perfeito.',
            'erro_do_php' => json_last_error_msg(),
            'texto_cru_da_ia' => $conteudo_limpo // Retorna o texto quebrado para você entender onde a IA errou
        ));
    }

    // 13. Sucesso! Retorna a configuração estruturada e a resposta amigável para o Front
    return rest_ensure_response(array(
        'status' => 'sucesso',
        'dados'  => $json_extraido
    ));
}

// Registra o endpoint
function registrar_rota_ia() {
    register_rest_route('api', '/chat-ia', array(
        'methods' => 'POST',
        'callback' => 'enviar_mensagem_para_ia',
        'permission_callback' => '__return_true'
    ));
}
add_action('rest_api_init', 'registrar_rota_ia');

?>