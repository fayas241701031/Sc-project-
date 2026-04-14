<?php
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$message = trim($_POST['message'] ?? '');
if (empty($message)) {
    echo json_encode(['response' => 'Please enter a message.']);
    exit;
}

// Sanitize
$message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

// Define FAQs
$faqs = [
    'shipping' => 'We offer free shipping on orders over ₹500. Standard delivery takes 3-5 business days.',
    'returns' => 'You can return items within 30 days of purchase. Items must be in original condition.',
    'payment' => 'We accept credit cards, debit cards, UPI, and net banking.',
    'contact' => 'You can contact us at support@bazaar.local or call 1800-123-456.',
];

// Check if message is product-related
$product_keywords = ['product', 'price', 'buy', 'available', 'stock', 'category', 'item'];
$is_product_query = false;
foreach ($product_keywords as $kw) {
    if (stripos($message, $kw) !== false) {
        $is_product_query = true;
        break;
    }
}

$response = '';

if ($is_product_query) {
    // Search for products
    $stmt = $pdo->prepare('SELECT p.name, p.description, p.price, p.stock, c.name AS category FROM products p JOIN categories c ON p.category_id = c.id WHERE p.status = "active" AND (p.name LIKE :q1 OR p.description LIKE :q2 OR c.name LIKE :q3)');
    $stmt->execute([':q1' => '%' . $message . '%', ':q2' => '%' . $message . '%', ':q3' => '%' . $message . '%']);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($products) {
        $response = "I found these products:\n";
        foreach ($products as $p) {
            $response .= "- {$p['name']}: ₹{$p['price']}, Stock: {$p['stock']}\n";
        }
    } else {
        $response = "Sorry, I couldn't find any products matching your query.";
    }
} else {
    // Check for FAQ match
    $matched_faq = null;
    foreach ($faqs as $key => $ans) {
        if (stripos($message, $key) !== false) {
            $matched_faq = $ans;
            break;
        }
    }
    if ($matched_faq) {
        $response = $matched_faq;
    } else {
        // Check for greetings
        $greetings = ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening'];
        $is_greeting = false;
        foreach ($greetings as $greet) {
            if (stripos(strtolower($message), $greet) !== false) {
                $is_greeting = true;
                break;
            }
        }
        if ($is_greeting) {
            $response = "Hello! Welcome to Bazaar. How can I help you today? You can ask about products, search for items, or inquire about shipping and returns.";
        } else {
            // Use OpenAI if available, else simple response
            $api_key = getenv('OPENAI_API_KEY') ?: '';
            if (empty($api_key) || $api_key === 'your_openai_api_key_here') {
                $response = "I'm sorry, I'm currently operating in basic mode. For intelligent responses, please configure an OpenAI API key. For now, I can help with product searches and basic FAQs.";
            } else {
                $faq_text = '';
                foreach ($faqs as $q => $a) {
                    $faq_text .= "$q: $a\n";
                }
                $prompt = "You are a helpful chatbot for an e-commerce website. Here are the FAQs:\n$faq_text\nAnswer the user's question: $message";
                $response = callOpenAI($prompt, $api_key);
            }
        }
    }
}

echo json_encode(['response' => $response]);

function callOpenAI($prompt, $api_key) {
    $url = 'https://api.openai.com/v1/chat/completions';
    $data = [
        'model' => 'gpt-3.5-turbo',
        'messages' => [['role' => 'user', 'content' => $prompt]],
        'max_tokens' => 150,
        'temperature' => 0.7,
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key,
    ]);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        return 'Sorry, I am unable to respond right now.';
    }
    curl_close($ch);

    $response = json_decode($result, true);
    return $response['choices'][0]['message']['content'] ?? 'Sorry, I couldn\'t understand that.';
}
?>