<?php

// Função de callback para recuperar TODOS os usuários nativos do WP
function api_usuario_get_all(WP_REST_Request $request) {
    
    // 1. Busca todos os usuários do banco de dados ordenados por data de criação
    // O parâmetro 'number' => -1 força o WordPress a ignorar qualquer limite e trazer todos.
    // O parâmetro 'orderby' => 'registered' usa a data de cadastro do usuário como base.
    // O parâmetro 'order' => 'DESC' garante que os maiores (mais recentes) venham primeiro.
    $users = get_users(array(
        'number'  => -1,
        'orderby' => 'registered', 
        'order'   => 'DESC'        
    ));

    // 2. Se por algum motivo bizarro não houver usuários, retorna array vazio
    if (empty($users)) {
        return rest_ensure_response(array());
    }

    // 3. Prepara a lista final que será transformada em JSON
    $response = array();

    // 4. Faz um loop por todos os usuários encontrados
    foreach ($users as $user) {
        $user_id = $user->ID;

        // Pega os metadados exatamente como você fez na função get_by_id
        $cpf = get_user_meta($user_id, 'cpf', true);
        $turma = get_user_meta($user_id, 'turma', true);
        
        // Garante que o WP devolva um booleano real (true/false)
        $professor = filter_var(get_user_meta($user_id, 'professor', true), FILTER_VALIDATE_BOOLEAN);

        // Monta o objeto de dados deste usuário específico
        $user_data = array(
            'ID' => $user_id,
            'email' => $user->user_email,
            'nome' => $user->display_name,
            'cpf' => $cpf,
            'professor' => $professor,
            'turma' => $turma ? (int) $turma : null,
        );

        // Adiciona este usuário estruturado na lista de resposta
        $response[] = $user_data;
    }

    // 5. Retorna a lista completa com Status 200 (OK)
    return rest_ensure_response($response);
}

// Função para registrar o endpoint da lista geral
function registrar_api_usuario_get_all() {
    register_rest_route('api', '/usuario', array(
        'methods' => 'GET',
        'callback' => 'api_usuario_get_all',
        'permission_callback' => '__return_true' // Exige permissão pública para o GET
    ));
}

add_action('rest_api_init', 'registrar_api_usuario_get_all');

?>