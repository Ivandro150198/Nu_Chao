# Nu Chao — Loja de Roupas e Acessórios (Guiné-Bissau)

E-commerce com **pagamento na entrega (COD)**, área de **gestão de produtos** e área do **entregador (deliver)**.

## Requisitos

- XAMPP (Apache + MySQL + PHP 8+)
- Pasta do projecto em `C:\xampp\htdocs\No_chao`

## Instalação

1. Inicie **Apache** e **MySQL** no XAMPP.
2. Abra no browser: [http://localhost/No_chao/install.php](http://localhost/No_chao/install.php)
3. Depois de instalar, apague ou renomeie `install.php`.
4. Loja: [http://localhost/No_chao/](http://localhost/No_chao/)

## WhatsApp

Ao confirmar o checkout, o pedido é guardado na base de dados e o WhatsApp abre com a mensagem pronta (itens, zona, total e pagamento na entrega).

Altere o número da loja em `config/database.php`:

```php
define('APP_WHATSAPP', '245955000000'); // só dígitos, com indicativo do país
```

## Login com Google

1. Crie credenciais OAuth em [Google Cloud Console](https://console.cloud.google.com/apis/credentials) (tipo **Aplicação Web**).
2. Adicione o URI de redirecionamento:
   `http://localhost/No_chao/auth/google_callback.php`
3. Cole o Client ID e o Client Secret em `config/google.php`.

No **registo**, escolha Cliente ou Entregador e use **Registar com Google**.  
Entregadores continuam a precisar de aprovação do admin. Clientes entram logo.

## Contas de demonstração

| Perfil | Email | Palavra-passe |
|--------|-------|---------------|
| Admin | `admin@nochao.gw` | `admin123` |
| Entregador | `entregador@nochao.gw` | `entrega123` |
| Cliente | `cliente@nochao.gw` | `cliente123` |

## Fluxo

1. **Cliente** regista-se, adiciona produtos ao carrinho e finaliza com zona + morada (paga só na entrega).
2. **Admin** confirma o pedido e atribui um entregador em `/admin/pedidos.php`.
3. **Entregador** em `/entregador/` inicia a entrega e, ao entregar, marca **Entregue · Pagamento recebido**.

## Áreas

- `/` — Loja (início)
- `/loja/` — Produtos, carrinho, checkout, contacto, sobre
- `/conta/` — Perfil, pedidos do cliente
- `/auth/` — Login, logout, registo, Google OAuth
- `/admin/` — Dashboard, produtos, pedidos, entregadores, CMS
- `/entregador/` — Entregas activas e conclusão com pagamento
- `/api/` — Endpoints (ex.: registo)

## Moeda e zonas

Preços em **XOF** (Franco CFA). Zonas de exemplo: Bissau Centro, Bandim, Antula, Bairro Militar, Cuntum, Mindara, Cupelon, Bôr.

## Configuração da BD

Edite `config/database.php` se o MySQL não usar `root` sem palavra-passe.
