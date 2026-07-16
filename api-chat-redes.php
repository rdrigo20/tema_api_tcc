<?php
/*
Plugin Name: API Chat de Redes
Description: Plugin responsável por gerenciar os endpoints, CPTs e a lógica do chat de iptables.
Version: 1.0
Author: Seu Nome
*/

// Segurança: Bloqueia o acesso direto a este arquivo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


// ----------------------------------------------------
// INCLUINDO OS ARQUIVOS DE CUSTOM POST TYPES
// ----------------------------------------------------
require_once(plugin_dir_path( __FILE__ ) . "custom-post-type/conversa.php");
require_once(plugin_dir_path( __FILE__ ) . "custom-post-type/pergunta.php");
require_once(plugin_dir_path( __FILE__ ) . "custom-post-type/resposta.php");
require_once(plugin_dir_path( __FILE__ ) . "custom-post-type/resultado.php");

// ----------------------------------------------------
// INCLUINDO OS ARQUIVOS DE ENDPOINTS
// ----------------------------------------------------

// Arquivos do usuário
require_once(plugin_dir_path( __FILE__ ) . "endpoints/user/usuario_post.php");
require_once(plugin_dir_path( __FILE__ ) . "endpoints/user/usuario_get.php");
require_once(plugin_dir_path( __FILE__ ) . "endpoints/user/usuario_put.php");

// Arquivos do custom post type 'pergunta'
require_once(plugin_dir_path( __FILE__ ) . "endpoints/pergunta/pergunta_create.php");
require_once(plugin_dir_path( __FILE__ ) . "endpoints/pergunta/pergunta_get_all.php");
require_once(plugin_dir_path( __FILE__ ) . "endpoints/pergunta/pergunta_get_by_slug.php");
require_once(plugin_dir_path( __FILE__ ) . "endpoints/pergunta/pergunta_get_by_id.php");
require_once(plugin_dir_path( __FILE__ ) . "endpoints/pergunta/pergunta_delete_by_slug.php");
require_once(plugin_dir_path( __FILE__ ) . "endpoints/pergunta/pergunta_delete_by_id.php");
require_once(plugin_dir_path( __FILE__ ) . "endpoints/pergunta/pergunta_update_by_slug.php");
require_once(plugin_dir_path( __FILE__ ) . "endpoints/pergunta/pergunta_update_by_id.php");

// Arquivos do custom post type 'conversa'
require_once(plugin_dir_path( __FILE__ ) . "endpoints/conversa/conversa_create.php");
require_once(plugin_dir_path( __FILE__ ) . "endpoints/conversa/conversa_get_all.php");
require_once(plugin_dir_path( __FILE__ ) . "endpoints/conversa/conversa_get_by_id.php");
require_once(plugin_dir_path( __FILE__ ) . "endpoints/conversa/conversa_delete_by_id.php");
require_once(plugin_dir_path( __FILE__ ) . "endpoints/conversa/conversa_update_by_id.php");


// Arquivos do custom post type 'resposta'
require_once(plugin_dir_path( __FILE__ ) . "endpoints/resposta/resposta_create.php");
require_once(plugin_dir_path( __FILE__ ) . "endpoints/resposta/resposta_get_all.php");
require_once(plugin_dir_path( __FILE__ ) . "endpoints/resposta/resposta_get_by_id.php");
require_once(plugin_dir_path( __FILE__ ) . "endpoints/resposta/resposta_delete_by_id.php");
require_once(plugin_dir_path( __FILE__ ) . "endpoints/resposta/resposta_update_by_id.php");



?>