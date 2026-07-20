<?php

//ESSA MERDA N FOI TESTADA e Provavelmente n funciona!!!!!!!!!!
//ESSA MERDA N FOI TESTADA e Provavelmente n funciona!!!!!!!!!!
//ESSA MERDA N FOI TESTADA e Provavelmente n funciona!!!!!!!!!!
//ESSA MERDA N FOI TESTADA e Provavelmente n funciona!!!!!!!!!!
//ESSA MERDA N FOI TESTADA e Provavelmente n funciona!!!!!!!!!!
//ESSA MERDA N FOI TESTADA e Provavelmente n funciona!!!!!!!!!!

function api_usuario_put($request) {
    // Tenta pegar o usuário autenticado (Requer o Plugin JWT no modelo Headless)
    $user = wp_get_current_user();
    $user_id = $user->ID;

    // Se for 0, o usuário não está logado ou o Token não foi enviado corretamente
    if ($user_id === 0) {
        return new WP_Error('permissao', 'Usuário não autenticado ou token inválido.', array('status' => 401));
    }

    // Pega os dados enviados pelo Front-end
    $email = sanitize_email($request['email']);
    $senha = isset($request['senha']) ? $request['senha'] : '';
    $cpf = sanitize_text_field($request['cpf']);
    
    // Separa o nome em nativos do WP (First e Last)
    $nome_completo = sanitize_text_field($request['nome']);
    $partes_nome = explode(' ', $nome_completo, 2);
    $first_name = $partes_nome[0];
    $last_name = isset($partes_nome[1]) ? $partes_nome[1] : '';

    // Mesmo tratamento booleano à prova de falhas do POST
    $professor_raw = isset($request['professor']) ? $request['professor'] : false;
    $professor = filter_var($professor_raw, FILTER_VALIDATE_BOOLEAN);
    $turma = isset($request['turma']) ? intval($request['turma']) : null;

    // A função email_exists retorna o ID do dono do e-mail (ou false se estiver livre)
    $email_exists = email_exists($email);

    // Se o e-mail existe E NÃO PERTENCE ao usuário atual, bloqueia.
    if ($email_exists && $email_exists !== $user_id) {
        return new WP_Error('email_exists', 'Este email já está sendo utilizado por outra conta.', array('status' => 403));
    }

    // Prepara os dados nativos que serão atualizados
    $dados_atualizacao = array(
        'ID'           => $user_id,
        'display_name' => $nome_completo,
        'first_name'   => $first_name,
        'last_name'    => $last_name,
    );

    // Só atualiza o e-mail se foi enviado um válido
    if (!empty($email)) {
        $dados_atualizacao['user_email'] = $email;
    }

    // REGRA DE OURO: Só atualiza a senha se o usuário digitou uma nova. 
    // Se você não fizer isso, o WP apaga a senha dele se o campo vier vazio.
    if (!empty($senha)) {
        $dados_atualizacao['user_pass'] = $senha;
    }

    // Atualiza os dados principais no banco
    $resultado = wp_update_user($dados_atualizacao);

    if (is_wp_error($resultado)) {
        return $resultado;
    }

    // Atualiza os metadados
    if (!empty($cpf)) {
        update_user_meta($user_id, 'cpf', $cpf);
    }
    
    update_user_meta($user_id, 'professor', $professor);
    
    if ($turma !== null) {
        update_user_meta($user_id, 'turma', $turma);
    }

    return rest_ensure_response(array(
        'status' => 'sucesso',
        'mensagem' => 'Perfil atualizado com sucesso!'
    ));
}

function registrar_api_usuario_put() {
    register_rest_route('api', '/usuario', array(
        array(
            'methods' => WP_REST_Server::EDITABLE, // PUT ou PATCH
            'callback' => 'api_usuario_put',
            'permission_callback' => 'is_user_logged_in' // Só roda se o sistema reconhecer o login/token
        ),
    ));
}

add_action('rest_api_init', 'registrar_api_usuario_put');
?>