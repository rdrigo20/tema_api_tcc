<?php

function enviar_mensagem_para_ia($request) {
    // 1. Recebe a mensagem do usuário vinda do JS
    $mensagem_usuario = sanitize_text_field($request['mensagem']);
    
    // (Opcional) Aqui você pode buscar o histórico da conversa no banco 
    // para enviar junto, mas vamos focar na conexão básica primeiro.

    // 2. Monta o Prompt do Sistema (As regras da IA)
    $system_prompt = "Você é um assistente especialista em redes e IPTables. 
    Seu objetivo é extrair as configurações que o usuário deseja.
    Você DEVE retornar APENAS um objeto JSON válido contendo os campos: 
    'acao' (ex: bloquear_ip, liberar_porta), 'valor' (o ip ou porta) e 'resposta_amigavel' (uma fala para o usuário).";

    // 3. Estrutura o pacote para o Ollama
    $payload = array(
        'model' => 'llama3',
        'format' => 'json', // OBRIGA a IA a devolver apenas JSON
        'stream' => false,  // Espera a IA terminar de pensar antes de responder
        'messages' => array(
            array('role' => 'system', 'content' => $system_prompt),
            array('role' => 'user', 'content' => $mensagem_usuario)
        )
    );

    // 4. Configura a requisição com o TIMEOUT prolongado
    $args = array(
        'body'    => json_encode($payload),
        'headers' => array('Content-Type' => 'application/json'),
        'timeout' => 120 // AUMENTA O TEMPO: Espera até 2 minutos pela IA
    );

    // 5. Bate no Ollama (através do túnel SSH que está no localhost)
    $resposta = wp_remote_post('http://localhost:11434/api/chat', $args);

    if (is_wp_error($resposta)) {
        return new WP_Error('erro_ia', 'Erro ao conectar com a IA: ' . $resposta->get_error_message(), array('status' => 500));
    }

    // 6. Decodifica e devolve para o Frontend
    $corpo = wp_remote_retrieve_body($resposta);
    $dados_ia = json_decode($corpo, true);

    // O texto gerado pela IA fica dentro deste caminho do array:
    $conteudo_ia = $dados_ia['message']['content'];

    // Como pedimos JSON, $conteudo_ia já é uma string JSON pura. 
    // Podemos decodificá-la para o PHP ler ou mandar direto para o JS.
    $json_extraido = json_decode($conteudo_ia, true);

    return rest_ensure_response(array(
        'status' => 'sucesso',
        'dados' => $json_extraido
    ));
}

// Registra a rota
function registrar_rota_ia() {
    register_rest_route('api', '/chat-ia', array(
        'methods' => 'POST',
        'callback' => 'enviar_mensagem_para_ia',
        'permission_callback' => '__return_true'
    ));
}
add_action('rest_api_init', 'registrar_rota_ia');

?>