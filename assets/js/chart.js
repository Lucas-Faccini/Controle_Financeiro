// assets/js/chart.js - Gráficos opcionais
class FinanceCharts {
    constructor() {
        this.charts = {};
        this.initialize();
    }

    initialize() {
        // Aguardar carregamento do DOM e dados
        setTimeout(() => {
            this.createCharts();
            this.setupChartUpdates();
        }, 1000);
    }

    createCharts() {
        this.createExpenseChart();
        this.createIncomeChart();
        this.createGoalProgressChart();
    }

    createExpenseChart() {
        const ctx = document.getElementById('expense-chart');
        if (!ctx) return;

        const data = window.financialManager?.data;
        if (!data || !data.gastos_fixos) return;

        // Agrupar por categoria
        const categories = {};
        data.gastos_fixos.forEach(expense => {
            const category = expense.categoria || 'Outro';
            categories[category] = (categories[category] || 0) + (expense.valor || 0);
        });

        this.charts.expense = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(categories),
                datasets: [{
                    data: Object.values(categories),
                    backgroundColor: this.generateColors(Object.keys(categories).length)
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    title: {
                        display: true,
                        text: 'Distribuição de Gastos Fixos'
                    }
                }
            }
        });
    }

    createIncomeChart() {
        const ctx = document.getElementById('income-chart');
        if (!ctx) return;

        const data = window.financialManager?.data;
        if (!data) return;

        const fixedIncome = data.renda_fixa || 0;
        const variableIncome = data.renda_variavel?.reduce((sum, item) => sum + (item.valor || 0), 0) || 0;

        this.charts.income = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Renda Fixa', 'Renda Variável'],
                datasets: [{
                    label: 'Valor (R$)',
                    data: [fixedIncome, variableIncome],
                    backgroundColor: ['#4361ee', '#4cc9f0']
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => `R$ ${value.toFixed(2)}`
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Comparativo de Renda'
                    }
                }
            }
        });
    }

    createGoalProgressChart() {
        const ctx = document.getElementById('goal-chart');
        if (!ctx) return;

        const data = window.financialManager?.data;
        if (!data || !data.objetivos || data.objetivos.length === 0) return;

        const goals = data.objetivos.slice(0, 5); // Mostrar apenas 5 primeiros

        this.charts.goals = new Chart(ctx, {
            type: 'horizontalBar',
            data: {
                labels: goals.map(g => g.nome || 'Objetivo'),
                datasets: [{
                    label: 'Valor Atual',
                    data: goals.map(g => g.valor_atual || 0),
                    backgroundColor: '#4361ee'
                }, {
                    label: 'Meta',
                    data: goals.map(g => g.valor_meta || 0),
                    backgroundColor: '#f72585'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => `R$ ${value.toFixed(2)}`
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Progresso dos Objetivos'
                    }
                }
            }
        });
    }

    generateColors(count) {
        const colors = [
            '#4361ee', '#3a0ca3', '#7209b7', '#f72585',
            '#4cc9f0', '#4895ef', '#560bad', '#b5179e'
        ];
        
        return colors.slice(0, count);
    }

    setupChartUpdates() {
        // Observar mudanças nos dados
        if (window.financialManager) {
            // Atualizar gráficos quando os dados mudarem
            const originalUpdate = window.financialManager.updateDashboard;
            window.financialManager.updateDashboard = function() {
                originalUpdate.call(this);
                FinanceCharts.updateAllCharts();
            };
        }
    }

    static updateAllCharts() {
        const instance = window.financeCharts;
        if (instance) {
            // Destruir gráficos antigos
            Object.values(instance.charts).forEach(chart => {
                if (chart) chart.destroy();
            });
            
            // Criar novos gráficos
            instance.createCharts();
        }
    }
}

// Inicializar gráficos quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    if (typeof Chart !== 'undefined') {
        window.financeCharts = new FinanceCharts();
    }
});