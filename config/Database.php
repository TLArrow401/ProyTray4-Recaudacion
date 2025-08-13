<?php

class Database {
    private $host = 'localhost';
    private $db_name = 'seramer2_new';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';
    private $conn;

    /**
     * Obtener la conexión a la base de datos
     * @return PDO|null
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            
            // Configurar PDO para mostrar errores
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }

        return $this->conn;
    }

    /**
     * Cerrar la conexión a la base de datos
     */
    public function closeConnection() {
        $this->conn = null;
    }

    /**
     * Verificar si la conexión está activa
     * @return bool
     */
    public function isConnected() {
        return $this->conn !== null;
    }

    /**
     * Obtener información de la conexión
     * @return array
     */
    public function getConnectionInfo() {
        return [
            'host' => $this->host,
            'database' => $this->db_name,
            'username' => $this->username,
            'charset' => $this->charset
        ];
    }
}

?>
