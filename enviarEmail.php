<?php
// Endpoint legado: mensagens são salvas pelo formulário de index.php.
http_response_code(410);
header('Content-Type: text/plain; charset=utf-8');
echo 'Use o formulário de contato da página inicial.';
