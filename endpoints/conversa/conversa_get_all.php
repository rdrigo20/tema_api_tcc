<?php
// Função de callback para recuperar os conversa
function get_all_conversa(WP_REST_Request $request) {
    $args = array(
        'post_type' => 'conversa', // Tipo de post
        'post_status' => 'publish',
        'numberposts' => -1, // Obter todos os posts
    );

    $posts = get_posts($args);

    if (empty($posts)) {
        return new WP_Error('no_posts', 'No conversa found', array('status' => 404));
    }

    // Preparar os dados para a resposta
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
        );
        $data[] = $post_data;
    }
    return new WP_REST_Response($data, 200);
}

// Função para registrar o endpoint
function registrar_get_all_conversa() {
    register_rest_route('api', '/conversa', array(
        'methods' => 'GET',
        'callback' => 'get_all_conversa',
    ));
}
add_action('rest_api_init', 'registrar_get_all_conversa');
?>