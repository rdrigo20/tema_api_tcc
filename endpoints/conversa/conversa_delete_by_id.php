<?php
// Função de callback para deletar um post por id conversa
function delete_conversa_by_id(WP_REST_Request $request) {
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
        return new WP_Error('no_post', 'conversa not found', array('status' => 404));
    }

    $post = $posts[0]; // Obter o primeiro (e único) post encontrado

    // Verificar permissões
    /*if (!current_user_can('delete_post', $post->ID)) {
        return new WP_Error('rest_forbidden', esc_html__('You cannot delete this post.'), array('status' => rest_authorization_required_code()));
    }*/

    // Deletar o post
    wp_delete_post($post->ID, true);

    return new WP_REST_Response(array('message' => 'conversa deletado'), 200);
}

// Função para registrar o endpoint
function registrar_delete_conversa_by_id() {
    register_rest_route('api', '/conversa/(?P<id>\d+)', array(
        'methods' => 'DELETE',
        'callback' => 'delete_conversa_by_id',
    ));
}
add_action('rest_api_init', 'registrar_delete_conversa_by_id');
?>