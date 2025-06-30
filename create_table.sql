-- PL勘定科目管理テーブル作成
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='PL勘定科目マスタ';

-- サンプルデータ挿入
INSERT INTO pl_accounts (account_code, account_name, account_type, description) VALUES
('1001', '売上高', '収益', '商品・サービスの売上'),
('2001', '売上原価', '売上原価', '商品の仕入れ原価'),
('3001', '人件費', '販管費', '従業員の給与・賞与'),
('3002', '家賃', '販管費', 'オフィス・店舗の賃貸料'),
('3003', '水道光熱費', '販管費', '電気・ガス・水道代'),
('4001', '受取利息', '営業外収益', '預金利息'),
('4002', '支払利息', '営業外費用', '借入金利息'),
('5001', '固定資産売却益', '特別利益', '固定資産の売却による利益'),
('5002', '災害損失', '特別損失', '災害による損失'); 