# Nu Chao - Loja de Roupas (E-Commerce Frontend)

Interface de e-commerce em **HTML, CSS e JavaScript** para uma loja de roupas focada na Guiné-Bissau. Tudo funciona no frontend com dados em **localStorage** (sem servidor).

## Como usar

- Abra o ficheiro `index.html` no navegador (ou sirva a pasta com o seu servidor local, ex.: XAMPP).
- A listagem de produtos, filtros e carrinho funcionam no frontend.
- Para **finalizar a compra** é necessário **fazer login**. Depois de clicar em "Finalizar pedido", aparece um formulário para nome, telefone, email e morada. Ao confirmar, o pedido é guardado no painel admin e abre o WhatsApp com a mensagem do pedido.

## Login e administração

- **Página de login:** `login.html`
- **Credenciais padrão:** utilizador `admin`, palavra-passe `admin123`
- **Painel administrativo:** `admin.html` (acessível após login), com abas:

  - **Catálogo** — Produtos (título, categoria, coleção, tipo, descrição técnica, composição, guia de tamanhos, preço, tamanhos, etiqueta, variações/SKU, imagem, galeria, stock) e **Slides do hero** (carrossel da página inicial).
  - **Pedidos** — Lista de pedidos com alteração de status (Aguardando Pagamento → Pago → Em Separação → Enviado → Entregue / Cancelado) e código de rastreio.
  - **Estoque** — Limiar de alerta de stock baixo e quantidade por produto (alterar quantidade).
  - **Clientes** — Lista de clientes extraída dos pedidos (nome, telefone, email, número de pedidos).
  - **Marketing** — Cupons (código, tipo: valor fixo / percentagem / frete grátis, validade, pedido mínimo) e **Newsletter** (lista de emails).
  - **Relatórios** — Dashboard com total de pedidos, faturamento, alertas de estoque, produtos mais vendidos e lista de produtos em stock baixo.
  - **Configurações** — SEO (meta título e descrição), utilizadores do admin (login, palavra-passe, perfil) e **log de auditoria** (últimas alterações).

Os dados são guardados no **banco de dados local** `loja_bd` (uma única chave no localStorage com todas as coleções: produtos, heróis, pedidos, cupons, newsletter, definições, utilizadores, auditoria). A loja e o admin leem e escrevem através do módulo `js/db.js`. Se não houver produtos ou heróis em `loja_bd`, a loja usa listas por defeito. Ao carregar pela primeira vez com dados antigos noutras chaves, o `db.js` faz uma migração única para `loja_bd`.

## Estrutura

- `index.html` — Página principal da loja (com modal de checkout).
- `login.html` — Página de login.
- `admin.html` — Painel administrativo com abas.
- `styles.css` — Estilos da loja.
- `css/admin.css` — Estilos do painel admin.
- `script.js` — Lógica da loja (produtos, carrinho, heróis, checkout, gravação de pedidos).
- `js/db.js` — Banco de dados local **loja_bd** (localStorage com chave `loja_bd`).
- `js/auth.js` — Autenticação (sessão em localStorage).
- `js/admin.js` — Lógica do painel (CRUD via LojaDB).
