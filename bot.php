<?php
require_once "bootstrap.php";
require 'database_config.php';
require 'config/api_config.php';
require 'enhanced_search.php';

header('Content-Type: application/json');

try {
    $enhancedSearch = new EnhancedSearch();
    $apiConfig = new APIConfig();
    
    // Get settings
    $settings = $apiConfig->getAllSettings();
    $useAI = isset($settings['enable_ai']) && $settings['enable_ai'];
    $useInternet = isset($settings['enable_internet']) && $settings['enable_internet'];
    
    if (isset($_POST['messageValue'])) {
        $userMessage = trim($_POST['messageValue']);
        
        if (empty($userMessage)) {
            echo json_encode([
                'success' => false,
                'error' => 'Please enter a message.'
            ]);
            exit;
        }
        
        // Perform enhanced search
        $searchOptions = [
            'use_ai' => $useAI,
            'use_internet' => $useInternet,
            'max_results' => 5,
            'ai_provider' => 'auto'
        ];
        
        $results = $enhancedSearch->search($userMessage, $searchOptions);
        
        // Format response
        $response = '';
        $sources = [];
        
        // Prioritize database results
        if (!empty($results['sources']['database'])) {
            $dbResult = $results['sources']['database'][0];
            $response = $dbResult['response'];
            $sources[] = 'Database';
        }
        
        // Add document results if available
        if (!empty($results['sources']['documents'])) {
            $docResult = $results['sources']['documents'][0];
            if (empty($response)) {
                $response = $docResult['content'];
            }
            $sources[] = 'Documents';
        }
        
        // Fallback to AI if no database results
        if (empty($response) && !empty($results['sources']['ai'])) {
            $aiResult = $results['sources']['ai'][0];
            $response = $aiResult['response'];
            $sources[] = $aiResult['provider'];
        }
        
        // Fallback to internet search
        if (empty($response) && !empty($results['sources']['internet'])) {
            $internetResults = $results['sources']['internet'];
            $response = "Based on internet search:\n\n";
            foreach (array_slice($internetResults, 0, 3) as $result) {
                $response .= "• " . $result['title'] . "\n";
                $response .= $result['snippet'] . "\n";
                if (!empty($result['url'])) {
                    $response .= "Source: " . $result['url'] . "\n\n";
                }
            }
            $sources[] = 'Internet';
        }
        
        // Default response if nothing found
        if (empty($response)) {
            $response = "I couldn't find specific information about that in my knowledge base. Could you please rephrase your question or provide more details?";
        }
        
        // Add disclaimer for medical information
        $response .= "\n\n<small style='color: #acacbe; font-size: 12px;'>⚠️ This information is for educational purposes only and is not a substitute for professional medical advice, diagnosis, or treatment.</small>";
        
        echo json_encode([
            'success' => true,
            'response' => $response,
            'sources' => $sources,
            'response_time_ms' => $results['response_time_ms'],
            'has_multiple_sources' => count($results['sources']) > 1
        ]);
        
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'No message value received.'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')
    ]);
}
