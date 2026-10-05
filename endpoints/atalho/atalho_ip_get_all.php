<?php

// Função para buscar atalhos de um usuário específico
function get_atalhos_by_usuario(WP_REST_Request $request) {
    $user_id = (int) $request['user_id'];

    if (!$user_id) {
        return new WP_Error('falta_user', 'ID do usuário não fornecido.', array('status' => 400));
    }

    // Busca posts onde o autor é o usuário logado OU o escopo é global (1)
    $args = array(
        'post_type' => 'atalho_ip',
        'post_status' => 'publish',
        'numberposts' => -1,
        'meta_query' => array(
            'relation' => 'OR',
            array(
                'key' => 'escopo_atalho_ip',
                'value' => '1', // 1 = Global (visível para todos)
                'compare' => '='
            ),
            // Para filtrar pelo autor, usamos o parâmetro padrão 'author' fora da meta_query
        ),
        'author' => $user_id // Busca os pessoais criados por este usuário
    );

    // DICA: Se você quiser buscar APENAS os do usuário e ignorar os globais, 
    // basta remover a 'meta_query' inteira e deixar apenas o 'author'.

    $posts = get_posts($args);

    if (empty($posts)) {
        return new WP_REST_Response(array(), 200); // Retorna array vazio em vez de erro 404 para facilitar no JS
    }

    $data = array();
    
    foreach ($posts as $post) {
        $post_data = array(
            'id' => $post->ID,
            'title' => $post->post_title,
            'meta' => array(
                // Resgata os metadados exatos que foram salvos na criação
                'nome_atalho_ip' => get_post_meta($post->ID, 'nome_atalho_ip', true),
                'ip_atalho_ip' => get_post_meta($post->ID, 'ip_atalho_ip', true),
                'escopo_atalho_ip' => get_post_meta($post->ID, 'escopo_atalho_ip', true),
            ),
        );
        $data[] = $post_data;
    }
    return new WP_REST_Response($data, 200);
}

function registrar_get_atalhos_by_usuario() {
    register_rest_route('api', '/atalho_ip/usuario/(?P<user_id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'get_atalhos_by_usuario',
        'permission_callback' => '__return_true'
    ));
}
add_action('rest_api_init', 'registrar_get_atalhos_by_usuario');

?>