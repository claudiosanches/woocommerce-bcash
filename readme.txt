=== WooCommerce Bcash ===
Contributors: claudiosanches
Donate link: http://claudiosmweb.com/doacoes/
Tags: woocommerce, checkout, payment, bcash
Requires at least: 3.9
Tested up to: 3.9
Stable tag: 1.7.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds Bcash gateway to the WooCommerce plugin

== Description ==

### Add Bcash gateway to WooCommerce ###

This plugin adds Bcash gateway to WooCommerce.

Please notice that WooCommerce must be installed and active.

= Contribute =

You can contribute to the source code in our [GitHub](https://github.com/claudiosmweb/woocommerce-bcash) page.

### Descrição em Português: ###

Adicione o Bcash como método de pagamento em sua loja WooCommerce.

[Bcash](https://www.bcash.com.br/) é um método de pagamento brasileiro desenvolvido pela Buscapé Company.

O plugin WooCommerce Bcash foi desenvolvido sem nenhum incentivo do Bcash ou Buscapé Company. Nenhum dos desenvolvedores deste plugin possuem vínculos com estas duas empresas.

Este plugin foi feito baseado na [documentação oficial do Bcash](https://www.bcash.com.br/integracao-bcash.html).

= Compatibilidade =

Compatível com as versões 2.0.x e 2.1.x do WooCommerce.

= Instalação: =

Confira o nosso guia de instalação e configuração do WooCommerce Bcash na aba [Installation](http://wordpress.org/extend/plugins/woocommerce-bcash/installation/).

= Dúvidas? =

Você pode esclarecer suas dúvidas usando:

* A nossa sessão de [FAQ](http://wordpress.org/extend/plugins/woocommerce-bcash/faq/).
* Criando um tópico no [fórum de ajuda do WordPress](http://wordpress.org/support/plugin/woocommerce-bcash) (apenas em inglês).
* Ou entre em contato com os desenvolvedores do plugin em nossa [página](http://claudiosmweb.com/plugins/bcash-para-woocommerce/).

= Coloborar =

Você pode contribuir com código-fonte em nossa página no [GitHub](https://github.com/claudiosmweb/woocommerce-bcash).

== Installation ==

* Upload plugin files to your plugins folder, or install using WordPress built-in Add New Plugin installer;
* Activate the plugin;
* Navigate to WooCommerce -> Settings -> Payment Gateways, choose Bcash and fill in your Bcash Email and Token.

### Instalação e configuração em Português: ###

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

= Configurações no WooCommerce =

No WooCommerce 2.0 ou superior existe uma opção para cancelar a compra e liberar o estoque depois de alguns minutos.

Esta opção não funciona muito bem com o Bcash, pois pagamentos por boleto bancário pode demorar até 48 horas para serem validados.

Para corrigir isso é necessário ir em "WooCommerce" > "Configurações" > "Inventário" e limpar (deixe em branco) o valor da opção **Manter Estoque (minutos)**.

Pronto, sua loja já pode receber pagamentos pelo Bcash.

== Frequently Asked Questions ==

= What is the plugin license? =

* This plugin is released under a GPL license.

= What is needed to use this plugin? =

* WooCommerce version 2.0 or latter installed and active.
* Have a account on [Bcash](http://www.bcash.com.br/).
* Generates a **Access key** in Bcash.

### FAQ em Português: ###

= Qual é a licença do plugin? =

Este plugin esta licenciado como GPL.

= O que eu preciso para utilizar este plugin? =

* Ter instalado o plugin WooCommerce 2.0 ou superior.
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

= A compra é cancelada após alguns minutos, mesmo com o pedido sendo pago, como resolvo isso? =

Para resolver este problema vá até "WooCommerce" > "Configurações" > "Inventário" e limpe (deixe em branco) o valor da opção **Manter Estoque (minutos)**.

= Mais dúvidas relacionadas ao funcionamento do plugin? =

Entre em contato [clicando aqui](http://claudiosmweb.com/plugins/bcash-para-woocommerce/).

== Screenshots ==

1. Settings page.
2. Checkout page.

== Changelog ==

= 1.7.0 - 14/12/2013 =

* Corrigido padrões de código.
* Removida compatibilidade com versões 1.6.x ou inferiores do WooCommerce.
* Adicionada compatibilidade com WooCommerce 2.1 ou superior.

= 1.6.0 - 26/07/2013 =

* Melhoria nas mensagens de status do pedido.
* Melhoria no código do plugin.

= 1.5.0 - 24/07/2013 =

* Adicionado link de `Configurações` na página de plugins.
* Melhorias no código.
* Adicionado suporte para WooCommerce 2.1.
* Adicionado objeto do pedido no filtro `woocommerce_bcash_args`.

= 1.4 - 08/04/2013 =

* Correção do retorno automático de dados na versão 2.0.0 ou superior do WooCommerce.

= 1.3.3 - 06/03/2013 =

* Corrigida a compatibilidade com WooCommerce 2.0.0 ou mais recente.

= 1.3.2 - 08/02/2013 =

* Corrigido o hook responsavel por salvar as opções para a versão 2.0 RC do WooCommerce.

= 1.3.1 - 08/02/2013 =

* Plugin corrigido para a versão 2.0 do WooCommerce.

= 1.3 - 30/11/2012 =

* Adicionada opção para logs de erro.

= 1.2.1 =

* Corrigido standards de código.
* Corrigida a URL de retorno automático de dados.

= 1.2 =

* Correção da tradução.
* Construção do readme.txt.

= 1.1 =

* Removida a classe do retorno automático que usava cURL em favor da função wp_remote_post().

= 1.0 =

* Versão incial do plugin.

== Upgrade Notice ==

= 1.7.0 =

* Corrigido padrões de código.
* Removida compatibilidade com versões 1.6.x ou inferiores do WooCommerce.
* Adicionada compatibilidade com WooCommerce 2.1 ou superior.

== License ==

WooCommerce Bcash is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published
by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

WooCommerce Bcash is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with WooCommerce Bcash. If not, see <http://www.gnu.org/licenses/>.
