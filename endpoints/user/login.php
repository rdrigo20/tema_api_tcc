<?php

// Adicione isso no seu plugin do WordPress

function api_usuario_login($request) {
    $email = sanitize_email($request['email']);
    $senha = $request['senha'];

    // wp_authenticate é a função nativa do WP que checa se o email e a senha batem
    $user = wp_authenticate($email, $senha);

    // Se a senha estiver errada ou o usuário não existir, retorna erro
    if (is_wp_error($user)) {
        return new WP_Error('auth_failed', 'E-mail ou senha incorretos.', array('status' => 401));
    }

    // Se deu certo, devolvemos os dados da pessoa
    return rest_ensure_response(array(
        'status' => 'sucesso',
        'mensagem' => 'Login realizado com sucesso',
        'dados' => array(
            'id'    => $user->ID,
            'nome'  => $user->first_name,
            'email' => $user->user_email
        )
    ));
}

function registrar_api_usuario_login() {
    register_rest_route('api', '/login', array(
        array(
            'methods'             => 'POST',
            'callback'            => 'api_usuario_login',
            'permission_callback' => '__return_true'
        ),
    ));
}
add_action('rest_api_init', 'registrar_api_usuario_login');

?>