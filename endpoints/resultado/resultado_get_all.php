<?php
// Função de callback para recuperar os resultado
function get_all_resultado(WP_REST_Request $request) {
    $args = array(
        'post_type' => 'resultado', // Tipo de post
        'post_status' => 'publish',
        'numberposts' => -1, // Obter todos os posts
    );

    $posts = get_posts($args);

    if (empty($posts)) {
        return new WP_Error('no_posts', 'No resultado found', array('status' => 404));
    }

    // Preparar os dados para a resultado
    $data = array();
    // Loop pelos posts
    foreach ($posts as $post) {
        $post_data = array(
            'id' => $post->ID, //pega o ID do post da vez
            'title' => $post->post_title,
            'content' => $post->post_content, //se n for o post_content o bagulho n vai
            'author' => get_the_author_meta('display_name', $post->post_author),
            'date' => $post->post_date,
            'modified' => $post->post_modified, //se nada for modificado, vai ser a mesma data do post
            'slug' => $post->post_name,
            //vai pegar os campos personalizados do custom post type
            'meta' => array(
                //'conteudo' => get_post_meta($post->ID, 'conteudo', true),
                'id_conversa' => get_post_meta($post->ID, 'id_conversa', true),
            ),
        );
        $data[] = $post_data;
    }
    return new WP_REST_Response($data, 200);
}

// Função para registrar o endpoint
function registrar_get_all_resultado() {
    register_rest_route('api', '/resultado', array(
        'methods' => 'GET',
        'callback' => 'get_all_resultado',
    ));
}
add_action('rest_api_init', 'registrar_get_all_resultado');
?>