<?php
function api_usuario_get($request) {
    // Segurança: força a variável a ser um número inteiro
    $user_id = (int) $request['id'];

    $user = get_userdata($user_id);
    if (!$user) {
        return new WP_Error('no_user', 'Usuário não encontrado', array('status' => 404));
    }

    // Pega os metadados
    $cpf = get_user_meta($user_id, 'cpf', true);
    $turma = get_user_meta($user_id, 'turma', true);
    
    // Garante que o WP devolva um booleano real (true/false) e não uma string "1" ou vazia
    $professor = filter_var(get_user_meta($user_id, 'professor', true), FILTER_VALIDATE_BOOLEAN);

    $response = array(
        'ID' => $user->ID,
        'email' => $user->user_email,
        'nome' => $user->display_name,
        'cpf' => $cpf,
        'professor' => $professor,
        'turma' => $turma ? (int) $turma : null,
    );

    return rest_ensure_response($response);
}

function registrar_api_usuario_get() {
    register_rest_route('api', '/usuario/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'api_usuario_get',
        'permission_callback' => '__return_true' // Exige permissão pública para o GET
    ));
}

add_action('rest_api_init', 'registrar_api_usuario_get');
?>