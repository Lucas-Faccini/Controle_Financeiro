// assets/js/main.js - VERSÃO SIMPLIFICADA E FUNCIONAL
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard carregado');
    
    // Inicializar dados
    initDashboard();
    setupEventListeners();
});

function initDashboard() {
    console.log('Inicializando dashboard...');
    
    // Carregar dados do localStorage ou inicializar
    let financialData = JSON.parse(localStorage.getItem('financial_data')) || getInitialData();
    
    // Atualizar interface
    updateUI(financialData);
    
    // Salvar referência global
    window.financialData = financialData;
}

function getInitialData() {
    return {
        renda_fixa: 0,
        renda_variavel: [],
        gastos_fixos: [],
        gastos_entretenimento: [],
        investimentos: [],
        objetivos: [],
        reserva: 0,
        config: {
            moeda: 'R$',
            formato_data: 'dd/mm/yyyy'
        }
    };
}

function updateUI(data) {
    console.log('Atualizando UI...');
    
    // Atualizar saldos
    updateBalances(data);
    
    // Atualizar listas
    updateLists(data);
}

function updateBalances(data) {
    // Calcular totais
    let totalRendaVariavel = data.renda_variavel.reduce((sum, item) => sum + (parseFloat(item.valor) || 0), 0);
    let totalGastosFixos = data.gastos_fixos.reduce((sum, item) => sum + (parseFloat(item.valor) || 0), 0);
    let totalEntretenimento = data.gastos_entretenimento.reduce((sum, item) => sum + (parseFloat(item.valor) || 0), 0);
    let totalInvestimentos = data.investimentos.reduce((sum, item) => sum + (parseFloat(item.valor) || 0), 0);
    let totalObjetivos = data.objetivos.reduce((sum, item) => sum + (parseFloat(item.valor_atual) || 0), 0);
    
    let rendaTotal = (parseFloat(data.renda_fixa) || 0) + totalRendaVariavel;
    let gastosTotal = totalGastosFixos + totalEntretenimento;
    let saldoDisponivel = rendaTotal - gastosTotal;
    
    // Atualizar elementos
    updateElement('available-balance', formatCurrency(saldoDisponivel));
    updateElement('invested-balance', formatCurrency(totalInvestimentos));
    updateElement('reserve-balance', formatCurrency(data.reserva || 0));
    updateElement('goal-balance', formatCurrency(totalObjetivos));
    
    // Rendas
    updateElement('fixed-income-display', formatCurrency(data.renda_fixa || 0));
    updateElement('variable-income-display', formatCurrency(totalRendaVariavel));
}

