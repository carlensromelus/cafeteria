<?php
require __DIR__ . '/pedidos_base.php';
$produtos = $conn->query('SELECT id, nome, preco FROM produtos ORDER BY nome')->fetch_all(MYSQLI_ASSOC);
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    conferir_formulario();
    $campos = ['nome_cliente','telefone','cep','endereco','numero','complemento','bairro','cidade','estado'];
    $dados = [];
    foreach ($campos as $campo) { $dados[$campo] = trim((string)($_POST[$campo] ?? '')); }
    $limites = [100,25,9,200,20,100,100,100,2];
    foreach ($campos as $i => $campo) {
        if (($campo !== 'complemento' && $dados[$campo] === '') || strlen($dados[$campo]) > $limites[$i]) {
            $erro = 'Preencha corretamente os dados de entrega.';
        }
    }
    if (!preg_match('/^\d{5}-?\d{3}$/', $dados['cep']) || !preg_match('/^[A-Za-z]{2}$/', $dados['estado'])) {
        $erro = 'Confira o CEP e a sigla do estado.';
    }
    // O preço vem do banco; o navegador envia apenas as quantidades.
    $totalCentavos = 0; $linhas = [];
    foreach ($produtos as $produto) {
        $q = filter_var($_POST['quantidade'][$produto['id']] ?? 0, FILTER_VALIDATE_INT);
        if ($q === false || $q < 0 || $q > 50) { $erro = 'Use quantidades entre 0 e 50.'; break; }
        if ($q > 0) {
            $centavos = (int)round((float)$produto['preco'] * 100);
            $totalCentavos += $centavos * $q;
            $linhas[] = $q . ' × ' . $produto['nome'] . ' — R$ ' . dinheiro($centavos * $q / 100);
        }
    }
    if (!$linhas) { $erro = 'Escolha pelo menos um produto.'; }
    if ($erro === '') {
        $itens = implode("\n", $linhas); $total = $totalCentavos / 100;
        $stmt = $conn->prepare('INSERT INTO pedidos (usuario_id,nome_cliente,telefone,cep,endereco,numero,complemento,bairro,cidade,estado,itens,total) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
        $stmt->bind_param('issssssssssd', $cliente['id'], $dados['nome_cliente'], $dados['telefone'], $dados['cep'], $dados['endereco'], $dados['numero'], $dados['complemento'], $dados['bairro'], $dados['cidade'], $dados['estado'], $itens, $total);
        $stmt->execute(); header('Location: meus_pedidos.php'); exit;
    }
}
?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Novo pedido — Lumora</title><link rel="stylesheet" href="pedidos.css"></head><body><main>
<nav><a href="index.php">Início</a><a href="meus_pedidos.php">Meus pedidos</a></nav><h1>Faça seu pedido</h1>
<p>Pagamento combinado com a cafeteria na entrega. O total abaixo corresponde aos produtos; confirme disponibilidade e eventual taxa de entrega com a loja.</p>
<p class="erro"><?= h($erro) ?></p><form method="post" class="pedido"><input type="hidden" name="csrf" value="<?= h($_SESSION['csrf_pedidos']) ?>">
<div class="produtos"><?php foreach ($produtos as $produto): ?><label><?= h($produto['nome']) ?> — R$ <?= dinheiro($produto['preco']) ?><input aria-label="Quantidade de <?= h($produto['nome']) ?>" type="number" name="quantidade[<?= (int)$produto['id'] ?>]" min="0" max="50" value="<?= h($_POST['quantidade'][$produto['id']] ?? 0) ?>"></label><?php endforeach; ?></div>
<h2>Endereço de entrega</h2>
<?php foreach (['nome_cliente'=>'Nome','telefone'=>'Telefone','cep'=>'CEP','endereco'=>'Rua','numero'=>'Número','complemento'=>'Complemento','bairro'=>'Bairro','cidade'=>'Cidade','estado'=>'Estado (UF)'] as $campo=>$rotulo): ?>
<label><?= $rotulo ?><input name="<?= $campo ?>" id="<?= $campo ?>" value="<?= h($_POST[$campo] ?? ($campo === 'nome_cliente' ? $cliente['nome'] : '')) ?>" <?= $campo !== 'complemento' ? 'required' : '' ?>></label>
<?php endforeach; ?><p id="cep-aviso" role="status"></p><button>Confirmar endereço e fazer pedido</button></form></main>
<script>
document.getElementById('cep').addEventListener('blur', async function () {
 const cep = this.value.replace(/\D/g, ''); const aviso = document.getElementById('cep-aviso');
 if (cep.length !== 8) { aviso.textContent = 'Digite um CEP com 8 números.'; return; }
 aviso.textContent = 'Consultando CEP…';
 try {
  const resposta = await fetch('https://viacep.com.br/ws/' + cep + '/json/');
  if (!resposta.ok) throw new Error(); const dados = await resposta.json();
  if (dados.erro) throw new Error();
  if (this.value.replace(/\D/g, '') !== cep) return;
  for (const [campo, chave] of Object.entries({endereco:'logradouro',bairro:'bairro',cidade:'localidade',estado:'uf'})) document.getElementById(campo).value = dados[chave] || '';
  aviso.textContent = 'Confira o endereço e preencha o número antes de confirmar.';
 } catch (_) { aviso.textContent = 'Não foi possível consultar o CEP. Preencha o endereço manualmente.'; }
});
</script></body></html>
