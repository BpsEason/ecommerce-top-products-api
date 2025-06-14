<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$log = new Logger('top_products');
$log->pushHandler(new StreamHandler(__DIR__ . '/logs/app.log', Logger::INFO));

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);

    $pdo->beginTransaction();
    $stmt = $pdo->query("
        SELECT
            p.id AS product_id,
            p.name,
            p.price,
            p.image_url,
            SUM(oi.quantity) AS sales_count
        FROM products p
        JOIN order_items oi ON p.id = oi.product_id
        JOIN orders o ON oi.order_id = o.id
        WHERE oi.created_at >= NOW() - INTERVAL 30 DAY
        AND o.status IN ('shipped', 'delivered')
        GROUP BY p.id, p.name, p.price, p.image_url
        ORDER BY sales_count DESC
        LIMIT 10
    ");

    $products = $stmt->fetchAll();

    $insertStmt = $pdo->prepare("
        INSERT INTO top_products_cache (product_id, name, price, image_url, sales_count, rank_order)
        VALUES (:product_id, :name, :price, :image_url, :sales_count, :rank_order)
        ON DUPLICATE KEY UPDATE
        name = VALUES(name),
        price = VALUES(price),
        image_url = VALUES(image_url),
        sales_count = VALUES(sales_count),
        rank_order = VALUES(rank_order),
        updated_at = NOW()
    ");

    $position = 1;
    foreach ($products as $product) {
        $insertStmt->execute([
            'product_id' => $product['product_id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'image_url' => $product['image_url'],
            'sales_count' => $product['sales_count'],
            'rank_order' => $position++,
        ]);
    }

    $pdo->commit();
    $log->info("Top products cache updated successfully at " . date('Y-m-d H:i:s'));
    echo "Top products cache updated successfully\n";

} catch (PDOException $e) {
    $pdo->rollBack();
    $log->error("Database Error: " . preg_replace('/\b(?:host|user|password|database)\b[^;]*/i', '[REDACTED]', $e->getMessage()));
    echo "Error updating top products cache. Please check logs for details.\n";
} catch (Exception $e) {
    $pdo->rollBack();
    $log->error("General Error: " . preg_replace('/\b(?:host|user|password|database)\b[^;]*/i', '[REDACTED]', $e->getMessage()));
    echo "An unexpected error occurred. Please check logs for details.\n";
}
?>