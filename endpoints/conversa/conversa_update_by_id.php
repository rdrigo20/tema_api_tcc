<?php
// Função de callback para atualizar um post por id conversa
function update_conversa_by_id(WP_REST_Request $request) {
    // 1. Pega o ID e garante que é um número inteiro
    $id = (int) $request['id']; 

    // 2. Busca o post diretamente pelo ID
    $post = get_post($id);

    // 3. Verifica se existe e se é do tipo 'conversa'
    if (empty($post) || $post->post_type !== 'conversa') {
        return new WP_Error('no_post', 'Conversa não encontrada', array('status' => 404));
    }

    // Verificar permissões (Deixei comentado como no seu código original)
    /*if (!current_user_can('edit_post', $post->ID)) {
        return new WP_Error('rest_forbidden', esc_html__('You cannot edit this post.'), array('status' => rest_authorization_required_code()));
    }*/

    // 4. Cria o array de atualização apenas com o ID (obrigatório)
    $updated_post = array(
        'ID' => $post->ID,
    );

    // 5. Só atualiza o título SE ele tiver sido enviado na requisição
    if (isset($request['titulo'])) {
        $updated_post['post_title'] = sanitize_text_field($request['titulo']);
    }

    // 6. Só atualiza o conteúdo SE ele tiver sido enviado (usando textarea para manter quebras de linha)
    if (isset($request['conteudo'])) {
        $updated_post['post_content'] = sanitize_textarea_field($request['conteudo']);
    }

    // 7. Faz a atualização. O segundo parâmetro (true) força o retorno de um WP_Error se falhar
    $post_id = wp_update_post($updated_post, true);

    if (is_wp_error($post_id)) {
        // Retorna o erro exato que o WordPress encontrou
        return $post_id; 
    }

    // 8. Resposta de sucesso
    return new WP_REST_Response(array('message' => 'Conversa atualizada com sucesso', 'id' => $post_id), 200);
}

// Função para registrar o endpoint
function registrar_update_conversa_by_id() {
    register_rest_route('api', '/conversa/(?P<id>\d+)', array(
        'methods' => 'PUT', // ou WP_REST_Server::EDITABLE
        'callback' => 'update_conversa_by_id',
        'permission_callback' => '__return_true' // Necessário para não dar erro 401 ao testar o PUT
    ));
}
add_action('rest_api_init', 'registrar_update_conversa_by_id');
?>