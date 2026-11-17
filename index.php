<?php
// Se a hospedagem apontar para a raiz do projeto (e não para /public),
// redireciona o navegador para a pasta pública.
header('Location: public/');
exit;
