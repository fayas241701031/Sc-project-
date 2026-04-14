<?php
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json');

$query = $_GET['q'] ?? '';
$category = $_GET['category'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$sort = $_GET['sort'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 6; // 6 items per page for pagination
$offset = ($page - 1) * $limit;

$params = [];
$sql = 'SELECT p.*, c.name AS category_name, u.is_verified_seller FROM products p JOIN categories c ON p.category_id = c.id JOIN users u ON p.seller_id = u.id';
$countSql = 'SELECT COUNT(*) FROM products p JOIN categories c ON p.category_id = c.id JOIN users u ON p.seller_id = u.id';
$conditions = ['p.status = \'active\''];

if (!empty($query)) {
    $conditions[] = '(p.name LIKE :query1 OR p.description LIKE :query2 OR c.name LIKE :query3)';
    $params[':query1'] = '%' . $query . '%';
    $params[':query2'] = '%' . $query . '%';
    $params[':query3'] = '%' . $query . '%';
}
if (!empty($category)) {
    // Check if category is a comma separated list
    $cats = explode(',', $category);
    $catPlaceholders = [];
    foreach ($cats as $i => $catId) {
        $pName = ':cat_' . $i;
        $catPlaceholders[] = $pName;
        $params[$pName] = (int)$catId;
    }
    $conditions[] = 'p.category_id IN (' . implode(',', $catPlaceholders) . ')';
}
if (!empty($max_price)) {
    $conditions[] = 'p.price <= :max_price';
    $params[':max_price'] = (float)$max_price;
}

$whereClause = ' WHERE ' . implode(' AND ', $conditions);
$sql .= $whereClause;
$countSql .= $whereClause;

switch ($sort) {
    case 'price_asc': $sql .= ' ORDER BY p.price ASC'; break;
    case 'price_desc': $sql .= ' ORDER BY p.price DESC'; break;
    case 'popularity': $sql .= ' ORDER BY p.popularity DESC'; break;
    default: $sql .= ' ORDER BY p.id DESC';
}

$sql .= ' LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;

try {
    // Get total count for pagination
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalItems = $countStmt->fetchColumn();
    $totalPages = ceil($totalItems / $limit);

    // Get items
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Inject mock ratings since DB doesn't have it
    foreach ($products as &$p) {
        // mock rating based on id to keep it consistent
        $p['rating'] = 3.5 + (($p['id'] * 7) % 15) / 10; // returns 3.5 to 5.0
        $p['reviews_count'] = ($p['popularity'] * 3 + $p['id'] * 12) % 300;
    }

    echo json_encode([
        'products' => $products,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_items' => $totalItems
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>