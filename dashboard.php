<?php
// dashboard.php
session_start();
require_once 'config/db_config.php';

// Verificar se usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];

// Obter dados financeiros
$financial_data = DBConfig::getFinancialData($user_id);

if (!$financial_data) {
    $financial_data = [
        'renda_fixa' => 0,
        'renda_variavel' => [],
        'gastos_fixos' => [],
        'gastos_entretenimento' => [],
        'investimentos' => [],
        'objetivos' => [],
        'reserva' => 0,
        'ultimo_mes' => date('Y-m'),
        'config' => [
            'moeda' => 'R$',
            'formato_data' => 'dd/mm/yyyy',
            'notificacoes' => true,
            'tema' => 'claro',
            'limite_entretenimento' => 500
        ]
    ];
}

// Processar requisições AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'save_data':
            $input = json_decode(file_get_contents('php://input'), true);
            if ($input) {
                DBConfig::updateFinancialData($user_id, $input);
                echo json_encode(['success' => true, 'message' => 'Dados salvos com sucesso!']);
            }
            exit;
            
        case 'get_data':
            echo json_encode([
                'success' => true,
                'data' => $financial_data,
                'user' => [
                    'nome' => $user_name,
                    'email' => $user_email
                ]
            ]);
            exit;
    }
}

// Calcular totais para exibição inicial
function calcularTotais($data) {
    $totais = [
        'renda_variavel' => 0,
        'gastos_fixos' => 0,
        'entretenimento' => 0,
        'investimentos' => 0,
        'objetivos' => 0
    ];
    
    if (isset($data['renda_variavel']) && is_array($data['renda_variavel'])) {
        foreach ($data['renda_variavel'] as $item) {
            $totais['renda_variavel'] += $item['valor'] ?? 0;
        }
    }
    
    if (isset($data['gastos_fixos']) && is_array($data['gastos_fixos'])) {
        foreach ($data['gastos_fixos'] as $item) {
            $totais['gastos_fixos'] += $item['valor'] ?? 0;
        }
    }
    
    if (isset($data['gastos_entretenimento']) && is_array($data['gastos_entretenimento'])) {
        foreach ($data['gastos_entretenimento'] as $item) {
            $totais['entretenimento'] += $item['valor'] ?? 0;
        }
    }
    
    if (isset($data['investimentos']) && is_array($data['investimentos'])) {
        foreach ($data['investimentos'] as $item) {
            $totais['investimentos'] += $item['valor'] ?? 0;
        }
    }
    
    if (isset($data['objetivos']) && is_array($data['objetivos'])) {
        foreach ($data['objetivos'] as $item) {
            $totais['objetivos'] += $item['valor_atual'] ?? 0;
        }
    }
    
    return $totais;
}

