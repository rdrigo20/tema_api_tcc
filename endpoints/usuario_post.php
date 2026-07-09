<?php


function api_usuario_post($request) {
    $email = sanitize_email($request['email']);
    $senha = $request['senha'];
    $nome = sanitize_text_field($request['nome']);
    $cpf = sanitize_text_field($request['cpf']);
    //NÃO ESTÁ FUNCIONANDO COMO DEVERIA
    // filter_var converte corretamente 'true', '1', 1 ou true para o booleano TRUE (e vice-versa para FALSE)
    $professor = filter_var($request['professor'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    // intval garante que o valor da turma será salvo estritamente como um número inteiro (a turma provavelmete sera o id do user do professor)
    $turma = isset($request['turma']) ? intval($request['turma']) : null;
    

    // Verificação de campos obrigatórios
    if (!$email || !$senha || !$nome || !$cpf) {
        return new WP_Error('missing_fields', 'Os campos email, senha, nome e cpf são obrigatórios.', array('status' => 400));
    }

    //NÃO ESTÁ FUNCIONANDO COMO DEVERIA
    // se nada for enviado para o campo professor, ele será considerado como false
    if(!$professor) {
       $professor = FALSE;
    }

    $user_exists = username_exists($email);
    $email_exists = email_exists($email);

    if (!$user_exists && !$email_exists) {
        // Criar usuário
        $user_id = wp_create_user($email, $senha, $email);

        // isso aqui é uma verificação q eu n entendo muito mas basicamente se na hora de criar der errado ao inves da variável receber o valor do id ela recebe o objeto de erro e é isso q ele está verificando
        if (is_wp_error($user_id)) {
            return $user_id;
        }

        // Atualizar dados do usuário
        $response = array(
            'ID' => $user_id,
            'display_name' => $nome,
            'first_name' => $nome,
            'role' => 'subscriber',
        );
        wp_update_user($response);

        // Atualizar metadados do usuário (tabela wp_usermeta)
        update_user_meta($user_id, 'cpf', $cpf);
        update_user_meta($user_id, 'professor', $professor);
        //só atualiza a turma se o valor for diferente de null, para não sobrescrever caso o usuário não tenha enviado esse campo
        if ($turma !== null) {
            update_user_meta($user_id, 'turma', $turma);
        }

        return rest_ensure_response($response);
    } else {
        return new WP_Error('email_exists', 'Email já cadastrado.', array('status' => 403));
    }
}

function registrar_api_usuario_post() {
    register_rest_route('api', '/usuario', array(
        array(
            'methods' => WP_REST_Server::CREATABLE, //esse método é o POST nativo do WordPress
            'callback' => 'api_usuario_post',
        ),
    ));
}

//setando a função para ser chamada quando a API REST for inicializada
add_action('rest_api_init', 'registrar_api_usuario_post');

?>