<?php

function api_usuario_post($request) {
    $email = sanitize_email($request['email']);
    $senha = $request['senha'];
    $cpf = sanitize_text_field($request['cpf']);
    
    // Pega apenas o nome enviado no formulário
    $nome = sanitize_text_field($request['nome']);
    
    // Converte o booleano de forma segura
    $professor_raw = isset($request['professor']) ? $request['professor'] : false;
    $professor = filter_var($professor_raw, FILTER_VALIDATE_BOOLEAN);

    $turma = isset($request['turma']) ? intval($request['turma']) : null;

    // Verificação de campos obrigatórios
    if (empty($email) || empty($senha) || empty($nome) || empty($cpf)) {
        return new WP_Error('missing_fields', 'Os campos email, senha, nome e cpf são obrigatórios.', array('status' => 400));
    }

    $user_exists = username_exists($email);
    $email_exists = email_exists($email);

    if (!$user_exists && !$email_exists) {
        
        // Criar usuário (O email é usado como login e como email)
        $user_id = wp_create_user($email, $senha, $email);

        if (is_wp_error($user_id)) {
            return $user_id;
        }

        // Atualizar dados nativos do usuário
        wp_update_user(array(
            'ID'           => $user_id,
            'display_name' => $nome, // Como o nome aparece no site
            'first_name'   => $nome, // Campo nativo de nome
            'role'         => 'subscriber',
        ));

        // Atualizar metadados personalizados
        update_user_meta($user_id, 'cpf', $cpf);
        update_user_meta($user_id, 'professor', $professor);
        
        if ($turma !== null) {
            update_user_meta($user_id, 'turma', $turma);
        }

        return rest_ensure_response(array(
            'status'   => 'sucesso',
            'mensagem' => 'Usuário criado com sucesso',
            'user_id'  => $user_id
        ));
    } else {
        return new WP_Error('email_exists', 'Email já cadastrado.', array('status' => 403));
    }
}

function registrar_api_usuario_post() {
    register_rest_route('api', '/usuario', array(
        array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => 'api_usuario_post',
            'permission_callback' => '__return_true'
        ),
    ));
}
add_action('rest_api_init', 'registrar_api_usuario_post');

?>