-- Banco de dados para Controle Financeiro Pessoal
-- Execute este script no phpMyAdmin ou via linha de comando

CREATE DATABASE IF NOT EXISTS controle_financeiro 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE controle_financeiro;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultimo_login DATETIME,
    dados_financeiros JSON,
    
    -- Índices para performance
    INDEX idx_email (email),
    INDEX idx_data_cadastro (data_cadastro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserir usuário de teste
INSERT IGNORE INTO usuarios (nome, email, senha, dados_financeiros) VALUES 
('João Silva', 'joao@email.com', SHA2('123456', 256), 
'{
    "renda_fixa": 3500.00,
    "renda_variavel": [
        {"id": 1, "descricao": "Freelance", "valor": 500.00, "data": "2024-01-15", "categoria": "Trabalho"},
        {"id": 2, "descricao": "Venda de produtos", "valor": 300.00, "data": "2024-01-20", "categoria": "Vendas"}
    ],
    "gastos_fixos": [
        {"id": 1, "nome": "Aluguel", "valor": 1200.00, "dia_vencimento": 5, "status": "pendente", "categoria": "Moradia"},
        {"id": 2, "nome": "Energia Elétrica", "valor": 250.00, "dia_vencimento": 10, "status": "pendente", "categoria": "Contas"},
        {"id": 3, "nome": "Internet", "valor": 120.00, "dia_vencimento": 15, "status": "pago", "categoria": "Contas"},
        {"id": 4, "nome": "Plano de Saúde", "valor": 450.00, "dia_vencimento": 20, "status": "pendente", "categoria": "Saúde"}
    ],
    "gastos_entretenimento": [
        {"id": 1, "descricao": "Cinema", "valor": 80.00, "data": "2024-01-05", "categoria": "Lazer"},
        {"id": 2, "descricao": "Jantar fora", "valor": 150.00, "data": "2024-01-12", "categoria": "Alimentação"},
        {"id": 3, "descricao": "Assinatura Netflix", "valor": 39.90, "data": "2024-01-01", "categoria": "Streaming"}
    ],
    "investimentos": [
        {"id": 1, "nome": "Tesouro Direto", "valor": 2000.00, "tipo": "Renda Fixa", "data_inicio": "2024-01-01"},
        {"id": 2, "nome": "Ações", "valor": 1500.00, "tipo": "Renda Variável", "data_inicio": "2024-01-10"}
    ],
    "objetivos": [
        {"id": 1, "nome": "Viagem para praia", "valor_meta": 5000.00, "valor_atual": 1500.00, "prioridade": "alta", "data_limite": "2024-12-31"},
        {"id": 2, "nome": "Notebook novo", "valor_meta": 3500.00, "valor_atual": 800.00, "prioridade": "media", "data_limite": "2024-06-30"}
    ],
    "reserva": 2000.00,
    "ultimo_mes": "2024-01",
    "config": {
        "moeda": "R$",
        "formato_data": "dd/mm/yyyy",
        "notificacoes": true,
        "tema": "claro",
        "limite_entretenimento": 500.00
    }
}'),

('Maria Santos', 'maria@email.com', SHA2('123456', 256),
'{
    "renda_fixa": 4200.00,
    "renda_variavel": [],
    "gastos_fixos": [
        {"id": 1, "nome": "Condomínio", "valor": 800.00, "dia_vencimento": 10, "status": "pendente", "categoria": "Moradia"},
        {"id": 2, "nome": "Supermercado", "valor": 600.00, "dia_vencimento": 5, "status": "pendente", "categoria": "Alimentação"}
    ],
    "gastos_entretenimento": [],
    "investimentos": [],
    "objetivos": [
        {"id": 1, "nome": "Reserva de Emergência", "valor_meta": 10000.00, "valor_atual": 3000.00, "prioridade": "alta", "data_limite": "2024-12-31"}
    ],
    "reserva": 3000.00,
    "ultimo_mes": "2024-01",
    "config": {
        "moeda": "R$",
        "formato_data": "dd/mm/yyyy",
        "notificacoes": true,
        "tema": "claro",
        "limite_entretenimento": 300.00
    }
}');

-- Tabela de logs (opcional, para auditoria)
CREATE TABLE IF NOT EXISTS logs_acesso (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT,
    acao VARCHAR(50),
    descricao TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    data_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- View para relatório mensal (opcional)
CREATE OR REPLACE VIEW view_resumo_mensal AS
SELECT 
    id,
    nome,
    email,
    JSON_EXTRACT(dados_financeiros, '$.renda_fixa') as renda_fixa,
    JSON_LENGTH(JSON_EXTRACT(dados_financeiros, '$.renda_variavel')) as qtd_renda_variavel,
    JSON_LENGTH(JSON_EXTRACT(dados_financeiros, '$.gastos_fixos')) as qtd_gastos_fixos,
    JSON_EXTRACT(dados_financeiros, '$.reserva') as reserva,
    DATE_FORMAT(data_cadastro, '%d/%m/%Y') as data_cadastro_br,
    DATE_FORMAT(ultimo_login, '%d/%m/%Y %H:%i') as ultimo_login_br
FROM usuarios;

-- Criar usuário específico para a aplicação (opcional)
-- CREATE USER IF NOT EXISTS 'app_financeiro'@'localhost' IDENTIFIED BY 'SenhaSegura123';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON controle_financeiro.* TO 'app_financeiro'@'localhost';
-- FLUSH PRIVILEGES;

-- Exemplo de consulta útil:
-- SELECT nome, email, JSON_EXTRACT(dados_financeiros, '$.renda_fixa') as renda 
-- FROM usuarios 
-- WHERE JSON_EXTRACT(dados_financeiros, '$.renda_fixa') > 3000;