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
require_once($template_diretorio . "/endpoints/usuario_post.php");
require_once($template_diretorio . "/endpoints/usuario_get.php");
require_once($template_diretorio . "/endpoints/usuario_put.php");

//Arquivos do custom post type pergunta
require_once($template_diretorio . "/endpoints/pergunta_create.php");
require_once($template_diretorio . "/endpoints/pergunta_get_all.php");

?>