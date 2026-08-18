<?php

function conversa_create($request) {
    
    $user_id = isset($request['user_id']) ? (int) $request['user_id'] : 0;

    if (!$user_id) {
        return new WP_Error('falta_user', 'ID do usuário não fornecido.', array('status' => 400));
    }

    $titulo = sanitize_text_field($request['titulo']);
    $conteudo = sanitize_textarea_field($request['conteudo']);

    // 1. O ESQUELETO PADRÃO DA REDE (Vazio)
    $config_padrao = array(
        "interfaces" => array("wan" => null, "lan" => null),
        "lan_network" => null,
        "policies" => array("input" => "ACCEPT", "forward" => "ACCEPT", "output" => "ACCEPT"),
        "nat" => false,
        "lan_free_internet" => false,
        "connection_states" => array(),
        "drop_invalid" => false,
        "services" => array(),
        "blocked_ips" => array()
    );

    // 2. Cria o post já com os metadados
    $response = array(
        'post_author'  => $user_id,
        'post_type'    => 'conversa',
        'post_title'   => $titulo,
        'post_status'  => 'publish',
        'post_content' => $conteudo,
        'meta_input'   => array(
            // wp_json_encode transforma o array em string JSON para salvar no banco
            // wp_slash protege contra injeções SQL
            'config_atual' => wp_slash(wp_json_encode($config_padrao)),
            
            // Criamos um array vazio para ir guardando as falas do chat futuramente
            //'historico_chat' => wp_slash(wp_json_encode(array())) 
        )
    );

    $conversa_id = wp_insert_post($response);

    if (is_wp_error($conversa_id)) {
        return $conversa_id;
    }

    return rest_ensure_response(array(
        'status'      => 'sucesso',
        'conversa_id' => $conversa_id
    ));
}

function registrar_conversa_create() {
    register_rest_route('api', '/conversa', array(
        array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => 'conversa_create',
            'permission_callback' => '__return_true'
        ),
    ));
}

add_action('rest_api_init', 'registrar_conversa_create');

?>