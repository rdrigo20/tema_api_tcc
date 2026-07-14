<?php
// Função de callback para atualizar um post por id conversa
function update_conversa_by_id(WP_REST_Request $request) {
    $id = $request['id']; // Obter o id da requisição

    // Argumentos para buscar o post por id
    $args = array(
        'ID' => $id,
        'post_type' => 'conversa',
        'post_status' => 'publish',
        'numberposts' => 1, // Obter apenas um post
    );

    $posts = get_posts($args);

    if (empty($posts)) {
        return new WP_Error('no_post', 'event not found', array('status' => 404));
    }

    $post = $posts[0]; // Obter o primeiro (e único) post encontrado

    // Verificar permissões
    /*if (!current_user_can('edit_post', $post->ID)) {
        return new WP_Error('rest_forbidden', esc_html__('You cannot edit this post.'), array('status' => rest_authorization_required_code()));
    }*/

    // Atualizar o post
    $updated_post = array(
        'ID' => $post->ID,
        'post_title' => sanitize_text_field($request['titulo']),
        'post_content' => sanitize_text_field($request['conteudo']),
    );

    $post_id = wp_update_post($updated_post);

    if (is_wp_error($post_id)) {
        return new WP_Error('post_update_failed', 'Failed to update post', array('status' => 500));
    }

    return new WP_REST_Response(array('message' => 'conversa updated'), 200);
}

// Função para registrar o endpoint
function registrar_update_conversa_by_id() {
    register_rest_route('api', '/conversa/(?P<id>\d+)', array(
        'methods' => 'PUT',
        'callback' => 'update_conversa_by_id',
    ));
}
add_action('rest_api_init', 'registrar_update_conversa_by_id');
?>