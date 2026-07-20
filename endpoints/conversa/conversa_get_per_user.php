<?php

// Função de callback para obter todas as conversas de um usuário
function conversa_get_per_user(WP_REST_Request $request) {
    // 1. Pega o ID do usuário que virá na URL da requisição
    $user_id = (int) $request['user_id']; 

    // (Opcional) Verifica se o usuário realmente existe no banco
    if (!get_userdata($user_id)) {
        return new WP_Error('invalid_user', 'Usuário não encontrado.', array('status' => 404));
    }

    // 2. Monta os filtros da busca
    $args = array(
        'post_type'      => 'conversa',
        'post_status'    => 'publish',
        'author'         => $user_id,    // O PULO DO GATO: Filtra para trazer só os posts deste usuário
        'posts_per_page' => -1,          // -1 significa "trazer todos". Se quiser um limite, mude para 20, 50, etc.
        'orderby'        => 'date',      // Ordena pela data
        'order'          => 'DESC',      // Traz as configurações mais recentes primeiro
    );

    // 3. Executa a busca no banco de dados
    $posts = get_posts($args);

    // 4. Formata os dados
    $historico = array();

    // Se ele não tiver nenhuma conversa, simplesmente devolvemos um array vazio (não é um erro)
    if (!empty($posts)) {
        foreach ($posts as $post) {
            $historico[] = array(
                'id'       => $post->ID,
                'titulo'   => $post->post_title,
                'conteudo' => $post->post_content,
                // A função get_the_date já formata a data no padrão brasileiro bonitinho
                'data'     => get_the_date('d/m/Y', $post->ID) 
            );
        }
    }

    // 5. Retorna o array pronto em formato JSON
    return new WP_REST_Response($historico, 200);
}

// Função para registrar o endpoint
function registrar_conversa_get_per_user() {
    // A rota será algo como: http://localhost/seu-wp/wp-json/api/conversa/usuario/5
    register_rest_route('api', '/conversa/usuario/(?P<user_id>\d+)', array(
        'methods'  => 'GET',
        'callback' => 'conversa_get_per_user',
    ));
}
add_action('rest_api_init', 'registrar_conversa_get_per_user');

?>