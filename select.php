<?php
// エラー表示を有効にする（デバッグ用）
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("funcs.php");

// データベース接続情報
$db_name = 'xxxxxxxxxxxxxxxxxxx';
$db_host = 'xxxxxxxxxxxxxxxxxxx';
$db_id   = 'xxxxxxxxxxxxxxxxxxx';
$db_pw   = 'xxxxxxxxxxxxxxxxxxx';

try {
    $server_info = "mysql:dbname={$db_name};charset=utf8mb4;host={$db_host}";
    $pdo = new PDO($server_info, $db_id, $db_pw);
} catch (PDOException $e) {
    exit('DB Connection Error:' . $e->getMessage());
}

// 直近のテーブル名を取得（prefix: monthly_data_）
$table_stmt = $pdo->query("SHOW TABLES LIKE 'monthly_data_%'");
$tables = $table_stmt->fetchAll(PDO::FETCH_COLUMN);
rsort($tables); // 降順ソート（新しい順）
$table_name = $tables[0] ?? '';

if (!$table_name) {
    exit("データが存在しません。<a href='index.php'>戻る</a>");
}

$sql = "SELECT * FROM `{$table_name}` ORDER BY account_name ASC";
$stmt = $pdo->prepare($sql);
$status = $stmt->execute();

$view = "";
$total_amount = 0;

if ($status) {
    while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $amount = floatval($result["amount"]);
        $total_amount += $amount;

        $view .= '<div class="data-item">';
        $view .= '<div class="account-name">' . h($result["account_name"]) . '</div>';
        $view .= '<div class="amount">' . formatAmount($result["amount"]) . '円</div>';
        $view .= '</div>';
    }
} else {
    $error = $stmt->errorInfo();
    exit("ErrorQuery: " . $error[2]);
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📊 CSV月次推移表</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
<header class="header">
    <div class="nav-container">
        <a href="#" class="logo">
            <i class="fas fa-chart-line"></i>
            月次推移表
        </a>
        <a href="index.php" class="nav-link">
            <i class="fas fa-upload"></i>
            CSVアップロード
        </a>
    </div>
</header>
<main class="main-container">
    <div class="content-card">
        <h1 class="page-title">📊 CSV月次推移表</h1>
        <p class="page-subtitle">最新のアップロードデータを表示</p>

        <div class="summary-section">
            <strong>総計: <?= formatAmount($total_amount) ?>円</strong>
        </div>

        <div class="data-container">
            <?= empty($view) ? '<p>まだデータがありません</p>' : $view ?>
        </div>
    </div>
</main>
</body>
</html>
