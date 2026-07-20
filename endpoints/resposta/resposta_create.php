<?php

function resposta_create($request) {
    
    // 1. Pegamos o ID do usuário que o JS enviou no JSON (payload)
    // Usamos (int) para garantir que seja um número seguro e não código malicioso
    $user_id = isset($request['user_id']) ? (int) $request['user_id'] : 0;

    // 2. Coletamos os dados da resposta enviados pelo JS
    $conteudo = sanitize_textarea_field($request['conteudo']);
    $id_pergunta = isset($request['pergunta_id']) ? (int) $request['pergunta_id'] : 0;
    
    // Deixei o id_conversa pronto para o futuro, caso você decida agrupar as respostas
    $id_conversa = isset($request['id_conversa']) ? sanitize_text_field($request['id_conversa']) : '';

    // Validação básica de segurança: Impede salvar no banco se faltar algo
    if (!$user_id || !$conteudo || !$id_pergunta) {
        return new WP_Error('dados_ausentes', 'Faltam dados para salvar a resposta.', array('status' => 400));
    }

    // 3. Criamos um título automático para organizar no painel do WordPress
    $titulo = 'Resposta do Usuário ' . $user_id . ' (Pergunta ' . $id_pergunta . ')';

    // 4. Preparamos o array para o wp_insert_post
    $post_data = array(
        'post_author'  => $user_id,
        'post_type'    => 'resposta',
        'post_title'   => $titulo,
        'post_status'  => 'publish',
        'post_content' => $conteudo,
        // meta_input salva perfeitamente os Custom Fields
        'meta_input'   => array(
            'id_pergunta' => $id_pergunta,
            'id_conversa' => $id_conversa,
        ),
    );

    // 5. Salva no banco de dados
    $resposta_id = wp_insert_post($post_data);

    // Verifica se deu erro ao salvar
    if (is_wp_error($resposta_id)) {
        return $resposta_id;
    }

    // Devolve uma resposta limpa e de sucesso para o JavaScript
    return rest_ensure_response(array(
        'status'      => 'sucesso',
        'mensagem'    => 'Resposta salva com sucesso!',
        'resposta_id' => $resposta_id
    ));
}

function registrar_resposta_create() {
    register_rest_route('api', '/resposta', array(
        array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => 'resposta_create',
            // ISSO É ESSENCIAL: Diz ao WP para aceitar o POST sem exigir o nonce/cookie nativo
            'permission_callback' => '__return_true', 
        ),
    ));
}

add_action('rest_api_init', 'registrar_resposta_create');

?>