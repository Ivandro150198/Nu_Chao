# Nu Chao

Loja online de **roupas e acessórios** para a Guiné-Bissau, com **pagamento na entrega (COD)**, gestão completa do site e área do entregador.

Stack: **PHP 8 + MySQL + XAMPP** (sem frameworks).

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

- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL + PHP 8+)
- Navegador moderno

Pasta recomendada:

```text
C:\xampp\htdocs\No_chao
```

URL local: `http://localhost/No_chao/`

---

## Instalação rápida

1. Copie o projecto para `htdocs\No_chao`
2. Inicie **Apache** e **MySQL** no XAMPP
3. Abra [http://localhost/No_chao/install.php](http://localhost/No_chao/install.php)
4. Escreva `INSTALAR` e confirme
5. Abra a loja: [http://localhost/No_chao/](http://localhost/No_chao/)

A instalação cria a base `nu_chao`, as tabelas e contas demo.  
É gerado o ficheiro `config/install.lock` para bloquear novas instalações.

> Em produção, remova ou proteja `install.php` após instalar.

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
├── admin/                 # Painel de gestão
├── entregador/            # Área do entregador
├── loja/                  # Produto, carrinho, checkout, contacto, sobre
├── conta/                 # Perfil e pedidos do cliente
├── auth/                  # Login, logout, registo, Google
├── api/                   # Endpoints (ex.: registo)
├── assets/                # CSS, JS, imagens, uploads
├── config/                # BD, Google OAuth
├── includes/              # Funções, header, footer, ícones
└── sql/                   # schema.sql e migrações
```

---

## Configuração

### Base de dados

Edite `config/database.php` se necessário:

```php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'nu_chao');
define('DB_USER', 'root');
define('DB_PASS', '');
define('APP_BASE_URL', '/No_chao');
define('APP_WHATSAPP', '245955000000'); // só dígitos, com indicativo
```

### WhatsApp da loja

No checkout, o pedido é guardado e abre o WhatsApp com a mensagem pronta.  
Configure o número em `APP_WHATSAPP` ou nas opções do admin.

### Google Login (opcional)

1. Crie credenciais OAuth em [Google Cloud Console](https://console.cloud.google.com/apis/credentials)
2. URI de redireccionamento:
   ```text
   http://localhost/No_chao/auth/google_callback.php
   ```
3. Copie `config/google.local.php.example` → `config/google.local.php`
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

| Área | URL |
|------|-----|
| Loja | `/No_chao/` |
| Admin | `/No_chao/admin/` |
| Entregador | `/No_chao/entregador/` |
| Login | `/No_chao/auth/login.php` |
| Carrinho | `/No_chao/loja/carrinho.php` |

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
