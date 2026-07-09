<?php

$template_diretorio = get_template_directory();



require_once($template_diretorio . "/endpoints/usuario_post.php");
require_once($template_diretorio . "/endpoints/usuario_get.php");
require_once($template_diretorio . "/endpoints/usuario_put.php");

?>