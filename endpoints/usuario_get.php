<?php

// Função de callback para obter informações de um usuário por ID
function api_usuario_get($request) {
    $user_id = $request['id'];

    // Verificar se o usuário existe
    $user = get_userdata($user_id);
    if (!$user) {
        return new WP_Error('no_user', 'Usuário não encontrado', array('status' => 404));
    }

    // Obter metadados do usuário
    $cpf = get_user_meta($user_id, 'cpf', true);
    $professor = get_user_meta($user_id, 'professor', true);
    $turma = get_user_meta($user_id, 'turma', true);

    // Preparar a resposta
    $response = array(
        'ID' => $user->ID,
        'email' => $user->user_email,
        'nome' => $user->display_name,
        'cpf' => $cpf,
        'professor' => $professor,
        'turma' => $turma,
    );

    return rest_ensure_response($response);
}

// Função para registrar o endpoint
function registrar_api_usuario_get() {
    register_rest_route('api', '/usuario/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'api_usuario_get',
    ));
}

add_action('rest_api_init', 'registrar_api_usuario_get');

?>