// assets/js/login.js
document.addEventListener('DOMContentLoaded', function() {
    // Elementos
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    const togglePasswordBtns = document.querySelectorAll('.toggle-password');
    const forgotLink = document.querySelector('.forgot-link');
    const forgotModal = document.getElementById('forgot-password-modal');
    const closeModalBtns = document.querySelectorAll('.close-modal');
    const forgotForm = document.getElementById('forgot-form');
    const registerForm = document.querySelector('#register-content form');
    const loginForm = document.querySelector('#login-content form');
    const demoAccount = document.querySelector('.demo-account');

    // Alternar entre login e cadastro
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Atualizar botões ativos
            tabBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Atualizar conteúdos ativos
            tabContents.forEach(content => {
                content.classList.remove('active');
                if (content.id === `${tabId}-content`) {
                    content.classList.add('active');
                }
            });
            
            // Focar no primeiro campo
            const activeForm = document.querySelector('.tab-content.active form');
            if (activeForm) {
                const firstInput = activeForm.querySelector('input:not([type="hidden"])');
                if (firstInput) {
                    setTimeout(() => firstInput.focus(), 100);
                }
            }
        });
    });

    // Mostrar/ocultar senha
    togglePasswordBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
            
            input.focus();
        });
    });

    // Modal de esqueci senha
    if (forgotLink && forgotModal) {
        forgotLink.addEventListener('click', function(e) {
            e.preventDefault();
            forgotModal.style.display = 'flex';
        });
    }

    // Fechar modal
    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.modal').style.display = 'none';
        });
    });

    // Fechar modal clicando fora
    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            e.target.style.display = 'none';
        }
    });

    // Formulário de esqueci senha
    if (forgotForm) {
        forgotForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('recovery-email').value;
            
            if (!email) {
                showToast('Preencha o email para recuperação', 'error');
                return;
            }
            
            // Simulação de envio
            showToast('Link de recuperação enviado para seu email!', 'success');
            setTimeout(() => {
                forgotModal.style.display = 'none';
                forgotForm.reset();
            }, 2000);
        });
    }

    // Validação do formulário de cadastro
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const password = document.getElementById('register-password');
            const confirmPassword = document.getElementById('register-confirm');
            const terms = document.querySelector('input[name="terms"]');
            
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                showToast('As senhas não coincidem!', 'error');
                confirmPassword.focus();
                return;
            }
            
            if (password.value.length < 6) {
                e.preventDefault();
                showToast('A senha deve ter pelo menos 6 caracteres!', 'error');
                password.focus();
                return;
            }
            
            if (!terms.checked) {
                e.preventDefault();
                showToast('Você deve aceitar os termos!', 'error');
                return;
            }
            
            // Mostrar loading
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Criando conta...';
            submitBtn.disabled = true;
            
            // Reativar botão após 3 segundos (em caso de erro)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 3000);
        });
    }

    // Preencher dados da demo
    if (demoAccount && loginForm) {
        demoAccount.addEventListener('click', function() {
            const emailInput = document.querySelector('#login-content input[name="email"]');
            const passwordInput = document.querySelector('#login-content input[name="password"]');
            
            if (emailInput && passwordInput) {
                emailInput.value = 'joao@email.com';
                passwordInput.value = '123456';
                showToast('Dados de demonstração preenchidos!', 'info');
            }
        });
    }

    // Efeitos visuais nos inputs
    const inputs = document.querySelectorAll('input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            if (!this.value) {
                this.parentElement.classList.remove('focused');
            }
        });
        
        // Animar label
        if (input.value) {
            input.parentElement.classList.add('focused');
        }
    });

    // Animar números da demo
    if (demoAccount) {
        const amounts = demoAccount.querySelectorAll('strong + span');
        amounts.forEach(amount => {
            const value = amount.textContent;
            amount.textContent = '';
            
            let i = 0;
            function typeAmount() {
                if (i < value.length) {
                    amount.textContent += value.charAt(i);
                    i++;
                    setTimeout(typeAmount, 50);
                }
            }
            
            // Iniciar após 2 segundos
            setTimeout(typeAmount, 2000);
        });
    }

    // Função para mostrar toast
    function showToast(message, type = 'info') {
        const toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            // Criar container se não existir
            const container = document.createElement('div');
            container.id = 'toast-container';
            document.body.appendChild(container);
        }
        
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        `;
        
        document.getElementById('toast-container').appendChild(toast);
        
        // Remover após 5 segundos
        setTimeout(() => {
            toast.style.animation = 'toastSlideOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    // Adicionar estilo para animação de saída
    const style = document.createElement('style');
    style.textContent = `
        @keyframes toastSlideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    `;
    document.head.appendChild(style);

    // Efeito de digitação no título
    const title = document.querySelector('.logo h1');
    if (title) {
        const text = title.textContent;
        title.textContent = '';
        
        let i = 0;
        function typeTitle() {
            if (i < text.length) {
                title.textContent += text.charAt(i);
                i++;
                setTimeout(typeTitle, 100);
            }
        }
        
        // Iniciar após 0.5 segundos
        setTimeout(typeTitle, 500);
    }

    // Prevenir múltiplos envios
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        let isSubmitting = false;
        
        form.addEventListener('submit', function(e) {
            if (isSubmitting) {
                e.preventDefault();
                return;
            }
            
            isSubmitting = true;
            
            // Reativar após 5 segundos (caso haja erro)
            setTimeout(() => {
                isSubmitting = false;
            }, 5000);
        });
    });
});