function updateLists(data) {
    // Lista de rendas variáveis (últimas 3)
    let incomeList = document.getElementById('variable-income-list');
    if (incomeList) {
        let lastIncomes = data.renda_variavel.slice(-3).reverse();
        if (lastIncomes.length > 0) {
            incomeList.innerHTML = lastIncomes.map(income => `
                <div class="transaction-item">
                    <div class="transaction-info">
                        <strong>${income.descricao || 'Sem descrição'}</strong>
                        <small>${formatDate(income.data)}</small>
                    </div>
                    <span class="transaction-amount positive">
                        + ${formatCurrency(income.valor || 0)}
                    </span>
                </div>
            `).join('');
        } else {
            incomeList.innerHTML = '<p class="empty-message">Nenhuma renda variável registrada</p>';
        }
    }
    
    // Tabela de gastos fixos
    let expensesTable = document.getElementById('fixed-expenses-table');
    if (expensesTable) {
        if (data.gastos_fixos.length > 0) {
            expensesTable.innerHTML = data.gastos_fixos.map((gasto, index) => `
                <tr>
                    <td>${gasto.nome || 'Sem nome'}</td>
                    <td>${formatCurrency(gasto.valor || 0)}</td>
                    <td>Dia ${gasto.dia_vencimento || '?'}</td>
                    <td>
                        <span class="status-badge status-${gasto.status || 'pendente'}">
                            ${gasto.status || 'pendente'}
                        </span>
                    </td>
                    <td>
                        <button class="btn-icon" onclick="toggleExpenseStatus(${index})">
                            <i class="fas fa-${gasto.status === 'pago' ? 'undo' : 'check'}"></i>
                        </button>
                        <button class="btn-icon btn-delete" onclick="deleteFixedExpense(${index})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        } else {
            expensesTable.innerHTML = `
                <tr>
                    <td colspan="5" class="empty-message">
                        Nenhum gasto fixo cadastrado
                    </td>
                </tr>
            `;
        }
    }
    
    // Lista de entretenimento
    let entertainmentList = document.getElementById('entertainment-list');
    if (entertainmentList) {
        if (data.gastos_entretenimento.length > 0) {
            let lastEntertainment = data.gastos_entretenimento.slice(-5).reverse();
            entertainmentList.innerHTML = lastEntertainment.map((gasto, index) => `
                <div class="entertainment-item">
                    <div class="entertainment-info">
                        <strong>${gasto.descricao || 'Sem descrição'}</strong>
                        <small>${gasto.categoria || 'Geral'} • ${formatDate(gasto.data, 'dd/mm')}</small>
                    </div>
                    <div class="entertainment-actions">
                        <span class="entertainment-amount">
                            ${formatCurrency(gasto.valor || 0)}
                        </span>
                        <button class="btn-icon btn-delete" onclick="deleteEntertainment(${data.gastos_entretenimento.length - 1 - index})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `).join('');
        } else {
            entertainmentList.innerHTML = '<p class="empty-message">Nenhum gasto com entretenimento</p>';
        }
    }
    
    // Lista de investimentos
    let investmentsList = document.getElementById('investments-list');
    if (investmentsList) {
        if (data.investimentos.length > 0) {
            investmentsList.innerHTML = data.investimentos.map(invest => `
                <div class="investment-item">
                    <div class="investment-info">
                        <strong>${invest.nome || 'Sem nome'}</strong>
                        <small>${invest.tipo || 'Investimento'}</small>
                    </div>
                    <span class="investment-amount">
                        ${formatCurrency(invest.valor || 0)}
                    </span>
                </div>
            `).join('');
        } else {
            investmentsList.innerHTML = '<p class="empty-message">Nenhum investimento cadastrado</p>';
        }
    }
    
    // Lista de objetivos
    let goalsList = document.getElementById('goals-list');
    if (goalsList) {
        if (data.objetivos.length > 0) {
            goalsList.innerHTML = data.objetivos.map(objetivo => {
                let progress = objetivo.valor_meta > 0 ? 
                    (objetivo.valor_atual / objetivo.valor_meta) * 100 : 0;
                
                return `
                    <div class="goal-item">
                        <div class="goal-header">
                            <h4>${objetivo.nome || 'Sem nome'}</h4>
                            <span class="goal-priority priority-${objetivo.prioridade || 'media'}">
                                ${objetivo.prioridade || 'media'}
                            </span>
                        </div>
                        
                        <div class="goal-progress">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: ${Math.min(100, progress)}%"></div>
                            </div>
                            <div class="goal-values">
                                <span>${formatCurrency(objetivo.valor_atual || 0)}</span>
                                <span>${formatCurrency(objetivo.valor_meta || 0)}</span>
                            </div>
                        </div>
                        
                        ${objetivo.data_limite ? `
                            <div class="goal-deadline">
                                <i class="fas fa-calendar-alt"></i>
                                ${formatDate(objetivo.data_limite)}
                            </div>
                        ` : ''}
                    </div>
                `;
            }).join('');
        } else {
            goalsList.innerHTML = '<p class="empty-message">Nenhum objetivo definido</p>';
        }
    }
}

function setupEventListeners() {
    console.log('Configurando event listeners...');
    
    // Botões de adicionar
    document.querySelectorAll('.btn-add').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            let modalType = this.getAttribute('data-modal');
            if (modalType) {
                openModal(modalType + '-modal');
            }
        });
    });
    
    // Botão editar renda fixa
    let editIncomeBtn = document.querySelector('.btn-edit');
    if (editIncomeBtn) {
        editIncomeBtn.addEventListener('click', editFixedIncome);
    }
    
    // Formulários dos modais
    setupModalForms();
    
    // Fechar modais
    document.querySelectorAll('.close-modal').forEach(btn => {
        btn.addEventListener('click', function() {
            let modal = this.closest('.modal');
            if (modal) {
                closeModal(modal.id);
            }
        });
    });
    
    // Fechar modal ao clicar fora
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });
    
    // Logout
    let logoutBtn = document.querySelector('.logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Deseja realmente sair?')) {
                window.location.href = 'logout.php';
            }
        });
    }
}

