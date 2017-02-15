=== WooCommerce Bcash ===
Contributors: claudiosanches
Donate link: http://claudiosmweb.com/doacoes/
Tags: woocommerce, checkout, payment, bcash
Requires at least: 4.0
Tested up to: 4.7
Stable tag: 1.13.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds Bcash gateway to the WooCommerce plugin

== Description ==

Adicione o Bcash como método de pagamento em sua loja WooCommerce.

[Bcash](https://www.bcash.com.br/) é um método de pagamento brasileiro desenvolvido pelo PayU.

O plugin WooCommerce Bcash foi desenvolvido sem nenhum incentivo do Bcash ou PayU. Nenhum dos desenvolvedores deste plugin possuem vínculos com estas duas empresas.

Este plugin foi feito baseado na [documentação oficial do Bcash](https://www.bcash.com.br/integracao-bcash.html).

= Compatibilidade =

Compatível desde a versão 2.2.x até 2.7.x do WooCommerce.

= Instalação: =

Confira o nosso guia de instalação e configuração do WooCommerce Bcash na aba [Installation](http://wordpress.org/extend/plugins/woocommerce-bcash/installation/).

= Dúvidas? =

Você pode esclarecer suas dúvidas usando:

* A nossa sessão de [FAQ](http://wordpress.org/extend/plugins/woocommerce-bcash/faq/).
* Criando um tópico no [fórum de ajuda do WordPress](http://wordpress.org/support/plugin/woocommerce-bcash).
* Utilizando o nosso [fórum no Github](https://github.com/claudiosanches/woocommerce-bcash).

= Colaborar =

Você pode contribuir com código-fonte em nossa página no [GitHub](https://github.com/claudiosanches/woocommerce-bcash).

== Installation ==

= Instalação do plugin: =

* Envie os arquivos do plugin para a pasta wp-content/plugins ou usando o instalador de plugins do WordPress.
* Ative o plugin.

= Requerimentos: =

É necessário possuir uma conta no [Bcash](https://www.bcash.com.br/) e instalar a última versão do [WooCommerce](http://wordpress.org/extend/plugins/woocommerce/).

= Configurações no Bcash: =

No Bcash você precisa apenas validar sua conta e gerar um **Chave acesso** em "Ferramentas" > "Códigos de Integração".

= Configurações do Plugin: =

Com o plugin instalado acesse o admin do WordPress e entre em "WooCommerce" > "Configurações" > "Portais de pagamento"  > "Bcash".

Habilite o Bcash, adicione o seu e-mail e a Chave acesso (utilizado para validar o retorno automático de dados).

Pronto, sua loja já pode receber pagamentos pelo Bcash.

== Frequently Asked Questions ==

= Qual é a licença do plugin? =

Este plugin esta licenciado como GPL.

= O que eu preciso para utilizar este plugin? =

* Ter instalado o plugin WooCommerce 2.1 ou superior.
* Possuir uma conta no [Bcash](http://www.bcash.com.br/).
* Gerar uma **Chave acesso** no Bcash.

= Como funciona o Bcash? =

* Saiba mais em "[O que é Bcash? Conheça o meio de pagamento do Buscapé Company. - Bcash](https://www.bcash.com.br/o-que-e-bcash.html)".

= Bcash recebe pagamentos de quais países? =

No momento o Bcash recebe pagamentos apenas do Brasil.

Configuramos o plugin para receber pagamentos apenas de usuários que selecionarem o Brasil nas informações de pagamento durante o checkout.

= Quais são os meios de pagamento que o plugin aceita? =

São aceitos todos os meios de pagamentos que o Bcash disponibiliza.
Entretanto você precisa ativa-los na sua conta no Bcash.

Confira os meios de pagamento em "[Compre de forma rápida e segura em lojas online. Sua compra  protegida - Bcash](https://www.bcash.com.br/para-quem-compra-pela-internet.html)".

= Quais são as taxas de transações que o Bcash cobra? =

Consulte a página "[Tarifas para Comprador e Vendedor - Bcash](https://www.bcash.com.br/tarifas-bcash.html)".

= Como que plugin faz integração com Bcash? =

Fazemos a integração baseada na documentação oficial do Bcash que pode ser encontrada em "[Integração com Lojas Online, Carrinho de compras - Bcash para Desenvolvedores](https://www.bcash.com.br/integracao-bcash.html)"

= Instalei o plugin, mas a opção de pagamento do Bcash some durante o checkout. O que fiz de errado? =

Você esqueceu de selecionar o Brasil durante o cadastro no checkout.
A opção de pagamento pelo Bcash funciona apenas com o Brasil.

= O status do pedido não é alterado automaticamente? =

Sim, o status é alterado automaticamente usando a API de notificações de mudança de status do Bcash.

Caso o status dos seus pedidos não estiverem sendo alterados pode ser por causa de um dos motivos a baixo:

* Site com CloudFlare, pois por padrão sera bloqueada qualquer comunicação de outros servidores com o seu.
* Plugin de segurança como o "iThemes Security" com a opção para adicionar a lista do HackRepair.com no .htaccess do site. Acontece que o user-agent do Bcash pode estar no meio da lista e vai bloquear qualquer comunicação).
* `mod_security` habilitado, neste caso vai acontecer igual com o CloudFlare bloqueando qualquer comunicação de outros servidores com o seu.

= O pedido foi pago e ficou com o status de "processando" e não como "concluído", isto esta certo ? =

Sim, esta certo e significa que o plugin esta trabalhando como deveria.

Todo gateway de pagamentos no WooCommerce deve mudar o status do pedido para "processando" no momento que é confirmado o pagamento e nunca deve ser alterado sozinho para "concluído", pois o pedido deve ir apenas para o status "concluído" após ele ter sido entregue.

Para produtos baixáveis a configuração padrão do WooCommerce é permitir o acesso apenas quando o pedido tem o status "concluído", entretanto nas configurações do WooCommerce na aba *Produtos* é possível ativar a opção **"Conceder acesso para download do produto após o pagamento"** e assim liberar o download quando o status do pedido esta como "processando".

= Mais dúvidas relacionadas ao funcionamento do plugin? =

Por favor, caso você tenha algum problema com o funcionamento do plugin, [abra um tópico no fórum do plugin](https://wordpress.org/support/plugin/woocommerce-bcash#postform) com o link arquivo de log (ative ele nas opções do plugin e tente fazer uma compra, depois vá até WooCommerce > Status do Sistema, selecione o log do *bcash* e copie os dados, depois crie um link usando o [pastebin.com](http://pastebin.com) ou o [gist.github.com](http://gist.github.com)).

== Screenshots ==

1. Settings page.
2. Checkout page.

== Changelog ==

= 1.13.1 - 2017/02/15 =

- Corrigido suporte a PHP 5.2 e 5.3

= 1.13.0 - 2017/02/14 =

- Adicionado suporte ao WooCommerce 2.7.0.
- Adicionado suporte ao Sandbox do Bcash (contribuição de [Alex Koti](http://alexkoti.com/)).

= 1.12.0 - 2016/06/30 =

- Adicionado suporte para hash de validação.

== Upgrade Notice ==

= 1.13.0 =

- Adicionado suporte ao WooCommerce 2.7.0.
- Adicionado suporte ao Sandbox do Bcash (contribuição de Alex Koti).
