<?php
// エラー表示を有効にする
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>データベースセットアップ開始</h2>";

// データベース接続情報
$db_name = 'xxxxxxxxxxxxxxxxxxx';
$db_host = 'xxxxxxxxxxxxxxxxxxx';
$db_id   = 'xxxxxxxxxxxxxxxxxxx';
$db_pw   = 'xxxxxxxxxxxxxxxxxxx';

echo "接続情報確認完了<br>";

try {
    $dsn = "mysql:dbname={$db_name};charset=utf8;host={$db_host}";
    echo "DSN: " . $dsn . "<br>";
    
    $pdo = new PDO($dsn, $db_id, $db_pw);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<strong>✅ データベース接続成功</strong><br>";
    
    // 既存テーブルの確認
    echo "<h3>既存テーブルの確認</h3>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "テーブルが存在しません<br>";
    } else {
        echo "既存テーブル:<br>";
        foreach ($tables as $table) {
            echo "- " . $table . "<br>";
        }
    }
    
    // pl_accountsテーブルの存在確認
    $stmt = $pdo->query("SHOW TABLES LIKE 'pl_accounts'");
    if ($stmt->rowCount() > 0) {
        echo "<strong>✅ pl_accountsテーブルは既に存在します</strong><br>";
    } else {
        echo "<strong>❌ pl_accountsテーブルが存在しません。作成します...</strong><br>";
        
        // テーブル作成SQL
        $create_table_sql = "
        CREATE TABLE IF NOT EXISTS pl_accounts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            account_code VARCHAR(20) NOT NULL UNIQUE COMMENT '科目コード',
            account_name VARCHAR(100) NOT NULL COMMENT '科目名',
            account_type ENUM('収益', '売上原価', '販管費', '営業外収益', '営業外費用', '特別利益', '特別損失') NOT NULL COMMENT '科目区分',
            description TEXT COMMENT '備考',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '登録日時',
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新日時',
            INDEX idx_account_code (account_code),
            INDEX idx_account_type (account_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='PL勘定科目マスタ'
        ";
        
        echo "テーブル作成SQL実行中...<br>";
        $result = $pdo->exec($create_table_sql);
        
        if ($result !== false) {
            echo "<strong>✅ テーブル作成完了</strong><br>";
        } else {
            echo "<strong>❌ テーブル作成に失敗しました</strong><br>";
            $error = $pdo->errorInfo();
            echo "エラー: " . $error[2] . "<br>";
        }
    }
    
    // テーブル構造の確認
    echo "<h3>テーブル構造の確認</h3>";
    try {
        $stmt = $pdo->query("DESCRIBE pl_accounts");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "pl_accountsテーブルの構造:<br>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>フィールド</th><th>型</th><th>NULL</th><th>キー</th><th>デフォルト</th><th>その他</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . $column['Field'] . "</td>";
            echo "<td>" . $column['Type'] . "</td>";
            echo "<td>" . $column['Null'] . "</td>";
            echo "<td>" . $column['Key'] . "</td>";
            echo "<td>" . $column['Default'] . "</td>";
            echo "<td>" . $column['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } catch (PDOException $e) {
        echo "テーブル構造確認エラー: " . $e->getMessage() . "<br>";
    }
    
    // サンプルデータ挿入
    echo "<h3>サンプルデータ挿入</h3>";
    $sample_data = [
        ['1001', '売上高', '収益', '商品・サービスの売上'],
        ['2001', '売上原価', '売上原価', '商品の仕入れ原価'],
        ['3001', '人件費', '販管費', '従業員の給与・賞与'],
        ['3002', '家賃', '販管費', 'オフィス・店舗の賃貸料'],
        ['3003', '水道光熱費', '販管費', '電気・ガス・水道代'],
        ['4001', '受取利息', '営業外収益', '預金利息'],
        ['4002', '支払利息', '営業外費用', '借入金利息'],
        ['5001', '固定資産売却益', '特別利益', '固定資産の売却による利益'],
        ['5002', '災害損失', '特別損失', '災害による損失']
    ];
    
    $insert_sql = "INSERT INTO pl_accounts (account_code, account_name, account_type, description) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($insert_sql);
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($sample_data as $data) {
        try {
            $stmt->execute($data);
            echo "✅ 挿入成功: {$data[1]}<br>";
            $success_count++;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // 重複エラー
                echo "⚠️ 既に存在: {$data[1]}<br>";
            } else {
                echo "❌ 挿入エラー: {$data[1]} - " . $e->getMessage() . "<br>";
                $error_count++;
            }
        }
    }
    
    echo "<h3>挿入結果</h3>";
    echo "成功: {$success_count}件<br>";
    echo "エラー: {$error_count}件<br>";
    
    // 最終確認
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM pl_accounts");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "<strong>テーブル内の総レコード数: {$count}件</strong><br>";
    
    echo "<h3>セットアップ完了！</h3>";
    echo "<a href='index.php' style='display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 8px; margin: 5px;'>登録ページへ</a>";
    echo "<a href='select.php' style='display: inline-block; padding: 10px 20px; background: #764ba2; color: white; text-decoration: none; border-radius: 8px; margin: 5px;'>一覧ページへ</a>";
    
} catch (PDOException $e) {
    echo "<strong>❌ エラー: " . $e->getMessage() . "</strong><br>";
    echo "エラーコード: " . $e->getCode() . "<br>";
}
?> 