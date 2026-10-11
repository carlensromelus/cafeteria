// Consultas em sequência evitam respostas fora de ordem e não recarregam a página.
const nomes = {recebido:'Pedido recebido', preparando:'Em preparação', saiu_entrega:'Saiu para entrega', entregue:'Entregue', cancelado:'Cancelado'};
const etapas = ['recebido','preparando','saiu_entrega','entregue'];
async function consultarPedidos() {
 const aviso = document.getElementById('conexao');
 try {
  const resposta = await fetch('status_pedidos.php', {cache:'no-store', signal:AbortSignal.timeout(10000)});
  if (resposta.status === 401) { aviso.textContent = 'Sua sessão expirou. Faça login novamente para acompanhar.'; return; }
  if (!resposta.ok) throw new Error();
  const dados = await resposta.json();
  if (!Array.isArray(dados.pedidos)) throw new Error();
  const novidades = [];
  for (const pedido of dados.pedidos) {
   const cartao = document.querySelector('[data-pedido="' + Number(pedido.id) + '"]');
   if (!cartao) continue;
   if (cartao.dataset.status !== pedido.status) novidades.push('Pedido #' + pedido.id + ': ' + (nomes[pedido.status] || pedido.status));
   cartao.dataset.status = pedido.status;
   cartao.querySelector('.status').textContent = nomes[pedido.status] || pedido.status;
   cartao.querySelector('.atualizacao').textContent = 'Última alteração: ' + pedido.atualizado_em;
   const atual = etapas.indexOf(pedido.status);
   cartao.querySelectorAll('[data-etapa]').forEach((etapa, i) => {
    etapa.classList.toggle('concluida', atual >= 0 && i < atual);
    etapa.classList.toggle('atual', i === atual);
   });
  }
  if (novidades.length) document.getElementById('novidades').textContent = novidades.join('. ');
  aviso.textContent = 'Status conferidos às ' + new Date().toLocaleTimeString('pt-BR') + '. Atualização a cada 5 segundos.';
 } catch (_) { aviso.textContent = 'Conexão indisponível. Os últimos status estão na tela; tentaremos novamente.'; }
 setTimeout(consultarPedidos, 5000);
}
consultarPedidos();
