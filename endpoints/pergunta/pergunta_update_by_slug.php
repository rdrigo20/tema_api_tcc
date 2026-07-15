<?php


// NÃO GARANTO Q NENHUM DOS ENDPOINTS "BY_SLUG" FUNCIONEM




// Função de callback para atualizar um post por slug pergunta
function update_pergunta_by_slug(WP_REST_Request $request) {
    $slug = $request['slug']; // Obter o slug da requisição

    // Argumentos para buscar o post por slug
    $args = array(
        'name' => $slug,
        'post_type' => 'pergunta',
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
        //vai pegar os campos personalizados do custom post type
        'meta_input' => array(
            //'conteudo' => get_post_meta($post->ID, 'conteudo', true),
            'ordem' => get_post_meta($post->ID, 'ordem', true),
            'mostrar' => get_post_meta($post->ID, 'mostrar', true),
        ),
    );

    $post_id = wp_update_post($updated_post);

    if (is_wp_error($post_id)) {
        return new WP_Error('post_update_failed', 'Failed to update post', array('status' => 500));
    }

    return new WP_REST_Response(array('message' => 'pergunta updated'), 200);
}

// Função para registrar o endpoint
function registrar_update_pergunta_by_slug() {
    register_rest_route('api', '/pergunta/slug/(?P<slug>[a-zA-Z0-9-]+)', array(
        'methods' => 'PUT',
        'callback' => 'update_pergunta_by_slug',
    ));
}
add_action('rest_api_init', 'registrar_update_pergunta_by_slug');
?>