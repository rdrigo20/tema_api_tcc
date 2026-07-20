<?php

function resultado_create($request) {
    
    //isso aqui n é 100% seguro e se n der certo ele vai por id = 0 e vai dar merda
    $user = wp_get_current_user();
    $user_id = $user->ID;

    //pega os dados q seram os campos personalizados do custom post type + o título q eu acho q é obrigatório
    $titulo = sanitize_text_field($request['titulo']);
    $conteudo = sanitize_textarea_field($request['conteudo']);
    $id_conversa = $request['id_conversa'];


    $response = array(
        'post_author' => $user_id,
        'post_type' => 'resultado',
        'post_title' => $titulo,
        'post_status' => 'publish',
        'post_content' => $conteudo,
        //meta_input é um array que permite salvar os campos personalizados do custom post type
        'meta_input' => array(
            'id_conversa' => $id_conversa,
        ),
    );

    $produto_id = wp_insert_post($response);
    $response['id'] = get_post_field('post_name', $produto_id);


    return rest_ensure_response($response);
}

function registrar_resultado_create() {
    register_rest_route('api', '/resultado', array(
        array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => 'resultado_create',
        ),
    ));
}

add_action('rest_api_init', 'registrar_resultado_create');

?>