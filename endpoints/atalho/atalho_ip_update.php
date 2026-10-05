<?php

// Função de callback para atualizar um atalho_ip existente
function update_atalho_ip_by_id(WP_REST_Request $request) {
    // 1. Pega o ID da URL e garante que é um número inteiro
    $id = (int) $request['id']; 

    // 2. Busca o post no banco de dados
    $post = get_post($id);

    // 3. Verifica se o post existe e se é do tipo correto
    if (empty($post) || $post->post_type !== 'atalho_ip') {
        return new WP_Error('no_post', 'Atalho de IP não encontrado.', array('status' => 404));
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

    // 6. Atualiza os Meta Dados (Os campos personalizados do atalho)
    if (isset($request['nome_atalho_ip'])) {
        update_post_meta($post_id, 'nome_atalho_ip', sanitize_text_field($request['nome_atalho_ip']));
    }
    if (isset($request['ip_atalho_ip'])) {
        update_post_meta($post_id, 'ip_atalho_ip', sanitize_text_field($request['ip_atalho_ip']));
    }
    if (isset($request['escopo_atalho_ip'])) {
        // Valida se o escopo enviado é um número inteiro (0 ou 1)
        update_post_meta($post_id, 'escopo_atalho_ip', (int) $request['escopo_atalho_ip']);
    }

    return new WP_REST_Response(array('message' => 'Atalho de IP atualizado com sucesso.', 'id' => $post_id), 200);
}

// Registra a rota PUT
function registrar_update_atalho_ip_by_id() {
    register_rest_route('api', '/atalho_ip/(?P<id>\d+)', array(
        'methods' => 'PUT',
        'callback' => 'update_atalho_ip_by_id',
        'permission_callback' => '__return_true'
    ));
}
add_action('rest_api_init', 'registrar_update_atalho_ip_by_id');

?>