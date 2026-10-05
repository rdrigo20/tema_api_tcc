<?php

function atalho_ip_create($request) {
    
    $user_id = isset($request['user_id']) ? (int) $request['user_id'] : 0;

    if (!$user_id) {
        return new WP_Error('falta_user', 'ID do usuário não fornecido.', array('status' => 400));
    }

    $titulo = sanitize_text_field($request['titulo']);
    $conteudo = sanitize_textarea_field($request['conteudo']);

    $nome_atalho_ip = $request['nome_atalho_ip']; //nome do atalho vai ser a partir dele q a IA vai indentificar o atalho
    $ip_atalho_ip = $request['ip_atalho_ip']; 
    $escopo_atalho_ip = $request['escopo_atalho_ip']; //escopo pessoal = 0, escopo global = 1

    // 2. Cria o post já com os metadados
    $response = array(
        'post_author'  => $user_id,
        'post_type'    => 'atalho_ip',
        'post_title'   => $titulo,
        'post_status'  => 'publish',
        'post_content' => $conteudo,
        'meta_input'   => array(
            // wp_json_encode transforma o array em string JSON para salvar no banco
            // wp_slash protege contra injeções SQL
            'nome_atalho_ip' => $nome_atalho_ip,
            'ip_atalho_ip' => $ip_atalho_ip,
            'escopo_atalho_ip' => $escopo_atalho_ip
        )
    );

    $atalho_ip_id = wp_insert_post($response);

    if (is_wp_error($atalho_ip_id)) {
        return $atalho_ip_id;
    }

    return rest_ensure_response(array(
        'status'      => 'sucesso',
        'atalho_ip_id' => $atalho_ip_id
    ));
}

function registrar_atalho_ip_create() {
    register_rest_route('api', '/atalho_ip', array(
        array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => 'atalho_ip_create',
            'permission_callback' => '__return_true'
        ),
    ));
}

add_action('rest_api_init', 'registrar_atalho_ip_create');

?>