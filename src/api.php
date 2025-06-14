<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);

    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && preg_match('#/api/v1/top-products$#', $requestUri)) {
        $stmt = $pdo->query('SELECT product_id, name, price, image_url, sales_count FROM top_products_cache ORDER BY rank_order ASC LIMIT 10');
        $products = $stmt->fetchAll();

        // 檢查快取是否過期
        $stmt = $pdo->query('SELECT MAX(updated_at) as last_updated FROM top_products_cache');
        $lastUpdated = $stmt->fetchColumn();
        $message = empty($products) ? 'No top products available' : 'Top products retrieved successfully';
        if ($lastUpdated && strtotime($lastUpdated) < time() - 3600) {
            $message = 'Top products data may be outdated';
            error_log('Top products cache is outdated at ' . date('Y-m-d H:i:s'));
        }

        echo json_encode([
            'success' => true,
            'data' => $products,
            'message' => $message
        ]);
        exit;
    }

    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Not Found', 'message' => 'The requested resource was not found']);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    error_log("Database Error in api.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Server error', 'message' => 'Database connection or query failed']);
} catch (Exception $e) {
    http_response_code(500);
    error_log("General Error in api.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Server error', 'message' => 'An unexpected error occurred']);
}
?>