function setupModalForms() {
    // Formulário de renda fixa
    let fixedIncomeForm = document.getElementById('fixed-income-form');
    if (fixedIncomeForm) {
        fixedIncomeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            let amount = parseFloat(document.getElementById('fixed-income-amount').value) || 0;
            window.financialData.renda_fixa = amount;
            saveData();
            closeModal('income-modal');
        });
    }
    
    // Formulário de renda variável
    let variableIncomeForm = document.getElementById('variable-income-form');
    if (variableIncomeForm) {
        variableIncomeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            let desc = document.getElementById('var-income-desc').value.trim();
            let amount = parseFloat(document.getElementById('var-income-amount').value) || 0;
            let date = document.getElementById('var-income-date').value;
            let category = document.getElementById('var-income-category').value;
            
            if (!desc || amount <= 0) {
                alert('Preencha todos os campos corretamente!');
                return;
            }
            
            window.financialData.renda_variavel.push({
                id: Date.now(),
                descricao: desc,
                valor: amount,
                data: date,
                categoria: category
            });
            
            saveData();
            this.reset();
            closeModal('variable-income-modal');
        });
    }
    
    // Formulário de gastos fixos
    let fixedExpenseForm = document.getElementById('fixed-expense-form');
    if (fixedExpenseForm) {
        fixedExpenseForm.addEventListener('submit', function(e) {
            e.preventDefault();
            let name = document.getElementById('expense-name').value.trim();
            let amount = parseFloat(document.getElementById('expense-amount').value) || 0;
            let dueDay = parseInt(document.getElementById('expense-due-day').value) || 1;
            let category = document.getElementById('expense-category').value;
            
            if (!name || amount <= 0) {
                alert('Preencha todos os campos corretamente!');
                return;
            }
            
            window.financialData.gastos_fixos.push({
                id: Date.now(),
                nome: name,
                valor: amount,
                dia_vencimento: dueDay,
                categoria: category,
                status: 'pendente'
            });
            
            saveData();
            this.reset();
            closeModal('fixed-expense-modal');
        });
    }
    
    // Formulário de entretenimento
    let entertainmentForm = document.getElementById('entertainment-form');
    if (entertainmentForm) {
        entertainmentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            let desc = document.getElementById('entertainment-desc').value.trim();
            let amount = parseFloat(document.getElementById('entertainment-amount').value) || 0;
            let date = document.getElementById('entertainment-date').value;
            let category = document.getElementById('entertainment-category').value;
            
            if (!desc || amount <= 0) {
                alert('Preencha todos os campos corretamente!');
                return;
            }
            
            window.financialData.gastos_entretenimento.push({
                id: Date.now(),
                descricao: desc,
                valor: amount,
                data: date,
                categoria: category
            });
            
            saveData();
            this.reset();
            closeModal('entertainment-modal');
        });
    }
}

// Funções utilitárias globais
window.openModal = function(modalId) {
    console.log('Abrindo modal:', modalId);
    let modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        
        // Focar no primeiro campo
        setTimeout(() => {
            let firstInput = modal.querySelector('input, select');
            if (firstInput) firstInput.focus();
        }, 100);
    }
}

