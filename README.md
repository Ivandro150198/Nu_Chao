# Nu Chao

Loja online de **roupas e acessórios** para a Guiné-Bissau, com **pagamento na entrega (COD)**, gestão completa do site e área do entregador.

Stack: **PHP 8 + MySQL** (sem frameworks). Local: XAMPP. Produção: **Vercel** + MySQL externo.

---

## Funcionalidades

- Catálogo com categorias, promoções, stock e **várias imagens** por produto
- Página de **detalhes** do produto com galeria
- Carrinho e checkout com zonas de entrega em Bissau
- Confirmação do pedido via **WhatsApp**
- Conta de cliente: perfil e acompanhamento de pedidos
- **Admin**: produtos, pedidos, hero/slides, conteúdos, zonas, promoções, opções
- **Entregador**: entregas activas e marcação de pagamento recebido
- Login local + opcional **Google OAuth**
- Tema claro/escuro e layout responsivo (telemóvel e desktop)

---

## Requisitos

- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL + PHP 8+) para desenvolvimento local
- Em produção: conta [Vercel](https://vercel.com/) + **MySQL externo** (ex.: Railway, Aiven, PlanetScale, TiDB)
- Navegador moderno

Pasta recomendada (local):

```text
C:\xampp\htdocs\No_chao
```

URL local: `http://localhost/No_chao/`

---

## Instalação rápida (local)

1. Copie o projecto para `htdocs\No_chao`
2. Inicie **Apache** e **MySQL** no XAMPP
3. Abra [http://localhost/No_chao/install.php](http://localhost/No_chao/install.php)
4. Escreva `INSTALAR` e confirme
5. Abra a loja: [http://localhost/No_chao/](http://localhost/No_chao/)

A instalação cria a base `nu_chao`, as tabelas e contas demo.  
É gerado o ficheiro `config/install.lock` para bloquear novas instalações.

> Em produção, remova ou proteja `install.php` após instalar.

---

## Deploy no Vercel

O PHP corre via runtime comunitário [`vercel-php`](https://github.com/vercel-community/php) (`api/index.php` como front-controller). O Vercel **não inclui MySQL** nem disco persistente.

### 1. Base de dados MySQL externa

1. Crie uma base MySQL acessível na Internet
2. Importe [`sql/schema.sql`](sql/schema.sql) e, se necessário, as migrações em `sql/`
3. Guarde host, nome da BD, utilizador e palavra-passe

### 2. Projecto no Vercel

1. Faça push do repositório para GitHub/GitLab/Bitbucket
2. Em [vercel.com](https://vercel.com/) → **Add New Project** → importe o repo
3. Framework preset: **Other** (não é Next.js)
4. Defina as variáveis de ambiente (abaixo) e faça o deploy

Ficheiros de deploy já incluídos: `vercel.json`, `api/index.php`, `.vercelignore`.

### 3. Variáveis de ambiente

| Variável | Valor no Vercel | Notas |
|----------|-----------------|--------|
| `DB_HOST` | host do MySQL | obrigatório |
| `DB_NAME` | nome da base | obrigatório |
| `DB_USER` | utilizador | obrigatório |
| `DB_PASS` | palavra-passe | obrigatório (pode ser vazia) |
| `APP_BASE_URL` | *(vazio)* | site na raiz `https://seu-projecto.vercel.app` |
| `APP_WHATSAPP` | ex. `245955000000` | só dígitos, com indicativo |
| `GOOGLE_CLIENT_ID` | opcional | OAuth |
| `GOOGLE_CLIENT_SECRET` | opcional | OAuth |
| `GOOGLE_REDIRECT_URI` | `https://seu-projecto.vercel.app/auth/google_callback.php` | se usar Google |

Se `VERCEL` estiver definido e `APP_BASE_URL` não for definida, a base URL fica vazia automaticamente.

### 4. Limitações no Vercel

- **Uploads de imagens** (produtos, hero) gravam em disco local e **não persistem** entre deploys/invocações. Para produção estável, será preciso storage externo (S3, Cloudinary, etc.) — ainda não integrado.
- Prefira importar o schema SQL na BD externa em vez de depender de `install.php` no Vercel.
- Após o primeiro deploy, altere as palavras-passe das contas demo.

### 5. CLI (opcional)

```bash
npm i -g vercel
vercel login
vercel
```

---

## Contas demo

| Perfil | Email | Senha |
|--------|-------|-------|
| Admin | `admin@nochao.gw` | `admin123` |
| Entregador | `entregador@nochao.gw` | `entrega123` |
| Cliente | `cliente@nochao.gw` | `cliente123` |

**Altere estas palavras-passe** depois do primeiro acesso.

---

## Estrutura do projecto

```text
No_chao/
├── index.php              # Página inicial (loja)
├── install.php            # Instalador da BD
├── vercel.json            # Configuração Vercel
├── api/
│   ├── index.php          # Front-controller (Vercel)
│   └── registar.php       # API de registo
├── admin/                 # Painel de gestão
├── entregador/            # Área do entregador
├── loja/                  # Produto, carrinho, checkout, contacto, sobre
├── conta/                 # Perfil e pedidos do cliente
├── auth/                  # Login, logout, registo, Google
├── assets/                # CSS, JS, imagens, uploads
├── config/                # BD, Google OAuth
├── includes/              # Funções, header, footer, ícones
└── sql/                   # schema.sql e migrações
```

---

## Configuração

### Base de dados

Por defeito (local XAMPP) usa `127.0.0.1` / `root` / BD `nu_chao` e `APP_BASE_URL=/No_chao`.  
Em produção, prefira **variáveis de ambiente** (ver secção Vercel). Fallback em `config/database.php`:

```text
DB_HOST, DB_NAME, DB_USER, DB_PASS, APP_BASE_URL, APP_WHATSAPP
```

### WhatsApp da loja

No checkout, o pedido é guardado e abre o WhatsApp com a mensagem pronta.  
Configure o número em `APP_WHATSAPP` ou nas opções do admin.

### Google Login (opcional)

1. Crie credenciais OAuth em [Google Cloud Console](https://console.cloud.google.com/apis/credentials)
2. URI de redireccionamento (local):
   ```text
   http://localhost/No_chao/auth/google_callback.php
   ```
   Em produção (Vercel), use o domínio do projecto e a env `GOOGLE_REDIRECT_URI`.
3. Copie `config/google.local.php.example` → `config/google.local.php` (local) **ou** defina as envs no Vercel
4. Preencha `GOOGLE_CLIENT_ID` e `GOOGLE_CLIENT_SECRET`

`google.local.php` não deve ser commitado (já está no `.gitignore`).

---

## Fluxo de venda

1. **Cliente** navega, adiciona ao carrinho e finaliza (zona + morada)
2. **Admin** confirma o pedido e atribui um entregador (`/admin/pedidos.php`)
3. **Entregador** inicia a entrega e, ao concluir, marca **Entregue · Pagamento recebido**

Moeda: **XOF** (Franco CFA).

---

## URLs principais

| Área | Local (XAMPP) | Vercel |
|------|---------------|--------|
| Loja | `/No_chao/` | `/` |
| Admin | `/No_chao/admin/` | `/admin/` |
| Entregador | `/No_chao/entregador/` | `/entregador/` |
| Login | `/No_chao/auth/login.php` | `/auth/login.php` |
| Carrinho | `/No_chao/loja/carrinho.php` | `/loja/carrinho.php` |

---

## Segurança (resumo)

- Sessões com cookies `HttpOnly` / `SameSite`
- Protecção **CSRF** nos formulários POST
- Rate limit no login/registo
- Uploads validados (MIME + imagem real)
- Pasta `uploads` sem execução de PHP
- Segredos Google fora do repositório

---

## Licença / uso

Projecto da loja **Nu Chao**. Use e adapte conforme a sua operação em Bissau e arredores.
