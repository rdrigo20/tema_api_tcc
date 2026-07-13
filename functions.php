<?php

//função q vai puxar o caminho do php até essa pasta
$template_diretorio = get_template_directory();

//INCLUINDO OS ARQUIVOS DE CUSTOM POST TYPES
require_once($template_diretorio . "/custom-post-type/conversa.php");
require_once($template_diretorio . "/custom-post-type/pergunta.php");
require_once($template_diretorio . "/custom-post-type/resposta.php");
require_once($template_diretorio . "/custom-post-type/resultado.php");

//INCLUINDO OS ARQUIVOS DE ENDPOINTS

//Arquivos do usuário
require_once($template_diretorio . "/endpoints/user/usuario_post.php");
require_once($template_diretorio . "/endpoints/user/usuario_get.php");
require_once($template_diretorio . "/endpoints/user/usuario_put.php");

//Arquivos do custom post type pergunta
require_once($template_diretorio . "/endpoints/pergunta/pergunta_create.php");
require_once($template_diretorio . "/endpoints/pergunta/pergunta_get_all.php");
require_once($template_diretorio . "/endpoints/pergunta/pergunta_get_by_slug.php");
require_once($template_diretorio . "/endpoints/pergunta/pergunta_get_by_id.php");
require_once($template_diretorio . "/endpoints/pergunta/pergunta_delete_by_slug.php");
require_once($template_diretorio . "/endpoints/pergunta/pergunta_delete_by_id.php");
require_once($template_diretorio . "/endpoints/pergunta/pergunta_update_by_slug.php");
require_once($template_diretorio . "/endpoints/pergunta/pergunta_update_by_id.php");

?>