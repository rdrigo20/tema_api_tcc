<?php

// Função de callback para deletar um post por id pergunta
function delete_pergunta_by_id(WP_REST_Request $request) {
    // Força o ID a ser um número inteiro por segurança
    $id = (int) $request['id']; 

    // Puxa o post diretamente pelo ID
    $post = get_post($id);

    // Verifica se o post existe e se realmente é do tipo 'pergunta'
    if (empty($post) || $post->post_type !== 'pergunta') {
        return new WP_Error('no_post', 'Pergunta não encontrada', array('status' => 404));
    }

    // Tenta deletar o post (true = força exclusão permanente, pulando a lixeira)
    $deleted = wp_delete_post($post->ID, true);

    // Verifica se o WordPress conseguiu deletar
    if (!$deleted) {
        return new WP_Error('delete_failed', 'Erro interno ao tentar deletar a pergunta', array('status' => 500));
    }

    // Retorna sucesso
    return new WP_REST_Response(array(
        'message' => 'Pergunta deletada com sucesso',
        'deleted_id' => $id
    ), 200);
}

// Função para registrar o endpoint
function registrar_delete_pergunta_by_id() {
    register_rest_route('api', '/pergunta/(?P<id>\d+)', array(
        'methods' => 'DELETE',
        'callback' => 'delete_pergunta_by_id',
        
        // PERMISSÃO: "__return_true" deixa qualquer um deletar. 
        // Use isso APENAS para o protótipo local. 
        // Em produção, troque para uma função que verifique se o usuário está logado.
        'permission_callback' => '__return_true', 
    ));
}
add_action('rest_api_init', 'registrar_delete_pergunta_by_id');

?>