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

// Função que puxa o caminho absoluto da pasta DESTE plugin
$plugin_diretorio = plugin_dir_path( __FILE__ );

// ----------------------------------------------------
// INCLUINDO OS ARQUIVOS DE CUSTOM POST TYPES
// ----------------------------------------------------
require_once($plugin_diretorio . "custom-post-type/conversa.php");
require_once($plugin_diretorio . "custom-post-type/pergunta.php");
require_once($plugin_diretorio . "custom-post-type/resposta.php");
require_once($plugin_diretorio . "custom-post-type/resultado.php");

// ----------------------------------------------------
// INCLUINDO OS ARQUIVOS DE ENDPOINTS
// ----------------------------------------------------

// Arquivos do usuário
require_once($plugin_diretorio . "endpoints/user/usuario_post.php");
require_once($plugin_diretorio . "endpoints/user/usuario_get.php");
require_once($plugin_diretorio . "endpoints/user/usuario_put.php");

// Arquivos do custom post type 'pergunta'
require_once($plugin_diretorio . "endpoints/pergunta/pergunta_create.php");
require_once($plugin_diretorio . "endpoints/pergunta/pergunta_get_all.php");
require_once($plugin_diretorio . "endpoints/pergunta/pergunta_get_by_slug.php");
require_once($plugin_diretorio . "endpoints/pergunta/pergunta_get_by_id.php");
require_once($plugin_diretorio . "endpoints/pergunta/pergunta_delete_by_slug.php");
require_once($plugin_diretorio . "endpoints/pergunta/pergunta_delete_by_id.php");
require_once($plugin_diretorio . "endpoints/pergunta/pergunta_update_by_slug.php");
require_once($plugin_diretorio . "endpoints/pergunta/pergunta_update_by_id.php");

?>