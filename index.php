<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📊 CSV月次推移表 - アップロード</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>

<body>
    <!-- 装飾要素 -->
    <div class="decoration"></div>
    <div class="decoration"></div>

    <!-- ヘッダー -->
    <header class="header">
        <div class="nav-container">
            <a href="#" class="logo">
                <i class="fas fa-chart-line"></i>
                CSV月次推移表
            </a>
            <a href="select.php" class="nav-link">
                <i class="fas fa-list"></i>
                データ一覧
            </a>
        </div>
    </header>

    <!-- メインコンテンツ -->
    <main class="main-container form-page">
        <div class="form-card">
            <h1 class="form-title">📊 CSV月次推移表アップロード</h1>
            <p class="form-subtitle">月次データのCSVファイルをアップロードしてください</p>
            
            <div class="csv-info">
                <h3><i class="fas fa-info-circle"></i> CSVファイル形式</h3>
                <p>以下の形式でCSVファイルを準備してください：</p>
                <div class="csv-format">
                <table>
    <tr>
        <th>科目名</th>
        <th>2024-01</th>
        <th>2024-02</th>
        <th>2024-03</th>
    </tr>
    <tr>
        <td>売上高</td>
        <td>1000000</td>
        <td>1200000</td>
        <td>1100000</td>
    </tr>
</table>

                </div>
                <p class="csv-note">
                    <strong>注意：</strong>年月はYYYY-MM形式、金額は数値のみ入力してください
                </p>
            </div>
            
            <form method="post" action="insert.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="csv_file" class="form-label">
                        <i class="fas fa-file-csv"></i> CSVファイル選択
                    </label>
                    <input type="file" id="csv_file" name="csv_file" class="form-input" accept=".csv" required>
                    <small class="form-help">5MB以下のCSVファイルを選択してください</small>
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">
                        <i class="fas fa-comment"></i> 備考（任意）
                    </label>
                    <textarea id="description" name="description" class="form-textarea" placeholder="アップロード内容の説明や注意事項を記載してください..."></textarea>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-upload"></i>
                    アップロード
                </button>
            </form>
        </div>
    </main>
</body>

</html>