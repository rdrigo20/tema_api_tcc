<?php

// Função de callback para atualizar um atalho_service existente
function update_atalho_service_by_id(WP_REST_Request $request) {
    // 1. Pega o ID da URL e garante que é um número inteiro
    $id = (int) $request['id']; 

    // 2. Busca o post no banco de dados
    $post = get_post($id);

    // 3. Verifica se o post existe e se é do tipo correto ('atalho_service')
    if (empty($post) || $post->post_type !== 'atalho_service') {
        return new WP_Error('no_post', 'Atalho de serviço não encontrado.', array('status' => 404));
    }

    // 4. Cria o array obrigatório para o wp_update_post
    $updated_post = array(
        'ID' => $post->ID,
    );

    // 5. Atualiza o Título e o Conteúdo (se enviados)
    if (isset($request['titulo'])) {
        $updated_post['post_title'] = sanitize_text_field($request['titulo']);
    }
    if (isset($request['conteudo'])) {
        $updated_post['post_content'] = sanitize_textarea_field($request['conteudo']);
    }

    $post_id = wp_update_post($updated_post, true);

    if (is_wp_error($post_id)) {
        return $post_id; 
    }

    // 6. Atualiza os Meta Dados específicos de Serviço (apenas os campos enviados na requisição)
    if (isset($request['nome_atalho_service'])) {
        update_post_meta($post_id, 'nome_atalho_service', sanitize_text_field($request['nome_atalho_service']));
    }
    if (isset($request['ip_atalho_service'])) {
        update_post_meta($post_id, 'ip_atalho_service', sanitize_text_field($request['ip_atalho_service']));
    }
    if (isset($request['port_atalho_service'])) {
        update_post_meta($post_id, 'port_atalho_service', (int) $request['port_atalho_service']);
    }
    if (isset($request['chain_atalho_service'])) {
        update_post_meta($post_id, 'chain_atalho_service', sanitize_text_field($request['chain_atalho_service']));
    }
    if (isset($request['protocol_atalho_service'])) {
        update_post_meta($post_id, 'protocol_atalho_service', sanitize_text_field($request['protocol_atalho_service']));
    }
    if (isset($request['allowed_atalho_service'])) {
        update_post_meta($post_id, 'allowed_atalho_service', sanitize_text_field($request['allowed_atalho_service']));
    }
    
    return new WP_REST_Response(array(
        'message' => 'Atalho de serviço atualizado com sucesso.', 
        'id'      => $post_id
    ), 200);
}

// Registra a rota PUT para /api/atalho_service/{id}
function registrar_update_atalho_service_by_id() {
    register_rest_route('api', '/atalho_service/(?P<id>\d+)', array(
        'methods'             => 'PUT',
        'callback'            => 'update_atalho_service_by_id',
        'permission_callback' => '__return_true'
    ));
}
add_action('rest_api_init', 'registrar_update_atalho_service_by_id');

?>