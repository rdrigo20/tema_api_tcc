<?php
// Função de callback para atualizar um post do tipo conversa pelo seu ID
function update_conversa_by_id(WP_REST_Request $request) {
    // 1. SEGURANÇA BÁSICA: Pega o ID da URL e força a ser um número inteiro
    $id = (int) $request['id']; 

    // 2. Busca o post diretamente pelo ID no banco de dados do WordPress
    $post = get_post($id);

    // 3. VALIDAÇÃO: Verifica se o post existe e se é realmente uma 'conversa'
    // Se tentarem passar o ID de uma página ou post normal, a API bloqueia.
    if (empty($post) || $post->post_type !== 'conversa') {
        return new WP_Error('no_post', 'Conversa não encontrada', array('status' => 404));
    }

    // 4. PREPARAÇÃO DOS DADOS: Cria o array de atualização
    // O ID é o único campo estritamente obrigatório para o wp_update_post saber quem atualizar
    $updated_post = array(
        'ID' => $post->ID,
    );

    // 5. ATUALIZAÇÃO DINÂMICA DO TÍTULO
    // Verifica se o frontend enviou o campo "titulo" no JSON. 
    // Se enviou, limpa contra injeções de código (sanitize_text_field) e adiciona ao array de update.
    if (isset($request['titulo'])) {
        $updated_post['post_title'] = sanitize_text_field($request['titulo']);
    }

    // 6. ATUALIZAÇÃO DINÂMICA DO CONTEÚDO
    // Mesma lógica do título, mas usando textarea para permitir múltiplas linhas caso exista.
    if (isset($request['conteudo'])) {
        $updated_post['post_content'] = sanitize_textarea_field($request['conteudo']);
    }

    // 7. EXECUTA A ATUALIZAÇÃO NO BANCO DE DADOS
    // O segundo parâmetro (true) faz a função retornar um WP_Error legível se algo falhar no MySQL.
    $post_id = wp_update_post($updated_post, true);

    if (is_wp_error($post_id)) {
        return $post_id; 
    }

    // 8. SUCESSO: Retorna Status 200 OK informando que deu certo.
    return new WP_REST_Response(array(
        'message' => 'Conversa atualizada com sucesso', 
        'id' => $post_id
    ), 200);
}

// Registra a rota da API no WordPress
function registrar_update_conversa_by_id() {
    register_rest_route('api', '/conversa/(?P<id>\d+)', array(
        'methods' => 'PUT', // PUT é o padrão RESTful para atualizações/edições
        'callback' => 'update_conversa_by_id',
        'permission_callback' => '__return_true' // Libera o acesso (ideal colocar auth no futuro)
    ));
}
add_action('rest_api_init', 'registrar_update_conversa_by_id');
?>