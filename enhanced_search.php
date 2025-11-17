<?php
/**
 * Enhanced Search with Relevance Scoring and Multiple Sources
 */
require_once "bootstrap.php";
require 'database_config.php';
require 'config/api_config.php';

class EnhancedSearch {
    private $conn;
    private $apiConfig;
    
    public function __construct() {
        try {
            $this->conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->apiConfig = new APIConfig();
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Search with relevance scoring and multiple sources
     */
    public function search($query, $options = []) {
        $useAI = $options['use_ai'] ?? true;
        $useInternet = $options['use_internet'] ?? true;
        $maxResults = $options['max_results'] ?? 5;
        $aiProvider = $options['ai_provider'] ?? 'auto'; // auto, openai, gemini
        
        $startTime = microtime(true);
        $results = [
            'query' => $query,
            'sources' => [],
            'response_time_ms' => 0
        ];
        
        // 1. Search local database first (fastest)
        $dbResults = $this->searchDatabase($query, $maxResults);
        if (!empty($dbResults)) {
            $results['sources']['database'] = $dbResults;
        }
        
        // 2. Search documents table
        $docResults = $this->searchDocuments($query, $maxResults);
        if (!empty($docResults)) {
            $results['sources']['documents'] = $docResults;
        }
        
        // 3. Try AI APIs if enabled and database results are insufficient
        if ($useAI && (empty($dbResults) || count($dbResults) < 2)) {
            $aiResult = $this->queryAI($query, $dbResults, $aiProvider);
            if ($aiResult) {
                $results['sources']['ai'] = [
                    [
                        'response' => $aiResult['response'],
                        'provider' => $aiResult['provider'],
                        'context_used' => !empty($dbResults)
                    ]
                ];
            }
        }
        
        // 4. Internet search if enabled
        if ($useInternet && (empty($dbResults) || count($dbResults) < 2)) {
            $internetResults = $this->searchInternet($query, $maxResults);
            if (!empty($internetResults)) {
                $results['sources']['internet'] = $internetResults;
            }
        }
        
        // Calculate response time
        $results['response_time_ms'] = round((microtime(true) - $startTime) * 1000);
        
        // Log search history
        $this->logSearch($query, $results);
        
        return $results;
    }
    
    /**
     * Search database with relevance scoring
     */
    private function searchDatabase($query, $maxResults) {
        $results = [];
        
        // Try full-text search first if available
        try {
            $stmt = $this->conn->prepare("
                SELECT messages, response, 
                       MATCH(messages, response) AGAINST(:query IN NATURAL LANGUAGE MODE) as relevance
                FROM chatbot
                WHERE MATCH(messages, response) AGAINST(:query IN NATURAL LANGUAGE MODE)
                ORDER BY relevance DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':query', $query);
            $stmt->bindValue(':limit', $maxResults, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Fallback to LIKE search if full-text not available
        }
        
        // Fallback to LIKE search with relevance scoring
        if (empty($results)) {
            $searchTerm = "%" . $query . "%";
            $stmt = $this->conn->prepare("
                SELECT messages, response,
                       (CASE 
                           WHEN messages LIKE :search THEN 10
                           WHEN response LIKE :search THEN 8
                           ELSE 5
                       END) as relevance
                FROM chatbot
                WHERE messages LIKE :search OR response LIKE :search
                ORDER BY relevance DESC, id DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':search', $searchTerm);
            $stmt->bindValue(':limit', $maxResults, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return $results;
    }
    
    /**
     * Search documents table
     */
    private function searchDocuments($query, $maxResults) {
        try {
            $stmt = $this->conn->prepare("
                SELECT id, title, content, source, document_type,
                       MATCH(title, content) AGAINST(:query IN NATURAL LANGUAGE MODE) as relevance
                FROM documents
                WHERE MATCH(title, content) AGAINST(:query IN NATURAL LANGUAGE MODE)
                ORDER BY relevance DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':query', $query);
            $stmt->bindValue(':limit', $maxResults, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Fallback to LIKE search
            $stmt = $this->conn->prepare("
                SELECT id, title, content, source, document_type
                FROM documents
                WHERE title LIKE CONCAT('%', :query, '%') OR content LIKE CONCAT('%', :query, '%')
                LIMIT :limit
            ");
            $stmt->bindValue(':query', $query);
            $stmt->bindValue(':limit', $maxResults, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    
    /**
     * Query AI APIs
     */
    private function queryAI($query, $dbContext, $provider = 'auto') {
        $context = '';
        if (!empty($dbContext)) {
            $context = implode(' ', array_slice(array_column($dbContext, 'response'), 0, 2));
        }
        
        // Try OpenAI first if auto or explicitly requested
        if ($provider === 'auto' || $provider === 'openai') {
            $openaiKey = $this->apiConfig->getAPIKey('openai_api_key');
            if ($openaiKey) {
                $openai = new OpenAIAPI($openaiKey);
                $response = $openai->query($query, $context);
                if ($response) {
                    return ['response' => $response, 'provider' => 'OpenAI'];
                }
            }
        }
        
        // Try Gemini if OpenAI failed or explicitly requested
        if ($provider === 'auto' || $provider === 'gemini') {
            $geminiKey = $this->apiConfig->getAPIKey('gemini_api_key');
            if ($geminiKey) {
                $gemini = new GeminiAPI($geminiKey);
                $response = $gemini->query($query, $context);
                if ($response) {
                    return ['response' => $response, 'provider' => 'Gemini'];
                }
            }
        }
        
        return null;
    }
    
    /**
     * Search internet
     */
    private function searchInternet($query, $maxResults) {
        $searchKey = $this->apiConfig->getAPIKey('search_api_key');
        $searchEngine = $this->apiConfig->getAPIKey('search_engine') ?: 'duckduckgo';
        
        $search = new InternetSearch($searchKey, $searchEngine);
        return $search->search($query, $maxResults);
    }
    
    /**
     * Log search to history
     */
    private function logSearch($query, $results) {
        $source = 'database';
        $preview = '';
        
        if (!empty($results['sources']['database'])) {
            $preview = substr($results['sources']['database'][0]['response'] ?? '', 0, 200);
        } elseif (!empty($results['sources']['ai'])) {
            $source = 'ai';
            $preview = substr($results['sources']['ai'][0]['response'] ?? '', 0, 200);
        } elseif (!empty($results['sources']['internet'])) {
            $source = 'internet';
            $preview = substr($results['sources']['internet'][0]['snippet'] ?? '', 0, 200);
        }
        
        $stmt = $this->conn->prepare("
            INSERT INTO search_history (query, source, response_preview, response_time_ms)
            VALUES (:query, :source, :preview, :time)
        ");
        $stmt->bindValue(':query', $query);
        $stmt->bindValue(':source', $source);
        $stmt->bindValue(':preview', $preview);
        $stmt->bindValue(':time', $results['response_time_ms']);
        $stmt->execute();
    }
}

?>
