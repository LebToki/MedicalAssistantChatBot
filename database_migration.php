<?php
/**
 * Database Migration Script
 * Adds indexes and full-text search capabilities for fast information retrieval
 */
require_once "bootstrap.php";
require 'database_config.php';

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Starting database migration...\n";
    
    // Check if indexes already exist
    $stmt = $conn->query("SHOW INDEXES FROM chatbot WHERE Key_name = 'idx_messages'");
    $indexExists = $stmt->fetch();
    
    if (!$indexExists) {
        // Add index on messages column for faster LIKE searches
        $conn->exec("CREATE INDEX idx_messages ON chatbot(messages(255))");
        echo "✓ Created index on messages column\n";
    } else {
        echo "✓ Index on messages already exists\n";
    }
    
    // Check for full-text index
    $stmt = $conn->query("SHOW INDEXES FROM chatbot WHERE Key_name = 'ft_messages_response'");
    $ftIndexExists = $stmt->fetch();
    
    if (!$ftIndexExists) {
        // Check if table uses MyISAM or InnoDB (InnoDB supports FULLTEXT in MySQL 5.6+)
        $stmt = $conn->query("SHOW TABLE STATUS WHERE Name = 'chatbot'");
        $tableInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (strtoupper($tableInfo['Engine']) === 'MYISAM' || 
            (strtoupper($tableInfo['Engine']) === 'INNODB' && version_compare($conn->getAttribute(PDO::ATTR_SERVER_VERSION), '5.6.0', '>='))) {
            try {
                $conn->exec("ALTER TABLE chatbot ADD FULLTEXT INDEX ft_messages_response(messages, response)");
                echo "✓ Created full-text index on messages and response columns\n";
            } catch (PDOException $e) {
                echo "⚠ Full-text index creation skipped: " . $e->getMessage() . "\n";
            }
        }
    } else {
        echo "✓ Full-text index already exists\n";
    }
    
    // Create settings table for API keys if it doesn't exist
    $conn->exec("CREATE TABLE IF NOT EXISTS api_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✓ Created/verified api_settings table\n";
    
    // Create search_history table for tracking searches
    $conn->exec("CREATE TABLE IF NOT EXISTS search_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        query TEXT NOT NULL,
        source VARCHAR(50) DEFAULT 'database',
        response_preview TEXT,
        response_time_ms INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_created_at (created_at),
        INDEX idx_source (source)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✓ Created/verified search_history table\n";
    
    // Create documents table for document retrieval
    $conn->exec("CREATE TABLE IF NOT EXISTS documents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(500) NOT NULL,
        content TEXT NOT NULL,
        source VARCHAR(200),
        document_type VARCHAR(50),
        metadata JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FULLTEXT INDEX ft_content (title, content)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✓ Created/verified documents table\n";
    
    echo "\nDatabase migration completed successfully!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

$conn = null;
?>
