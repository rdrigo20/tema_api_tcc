<?php

function conversa_create($request) {
    
    // 1. Pega o ID do usuário de forma segura via JSON (igual fizemos nas respostas)
    $user_id = isset($request['user_id']) ? (int) $request['user_id'] : 0;

    if (!$user_id) {
        return new WP_Error('falta_user', 'ID do usuário não fornecido.', array('status' => 400));
    }

    $titulo = sanitize_text_field($request['titulo']);
    $conteudo = sanitize_textarea_field($request['conteudo']);

    $response = array(
        'post_author'  => $user_id, // Atrela a conversa ao usuário corretamente!
        'post_type'    => 'conversa',
        'post_title'   => $titulo,
        'post_status'  => 'publish',
        'post_content' => $conteudo,
    );

    $conversa_id = wp_insert_post($response);

    if (is_wp_error($conversa_id)) {
        return $conversa_id;
    }

    // Devolve o ID real da conversa criada no banco de dados!
    return rest_ensure_response(array(
        'status'      => 'sucesso',
        'conversa_id' => $conversa_id
    ));
}

function registrar_conversa_create() {
    register_rest_route('api', '/conversa', array(
        array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => 'conversa_create',
            'permission_callback' => '__return_true' // Permite o acesso via Headless
        ),
    ));
}

add_action('rest_api_init', 'registrar_conversa_create');

?>