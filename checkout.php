<?php
require_once __DIR__ . '/bootstrap.php';
$user = require_user($conn);
$error = '';
$deliveryFields = ['recebedor','telefone','cep','rua','numero','complemento','bairro','cidade','estado','referencia'];
$input = [];
foreach ($deliveryFields as $field) $input[$field] = trim((string)($_POST[$field] ?? ''));
$delivery = (string)($_POST['tipo_entrega'] ?? 'retirada');
$notes = trim((string)($_POST['observacoes'] ?? ''));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $payment = $delivery === 'retirada' ? 'na_retirada' : 'na_entrega';
    $digitsPhone = preg_replace('/\D/', '', $input['telefone']);
    $digitsCep = preg_replace('/\D/', '', $input['cep']);
    $validDelivery = $input['recebedor'] !== '' && mb_strlen($input['recebedor']) <= 100
        && in_array(strlen($digitsPhone), [10,11], true) && strlen($digitsCep) === 8
        && $input['rua'] !== '' && mb_strlen($input['rua']) <= 150
        && $input['numero'] !== '' && mb_strlen($input['numero']) <= 20
        && $input['bairro'] !== '' && mb_strlen($input['bairro']) <= 80
        && $input['cidade'] !== '' && mb_strlen($input['cidade']) <= 80
        && preg_match('/^[A-Za-z]{2}$/', $input['estado'])
        && mb_strlen($input['complemento']) <= 100 && mb_strlen($input['referencia']) <= 150;
    if (!in_array($delivery, ['retirada','delivery'], true) || ($delivery === 'delivery' && !$validDelivery) || mb_strlen($notes) > 500) {
        $error = 'Confira os dados de entrega, telefone, CEP e endereço.';
    } elseif (empty($_SESSION['carrinho'])) {
        $error = 'O carrinho está vazio.';
    } else {
        if ($delivery === 'retirada') {
            foreach ($deliveryFields as $field) $input[$field] = '';
            $digitsPhone = $digitsCep = '';
            $address = '';
        } else {
            $input['estado'] = strtoupper($input['estado']);
            $address = $input['rua'] . ', ' . $input['numero'] . ', ' . $input['bairro'] . ' - ' . $input['cidade'] . '/' . $input['estado'];
        }
        try {
            $conn->begin_transaction(); $items = []; $total = 0;
            $select = $conn->prepare('SELECT id, nome, preco, estoque FROM produtos WHERE id = ? AND ativo = 1 FOR UPDATE');
            foreach ($_SESSION['carrinho'] as $id => $qty) {
                $id = (int)$id; $qty = (int)$qty;
                if ($id < 1 || $qty < 1 || $qty > 99) throw new RuntimeException('Quantidade inválida no carrinho.');
                $select->bind_param('i', $id); $select->execute(); $p = $select->get_result()->fetch_assoc();
                if (!$p || (int)$p['estoque'] < $qty) throw new RuntimeException('Estoque insuficiente. Atualize o carrinho.');
                $items[] = [$id, $p['nome'], $qty, $p['preco']];
                $total += (float)$p['preco'] * $qty;
            }
            if (!$items) throw new RuntimeException('O carrinho está vazio.');
            $insert = $conn->prepare('INSERT INTO pedidos (usuario_id,total,tipo_entrega,endereco,observacoes,forma_pagamento,recebedor,telefone,cep,rua,numero,complemento,bairro,cidade,estado,referencia) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
            $userId = (int)$user['id'];
            $insert->bind_param('idssssssssssssss', $userId,$total,$delivery,$address,$notes,$payment,$input['recebedor'],$digitsPhone,$digitsCep,$input['rua'],$input['numero'],$input['complemento'],$input['bairro'],$input['cidade'],$input['estado'],$input['referencia']);
            $insert->execute(); $orderId = $conn->insert_id;
            $line = $conn->prepare('INSERT INTO itens_pedido (pedido_id,produto_id,nome_produto,quantidade,preco_unitario) VALUES (?,?,?,?,?)');
            $stock = $conn->prepare('UPDATE produtos SET estoque = estoque - ? WHERE id = ? AND estoque >= ?');
            foreach ($items as [$id, $name, $qty, $price]) {
                $line->bind_param('iisid', $orderId, $id, $name, $qty, $price); $line->execute();
                $stock->bind_param('iii', $qty, $id, $qty); $stock->execute();
                if ($stock->affected_rows !== 1) throw new RuntimeException('Estoque insuficiente.');
            }
            $conn->commit(); unset($_SESSION['carrinho']);
            header('Location: pedidos.php?novo=' . $orderId); exit;
        } catch (Throwable $e) {
            $conn->rollback();
            $error = $e instanceof RuntimeException ? $e->getMessage() : 'Não foi possível concluir o pedido. Confira se a atualização SQL do delivery foi aplicada.';
        }
    }
}
function delivery_input(string $field, string $label, int $max, array $values, bool $required = true): void {
    $name = h($field); $value = h($values[$field]);
    echo '<label>' . h($label) . '<input name="' . $name . '" maxlength="' . $max . '" value="' . $value . '"' . ($required ? ' data-required-delivery' : '') . '></label>';
}
?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Finalizar pedido | Lumora Café</title><link rel="stylesheet" href="upgrade.css"></head><body><header class="bar"><a href="index.php">☕ Lumora Café</a><nav><a href="carrinho.php">Voltar ao carrinho</a></nav></header><main class="wrap"><h1>Finalizar pedido</h1><?php if ($error): ?><p class="alert"><?= h($error) ?></p><?php endif; ?><p>Pagamento presencial na retirada ou na entrega. O valor dos produtos será confirmado com os preços atuais; não há cobrança online nem taxa de entrega calculada automaticamente.</p><form method="post" class="panel"><input type="hidden" name="csrf" value="<?= h(csrf()) ?>"><label>Como quer receber?<select name="tipo_entrega" id="tipo-entrega" required><option value="retirada" <?= $delivery==='retirada'?'selected':'' ?>>Retirar na cafeteria</option><option value="delivery" <?= $delivery==='delivery'?'selected':'' ?>>Delivery</option></select></label><fieldset id="dados-delivery"><legend>Dados para delivery</legend><?php
 delivery_input('recebedor','Nome de quem receberá',100,$input);
 delivery_input('telefone','Celular com DDD',15,$input);
 delivery_input('cep','CEP (8 números)',9,$input);
 delivery_input('rua','Rua ou avenida',150,$input);
 delivery_input('numero','Número (ou S/N)',20,$input);
 delivery_input('complemento','Complemento (opcional)',100,$input,false);
 delivery_input('bairro','Bairro',80,$input);
 delivery_input('cidade','Cidade',80,$input);
 delivery_input('estado','UF (duas letras)',2,$input);
 delivery_input('referencia','Ponto de referência (opcional)',150,$input,false);
