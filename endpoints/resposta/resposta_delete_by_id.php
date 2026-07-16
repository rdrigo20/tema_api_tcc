<?php

// Função de callback para deletar um post por id (resposta)
function delete_resposta_by_id(WP_REST_Request $request) {
    // Força o ID a ser um número inteiro por segurança
    $id = (int) $request['id']; 

    // Puxa o post diretamente pelo ID
    $post = get_post($id);

    // Verifica se o post existe e se realmente é do tipo 'resposta'
    if (empty($post) || $post->post_type !== 'resposta') {
        return new WP_Error('no_post', 'resposta não encontrada', array('status' => 404));
    }

    // Verificar permissões (CUIDADO: não deixe isso comentado quando o site for ao ar)
    /*
    if (!current_user_can('delete_post', $post->ID)) {
        return new WP_Error('rest_forbidden', 'Você não tem permissão para deletar.', array('status' => 401));
    }
    */

    // Deleta o post. O 'true' força a exclusão definitiva (não vai para a lixeira)
    $resultado = wp_delete_post($post->ID, true);

    // Verifica se a função falhou ao tentar deletar
    if ($resultado === false || $resultado === null) {
        return new WP_Error('delete_failed', 'Erro ao tentar deletar a resposta no banco de dados.', array('status' => 500));
    }

    // Retorna a mensagem de sucesso com o ID que foi apagado
    return new WP_REST_Response(array('message' => 'resposta deletada com sucesso.', 'id_deletado' => $id), 200);
}

// Função para registrar o endpoint
function registrar_delete_resposta_by_id() {
    register_rest_route('api', '/resposta/(?P<id>\d+)', array(
        'methods'  => 'DELETE',
        'callback' => 'delete_resposta_by_id',
        
        // DICA DE SEGURANÇA: Bloqueia requisições de pessoas não logadas antes mesmo de rodar a função
        'permission_callback' => '__return_true' // Troque para 'is_user_logged_in' no futuro
    ));
}
add_action('rest_api_init', 'registrar_delete_resposta_by_id');

?>