<?php

// Função de callback para obter um post por id
function get_conversa_by_id(WP_REST_Request $request) {
    // 1. Converte o ID da URL para número inteiro (Segurança)
    $id = (int) $request['id']; 

    // 2. Busca o post no banco de dados
    $post = get_post($id);

    // 3. Verifica se o post existe, se é uma 'conversa' e se está publicado
    if (empty($post) || $post->post_type !== 'conversa' || $post->post_status !== 'publish') {
        return new WP_Error('no_post', 'Conversa não encontrada', array('status' => 404));
    }

    // 4. RESGATA O JSON DA MEMÓRIA
    // Busca a string salva no post_meta
    $config_string = get_post_meta($post->ID, 'config_atual', true);
    
    // Decodifica a string para que ela vá como um objeto real para o Frontend, 
    // ou cria um array vazio caso dê erro na leitura.
    $config_json = !empty($config_string) ? json_decode($config_string, true) : array();

    // 5. Monta a resposta final juntando os dados do Post e o Meta
    $post_data = array(
        'id'       => $post->ID,
        'title'    => $post->post_title,
        'content'  => $post->post_content, 
        'author'   => get_the_author_meta('display_name', $post->post_author),
        'date'     => $post->post_date,
        'meta'     => array(
            'config_atual' => $config_json // O Frontend precisa muito desta chave!
        )
    );

    return new WP_REST_Response($post_data, 200);
}

// Registra a rota GET para buscar 1 conversa específica
function registrar_get_conversa_by_id() {
    register_rest_route('api', '/conversa/(?P<id>\d+)', array(
        'methods'  => 'GET',
        'callback' => 'get_conversa_by_id',
        'permission_callback' => '__return_true' // Permite o acesso
    ));
}
add_action('rest_api_init', 'registrar_get_conversa_by_id');

?>