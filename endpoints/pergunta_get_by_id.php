<?php

// Função de callback para obter um post por id
function get_pergunta_by_id(WP_REST_Request $request) {
    $id = $request['id']; // Obter o id da requisição

    // Argumentos para buscar o post por id
    $args = array(
        'ID' => $id,
        'post_type' => 'pergunta',
        'post_status' => 'publish',
        'numberposts' => 1, // Obter apenas um post
    );

    $posts = get_posts($args);

    //verifica se post existe, se n existir retorna um erro 404
    if (empty($posts)) {
        return new WP_Error('no_post', 'pergunta not found', array('status' => 404));
    }

    $post = $posts[0]; // Obter o primeiro (e único) post encontrado

    // Preparar os dados para a resposta
    $post_data = array(
        'id' => $post->ID,
        'title' => $post->post_title,
        'content' => $post->post_content, //se n for o post_content o bagulho n vai
        'author' => get_the_author_meta('display_name', $post->post_author),
        'date' => $post->post_date,
        'slug' => $post->post_name,
        //vai pegar os campos personalizados do custom post type
        'meta' => array(
            'conteudo' => get_post_meta($post->ID, 'conteudo', true),
            'ordem' => get_post_meta($post->ID, 'ordem', true),
            'mostrar' => get_post_meta($post->ID, 'mostrar', true),
        ),
    );

    return new WP_REST_Response($post_data, 200);
}

// Função para registrar o endpoint
function registrar_get_pergunta_by_id() {
    register_rest_route('api', '/pergunta/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'get_pergunta_by_id',
    ));
}
add_action('rest_api_init', 'registrar_get_pergunta_by_id');

?>