$totais = calcularTotais($financial_data);
$renda_total = $financial_data['renda_fixa'] + $totais['renda_variavel'];
$gastos_total = $totais['gastos_fixos'] + $totais['entretenimento'];
$saldo_disponivel = $renda_total - $gastos_total;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Controle Financeiro</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
</head>
<body>
    <!-- Header do Dashboard -->
    <header class="dashboard-header">
        <div class="header-left">
            <div class="logo">
                <i class="fas fa-chart-line"></i>
                <h1>Controle Financeiro</h1>
            </div>
            <nav class="main-nav">
                <a href="#dashboard" class="nav-link active">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="#renda" class="nav-link">
                    <i class="fas fa-money-bill-wave"></i> Renda
                </a>
                <a href="#gastos" class="nav-link">
                    <i class="fas fa-receipt"></i> Gastos
                </a>
                <a href="#investimentos" class="nav-link">
                    <i class="fas fa-chart-bar"></i> Investimentos
                </a>
                <a href="#relatorios" class="nav-link">
                    <i class="fas fa-file-alt"></i> Relatórios
                </a>
            </nav>
        </div>
        
        <div class="header-right">
            <div class="user-menu">
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <div class="user-details">
                        <strong><?php echo htmlspecialchars($user_name); ?></strong>
                        <small><?php echo htmlspecialchars($user_email); ?></small>
                    </div>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-menu">
                    <a href="#perfil">
                        <i class="fas fa-user-cog"></i> Meu Perfil
                    </a>
                    <a href="#configuracoes">
                        <i class="fas fa-cog"></i> Configurações
                    </a>
                    <div class="divider"></div>
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Sair
                    </a>
                </div>
            </div>
            <button class="btn-notification">
                <i class="fas fa-bell"></i>
                <span class="notification-count">3</span>
            </button>
        </div>
    </header>

    <!-- Cards de Saldo -->
    <section class="balance-cards-section">
        <div class="container">
            <h2 class="section-title">
                <i class="fas fa-wallet"></i> Visão Geral
            </h2>
            
            <div class="balance-grid">
                <div class="balance-card card-saldo">
                    <div class="card-header">
                        <i class="fas fa-money-bill-wave"></i>
                        <h3>Saldo Disponível</h3>
                    </div>
                    <div class="card-body">
                        <p class="balance-amount" id="available-balance">
                            R$ <span><?php echo number_format($saldo_disponivel, 2, ',', '.'); ?></span>
                        </p>
                        <p class="balance-info">Renda total: R$ <?php echo number_format($renda_total, 2, ',', '.'); ?></p>
                    </div>
                </div>
                
                <div class="balance-card card-investido">
                    <div class="card-header">
                        <i class="fas fa-chart-line"></i>
                        <h3>Investido</h3>
                    </div>
                    <div class="card-body">
                        <p class="balance-amount" id="invested-balance">
                            R$ <span><?php echo number_format($totais['investimentos'], 2, ',', '.'); ?></span>
                        </p>
                        <p class="balance-info"><?php echo count($financial_data['investimentos'] ?? []); ?> investimento(s)</p>
                    </div>
                </div>
                
                <div class="balance-card card-reserva">
                    <div class="card-header">
                        <i class="fas fa-piggy-bank"></i>
                        <h3>Reserva</h3>
                    </div>
                    <div class="card-body">
                        <p class="balance-amount" id="reserve-balance">
                            R$ <span><?php echo number_format($financial_data['reserva'] ?? 0, 2, ',', '.'); ?></span>
                        </p>
                        <p class="balance-info">Fundo de emergência</p>
                    </div>
                </div>
                
                <div class="balance-card card-objetivo">
                    <div class="card-header">
                        <i class="fas fa-bullseye"></i>
                        <h3>Objetivos</h3>
                    </div>
                    <div class="card-body">
                        <p class="balance-amount" id="goal-balance">
                            R$ <span><?php echo number_format($totais['objetivos'], 2, ',', '.'); ?></span>
                        </p>
                        <p class="balance-info"><?php echo count($financial_data['objetivos'] ?? []); ?> meta(s)</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Conteúdo Principal -->
    <main class="dashboard-main">
        <div class="container">
            <div class="main-grid">
                <!-- Coluna Esquerda -->
                <div class="main-left">
                    <!-- Renda -->
                    <section class="dashboard-section">
                        <div class="section-header">
                            <h3><i class="fas fa-money-bill-wave"></i> Renda Mensal</h3>
                            <button class="btn-add" onclick="openModal('income-modal')">
                                <i class="fas fa-plus"></i> Adicionar
                            </button>
                        </div>
                        
                        <div class="income-cards">
                            <div class="income-card">
                                <h4>Renda Fixa</h4>
                                <p class="income-amount" id="fixed-income-display">
                                    R$ <?php echo number_format($financial_data['renda_fixa'] ?? 0, 2, ',', '.'); ?>
                                </p>
                                <button class="btn-edit" onclick="editFixedIncome()">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                            </div>
                            
                            <div class="income-card">
                                <h4>Renda Variável</h4>
                                <p class="income-amount" id="variable-income-display">
                                    R$ <?php echo number_format($totais['renda_variavel'], 2, ',', '.'); ?>
                                </p>
                                <button class="btn-add" onclick="openModal('variable-income-modal')">
                                    <i class="fas fa-plus"></i> Adicionar
                                </button>
                            </div>
                        </div>
                        
                        <div class="recent-transactions">
                            <h4>Últimas Rendas Variáveis</h4>
                            <div id="variable-income-list">
                                <?php if (!empty($financial_data['renda_variavel'])): ?>
                                    <?php foreach (array_slice($financial_data['renda_variavel'], -3) as $renda): ?>
                                        <div class="transaction-item">
                                            <div class="transaction-info">
                                                <strong><?php echo htmlspecialchars($renda['descricao'] ?? 'Sem descrição'); ?></strong>
                                                <small><?php echo date('d/m/Y', strtotime($renda['data'] ?? 'now')); ?></small>
                                            </div>
                                            <span class="transaction-amount positive">
                                                + R$ <?php echo number_format($renda['valor'] ?? 0, 2, ',', '.'); ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="empty-message">Nenhuma renda variável registrada</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </section>
                    
                    <!-- Gastos Fixos -->
                    <section class="dashboard-section">
                        <div class="section-header">
                            <h3><i class="fas fa-home"></i> Gastos Fixos</h3>
                            <button class="btn-add" onclick="openModal('fixed-expense-modal')">
                                <i class="fas fa-plus"></i> Adicionar
                            </button>
                        </div>
                        
                        <div class="expenses-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Descrição</th>
                                        <th>Valor</th>
                                        <th>Vencimento</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="fixed-expenses-table">
                                    <?php if (!empty($financial_data['gastos_fixos'])): ?>
                                        <?php foreach ($financial_data['gastos_fixos'] as $index => $gasto): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($gasto['nome'] ?? 'Sem nome'); ?></td>
                                                <td>R$ <?php echo number_format($gasto['valor'] ?? 0, 2, ',', '.'); ?></td>
                                                <td>Dia <?php echo $gasto['dia_vencimento'] ?? '?'; ?></td>
                                                <td>
                                                    <span class="status-badge status-<?php echo $gasto['status'] ?? 'pendente'; ?>">
                                                        <?php echo $gasto['status'] ?? 'pendente'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn-icon" onclick="toggleExpenseStatus(<?php echo $index; ?>)">
                                                        <i class="fas fa-<?php echo ($gasto['status'] ?? 'pendente') === 'pago' ? 'undo' : 'check'; ?>"></i>
                                                    </button>
                                                    <button class="btn-icon btn-delete" onclick="deleteFixedExpense(<?php echo $index; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="empty-message">
                                                Nenhum gasto fixo cadastrado
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="5" class="total-row">
                                            <strong>Total: R$ <?php echo number_format($totais['gastos_fixos'], 2, ',', '.'); ?></strong>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </section>
                </div>
                
                <!-- Coluna Direita -->
                <div class="main-right">
                    <!-- Gastos com Entretenimento -->
                    <section class="dashboard-section">
                        <div class="section-header">
                            <h3><i class="fas fa-gamepad"></i> Entretenimento</h3>
                            <button class="btn-add" onclick="openModal('entertainment-modal')">
                                <i class="fas fa-plus"></i> Adicionar
                            </button>
                        </div>
                        
                        <div class="entertainment-list" id="entertainment-list">
                            <?php if (!empty($financial_data['gastos_entretenimento'])): ?>
                                <?php foreach (array_slice($financial_data['gastos_entretenimento'], -5) as $index => $gasto): ?>
                                    <div class="entertainment-item">
                                        <div class="entertainment-info">
                                            <strong><?php echo htmlspecialchars($gasto['descricao'] ?? 'Sem descrição'); ?></strong>
                                            <small><?php echo htmlspecialchars($gasto['categoria'] ?? 'Geral'); ?> • 
                                                   <?php echo date('d/m', strtotime($gasto['data'] ?? 'now')); ?></small>
                                        </div>
                                        <div class="entertainment-actions">
                                            <span class="entertainment-amount">
                                                R$ <?php echo number_format($gasto['valor'] ?? 0, 2, ',', '.'); ?>
                                            </span>
                                            <button class="btn-icon btn-delete" onclick="deleteEntertainment(<?php echo $index; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="empty-message">Nenhum gasto com entretenimento</p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="entertainment-total">
                            <strong>Total do mês: R$ <?php echo number_format($totais['entretenimento'], 2, ',', '.'); ?></strong>
                            <?php if (isset($financial_data['config']['limite_entretenimento'])): ?>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo min(100, ($totais['entretenimento'] / $financial_data['config']['limite_entretenimento']) * 100); ?>%"></div>
                                </div>
                                <small>Limite: R$ <?php echo number_format($financial_data['config']['limite_entretenimento'], 2, ',', '.'); ?></small>
                            <?php endif; ?>
                        </div>
                    </section>
                    
                    <!-- Investimentos -->
                    <section class="dashboard-section">
                        <div class="section-header">
                            <h3><i class="fas fa-chart-pie"></i> Investimentos</h3>
                            <button class="btn-add" onclick="openModal('investment-modal')">
                                <i class="fas fa-plus"></i> Adicionar
                            </button>
                        </div>
                        
                        <div class="investments-list" id="investments-list">
                            <?php if (!empty($financial_data['investimentos'])): ?>
                                <?php foreach ($financial_data['investimentos'] as $index => $investimento): ?>
                                    <div class="investment-item">
                                        <div class="investment-info">
                                            <strong><?php echo htmlspecialchars($investimento['nome'] ?? 'Sem nome'); ?></strong>
                                            <small><?php echo htmlspecialchars($investimento['tipo'] ?? 'Investimento'); ?></small>
                                        </div>
                                        <span class="investment-amount">
                                            R$ <?php echo number_format($investimento['valor'] ?? 0, 2, ',', '.'); ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="empty-message">Nenhum investimento cadastrado</p>
                            <?php endif; ?>
                        </div>
                    </section>
                    
                    <!-- Objetivos -->
                    <section class="dashboard-section">
                        <div class="section-header">
                            <h3><i class="fas fa-bullseye"></i> Objetivos</h3>
                            <button class="btn-add" onclick="openModal('goal-modal')">
                                <i class="fas fa-plus"></i> Adicionar
                            </button>
                        </div>
                        
                        <div class="goals-list" id="goals-list">
                            <?php if (!empty($financial_data['objetivos'])): ?>
                                <?php foreach ($financial_data['objetivos'] as $index => $objetivo): ?>
                                    <div class="goal-item">
                                        <div class="goal-header">
                                            <h4><?php echo htmlspecialchars($objetivo['nome'] ?? 'Sem nome'); ?></h4>
                                            <span class="goal-priority priority-<?php echo $objetivo['prioridade'] ?? 'media'; ?>">
                                                <?php echo $objetivo['prioridade'] ?? 'media'; ?>
                                            </span>
                                        </div>
                                        
                                        <div class="goal-progress">
                                            <div class="progress-bar">
                                                <?php 
                                                $progress = ($objetivo['valor_meta'] ?? 1) > 0 ? 
                                                    (($objetivo['valor_atual'] ?? 0) / $objetivo['valor_meta']) * 100 : 0;
                                                ?>
                                                <div class="progress-fill" style="width: <?php echo min(100, $progress); ?>%"></div>
                                            </div>
                                            <div class="goal-values">
                                                <span>R$ <?php echo number_format($objetivo['valor_atual'] ?? 0, 2, ',', '.'); ?></span>
                                                <span>R$ <?php echo number_format($objetivo['valor_meta'] ?? 0, 2, ',', '.'); ?></span>
                                            </div>
                                        </div>
                                        
                                        <?php if (isset($objetivo['data_limite'])): ?>
                                            <div class="goal-deadline">
                                                <i class="fas fa-calendar-alt"></i>
                                                <?php echo date('d/m/Y', strtotime($objetivo['data_limite'])); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="empty-message">Nenhum objetivo definido</p>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal para Renda Fixa -->
    <div id="income-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('income-modal')">&times;</span>
            <h3><i class="fas fa-money-bill-wave"></i> Renda Fixa</h3>
            <form id="fixed-income-form">
                <div class="form-group">
                    <label for="fixed-income-amount">Valor Mensal (R$)</label>
                    <input type="number" id="fixed-income-amount" step="0.01" min="0" 
                           value="<?php echo $financial_data['renda_fixa']; ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Salvar
                </button>
            </form>
        </div>
    </div>

    <!-- Modal para Renda Variável -->
    <div id="variable-income-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('variable-income-modal')">&times;</span>
            <h3><i class="fas fa-money-bill-wave"></i> Nova Renda Variável</h3>
            <form id="variable-income-form">
                <div class="form-group">
                    <label for="var-income-desc">Descrição</label>
                    <input type="text" id="var-income-desc" placeholder="Ex: Freelance, Bico..." required>
                </div>
                <div class="form-group">
                    <label for="var-income-amount">Valor (R$)</label>
                    <input type="number" id="var-income-amount" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label for="var-income-date">Data</label>
                    <input type="date" id="var-income-date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="var-income-category">Categoria</label>
                    <select id="var-income-category">
                        <option value="trabalho">Trabalho</option>
                        <option value="vendas">Vendas</option>
                        <option value="investimentos">Investimentos</option>
                        <option value="presente">Presente</option>
                        <option value="outro">Outro</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Adicionar
                </button>
            </form>
        </div>
    </div>

    <!-- Modal para Gastos Fixos -->
    <div id="fixed-expense-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('fixed-expense-modal')">&times;</span>
            <h3><i class="fas fa-receipt"></i> Novo Gasto Fixo</h3>
            <form id="fixed-expense-form">
                <div class="form-group">
                    <label for="expense-name">Nome do Gasto</label>
                    <input type="text" id="expense-name" placeholder="Ex: Aluguel, Luz..." required>
                </div>
                <div class="form-group">
                    <label for="expense-amount">Valor Mensal (R$)</label>
                    <input type="number" id="expense-amount" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label for="expense-due-day">Dia de Vencimento</label>
                    <input type="number" id="expense-due-day" min="1" max="31" value="5" required>
                </div>
                <div class="form-group">
                    <label for="expense-category">Categoria</label>
                    <select id="expense-category">
                        <option value="moradia">Moradia</option>
                        <option value="alimentacao">Alimentação</option>
                        <option value="transporte">Transporte</option>
                        <option value="saude">Saúde</option>
                        <option value="educacao">Educação</option>
                        <option value="lazer">Lazer</option>
                        <option value="outro">Outro</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Adicionar
                </button>
            </form>
        </div>
    </div>

    <!-- Modal para Entretenimento -->
    <div id="entertainment-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('entertainment-modal')">&times;</span>
            <h3><i class="fas fa-gamepad"></i> Novo Gasto com Entretenimento</h3>
            <form id="entertainment-form">
                <div class="form-group">
                    <label for="entertainment-desc">Descrição</label>
                    <input type="text" id="entertainment-desc" placeholder="Ex: Cinema, Jantar..." required>
                </div>
                <div class="form-group">
                    <label for="entertainment-amount">Valor (R$)</label>
                    <input type="number" id="entertainment-amount" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label for="entertainment-date">Data</label>
                    <input type="date" id="entertainment-date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="entertainment-category">Categoria</label>
                    <select id="entertainment-category">
                        <option value="cinema">Cinema/Teatro</option>
                        <option value="restaurante">Restaurante</option>
                        <option value="jogos">Jogos</option>
                        <option value="shopping">Shopping</option>
                        <option value="viagem">Viagem</option>
                        <option value="hobby">Hobby</option>
                        <option value="outro">Outro</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Adicionar
                </button>
            </form>
        </div>
    </div>

    <!-- Modal para Investimentos -->
    <div id="investment-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('investment-modal')">&times;</span>
            <h3><i class="fas fa-chart-line"></i> Novo Investimento</h3>
            <form id="investment-form">
                <div class="form-group">
                    <label for="investment-name">Nome</label>
                    <input type="text" id="investment-name" placeholder="Ex: Tesouro Direto, Ações..." required>
                </div>
                <div class="form-group">
                    <label for="investment-amount">Valor Investido (R$)</label>
                    <input type="number" id="investment-amount" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label for="investment-type">Tipo</label>
                    <select id="investment-type">
                        <option value="renda_fixa">Renda Fixa</option>
                        <option value="renda_variavel">Renda Variável</option>
                        <option value="fundo">Fundo de Investimento</option>
                        <option value="cripto">Criptomoeda</option>
                        <option value="outro">Outro</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="investment-date">Data de Início</label>
                    <input type="date" id="investment-date" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Adicionar
                </button>
            </form>
        </div>
    </div>

    <!-- Modal para Objetivos -->
    <div id="goal-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('goal-modal')">&times;</span>
            <h3><i class="fas fa-bullseye"></i> Novo Objetivo</h3>
            <form id="goal-form">
                <div class="form-group">
                    <label for="goal-name">Nome do Objetivo</label>
                    <input type="text" id="goal-name" placeholder="Ex: Viagem, Carro Novo..." required>
                </div>
                <div class="form-group">
                    <label for="goal-target">Valor Meta (R$)</label>
                    <input type="number" id="goal-target" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label for="goal-current">Valor Atual (R$)</label>
                    <input type="number" id="goal-current" step="0.01" min="0" value="0">
                </div>
                <div class="form-group">
                    <label for="goal-priority">Prioridade</label>
                    <select id="goal-priority">
                        <option value="baixa">Baixa</option>
                        <option value="media" selected>Média</option>
                        <option value="alta">Alta</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="goal-deadline">Data Limite</label>
                    <input type="date" id="goal-deadline">
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Adicionar
                </button>
            </form>
        </div>
    </div>

    <!-- Toast Notifications -->
    <div id="toast-container"></div>

    <script src="assets/js/main.js"></script>
    <script src="assets/js/chart.js"></script>
    
    <script>
        // Dados iniciais
        let financialData = <?php echo json_encode($financial_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?>;
        
        // Inicializar
        document.addEventListener('DOMContentLoaded', function() {
            updateDashboard();
            setupEventListeners();
        });
    </script>
</body>
</html>