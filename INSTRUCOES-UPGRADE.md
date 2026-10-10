# Instalar Lumora Café com banco novo

1. Copie a pasta `cafeteria` deste ZIP para `C:\xampp\htdocs\cafeteria`.
2. Inicie Apache e MySQL no XAMPP. No phpMyAdmin, abra a aba **SQL** e execute todo o arquivo `banco_lumora_v2.sql`. Ele cria `lumora_cafe_v2`, cinco tabelas e quatro produtos de exemplo. Pode ser executado mais de uma vez sem duplicar os produtos de exemplo.
**Se você já importou `banco_lumora_v2.sql` da versão anterior e tem usuários ou pedidos**, não reimporte o banco nem apague tabelas: selecione `lumora_cafe_v2` no phpMyAdmin e execute apenas `atualizar_delivery.sql` antes de substituir os arquivos PHP. O script adiciona os campos sem apagar pedidos existentes.

3. O arquivo `conexao.php` já aponta para `lumora_cafe_v2` e usa `root` sem senha, padrão local do XAMPP. Se seu MySQL usa outra senha, ajuste esse arquivo.
4. Abra `http://localhost/cafeteria/cadastro.php`, crie a sua conta e faça login. Por segurança, toda conta nova é criada como `user`.
5. Para transformar **somente sua conta** em administradora, no phpMyAdmin selecione `lumora_cafe_v2`, abra SQL e execute após trocar pelo seu e-mail: `UPDATE usuarios SET role='admin' WHERE email='seu-email@exemplo.com';`. Saia e entre novamente. Abra `http://localhost/cafeteria/admin.php` e `http://localhost/cafeteria/admin_pedidos.php`.
6. Teste: adicione um produto ao carrinho, conclua um pedido de retirada e veja-o em **Meus pedidos** e no painel. Mensagens do contato aparecem em **Mensagens** no painel.

O banco antigo `lumora_cafe` não é alterado. Contas, produtos e pedidos antigos não são copiados automaticamente para o banco novo. O checkout registra pagamento presencial na retirada ou entrega; ainda não faz cobranças PIX/cartão, cálculo de frete ou envio automático de e-mail. Use endereço real e dados de operação válidos antes de disponibilizar a loja a clientes.

No checkout de delivery, informe recebedor, telefone com DDD, CEP, rua, número, bairro, cidade e UF. Complemento e ponto de referência são opcionais. O CEP é validado pelo formato; o endereço e a área atendida precisam ser conferidos pela cafeteria.

Ao digitar o CEP no checkout, o site consulta o ViaCEP e sugere rua, bairro, cidade e UF. O cliente confirma esses dados e informa número, complemento e ponto de referência quando necessário. Se a consulta falhar ou o CEP for genérico, os campos continuam editáveis para preenchimento manual. A busca depende da disponibilidade da internet e do serviço ViaCEP no navegador do cliente.

No delivery, ao clicar em **Confirmar dados e fazer pedido**, o navegador mostra o endereço para revisão. Se o cliente confirmar, o servidor salva o pedido e redireciona para **Meus pedidos**; se cancelar, ele pode editar o endereço.

O administrador vê os 10 pedidos mais recentes no topo de `admin.php`, incluindo telefone e endereço dos pedidos de delivery; `admin_pedidos.php` mostra até 100 e permite atualizar o status. Se um pedido de retirada aparece sem endereço, isso é esperado. Se um pedido antigo de delivery não tem dados registrados, eles não podem ser recuperados automaticamente: peça ao cliente para informar o endereço ou faça um novo pedido.
