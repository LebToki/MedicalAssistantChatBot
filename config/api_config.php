<?php
/**
 * API Configuration and Integration Layer
 * Supports OpenAI, Gemini, and Internet Search APIs
 */
require_once __DIR__ . "/../bootstrap.php";
require __DIR__ . "/../database_config.php";

class APIConfig {
    private $conn;
    
    public function __construct() {
        try {
            $this->conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Get API key from database
     */
    public function getAPIKey($key) {
        $stmt = $this->conn->prepare("SELECT setting_value FROM api_settings WHERE setting_key = :key");
        $stmt->bindParam(':key', $key);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['setting_value'] : null;
    }
    
    /**
     * Save API key to database
     */
    public function setAPIKey($key, $value) {
        $stmt = $this->conn->prepare("INSERT INTO api_settings (setting_key, setting_value) 
                                      VALUES (:key, :value) 
                                      ON DUPLICATE KEY UPDATE setting_value = :value, updated_at = CURRENT_TIMESTAMP");
        $stmt->bindParam(':key', $key);
        $stmt->bindParam(':value', $value);
        return $stmt->execute();
    }
    
    /**
     * Get all API settings
     */
    public function getAllSettings() {
        $stmt = $this->conn->query("SELECT setting_key, setting_value FROM api_settings");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }
}

class OpenAIAPI {
    private $apiKey;
    
    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
    }
    
    /**
     * Query OpenAI API
     */
    public function query($message, $context = '') {
        if (!$this->apiKey) {
            return null;
        }
        
        $url = "https://api.openai.com/v1/chat/completions";
        
        $systemPrompt = "You are a medical diagnostic assistant. Provide accurate, evidence-based medical information. " .
                       "Always remind users that this is for informational purposes and not a substitute for professional medical advice.";
        
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];
        
        if ($context) {
            $messages[] = ['role' => 'system', 'content' => "Context from knowledge base: " . $context];
        }
        
        $messages[] = ['role' => 'user', 'content' => $message];
        
        $data = [
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 500
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $result = json_decode($response, true);
            return $result['choices'][0]['message']['content'] ?? null;
        }
        
        return null;
    }
}

class GeminiAPI {
    private $apiKey;
    
    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
    }
    
    /**
     * Query Google Gemini API
     */
    public function query($message, $context = '') {
        if (!$this->apiKey) {
            return null;
        }
        
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . $this->apiKey;
        
        $prompt = "You are a medical diagnostic assistant. Provide accurate, evidence-based medical information. " .
                 "Always remind users that this is for informational purposes and not a substitute for professional medical advice.\n\n";
        
        if ($context) {
            $prompt .= "Context from knowledge base: " . $context . "\n\n";
        }
        
        $prompt .= "Question: " . $message;
        
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $result = json_decode($response, true);
            return $result['candidates'][0]['content']['parts'][0]['text'] ?? null;
        }
        
        return null;
    }
}

class InternetSearch {
    private $apiKey;
    private $searchEngine;
    
    public function __construct($apiKey = null, $searchEngine = 'duckduckgo') {
        $this->apiKey = $apiKey;
        $this->searchEngine = $searchEngine;
    }
    
    /**
     * Search the internet for medical information
     */
    public function search($query, $maxResults = 5) {
        $results = [];
        
        // Try DuckDuckGo Instant Answer API (no key required)
        if ($this->searchEngine === 'duckduckgo' || !$this->apiKey) {
            $results = $this->duckDuckGoSearch($query, $maxResults);
        }
        
        // If API key provided, try other search engines
        if (empty($results) && $this->apiKey) {
            if ($this->searchEngine === 'serpapi') {
                $results = $this->serpAPISearch($query, $maxResults);
            } elseif ($this->searchEngine === 'google') {
                $results = $this->googleCustomSearch($query, $maxResults);
            }
        }
        
        return $results;
    }
    
    /**
     * DuckDuckGo search (no API key required)
     */
    private function duckDuckGoSearch($query, $maxResults) {
        $url = "https://api.duckduckgo.com/?q=" . urlencode($query) . "&format=json&no_html=1&skip_disambig=1";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($response, true);
        $results = [];
        
        if (isset($data['AbstractText']) && !empty($data['AbstractText'])) {
            $results[] = [
                'title' => $data['Heading'] ?? $query,
                'snippet' => $data['AbstractText'],
                'url' => $data['AbstractURL'] ?? '',
                'source' => 'DuckDuckGo'
            ];
        }
        
        if (isset($data['RelatedTopics']) && is_array($data['RelatedTopics'])) {
            foreach (array_slice($data['RelatedTopics'], 0, $maxResults - 1) as $topic) {
                if (isset($topic['Text'])) {
                    $results[] = [
                        'title' => $topic['Text'],
                        'snippet' => $topic['Text'],
                        'url' => $topic['FirstURL'] ?? '',
                        'source' => 'DuckDuckGo'
                    ];
                }
            }
        }
        
        return $results;
    }
    
    /**
     * SerpAPI search (requires API key)
     */
    private function serpAPISearch($query, $maxResults) {
        $url = "https://serpapi.com/search.json?q=" . urlencode($query) . "&api_key=" . $this->apiKey;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($response, true);
        $results = [];
        
        if (isset($data['organic_results'])) {
            foreach (array_slice($data['organic_results'], 0, $maxResults) as $result) {
                $results[] = [
                    'title' => $result['title'] ?? '',
                    'snippet' => $result['snippet'] ?? '',
                    'url' => $result['link'] ?? '',
                    'source' => 'SerpAPI'
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Google Custom Search API
     */
    private function googleCustomSearch($query, $maxResults) {
        $url = "https://www.googleapis.com/customsearch/v1?key=" . $this->apiKey . 
               "&cx=YOUR_SEARCH_ENGINE_ID&q=" . urlencode($query) . "&num=" . $maxResults;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($response, true);
        $results = [];
        
        if (isset($data['items'])) {
            foreach ($data['items'] as $item) {
                $results[] = [
                    'title' => $item['title'] ?? '',
                    'snippet' => $item['snippet'] ?? '',
                    'url' => $item['link'] ?? '',
                    'source' => 'Google'
                ];
            }
        }
        
        return $results;
    }
}

?>
