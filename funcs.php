<?php
//共通に使う関数を記述

//XSS対応（ echoする場所で使用！それ以外はNG ）
function h($str){
    return htmlspecialchars($str, ENT_QUOTES);
}

//CSVファイルのセキュリティチェック
function checkCSVFile($file) {
    $errors = [];
    
    // ファイルサイズチェック（5MB以下）
    if ($file['size'] > 5 * 1024 * 1024) {
        $errors[] = "ファイルサイズが大きすぎます（5MB以下にしてください）";
    }
    
    // ファイル形式チェック
    $allowed_types = ['text/csv', 'application/csv', 'text/plain'];
    if (!in_array($file['type'], $allowed_types)) {
        $errors[] = "CSVファイルのみアップロード可能です";
    }
    
    // ファイル拡張子チェック
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($extension !== 'csv') {
        $errors[] = "CSVファイル（.csv）のみアップロード可能です";
    }
    
    return $errors;
}

//CSVデータの検証
function validateCSVData($data) {
    $errors = [];
    
    foreach ($data as $row_num => $row) {
        // 必須項目チェック
        if (empty($row[0]) || empty($row[1]) || !isset($row[2])) {
            $errors[] = "行" . ($row_num + 1) . ": 年月、科目コード、科目名、金額は必須です";
            continue;
        }
        
        // 年月形式チェック（YYYY-MM）
        if (!preg_match('/^\d{4}-\d{2}$/', $row[0])) {
            $errors[] = "行" . ($row_num + 1) . ": 年月はYYYY-MM形式で入力してください（例：2024-01）";
        }
        
        // 金額チェック（数値のみ）
        if (!is_numeric($row[2])) {
            $errors[] = "行" . ($row_num + 1) . ": 金額は数値で入力してください";
        }
    }
    
    return $errors;
}

//金額のフォーマット
function formatAmount($amount) {
    return number_format($amount, 0);
}

//年月の表示形式
function formatYearMonth($yearMonth) {
    $date = DateTime::createFromFormat('Y-m', $yearMonth);
    return $date ? $date->format('Y年m月') : $yearMonth;
}
?>