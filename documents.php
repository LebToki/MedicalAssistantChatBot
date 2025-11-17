<?php
require_once "bootstrap.php";
require 'database_config.php';

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Connection failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

$message = '';
$messageType = '';

// Handle document upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_document') {
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        $source = $_POST['source'] ?? '';
        $documentType = $_POST['document_type'] ?? 'general';
        
        if (!empty($title) && !empty($content)) {
            $stmt = $conn->prepare("INSERT INTO documents (title, content, source, document_type) VALUES (:title, :content, :source, :type)");
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':source', $source);
            $stmt->bindParam(':type', $documentType);
            
            if ($stmt->execute()) {
                $message = 'Document added successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error adding document.';
                $messageType = 'error';
            }
        }
    } elseif ($_POST['action'] === 'delete_document' && isset($_POST['id'])) {
        $stmt = $conn->prepare("DELETE FROM documents WHERE id = :id");
        $stmt->bindParam(':id', $_POST['id']);
        if ($stmt->execute()) {
            $message = 'Document deleted successfully!';
            $messageType = 'success';
        }
    }
}

// Get all documents
$stmt = $conn->query("SELECT id, title, source, document_type, created_at FROM documents ORDER BY created_at DESC LIMIT 50");
$documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Management - Medical Doctor AI Assistant</title>
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>assets/aibot.png">
    <link rel="stylesheet" href="css/bot.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
    body {
        background: #000;
        color: #fff;
        min-height: 100vh;
        padding: 20px;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        background: rgba(52, 53, 65, 1);
        border-radius: 15px;
        padding: 30px;
    }

    .header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid rgba(255, 255, 255, 0.1);
    }

    .header h1 {
        font-size: 24px;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .btn-primary {
        background: var(--color-primary);
        color: #fff;
    }

    .btn-danger {
        background: var(--color-error);
        color: #fff;
    }

    .btn-secondary {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
    }

    .message {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
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

    .form-group {
        margin-bottom: 20px;
    }

    label {
        display: block;
        margin-bottom: 8px;
        color: #fff;
        font-weight: 600;
    }

    input[type="text"],
    textarea,
    select {
        width: 100%;
        padding: 12px;
        border: 2px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        background: rgba(68, 70, 84, 1);
        color: #fff;
        font-size: 14px;
    }

    textarea {
        min-height: 200px;
        font-family: inherit;
    }

    .documents-list {
        margin-top: 30px;
    }

    .document-item {
        background: rgba(68, 70, 84, 0.5);
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .document-info h3 {
        margin-bottom: 5px;
        font-size: 16px;
    }

    .document-info small {
        color: #acacbe;
        font-size: 12px;
    }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                <img src="<?php echo BASE_URL; ?>assets/data.png" alt="Documents" style="width:40px;">
                Document Management
            </h1>
            <div>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Back to Chat
                </a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="action" value="add_document">
            
            <div class="form-group">
                <label>Document Title</label>
                <input type="text" name="title" required placeholder="e.g., Treatment Protocol for Diabetes">
            </div>

            <div class="form-group">
                <label>Content</label>
                <textarea name="content" required placeholder="Enter document content here..."></textarea>
            </div>

            <div class="form-group">
                <label>Source</label>
                <input type="text" name="source" placeholder="e.g., Medical Journal, Clinical Guidelines">
            </div>

            <div class="form-group">
                <label>Document Type</label>
                <select name="document_type">
                    <option value="general">General</option>
                    <option value="protocol">Treatment Protocol</option>
                    <option value="guideline">Clinical Guideline</option>
                    <option value="research">Research Paper</option>
                    <option value="reference">Reference Material</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fa fa-save"></i> Add Document
            </button>
        </form>

        <div class="documents-list">
            <h2 style="margin-bottom: 20px;">Recent Documents (<?php echo count($documents); ?>)</h2>
            
            <?php if (empty($documents)): ?>
                <p style="color: #acacbe;">No documents found. Add your first document above.</p>
            <?php else: ?>
                <?php foreach ($documents as $doc): ?>
                    <div class="document-item">
                        <div class="document-info">
                            <h3><?php echo htmlspecialchars($doc['title']); ?></h3>
                            <small>
                                Type: <?php echo htmlspecialchars($doc['document_type']); ?> | 
                                Source: <?php echo htmlspecialchars($doc['source'] ?: 'N/A'); ?> | 
                                Added: <?php echo date('Y-m-d', strtotime($doc['created_at'])); ?>
                            </small>
                        </div>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this document?');">
                            <input type="hidden" name="action" value="delete_document">
                            <input type="hidden" name="id" value="<?php echo $doc['id']; ?>">
                            <button type="submit" class="btn btn-danger">
                                <i class="fa fa-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