window.closeModal = function(modalId) {
    console.log('Fechando modal:', modalId);
    let modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        
        // Resetar formulário
        let form = modal.querySelector('form');
        if (form) form.reset();
    }
}

window.editFixedIncome = function() {
    let currentValue = window.financialData?.renda_fixa || 0;
    let newValue = prompt('Digite o novo valor da renda fixa:', currentValue);
    
    if (newValue !== null) {
        let amount = parseFloat(newValue.replace(',', '.')) || 0;
        if (!isNaN(amount) && amount >= 0) {
            window.financialData.renda_fixa = amount;
            saveData();
            showToast('Renda fixa atualizada!', 'success');
        } else {
            alert('Valor inválido!');
        }
    }
}

window.toggleExpenseStatus = function(index) {
    if (window.financialData?.gastos_fixos && window.financialData.gastos_fixos[index]) {
        let expense = window.financialData.gastos_fixos[index];
        expense.status = expense.status === 'pago' ? 'pendente' : 'pago';
        saveData();
        showToast(`Gasto ${expense.status === 'pago' ? 'marcado como pago' : 'pendente'}!`, 'success');
    }
}

window.deleteFixedExpense = function(index) {
    if (window.financialData?.gastos_fixos && window.financialData.gastos_fixos[index]) {
        if (confirm('Deseja realmente excluir este gasto fixo?')) {
            window.financialData.gastos_fixos.splice(index, 1);
            saveData();
            showToast('Gasto fixo excluído!', 'success');
        }
    }
}

window.deleteEntertainment = function(index) {
    if (window.financialData?.gastos_entretenimento && window.financialData.gastos_entretenimento[index]) {
        if (confirm('Deseja realmente excluir este gasto?')) {
            window.financialData.gastos_entretenimento.splice(index, 1);
            saveData();
            showToast('Gasto excluído!', 'success');
        }
    }
}

function saveData() {
    try {
        // Salvar no localStorage
        localStorage.setItem('financial_data', JSON.stringify(window.financialData));
        
        // Opcional: salvar no servidor
        saveToServer();
        
        // Atualizar UI
        updateUI(window.financialData);
        
        return true;
    } catch (error) {
        console.error('Erro ao salvar dados:', error);
        showToast('Erro ao salvar dados!', 'error');
        return false;
    }
}

function saveToServer() {
    // Implementação futura com AJAX
    console.log('Dados salvos localmente. Implementar AJAX para servidor.');
}

function updateElement(id, content) {
    let element = document.getElementById(id);
    if (element) {
        element.textContent = content;
    }
}

function formatCurrency(value) {
    return 'R$ ' + parseFloat(value).toFixed(2).replace('.', ',');
}

function formatDate(dateString, format = 'dd/mm/yyyy') {
    if (!dateString) return 'N/A';
    
    try {
        let date = new Date(dateString);
        if (isNaN(date.getTime())) return dateString;
        
        let day = date.getDate().toString().padStart(2, '0');
        let month = (date.getMonth() + 1).toString().padStart(2, '0');
        let year = date.getFullYear();
        
        if (format === 'dd/mm') {
            return `${day}/${month}`;
        }
        
        return `${day}/${month}/${year}`;
    } catch (e) {
        return dateString;
    }
}

function showToast(message, type = 'info') {
    // Criar container se não existir
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 10000;
        `;
        document.body.appendChild(container);
    }
    
    // Criar toast
    let toast = document.createElement('div');
    toast.style.cssText = `
        background: ${type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : '#d1ecf1'};
        color: ${type === 'success' ? '#155724' : type === 'error' ? '#721c24' : '#0c5460'};
        padding: 12px 20px;
        margin-bottom: 10px;
        border-radius: 5px;
        border-left: 4px solid ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#17a2b8'};
        animation: slideIn 0.3s ease;
    `;
    
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        ${message}
    `;
    
    container.appendChild(toast);
    
    // Remover após 3 segundos
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Adicionar animações CSS
let style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);

// Inicializar quando a página carregar
console.log('Sistema financeiro iniciado');