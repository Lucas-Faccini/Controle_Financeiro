<?php
// login.php
session_start();
require_once 'config/db_config.php';

$error = '';
$success = '';

// Verificar se usuário já está logado
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'login') {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        
        if (empty($email) || empty($password)) {
            $error = 'Preencha todos os campos!';
        } else {
            // Verificar usuário
            $user = DBConfig::getUserByEmail($email);
            
            if ($user) {
                // Verificar senha
                $check_sql = "SELECT COUNT(*) as count FROM usuarios 
                             WHERE email = :email AND senha = SHA2(:password, 256)";
                $stmt = DBConfig::query($check_sql, [
                    ':email' => $email,
                    ':password' => $password
                ]);
                $result = $stmt->fetch();
                
                if ($result['count'] > 0) {
                    // Login bem-sucedido
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['nome'];
                    $_SESSION['user_email'] = $user['email'];
                    
                    // Atualizar último login
                    DBConfig::updateLastLogin($user['id']);
                    
                    // Registrar log (opcional)
                    $ip = $_SERVER['REMOTE_ADDR'];
                    $user_agent = $_SERVER['HTTP_USER_AGENT'];
                    
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $error = 'Senha incorreta!';
                }
            } else {
                $error = 'Usuário não encontrado!';
            }
        }
    } 
    elseif ($action === 'register') {
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validações
        if (empty($nome) || empty($email) || empty($password)) {
            $error = 'Todos os campos são obrigatórios!';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Email inválido!';
        } elseif ($password !== $confirm_password) {
            $error = 'As senhas não coincidem!';
        } elseif (strlen($password) < 6) {
            $error = 'A senha deve ter pelo menos 6 caracteres!';
        } elseif (DBConfig::emailExists($email)) {
            $error = 'Este email já está cadastrado!';
        } else {
            // Criar novo usuário
            try {
                DBConfig::createUser($nome, $email, $password);
                $success = 'Cadastro realizado com sucesso! Faça login para continuar.';
            } catch (Exception $e) {
                $error = 'Erro ao criar conta: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Controle Financeiro</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
</head>
<body>
    <div class="background-animation"></div>
    
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo">
                    <i class="fas fa-chart-line"></i>
                    <h1>Controle Financeiro</h1>
                </div>
                <p class="subtitle">Gerencie suas finanças de forma inteligente</p>
            </div>
            
            <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars($success); ?></span>
            </div>
            <?php endif; ?>
            
            <div class="tabs">
                <button class="tab-btn active" data-tab="login">
                    <i class="fas fa-sign-in-alt"></i> Entrar
                </button>
                <button class="tab-btn" data-tab="register">
                    <i class="fas fa-user-plus"></i> Cadastrar
                </button>
            </div>
            
            <div class="tab-content active" id="login-content">
                <form method="POST" class="login-form">
                    <input type="hidden" name="action" value="login">
                    
                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i> Email
                        </label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                               placeholder="seu@email.com" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i> Senha
                        </label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" 
                                   placeholder="Sua senha" required>
                            <button type="button" class="toggle-password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-options">
                        <label class="checkbox">
                            <input type="checkbox" name="remember">
                            <span>Lembrar-me</span>
                        </label>
                        <a href="#forgot-password" class="forgot-link">Esqueci a senha</a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-sign-in-alt"></i> Entrar
                    </button>
                    
                    <div class="demo-account">
                        <h4><i class="fas fa-info-circle"></i> Conta de Demonstração</h4>
                        <p><strong>Email:</strong> joao@email.com</p>
                        <p><strong>Senha:</strong> 123456</p>
                    </div>
                </form>
            </div>
            
            <div class="tab-content" id="register-content">
                <form method="POST" class="login-form">
                    <input type="hidden" name="action" value="register">
                    
                    <div class="form-group">
                        <label for="register-nome">
                            <i class="fas fa-user"></i> Nome Completo
                        </label>
                        <input type="text" id="register-nome" name="nome" 
                               value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>"
                               placeholder="Seu nome completo" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="register-email">
                            <i class="fas fa-envelope"></i> Email
                        </label>
                        <input type="email" id="register-email" name="email" 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                               placeholder="seu@email.com" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="register-password">
                            <i class="fas fa-lock"></i> Senha
                        </label>
                        <div class="password-wrapper">
                            <input type="password" id="register-password" name="password" 
                                   placeholder="Mínimo 6 caracteres" required>
                            <button type="button" class="toggle-password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="register-confirm">
                            <i class="fas fa-lock"></i> Confirmar Senha
                        </label>
                        <div class="password-wrapper">
                            <input type="password" id="register-confirm" name="confirm_password" 
                                   placeholder="Digite novamente" required>
                            <button type="button" class="toggle-password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-terms">
                        <label class="checkbox">
                            <input type="checkbox" name="terms" required>
                            <span>Concordo com os <a href="#terms">Termos de Uso</a> e <a href="#privacy">Política de Privacidade</a></span>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fas fa-user-plus"></i> Criar Conta
                    </button>
                </form>
            </div>
            
            <div class="login-footer">
                <p>&copy; <?php echo date('Y'); ?> Controle Financeiro. Todos os direitos reservados.</p>
                <p class="version">v1.0.0</p>
            </div>
        </div>
        
        <div class="features">
            <h2><i class="fas fa-star"></i> Recursos do Sistema</h2>
            <div class="feature-list">
                <div class="feature">
                    <i class="fas fa-wallet"></i>
                    <h3>Controle Total</h3>
                    <p>Acompanhe todas as suas finanças em um só lugar</p>
                </div>
                <div class="feature">
                    <i class="fas fa-chart-pie"></i>
                    <h3>Relatórios</h3>
                    <p>Gráficos e relatórios detalhados</p>
                </div>
                <div class="feature">
                    <i class="fas fa-bullseye"></i>
                    <h3>Metas</h3>
                    <p>Defina e acompanhe seus objetivos</p>
                </div>
                <div class="feature">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Seguro</h3>
                    <p>Seus dados protegidos com criptografia</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de Esqueci Senha -->
    <div id="forgot-password-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h3><i class="fas fa-key"></i> Recuperar Senha</h3>
            <form id="forgot-form">
                <div class="form-group">
                    <label for="recovery-email">Email cadastrado</label>
                    <input type="email" id="recovery-email" required>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Enviar Link
                </button>
            </form>
        </div>
    </div>
    
    <script src="assets/js/login.js"></script>
</body>
</html>