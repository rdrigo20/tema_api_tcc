<?php
//***********************************************************************************************************
// ACHO Q ESSA MERDA N VAI FUNCIONAR MAS TBM N PRECISA FUNCIONAR, ESTOU SIMPLESMENTE SEGUINDO O TUTORIAL DO ORIGAMID E TEM AQUELE BAGULHO DE TOKEN
// COMO O WP N ESTÁ COM O PLUGIN DO TOKE ISSO AQUI PROVAVELMENTE N VAI FUNCIONAR MAS FODA-SE TBM
// DPS CONSERTA SE QUISER */

function api_usuario_put($request) {

    //verificar se o usuário existe
    $user = wp_get_current_user();
    $user_id = $user->ID;

    //se foi maior q 0 significa que o usuário existe
    if ($user_id > 0){

        $email = sanitize_email($request['email']);
        $senha = $request['senha'];
        $nome = sanitize_text_field($request['nome']);
        $cpf = sanitize_text_field($request['cpf']);
        //NÃO ESTÁ FUNCIONANDO COMO DEVERIA
        // filter_var converte corretamente 'true', '1', 1 ou true para o booleano TRUE (e vice-versa para FALSE)
        $professor = filter_var($request['professor'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        // intval garante que o valor da turma será salvo estritamente como um número inteiro (a turma provavelmete sera o id do user do professor)
        $turma = isset($request['turma']) ? intval($request['turma']) : null;
        

        // isso aqui verifica se o e-mail existe e se o e-mail exite ele retorna o id do usuário
        $email_exists = email_exists($email);


        if (!$email_exists || !$email_exists === $user_id) {
            

            // Atualizar dados do usuário
            $response = array(
                'ID' => $user_id,
                'user_pass' => $senha,
                'user_email' => $email,
                'display_name' => $nome,
                'first_name' => $nome,
            );
            wp_update_user($response);

            // Atualizar metadados do usuário (tabela wp_usermeta)
            update_user_meta($user_id, 'cpf', $cpf);
            update_user_meta($user_id, 'professor', $professor);
            //só atualiza a turma se o valor for diferente de null, para não sobrescrever caso o usuário não tenha enviado esse campo
            if ($turma !== null) {
                update_user_meta($user_id, 'turma', $turma);
            }

            
        } else {
            return new WP_Error('email_exists', 'Email já cadastrado.', array('status' => 403));
        }
    } else {
        return new WP_Error('permissao', 'Usuário não possui permissão', array('status' => 401));
    }
    return rest_ensure_response($response);
}

function registrar_api_usuario_put() {
    register_rest_route('api', '/usuario', array(
        array(
            'methods' => WP_REST_Server::EDITABLE, //esse método é o PUT nativo do WordPress
            'callback' => 'api_usuario_put',
        ),
    ));
}

//setando a função para ser chamada quando a API REST for inicializada
add_action('rest_api_init', 'registrar_api_usuario_put');

?>