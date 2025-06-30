<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("funcs.php");

echo "CSVアップロード処理開始<br>";

if (empty($_POST) || empty($_FILES['csv_file'])) {
    echo "CSVファイルが送信されていません<br>";
    echo "<a href='index.php'>戻る</a>";
    exit();
}

echo "CSVファイル受信完了<br>";

$csv_file = $_FILES['csv_file'];
$description = isset($_POST['description']) ? $_POST['description'] : '';

// セキュリティチェック
$file_errors = checkCSVFile($csv_file);
if (!empty($file_errors)) {
    echo "<h3>ファイルエラー</h3>";
    foreach ($file_errors as $error) {
        echo "❌ " . $error . "<br>";
    }
    echo "<a href='index.php'>戻る</a>";
    exit();
}

echo "ファイル検証完了<br>";

// 文字コード変換付きCSV読み込み
try {
    $original = file_get_contents($csv_file['tmp_name']);
    $converted = mb_convert_encoding($original, 'UTF-8', 'SJIS-win');
    $handle = fopen('php://memory', 'r+');
    fwrite($handle, $converted);
    rewind($handle);

    $header = fgetcsv($handle);
    if (!$header || count($header) < 2) {
        throw new Exception("ヘッダー行が不正です");
    }

    $year_months = array_slice($header, 1);
    $csv_data = [];
    $row_count = 0;

    while (($row = fgetcsv($handle)) !== false) {
        $account_name = $row[0];
        for ($i = 1; $i < count($row); $i++) {
            $year_month = $year_months[$i - 1];
            $amount = $row[$i];
            if ($amount === '' || $amount === '#####' || !is_numeric(str_replace(',', '', $amount))) {
                continue;
            }
            $csv_data[] = [
                $account_name,
                str_replace(',', '', $amount)
            ];
            $row_count++;
        }
    }
    fclose($handle);
    echo "CSV変換完了（{$row_count}件）<br>";
} catch (Exception $e) {
    echo "CSV読み込みエラー: " . $e->getMessage() . "<br>";
    echo "<a href='index.php'>戻る</a>";
    exit();
}

echo "データ検証完了<br>";

// DB接続
try {
    // データベース接続情報
$db_name = 'xxxxxxxxxxxxxxxxxxx';
$db_host = 'xxxxxxxxxxxxxxxxxxx';
$db_id   = 'xxxxxxxxxxxxxxxxxxx';
$db_pw   = 'xxxxxxxxxxxxxxxxxxx';

    $dsn = "mysql:dbname={$db_name};charset=utf8mb4;host={$db_host}";
    $pdo = new PDO($dsn, $db_id, $db_pw);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "データベース接続成功<br>";
} catch (PDOException $e) {
    echo "データベース接続エラー: " . $e->getMessage() . "<br>";
    echo "<a href='index.php'>戻る</a>";
    exit();
}

// テーブル作成
try {
    $table_name = 'monthly_data_' . date('Ymd_His');
    $create_table_sql = "
        CREATE TABLE `{$table_name}` (
            id INT AUTO_INCREMENT PRIMARY KEY,
            account_name VARCHAR(100) NOT NULL,
            amount DECIMAL(15,2) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($create_table_sql);
    echo "テーブル確認完了<br>";
} catch (PDOException $e) {
    echo "テーブル作成エラー: " . $e->getMessage() . "<br>";
    echo "<a href='index.php'>戻る</a>";
    exit();
}

// データ挿入
try {
    $insert_sql = "INSERT INTO {$table_name} (account_name, amount) VALUES (?, ?)";
    $stmt = $pdo->prepare($insert_sql);

    $success_count = 0;
    $error_count = 0;

    echo "<h3>データ挿入中...</h3>";

    foreach ($csv_data as $row) {
        try {
            $stmt->execute([$row[0], $row[1]]);
            $success_count++;
            echo "✅ 挿入成功: " . h($row[0]) . " - " . formatAmount($row[1]) . "円<br>";
        } catch (PDOException $e) {
            $error_count++;
            echo "❌ 挿入エラー: " . h($row[0]) . " - " . h($row[1]) . " - " . $e->getMessage() . "<br>";
        }
    }

    echo "<h3>挿入結果</h3>";
    echo "✅ 成功: {$success_count}件<br>";
    echo "❌ エラー: {$error_count}件<br>";

    if ($success_count > 0) {
        echo "<h3>アップロード完了！</h3>";
        echo "<a href='select.php' style='display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 8px; margin: 5px;'>データ一覧を見る</a>";
    }

    echo "<a href='index.php' style='display: inline-block; padding: 10px 20px; background: #764ba2; color: white; text-decoration: none; border-radius: 8px; margin: 5px;'>戻る</a>";
} catch (PDOException $e) {
    echo "データ挿入エラー: " . $e->getMessage() . "<br>";
    echo "<a href='index.php'>戻る</a>";
    exit();
}
?>
