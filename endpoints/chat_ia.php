<?php

function enviar_mensagem_para_ia($request) {

    // 1. AUMENTA O LIMITE GLOBAL DO PHP PARA 3 MINUTOS (180 segundos)
    set_time_limit(180);

    // 2. RECEBE OS DADOS DA REQUISIÇÃO
    $mensagem_usuario = sanitize_text_field($request['mensagem']);
    $id_conversa = isset($request['id_conversa']) ? (int) $request['id_conversa'] : 0;

    if (!$id_conversa) {
        return new WP_Error('falta_id_conversa', 'O ID da conversa é obrigatório para manter a memória.', array('status' => 400));
    }

    // 3. RESGATA A MEMÓRIA DA CONVERSA
    $post_conversa = get_post($id_conversa);
    if (empty($post_conversa) || $post_conversa->post_type !== 'conversa') {
        return new WP_Error('conversa_invalida', 'Conversa não encontrada.', array('status' => 404));
    }
    
    $config_salva_json = get_post_meta($id_conversa, 'config_atual', true);
    if (empty($config_salva_json)) {
        return new WP_Error('config_vazia', 'Nenhuma configuração base encontrada para esta conversa.', array('status' => 404));
    }

    // 4. RESGATA OS ATALHOS DE IP DO USUÁRIO
    $user_id_da_conversa = $post_conversa->post_author;
    
    // Busca atalhos globais (escopo 1) ou criados por este usuário
    $args_atalhos = array(
        'post_type' => 'atalho_ip',
        'post_status' => 'publish',
        'numberposts' => -1,
        'meta_query' => array(
            'relation' => 'OR',
            array('key' => 'escopo_atalho_ip', 'value' => '1', 'compare' => '='),
        ),
        'author' => $user_id_da_conversa
    );
    $posts_atalhos = get_posts($args_atalhos);

    // Monta o dicionário de atalhos para injetar no prompt
    $texto_atalhos = "Nenhum atalho configurado no momento.";
    if (!empty($posts_atalhos)) {
        $texto_atalhos = "";
        foreach ($posts_atalhos as $atalho) {
            $nome = get_post_meta($atalho->ID, 'nome_atalho_ip', true);
            $ip = get_post_meta($atalho->ID, 'ip_atalho_ip', true);
            $texto_atalhos .= "- Se o usuário se referir a '{$nome}', use o IP: {$ip}\n";
        }
    }

    // 5. O PROMPT DO SISTEMA (CÉREBRO DA IA)
    // Atualizado com regras rígidas de listas, máscaras e atalhos.
    $system_prompt = 'Você é um assistente especialista em redes IPTables.
    
    AQUI ESTÁ A CONFIGURAÇÃO ATUAL DO USUÁRIO:
    ' . $config_salva_json . '

    Sua tarefa é analisar o pedido do usuário, aplicar as mudanças solicitadas e retornar a configuração atualizada.
    
    REGRAS DE ESTRUTURA E COMPORTAMENTO (OBRIGATÓRIAS):
    1. O seu JSON DEVE ter EXATAMENTE duas chaves na raiz: "resposta_amigavel" e "configuracao".
    2. Na "resposta_amigavel", explique de forma clara o que você fez. A chave "resposta_amigavel" DEVE VIR PRIMEIRO.
    3. PRESERVAÇÃO DE LISTAS (CRÍTICO): Ao adicionar itens em "blocked_ips" ou "services", você DEVE COPIAR TODOS os itens da configuração atual e adicionar os novos ao final do array. Nunca apague um item a menos que o usuário peça explicitamente para "remover/apagar/deletar".
    4. Mantenha a configuração atual INTACTA para todos os campos que não sofreram alterações.
    
    5. DISTINÇÃO CRÍTICA: REDE LOCAL vs BLOQUEIOS:
       - Se o usuário informar "meu IP", "minha rede", ou o IP do seu próprio roteador/firewall, ATUALIZE O CAMPO "lan_network". NUNCA bloqueie o IP do próprio usuário.
       - SÓ adicione IPs ao campo "blocked_ips" se o usuário usar palavras claras de restrição (ex: "bloquear", "negar", "proibir", "dropar").

    6. REGRAS PARA SERVIÇOS ("services"):
       - "name": Nome descritivo (ex: "SSH").
       - "port": Número da porta em formato inteiro (ex: 22).
       - "protocol": "tcp" ou "udp".
       - "chain": "INPUT" (acesso ao roteador) ou "FORWARD" (redirecionamento interno).
       - "internal_ip": IP da máquina de destino. (APENAS se chain for "FORWARD").
       - "allowed_from": Array de origens (ex: ["any"], ["lan"], ou uma lista de IPs com CIDR ["203.0.113.15/32"]).

    7. DICIONÁRIO DE ATALHOS DE IP (CRÍTICO):
       Sempre verifique se o usuário usou um nome listado abaixo. Se sim, substitua IMEDIATAMENTE o nome pelo número de IP correspondente. NUNCA coloque o "nome" na configuração.
       ATALHOS DISPONÍVEIS NESTA SESSÃO:
       ' . $texto_atalhos . '

    8. PADRONIZAÇÃO DE IPs E MÁSCARAS (CIDR):
       Todos os endereços IP (exceto os atalhos antes de resolvidos) DEVEM conter a máscara no formato CIDR (ex: IP/máscara).
       - Para "blocked_ips" e "allowed_from": Se o usuário fornecer um IP único sem máscara, assuma /32 (ex: "192.168.1.10" -> "192.168.1.10/32").
       - Para "lan_network": Se o usuário fornecer um IP sem máscara, assuma /24 por padrão (ex: "192.168.1.0" -> "192.168.1.0/24"), a menos que ele especifique outra.
       - Se fornecido formato decimal (ex: 255.255.255.0), converta para CIDR (/24).
    
    A SUA SAÍDA DEVE SER ESTRITAMENTE UM JSON VÁLIDO SEGUINDO A ESTRUTURA ABAIXO. Retorne a configuração real e completa, sem usar placeholders ou textos de exemplo:
    {
      "resposta_amigavel": "Sua resposta aqui...",
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

    // 6. PACOTE PARA O OLLAMA (Usando o novo modelo Qwen 2.5 Coder 14B)
    $payload = array(
        'model' => 'qwen2.5-coder:14b', // <-- MODELO ATUALIZADO AQUI
        'format' => 'json',  
        'stream' => false,   
        'messages' => array(
            array('role' => 'system', 'content' => $system_prompt),
            array('role' => 'user', 'content' => $mensagem_usuario)
        )
    );

    // 7. CHAMADA À API NO TÚNEL SSH
    $args = array(
        'body'    => json_encode($payload),
        'headers' => array('Content-Type' => 'application/json'),
        'timeout' => 120 
    );

    $resposta = wp_remote_post('http://localhost:11434/api/chat', $args);

    // 8. TRATAMENTO DE ERROS DE CONEXÃO
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

    // 9. EXTRAÇÃO E LIMPEZA DO JSON
    $conteudo_ia = $dados_ollama['message']['content'];
    // Remove tags markdown caso a IA tente colocar ```json ... ```
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

    // 10. ATUALIZA A MEMÓRIA NO BANCO DE DADOS
    // Só atualiza se a chave 'configuracao' existir e não for vazia
    if (isset($json_extraido['configuracao'])) {
        update_post_meta(
            $id_conversa, 
            'config_atual', 
            wp_slash(wp_json_encode($json_extraido['configuracao']))
        );
    }

    // 11. DEVOLVE A RESPOSTA PARA O FRONTEND (chat.js)
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