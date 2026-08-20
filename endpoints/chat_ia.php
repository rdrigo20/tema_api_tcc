<?php

function enviar_mensagem_para_ia($request) {

    // AUMENTA O LIMITE GLOBAL DO PHP PARA 3 MINUTOS (180 segundos)
    set_time_limit(180);

    // 1. Recebe a mensagem e o ID da conversa
    $mensagem_usuario = sanitize_text_field($request['mensagem']);
    $id_conversa = isset($request['id_conversa']) ? (int) $request['id_conversa'] : 0;

    // Proteção: Se não enviar o ID da conversa, barra a requisição
    if (!$id_conversa) {
        return new WP_Error('falta_id_conversa', 'O ID da conversa é obrigatório para manter a memória.', array('status' => 400));
    }

    // 2. RESGATA A MEMÓRIA DO BANCO DE DADOS (Pega a string JSON exata que está salva)
    $config_salva_json = get_post_meta($id_conversa, 'config_atual', true);
    
    if (empty($config_salva_json)) {
        return new WP_Error('conversa_nao_encontrada', 'Nenhuma configuração encontrada para este ID de conversa.', array('status' => 404));
    }

    // 3. INJETA A MEMÓRIA NO PROMPT
    // 3. INJETA A MEMÓRIA NO PROMPT (Versão Blindada com Inversão)
    $system_prompt = 'Você é um assistente especialista em redes IPTables.
    
    AQUI ESTÁ A CONFIGURAÇÃO ATUAL DO USUÁRIO:
    ' . $config_salva_json . '

    Sua tarefa é analisar o pedido do usuário, explicar o que foi feito e atualizar a configuração atual.
    
    REGRAS DE ESTRUTURA (OBRIGATÓRIAS):
    1. O seu JSON DEVE ter EXATAMENTE duas chaves na raiz: "resposta_amigavel" e "configuracao".
    2. na resposta_amigavel, explique de forma clara e resumida o que foi feito ou responda a pergunta do usuário. A chave "resposta_amigavel" DEVE VIR PRIMEIRO.
    3. Para campos não informados, use null, false ou []. NÃO invente dados.
    4. Quando o usúario solicitar uma configuração vc deve alterar os valores referentes a solicitação e manter a configuração atual para os campos não alterados.  
    5.caso o usário não peça mudanças na configuração, a chave "configuracao" deve ser idêntica à configuração atual.
    
    A SUA SAÍDA DEVE SER ESTRITAMENTE NESTE FORMATO:
    {
      "resposta_amigavel": "explique de forma resumida o que você fez",
      "configuracao": {
        "interfaces": { "wan": null, "lan": null },
        "lan_network": null,
        "policies": {
          "input": "ACCEPT", 
          "forward": "ACCEPT", 
          "output": "ACCEPT" 
        },
        "nat": false,
        "lan_free_internet": false,
        "connection_states": [],
        "drop_invalid": false,
        "services": [],
        "blocked_ips": []
      }
    }';

    // 4. Monta o corpo da requisição para o Ollama
    $payload = array(
        'model' => 'llama3', 
        'format' => 'json',  
        'stream' => false,   
        'messages' => array(
            array('role' => 'system', 'content' => $system_prompt),
            array('role' => 'user', 'content' => $mensagem_usuario)
        )
    );

    // 5. Configurações da requisição 
    $args = array(
        'body'    => json_encode($payload),
        'headers' => array('Content-Type' => 'application/json'),
        'timeout' => 120 
    );

    // 6. Envia para o servidor Ollama no túnel SSH
    $resposta = wp_remote_post('http://localhost:11434/api/chat', $args);

    if (is_wp_error($resposta)) {
        return new WP_Error('erro_conexao_ia', 'Erro ao conectar com o servidor Ollama: ' . $resposta->get_error_message(), array('status' => 500));
    }

    $corpo_bruto = wp_remote_retrieve_body($resposta);
    $dados_ollama = json_decode($corpo_bruto, true);

    if (isset($dados_ollama['error'])) {
        return rest_ensure_response(array(
            'status' => 'erro_interno_ollama',
            'mensagem' => 'O Ollama retornou um erro.',
            'detalhes' => $dados_ollama['error']
        ));
    }

    if (!isset($dados_ollama['message']['content'])) {
        return rest_ensure_response(array(
            'status' => 'erro_estrutura',
            'mensagem' => 'A resposta do Ollama veio vazia ou em formato desconhecido.',
            'corpo_bruto' => $corpo_bruto
        ));
    }

    $conteudo_ia = $dados_ollama['message']['content'];
    $conteudo_limpo = preg_replace('/```json|```/i', '', $conteudo_ia);
    $conteudo_limpo = trim($conteudo_limpo);

    $json_extraido = json_decode($conteudo_limpo, true);

    if ($json_extraido === null) {
        return rest_ensure_response(array(
            'status' => 'erro_parse',
            'mensagem' => 'A IA não devolveu um JSON perfeito.',
            'erro_do_php' => json_last_error_msg(),
            'texto_cru_da_ia' => $conteudo_limpo 
        ));
    }

    // 7. A MÁGICA ACONTECE AQUI: Salva a nova configuração no banco de dados!
    // Pegamos apenas o array "configuracao" gerado pela IA, codificamos para string e salvamos por cima da antiga.
    if (isset($json_extraido['configuracao'])) {
        update_post_meta(
            $id_conversa, 
            'config_atual', 
            wp_slash(wp_json_encode($json_extraido['configuracao']))
        );
    }

    // 8. Retorna para o Front-end
    return rest_ensure_response(array(
        'status' => 'sucesso',
        'dados'  => $json_extraido
    ));
}

function registrar_rota_ia() {
    register_rest_route('api', '/chat-ia', array(
        'methods' => 'POST',
        'callback' => 'enviar_mensagem_para_ia',
        'permission_callback' => '__return_true'
    ));
}
add_action('rest_api_init', 'registrar_rota_ia');

?>