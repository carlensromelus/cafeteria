// Atualiza somente as listas; preserva formulários e status ainda não salvos.
const painelAvisos = document.getElementById('admin-avisos');
let ultimoPedido = Number(painelAvisos.dataset.pedidos);
let ultimaMensagem = Number(painelAvisos.dataset.mensagens);
let novosPedidos = 0, novasMensagens = 0;
const listaEventos = document.getElementById('admin-eventos');
document.getElementById('limpar-avisos').addEventListener('click', () => {
 novosPedidos = 0; novasMensagens = 0; listaEventos.replaceChildren(); atualizarContadores();
});
document.addEventListener('change', event => {
 if (event.target.matches('[data-admin-pedido] select')) event.target.closest('[data-admin-pedido]').dataset.editando = '1';
});
function atualizarContadores() {
 document.getElementById('avisos-pedidos').textContent = novosPedidos;
 document.getElementById('avisos-mensagens').textContent = novasMensagens;
}
async function atualizarAdmin() {
 const aviso = document.getElementById('admin-conexao');
 try {
  const resposta = await fetch('admin_atualizacoes.php?pedidos=' + ultimoPedido + '&mensagens=' + ultimaMensagem, {cache:'no-store',signal:AbortSignal.timeout(10000)});
  if (resposta.status === 401 || resposta.status === 403) { aviso.textContent = 'Acesso encerrado. Entre novamente como administrador.'; return; }
  if (!resposta.ok) throw new Error();
  const dados = await resposta.json();
  if (!Array.isArray(dados.pedidos) || !Array.isArray(dados.eventos) || typeof dados.mensagens_html !== 'string') throw new Error();
  const lista = document.getElementById('admin-lista-pedidos');
  if (lista) {
   const ids = new Set(dados.pedidos.map(p => String(p.id)));
   lista.querySelectorAll('[data-admin-pedido]').forEach(card => {if (!ids.has(card.dataset.adminPedido) && !card.dataset.editando) card.remove();});
   for (const pedido of dados.pedidos.slice().reverse()) {
    const antigo = lista.querySelector('[data-admin-pedido="' + Number(pedido.id) + '"]');
    if (antigo && (antigo.dataset.editando || antigo.contains(document.activeElement))) continue;
    const template = document.createElement('template'); template.innerHTML = pedido.html;
    const novo = template.content.firstElementChild;
    if (antigo) antigo.replaceWith(novo); else lista.prepend(novo);
   }
   const vazio = document.getElementById('admin-sem-pedidos');
   if (vazio) vazio.hidden = dados.pedidos.length > 0;
  }
  const mensagens = document.getElementById('admin-lista-mensagens');
  if (mensagens && !mensagens.contains(document.activeElement)) mensagens.innerHTML = dados.mensagens_html || '<p>Nenhuma mensagem recebida.</p>';
  for (const evento of dados.eventos) {
   if (evento.tipo === 'pedido') novosPedidos++; else if (evento.tipo === 'mensagem') novasMensagens++;
   const linha = document.createElement('p'); linha.textContent = evento.texto; listaEventos.prepend(linha);
  }
  while (listaEventos.children.length > 50) listaEventos.lastElementChild.remove();
  ultimoPedido = Number(dados.ultimo_pedido); ultimaMensagem = Number(dados.ultima_mensagem);
  atualizarContadores(); aviso.textContent = 'Painel atualizado às ' + new Date().toLocaleTimeString('pt-BR') + '. Consulta a cada 5 segundos.';
 } catch (_) { aviso.textContent = 'Falha na conexão. Mantivemos os dados e tentaremos novamente.'; }
 setTimeout(atualizarAdmin, 5000);
}
atualizarAdmin();
