

<?php

// Função de callback para obter um post por id
function get_pergunta_by_id(WP_REST_Request $request) {
    // É uma boa prática forçar que o ID seja um número inteiro por segurança
    $id = (int) $request['id']; 

    // Puxa o post diretamente pelo ID (muito mais rápido que get_posts)
    $post = get_post($id);

    // Verifica se o post existe, se é realmente uma 'pergunta' e se está publicado
    if (empty($post) || $post->post_type !== 'pergunta' || $post->post_status !== 'publish') {
        return new WP_Error('no_post', 'Pergunta não encontrada', array('status' => 404));
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
        // Pega os campos personalizados (meta) do post
        'meta'     => array(
            'conteudo' => get_post_meta($post->ID, 'conteudo', true),
            'ordem'    => get_post_meta($post->ID, 'ordem', true),
            'mostrar'  => get_post_meta($post->ID, 'mostrar', true),
        ),
    );

    return new WP_REST_Response($post_data, 200);
}

// Função para registrar o endpoint
function registrar_get_pergunta_by_id() {
    register_rest_route('api', '/pergunta/(?P<id>\d+)', array(
        'methods'  => 'GET',
        'callback' => 'get_pergunta_by_id',
    ));
}
add_action('rest_api_init', 'registrar_get_pergunta_by_id');

?>