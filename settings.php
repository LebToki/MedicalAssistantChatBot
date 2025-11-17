<?php
require_once "bootstrap.php";
require 'database_config.php';
require 'config/api_config.php';

$apiConfig = new APIConfig();
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $openaiKey = $_POST['openai_api_key'] ?? '';
    $geminiKey = $_POST['gemini_api_key'] ?? '';
    $searchKey = $_POST['search_api_key'] ?? '';
    $searchEngine = $_POST['search_engine'] ?? 'duckduckgo';
    $enableAI = isset($_POST['enable_ai']) ? 1 : 0;
    $enableInternet = isset($_POST['enable_internet']) ? 1 : 0;
    
    if ($apiConfig->setAPIKey('openai_api_key', $openaiKey) &&
        $apiConfig->setAPIKey('gemini_api_key', $geminiKey) &&
        $apiConfig->setAPIKey('search_api_key', $searchKey) &&
        $apiConfig->setAPIKey('search_engine', $searchEngine) &&
        $apiConfig->setAPIKey('enable_ai', $enableAI) &&
        $apiConfig->setAPIKey('enable_internet', $enableInternet)) {
        $message = 'Settings saved successfully!';
        $messageType = 'success';
    } else {
        $message = 'Error saving settings.';
        $messageType = 'error';
    }
}

$settings = $apiConfig->getAllSettings();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Settings - Medical Doctor AI Assistant</title>
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>assets/aibot.png">
    <link rel="stylesheet" href="css/bot.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
    :root {
        --color-primary: #19c37d;
        --color-secondary: #715fde;
        --color-error: #ef4146;
        --text-primary: #202123;
        --text-default: #353740;
        --text-secondary: #6e6e80;
    }

    * {
        padding: 0;
        margin: 0;
        font-family: sans-serif;
        box-sizing: border-box;
    }

    body {
        background: #000;
        color: #fff;
        min-height: 100vh;
        padding: 20px;
    }

    .container {
        max-width: 900px;
        margin: 0 auto;
        background: rgba(52, 53, 65, 1);
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }

    .header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid rgba(255, 255, 255, 0.1);
    }

    .header img {
        width: 40px;
        height: 40px;
    }

    .header h1 {
        font-size: 24px;
        color: #fff;
    }

    .form-group {
        margin-bottom: 25px;
    }

    label {
        display: block;
        margin-bottom: 8px;
        color: #fff;
        font-weight: 600;
        font-size: 14px;
    }

    .label-help {
        font-size: 12px;
        color: #acacbe;
        font-weight: normal;
        margin-top: 4px;
    }

    input[type="text"],
    input[type="password"],
    select {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        background: rgba(68, 70, 84, 1);
        color: #fff;
        font-size: 14px;
        transition: border-color 0.3s;
    }

    input[type="text"]:focus,
    input[type="password"]:focus,
    select:focus {
        outline: none;
        border-color: var(--color-primary);
    }

    input[type="checkbox"] {
        width: 20px;
        height: 20px;
        margin-right: 10px;
        cursor: pointer;
    }

    .checkbox-group {
        display: flex;
        align-items: center;
        margin-top: 10px;
    }

    .checkbox-group label {
        margin-bottom: 0;
        cursor: pointer;
    }

    .btn {
        padding: 12px 30px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-block;
    }

    .btn-primary {
        background: var(--color-primary);
        color: #fff;
    }

    .btn-primary:hover {
        background: #16b06d;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(25, 195, 125, 0.4);
    }

    .btn-secondary {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        margin-left: 10px;
    }

    .btn-secondary:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .message {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
    }

    .message.success {
        background: rgba(25, 195, 125, 0.2);
        border: 1px solid var(--color-primary);
        color: var(--color-primary);
    }

    .message.error {
        background: rgba(239, 65, 70, 0.2);
        border: 1px solid var(--color-error);
        color: var(--color-error);
    }

    .section {
        margin-bottom: 30px;
        padding: 20px;
        background: rgba(68, 70, 84, 0.5);
        border-radius: 10px;
    }

    .section-title {
        font-size: 18px;
        color: #fff;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .info-box {
        background: rgba(25, 195, 125, 0.1);
        border-left: 4px solid var(--color-primary);
        padding: 15px;
        margin-top: 15px;
        border-radius: 4px;
        font-size: 13px;
        color: #acacbe;
    }

    .info-box strong {
        color: var(--color-primary);
    }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="<?php echo BASE_URL; ?>assets/aibot.png" alt="AI Bot">
            <h1>API Settings & Configuration</h1>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <!-- AI Provider Settings -->
            <div class="section">
                <div class="section-title">AI Provider Settings</div>
                
                <div class="form-group">
                    <label>
                        OpenAI API Key
                        <span class="label-help">Get your key from <a href="https://platform.openai.com/api-keys" target="_blank" style="color: var(--color-primary);">OpenAI Platform</a></span>
                    </label>
                    <input type="password" name="openai_api_key" 
                           value="<?php echo htmlspecialchars($settings['openai_api_key'] ?? ''); ?>" 
                           placeholder="sk-...">
                </div>

                <div class="form-group">
                    <label>
                        Google Gemini API Key
                        <span class="label-help">Get your key from <a href="https://makersuite.google.com/app/apikey" target="_blank" style="color: var(--color-primary);">Google AI Studio</a></span>
                    </label>
                    <input type="password" name="gemini_api_key" 
                           value="<?php echo htmlspecialchars($settings['gemini_api_key'] ?? ''); ?>" 
                           placeholder="AIza...">
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" name="enable_ai" id="enable_ai" 
                           <?php echo (isset($settings['enable_ai']) && $settings['enable_ai']) ? 'checked' : ''; ?>>
                    <label for="enable_ai">Enable AI provider fallback when database search yields insufficient results</label>
                </div>
            </div>

            <!-- Internet Search Settings -->
            <div class="section">
                <div class="section-title">Internet Search Settings</div>
                
                <div class="form-group">
                    <label>
                        Search API Key (Optional)
                        <span class="label-help">For SerpAPI or Google Custom Search. Leave empty to use DuckDuckGo (free, no key required)</span>
                    </label>
                    <input type="password" name="search_api_key" 
                           value="<?php echo htmlspecialchars($settings['search_api_key'] ?? ''); ?>" 
                           placeholder="Optional">
                </div>

                <div class="form-group">
                    <label>Search Engine</label>
                    <select name="search_engine">
                        <option value="duckduckgo" <?php echo (($settings['search_engine'] ?? 'duckduckgo') === 'duckduckgo') ? 'selected' : ''; ?>>DuckDuckGo (Free, No API Key)</option>
                        <option value="serpapi" <?php echo (($settings['search_engine'] ?? '') === 'serpapi') ? 'selected' : ''; ?>>SerpAPI (Requires API Key)</option>
                        <option value="google" <?php echo (($settings['search_engine'] ?? '') === 'google') ? 'selected' : ''; ?>>Google Custom Search (Requires API Key)</option>
                    </select>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" name="enable_internet" id="enable_internet" 
                           <?php echo (isset($settings['enable_internet']) && $settings['enable_internet']) ? 'checked' : ''; ?>>
                    <label for="enable_internet">Enable internet search when database search yields insufficient results</label>
                </div>
            </div>

            <div class="info-box">
                <strong>How it works:</strong> The system first searches the local database for fast results. If results are insufficient, it will fall back to AI providers and/or internet search based on your settings. This ensures fast access to indexed information while providing comprehensive coverage.
            </div>

            <div style="margin-top: 30px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Save Settings
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Back to Chat
                </a>
            </div>
        </form>
    </div>
</body>
</html>
