<?php

// Função de callback para obter um post por id
function get_conversa_by_id(WP_REST_Request $request) {
    // Converte o ID para número inteiro por segurança
    $id = (int) $request['id']; 

    // Puxa o post diretamente pelo ID
    $post = get_post($id);

    // Verifica se o post existe, se é realmente uma 'conversa' e se está publicado
    if (empty($post) || $post->post_type !== 'conversa' || $post->post_status !== 'publish') {
        return new WP_Error('no_post', 'Conversa não encontrada', array('status' => 404));
    }

    // Preparar os dados para a resposta
    $post_data = array(
        'id'       => $post->ID,
        'title'    => $post->post_title,
        'content'  => $post->post_content, 
        'author'   => get_the_author_meta('display_name', $post->post_author),
        'date'     => $post->post_date,
        'modified' => $post->post_modified, 
        'slug'     => $post->post_name,
    );

    return new WP_REST_Response($post_data, 200);
}

// Função para registrar o endpoint
function registrar_get_conversa_by_id() {
    register_rest_route('api', '/conversa/(?P<id>\d+)', array(
        'methods'  => 'GET',
        'callback' => 'get_conversa_by_id',
    ));
}
add_action('rest_api_init', 'registrar_get_conversa_by_id');

?>