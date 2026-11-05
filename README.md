# 🚀 Gestor e CRM Open Source para Serviços de Assinatura & IPTV

Bem-vindo ao **Gestor IPTV**, uma solução moderna, robusta e segura para gestão de clientes, revendas e cobranças automáticas via Pix e WhatsApp. Criado com foco na comunidade e projetado para simplificar a operação de serviços de assinatura recorrente.

---

## ✨ Principais Funcionalidades

- 🔒 **Arquitetura Multi-Tenant com Isolamento Estrito:**
  - Revendedores só acessam dados vinculados ao seu próprio usuário (`user_id`) através de *TenantScope* global no Eloquent.
  - Controle de acesso baseado em funções (RBAC): Admin (1), Master (2), Cliente (3) e Revendedor (4).
- 🛡️ **Segurança Rigorosa de Credenciais:**
  - Chaves de API, segredos e tokens sensíveis do Mercado Pago e da Evolution API são criptografados no banco de dados (`encrypted` casts) e nunca salvos em texto puro.
- ⚡ **Integração com Mercado Pago (Pix Automático):**
  - Geração instantânea de Pix com QR Code e Copia e Cola.
  - Webhook seguro com validação de assinatura (`x-signature` HMAC SHA256) com renovação automática da data de vencimento e disparo de confirmação.
- 💬 **WhatsApp Automatizado (Evolution API):**
  - Gerenciamento de instâncias via QR Code diretamente no painel.
  - Envio automático de mensagens e mídias para notificações e cobranças.
- ⏰ **Automação de Cobranças (Artisan Scheduled Commands):**
  - Comando `php artisan cron:verificar-vencimentos` rodando diariamente via `schedule:run`.
  - Notifica assinantes com 3 dias de antecedência, no dia do vencimento e após 1 dia de atraso, com templates dinâmicos (`{nome}`, `{vencimento}`, `{link_pix}`, etc.).
- 📱 **Central do Assinante (Portal do Cliente):**
  - Consulta rápida de plano e vencimento pelo número de WhatsApp cadastrado.
  - Botão de autoatendimento para gerar Pix de renovação na hora.

---

## 🛠️ Tecnologias Utilizadas

- **PHP 8.2+** / **Laravel 11+**
- **Tailwind CSS** + **Alpine.js** + **Blade**
- **SQLite / MySQL / PostgreSQL**
- **Vite** para compilação de assets frontend

---

## 🚀 Como Executar Localmente

### 1. Clonar o repositório
```bash
git clone <url-do-repositorio>
cd <pasta-do-projeto>
```

### 2. Instalar dependências PHP e Node
```bash
composer install
npm install
npm run build
```

### 3. Configurar o ambiente
Copie o arquivo de exemplo e gere a chave da aplicação:
```bash
cp .env.example .env
php artisan key:generate
```

Ajuste as variáveis de conexão com o banco de dados e as integrações no arquivo `.env`.

### 4. Executar Migrações e Seeders
```bash
php artisan migrate --seed
```

> **Contas Padrão Criadas pelo Seeder:**
> - **Administrador:** WhatsApp `5511999999999` / Senha: `admin123`
> - **Revendedor:** WhatsApp `5511988888888` / Senha: `revenda123`

### 5. Iniciar o Servidor Local
```bash
php artisan serve
```
Acesse no seu navegador: `http://localhost:8000`

---

## ⏰ Configurando o Agendador de Tarefas (Cron)

Para que as cobranças automáticas funcionem todos os dias, configure o cron do seu servidor para executar o schedule do Laravel a cada minuto:

```bash
* * * * * cd /caminho-do-projeto && php artisan schedule:run >> /dev/null 2>&1
```

Você também pode testar a rotina manualmente a qualquer momento:
```bash
php artisan cron:verificar-vencimentos
```

---

## 🧪 Executando os Testes Automatizados

O projeto conta com uma suíte completa de testes de feature e unidade cobrindo autenticação, multi-tenant, webhooks, crons e segurança:

```bash
php artisan test
```

---

## 🤝 Contribuição e Comunidade

Contribuições são super bem-vindas! Sinta-se à vontade para abrir uma *Issue* ou enviar um *Pull Request* para melhorias, novos recursos ou integrações.
