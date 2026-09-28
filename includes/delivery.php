<?php
declare(strict_types=1);

/**
 * Entrega automática: separa um código não utilizado do estoque do pacote
 * comprado, marca como usado e devolve o código para exibir/enviar ao
 * cliente. Retorna null se o estoque estiver zerado (é preciso repor em
 * /admin/stock.php).
 */
function deliver_stock_code(PDO $pdo, int $packageId, int $purchaseId, ?int $userId): ?string
{
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        'SELECT id, code FROM product_stock WHERE package_id = ? AND used = 0 LIMIT 1 FOR UPDATE'
    );
    $stmt->execute([$packageId]);
    $row = $stmt->fetch();

    if (!$row) {
        $pdo->rollBack();
        return null;
    }

    $pdo->prepare('UPDATE product_stock SET used = 1, used_by = ?, used_at = NOW() WHERE id = ?')
        ->execute([$userId, $row['id']]);
    $pdo->prepare('UPDATE purchases SET status = "delivered", delivered_code = ? WHERE id = ?')
        ->execute([$row['code'], $purchaseId]);

    $pdo->commit();

    return $row['code'];
}
