<?php

function pergunta_create($request) {
    
    //pega e registra qual usuário está fazendo a requisição
    $user = wp_get_current_user();
    $user_id = $user->ID;

    //pega os dados q seram os campos personalizados do custom post type + o título q eu acho q é obrigatório
    $titulo = sanitize_text_field($request['titulo']);
    $conteudo = sanitize_text_field($request['conteudo']);
    $ordem = $request['ordem'];


    $response = array(
        'post_author' => $user_id,
        'post_type' => 'pergunta',
        'post_title' => $titulo,
        'post_status' => 'publish',
        'post_content' => $conteudo,
        //meta_input é um array que permite salvar os campos personalizados do custom post type
        'meta_input' => array(
            'ordem' => $ordem,
            'mostrar' => true, //esse campo é pra eu poder controlar se a pergunta vai ser mostrada ou não, por padrão ele vai ser true
        ),
    );

    $produto_id = wp_insert_post($response);
    $response['id'] = get_post_field('post_name', $produto_id);


    return rest_ensure_response($response);
}

function registrar_pergunta_create() {
    register_rest_route('api', '/pergunta', array(
        array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => 'pergunta_create',
        ),
    ));
}

add_action('rest_api_init', 'registrar_pergunta_create');

?>