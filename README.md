📊 Sistema de Controle Financeiro Pessoal
Um sistema completo para gestão de finanças pessoais desenvolvido em PHP, HTML, CSS e JavaScript, com armazenamento em MySQL.

    https://img.shields.io/badge/Status-Funcional-green
    https://img.shields.io/badge/PHP-8.2+-blue
    https://img.shields.io/badge/MySQL-5.7+-orange
    https://img.shields.io/badge/License-MIT-yellow

✨ Funcionalidades

💼 Gestão Financeira Completa:

    ✅ Dashboard com visão geral das finanças
    ✅ Renda fixa e variável com histórico
    ✅ Gastos fixos mensais com status de pagamento
    ✅ Controle de entretenimento e gastos não essenciais
    ✅ Investimentos e metas financeiras
    ✅ Reserva de emergência com acompanhamento

📱 Interface Moderna:

    ✅ Design responsivo (mobile/desktop)
    ✅ Cards de saldo em tempo real
    ✅ Modais intuitivos para adicionar dados
    ✅ Gráficos e relatórios visuais
    ✅ Notificações toast

🔐 Segurança e Usabilidade:

    ✅ Sistema de login e cadastro
    ✅ Sessões PHP seguras
    ✅ Armazenamento local (localStorage) como backup
    ✅ Auto-save automático
    ✅ Validação de formulários

🚀 Instalação Rápida
Pré-requisitos:

    XAMPP (Apache + MySQL + PHP)
    Navegador web moderno
    Conexão com internet (para Font Awesome)

Passo 1: Configurar Ambiente:

    Instale o XAMPP
    Inicie o Apache e MySQL no XAMPP Control Panel
    Acesse o phpMyAdmin: http://localhost/phpmyadmin

Passo 2: Configurar Banco de Dados:

    No phpMyAdmin, clique em SQL
    Execute o conteúdo do arquivo database.sql
    O banco controle_financeiro será criado automaticamente

Passo 3: Configurar Projeto:
    Coloque os arquivos na pasta
    C:\xampp\htdocs\controle-financeiro\

Estrutura de pastas:

    controle-financeiro/
    ├── index.php
    ├── login.php
    ├── dashboard.php
    ├── logout.php
    ├── config/
    │   └── db_config.php
    ├── assets/
    │   ├── css/
    │   │   ├── style.css
    │   │   └── login.css
    │   └── js/
    │       ├── main.js
    │       └── login.js
    └── database.sql

Passo 4: Testar o Sistema:

    Acesse: http://localhost/controle-financeiro/
    Use as credenciais de teste:
    Email: joao@email.com
    Senha: 123456

🎯 Como Usar
1. Primeiro Acesso
    Faça login com as credenciais de teste
    Explore o dashboard com dados de exemplo
    Ou crie uma nova conta

2. Configurar Suas Finanças
    Defina sua renda fixa (salário principal)
    Adicione gastos fixos (aluguel, contas, etc.)
    Configure metas (reserva, objetivos)
    Registre investimentos

3. Uso Diário
    Renda variável: Adicione quando receber extras
    Entretenimento: Registre gastos não essenciais
    Acompanhe: Veja saldos em tempo real
    Relatórios: Monitore seu progresso mensal

📁 Estrutura do Banco de Dados
Tabela Principal: usuarios
Campo	          Tipo	               Descrição
id	           INT                Identificador único
nome	       VARCHAR(100)	      Nome do usuário
email	       VARCHAR(100)	      Email (único)
senha	       VARCHAR(255)	      Senha criptografada SHA256
dados_financeiros	JSON	      Todos os dados financeiros

Estrutura JSON Armazenada:
        {
        "renda_fixa": 3500.00,
        "renda_variavel": [
            {"id": 1, "descricao": "Freelance", "valor": 500.00, "data": "2024-01-15"}
        ],
        "gastos_fixos": [
            {"id": 1, "nome": "Aluguel", "valor": 1200.00, "status": "pendente"}
        ],
        "gastos_entretenimento": [
            {"id": 1, "descricao": "Cinema", "valor": 80.00, "data": "2024-01-05"}
        ],
        "investimentos": [
            {"id": 1, "nome": "Tesouro Direto", "valor": 2000.00}
        ],
        "objetivos": [
            {"id": 1, "nome": "Viagem", "valor_meta": 5000.00, "valor_atual": 1500.00}
        ],
        "reserva": 2000.00,
        "config": {
            "moeda": "R$",
            "formato_data": "dd/mm/yyyy",
            "notificacoes": true
        }
        }

🔧 Tecnologias Utilizadas

Backend:
    PHP 8.2+ - Lógica do servidor
    MySQL 5.7+ - Banco de dados
    PDO - Conexão segura com banco
    Sessões PHP - Autenticação

Frontend:
    HTML5 - Estrutura semântica
    CSS3 - Estilos e responsividade
    JavaScript (ES6+) - Interatividade
    Font Awesome - Ícones
    Chart.js - Gráficos (opcional)

Arquitetura:
    MVC Simplificado - Separação de responsabilidades
    REST-like - Comunicação cliente-servidor
    Responsive Design - Mobile-first

🛠️ Personalização

Mudar Cores
Edite as variáveis CSS em assets/css/style.css:

    :root {
        --primary-color: #4361ee;    /* Azul principal */
        --secondary-color: #3a0ca3;  /* Azul escuro */
        --success-color: #4cc9f0;    /* Verde-azulado */
        --warning-color: #f72585;    /* Rosa */
        --danger-color: #7209b7;     /* Roxo */
    }