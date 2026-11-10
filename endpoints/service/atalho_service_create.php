<?php

function atalho_service_create($request) {
    
    $user_id = isset($request['user_id']) ? (int) $request['user_id'] : 0;

    if (!$user_id) {
        return new WP_Error('falta_user', 'ID do usuário não fornecido.', array('status' => 400));
    }

    $titulo = sanitize_text_field($request['titulo']);
    $conteudo = sanitize_textarea_field($request['conteudo']);

    $nome_atalho_service = $request['nome_atalho_service']; //nome do atalho vai ser a partir dele q a IA vai indentificar o atalho
    $ip_atalho_service = $request['ip_atalho_service']; 
    $escopo_atalho_service = $request['escopo_atalho_service']; //escopo pessoal = 0, escopo global = 1
    $port_atalho_service = $request['port_atalho_service']; 
    $chain_atalho_service = $request['chain_atalho_service']; 
    $protocol_atalho_service = $request['protocol_atalho_service']; 
    $allowed_atalho_service = $request['allowed_atalho_service']; 

    // 2. Cria o post já com os metadados
    $response = array(
        'post_author'  => $user_id,
        'post_type'    => 'atalho_service',
        'post_title'   => $titulo,
        'post_status'  => 'publish',
        'post_content' => $conteudo,
        'meta_input'   => array(
            // wp_json_encode transforma o array em string JSON para salvar no banco
            // wp_slash protege contra injeções SQL
            'nome_atalho_service' => $nome_atalho_service,
            'ip_atalho_service' => $ip_atalho_service,
            'escopo_atalho_service' => $escopo_atalho_service,
            'port_atalho_service' => $port_atalho_service,
            'chain_atalho_service' => $chain_atalho_service,
            'protocol_atalho_service' => $protocol_atalho_service,
            'allowed_atalho_service' => $allowed_atalho_service
        )
    );

    $atalho_service_id = wp_insert_post($response);

    if (is_wp_error($atalho_service_id)) {
        return $atalho_service_id;
    }

    return rest_ensure_response(array(
        'status'      => 'sucesso',
        'atalho_service_id' => $atalho_service_id
    ));
}

function registrar_atalho_service_create() {
    register_rest_route('api', '/atalho_service', array(
        array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => 'atalho_service_create',
            'permission_callback' => '__return_true'
        ),
    ));
}

add_action('rest_api_init', 'registrar_atalho_service_create');

?>