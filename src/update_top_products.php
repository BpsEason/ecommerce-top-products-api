<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);

    $pdo->beginTransaction();
    $pdo->exec('DELETE FROM top_products_cache');

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
        INSERT INTO top_products_cache (product_id, name, price, image_url, sales_count, rank_order, updated_at)
        VALUES (:product_id, :name, :price, :image_url, :sales_count, :rank_order, NOW())
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
    error_log("Top products cache updated successfully at " . date('Y-m-d H:i:s'));
    echo "Top products cache updated successfully\n";

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Database Error in update_top_products.php: " . $e->getMessage());
    echo "Error updating top products cache. Please check logs for details.\n";
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("General Error in update_top_products.php: " . $e->getMessage());
    echo "An unexpected error occurred. Please check logs for details.\n";
}
?>