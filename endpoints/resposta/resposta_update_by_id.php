<?php

// Função de callback para atualizar um post por id resposta
function update_resposta_by_id(WP_REST_Request $request) {
    // 1. Força o ID a ser um número para segurança
    $id = (int) $request['id']; 

    // 2. Busca o post diretamente
    $post = get_post($id);

    // 3. Valida se a resposta existe
    if (empty($post) || $post->post_type !== 'resposta' || $post->post_status !== 'publish') {
        return new WP_Error('no_post', 'resposta não encontrada', array('status' => 404));
    }

    // Verificar permissões (MUITO importante para rotas PUT/POST/DELETE)
    /*if (!current_user_can('edit_post', $post->ID)) {
        return new WP_Error('rest_forbidden', esc_html__('You cannot edit this post.'), array('status' => rest_authorization_required_code()));
    }*/

    // 4. Prepara o array base de atualização
    $updated_post = array(
        'ID' => $post->ID,
    );

    // 5. Verifica quais campos o JavaScript enviou e adiciona ao array
    if (isset($request['titulo'])) {
        $updated_post['post_title'] = sanitize_text_field($request['titulo']);
    }

    if (isset($request['conteudo'])) {
        // Para conteúdo longo (texto com quebras de linha), sanitize_textarea_field é melhor que sanitize_text_field
        $updated_post['post_content'] = sanitize_textarea_field($request['conteudo']); 
    }

    // 6. Prepara os Custom Fields (Metadados) recebidos pela requisição
    $meta_input = array();

    if (isset($request['id_conversa'])) {
        // Pega do $request, não do banco!
        $meta_input['id_conversa'] = sanitize_text_field($request['id_conversa']);
    }

    if (isset($request['id_pergunta'])) {
        $meta_input['id_pergunta'] = sanitize_text_field($request['id_pergunta']);
    }

    // Se a requisição enviou algum meta field novo, joga para o array de atualização
    if (!empty($meta_input)) {
        $updated_post['meta_input'] = $meta_input;
    }

    // 7. Executa a atualização
    // O parâmetro 'true' no final faz a função retornar um WP_Error caso algo dê errado no banco
    $resultado = wp_update_post($updated_post, true);

    // Se deu erro ao salvar
    if (is_wp_error($resultado)) {
        return new WP_Error('post_update_failed', 'Falha ao atualizar a resposta: ' . $resultado->get_error_message(), array('status' => 500));
    }

    // Se deu sucesso
    return new WP_REST_Response(array('message' => 'resposta atualizada com sucesso', 'id' => $post->ID), 200);
}

// Função para registrar o endpoint
function registrar_update_resposta_by_id() {
    register_rest_route('api', '/resposta/(?P<id>\d+)', array(
        'methods' => 'PUT',
        'callback' => 'update_resposta_by_id',
        // 'permission_callback' => function() { return current_user_can('edit_posts'); } // Quando quiser ligar a segurança, use isto!
    ));
}
add_action('rest_api_init', 'registrar_update_resposta_by_id');
?>