<?php
// config/db_config.php - Configuração do banco de dados
class DBConfig {
    private static $instance = null;
    private $conn;
    
    // Configurações do banco de dados (XAMPP padrão)
    private $host = 'localhost';
    private $user = 'root';
    private $pass = '';
    private $dbname = 'controle_financeiro';
    
    private function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                $this->user,
                $this->pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $e) {
            die("<div style='padding:20px; background:#f8d7da; color:#721c24; border-radius:5px;'>
                <h3>Erro de Conexão com o Banco de Dados</h3>
                <p><strong>Mensagem:</strong> {$e->getMessage()}</p>
                <p><strong>Verifique:</strong></p>
                <ul>
                    <li>O XAMPP está rodando?</li>
                    <li>O MySQL está ativo?</li>
                    <li>O banco 'controle_financeiro' existe?</li>
                    <li>As credenciais estão corretas?</li>
                </ul>
                <p><a href='install.php' style='color:#721c24; text-decoration:underline;'>Executar Instalador</a></p>
            </div>");
        }
    }
    
    public static function getConnection() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->conn;
    }
    
    // Método simples para executar queries
    public static function query($sql, $params = []) {
        $conn = self::getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    // Buscar usuário por email
    public static function getUserByEmail($email) {
        $sql = "SELECT * FROM usuarios WHERE email = :email";
        $stmt = self::query($sql, [':email' => $email]);
        return $stmt->fetch();
    }
    
    // Criar novo usuário
    public static function createUser($nome, $email, $senha) {
        $dados_iniciais = json_encode([
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
                'notificacoes' => true
            ]
        ]);
        
        $sql = "INSERT INTO usuarios (nome, email, senha, dados_financeiros) 
                VALUES (:nome, :email, SHA2(:senha, 256), :dados)";
        
        return self::query($sql, [
            ':nome' => $nome,
            ':email' => $email,
            ':senha' => $senha,
            ':dados' => $dados_iniciais
        ]);
    }
    
    // Atualizar dados financeiros
    public static function updateFinancialData($user_id, $data) {
        $sql = "UPDATE usuarios SET dados_financeiros = :dados WHERE id = :id";
        return self::query($sql, [
            ':dados' => json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            ':id' => $user_id
        ]);
    }
    
    // Obter dados financeiros
    public static function getFinancialData($user_id) {
        $sql = "SELECT dados_financeiros FROM usuarios WHERE id = :id";
        $stmt = self::query($sql, [':id' => $user_id]);
        $result = $stmt->fetch();
        
        if ($result && !empty($result['dados_financeiros'])) {
            $data = json_decode($result['dados_financeiros'], true);
            
            // Garantir estrutura padrão
            return array_merge([
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
                    'notificacoes' => true
                ]
            ], $data);
        }
        
        return null;
    }
    
    // Atualizar último login
    public static function updateLastLogin($user_id) {
        $sql = "UPDATE usuarios SET ultimo_login = NOW() WHERE id = :id";
        return self::query($sql, [':id' => $user_id]);
    }
    
    // Verificar se email já existe
    public static function emailExists($email) {
        $sql = "SELECT COUNT(*) as count FROM usuarios WHERE email = :email";
        $stmt = self::query($sql, [':email' => $email]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    // Mudar senha
    public static function changePassword($user_id, $new_password) {
        $sql = "UPDATE usuarios SET senha = SHA2(:senha, 256) WHERE id = :id";
        return self::query($sql, [
            ':senha' => $new_password,
            ':id' => $user_id
        ]);
    }
}