<?php

// Função antiga q n está mais certa, mas ela pegava todos os atalhos_ip independentemente do usuário



// Função de callback para recuperar os atalho_service
function get_all_atalho_service(WP_REST_Request $request) {
    $args = array(
        'post_type' => 'atalho_service', // Tipo de post
        'post_status' => 'publish',
        'numberposts' => -1, // Obter todos os posts
    );

    $posts = get_posts($args);

    if (empty($posts)) {
        return new WP_Error('no_posts', 'No atalho_service found', array('status' => 404));
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
            'author_id' => (int) $post->post_author, // <--- (ID numérico do criador)
            'date' => $post->post_date,
            'modified' => $post->post_modified, //se nada for modificado, vai ser a mesma data do post
            'slug' => $post->post_name,
            'meta' => array(
                'nome_atalho_service' => get_post_meta($post->ID, 'nome_atalho_service', true),
                'ip_atalho_service' => get_post_meta($post->ID, 'ip_atalho_service', true),
                'escopo_atalho_service' => get_post_meta($post->ID, 'escopo_atalho_service', true),
                //'historico_chat' => get_post_meta($post->ID, 'historico_chat', true),
            ),
        );
        $data[] = $post_data;
    }
    return new WP_REST_Response($data, 200);
}

// Função para registrar o endpoint
function registrar_get_all_atalho_service() {
    register_rest_route('api', '/atalho_service', array(
        'methods' => 'GET',
        'callback' => 'get_all_atalho_service',
    ));
}
add_action('rest_api_init', 'registrar_get_all_atalho_service');
?>