?><p id="cep-status" role="status" aria-live="polite"></p><p>Confira os dados preenchidos e informe o número. Alguns CEPs não trazem rua ou bairro; nesses casos, preencha manualmente.</p></fieldset><label>Observações do pedido<textarea name="observacoes" maxlength="500"><?= h($notes) ?></textarea></label><button>Confirmar dados e fazer pedido</button></form></main><script>const type=document.getElementById('tipo-entrega'),group=document.getElementById('dados-delivery');function toggleDelivery(){const enabled=type.value==='delivery';group.hidden=!enabled;group.querySelectorAll('input').forEach(input=>{input.disabled=!enabled;input.required=enabled&&input.hasAttribute('data-required-delivery')})}type.addEventListener('change',toggleDelivery);toggleDelivery();
const checkoutForm=document.querySelector('form.panel');
checkoutForm.addEventListener('submit',event=>{
  if(type.value!=='delivery') return;
  const field=name=>checkoutForm.elements.namedItem(name).value.trim();
  const address=[field('rua')+', '+field('numero'),field('complemento'),field('bairro'),field('cidade')+'/'+field('estado'), 'CEP: '+field('cep')].filter(Boolean).join(' — ');
  const message='Confirme os dados de entrega antes de fazer o pedido:\n\n'+field('recebedor')+' · '+field('telefone')+'\n'+address+'\n\nDepois de confirmar, você verá Meus pedidos.';
  if(!window.confirm(message)) event.preventDefault();
});</script><script src="cep.js" defer></script></body></html>
