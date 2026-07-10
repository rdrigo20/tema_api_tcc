<?php

// Registra o tipo de post 'conversa'
function registrar_cpt_conversa() {
    //função do WP para registrar um custom post type
    register_post_type('conversa',array(
        'label' => 'conversa',
        'description' => 'conversa',
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true, //n tinha no video mas ele faz q esse CPT apareça no menu lateral do WP, em teoria ele é inútil pois por padrão quando o 'show_ui' é true ele já aparece no menu, mas eu deixei pq n sei se em algum momento isso vai mudar
        'show_in_rest' => true, //esse parâmetro n tinha no vídeo mas aparentemente é o q faz com q o CPT seja acessível pela API nativa do WP e permita com q eu acesse esse CPT via JavaScript com fetch
        'capability_type' => 'post',
        'rewrite' => array('slug' => 'conversa', 'with_front' => true),
        'query_var' => true,
        //é esse 'custom-fields' no array q me pertime criar campos personalizados no WP
        'supports' => array('custom-fields', 'author', 'title', 'editor'), //no video n tinha o 'editor' no array mas ele aparentemente coloca uma caixa de texto grande no WP
        'publicly_queryable' => true,
    ));
}
add_action('init', 'registrar_cpt_conversa');

?>