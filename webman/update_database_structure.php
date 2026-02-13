<?php
require 'vendor/autoload.php';

try {
    $db = new PDO('mysql:host=localhost;dbname=mydream', 'root', '123456');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo '开始修改数据库表结构...' . PHP_EOL;
    
    // 1. 修改 sys_employee 表
    echo '修改 sys_employee 表...' . PHP_EOL;
    $db->exec('ALTER TABLE sys_employee ADD COLUMN position VARCHAR(50) AFTER department_id');
    $db->exec('ALTER TABLE sys_employee ADD COLUMN user_id INT(10) UNSIGNED AFTER position');
    $db->exec('ALTER TABLE sys_employee ADD COLUMN company_id INT(10) UNSIGNED AFTER user_id');
    echo 'sys_employee 表修改成功！' . PHP_EOL;
    
    // 2. 创建新表 sys_user_profile
    echo '创建 sys_user_profile 表...' . PHP_EOL;
    $db->exec('CREATE TABLE IF NOT EXISTS sys_user_profile (
        id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT(10) UNSIGNED NOT NULL,
        phone VARCHAR(20),
        email VARCHAR(100),
        birthday_solar DATE,
        birthday_lunar VARCHAR(20),
        id_card VARCHAR(18),
        address VARCHAR(255),
        emergency_contact VARCHAR(50),
        emergency_phone VARCHAR(20),
        entry_date DATE,
        leave_date DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES sys_user(id) ON DELETE CASCADE
    )');
    echo 'sys_user_profile 表创建成功！' . PHP_EOL;
    
    // 3. 修改 sys_user 表
    echo '修改 sys_user 表...' . PHP_EOL;
    $db->exec('ALTER TABLE sys_user MODIFY COLUMN nickname VARCHAR(50) NULL');
    $db->exec('ALTER TABLE sys_user MODIFY COLUMN avatar VARCHAR(255) NULL');
    echo 'sys_user 表修改成功！' . PHP_EOL;
    
    // 4. 修改 sys_user_store 表
    echo '修改 sys_user_store 表...' . PHP_EOL;
    $db->exec('ALTER TABLE sys_user_store DROP COLUMN created_at');
    $db->exec('ALTER TABLE sys_user_store DROP COLUMN updated_at');
    echo 'sys_user_store 表修改成功！' . PHP_EOL;
    
    // 5. 添加必要的索引
    echo '添加索引...' . PHP_EOL;
    // sys_user 表索引
    $db->exec('CREATE UNIQUE INDEX idx_sys_user_username ON sys_user(username)');
    $db->exec('CREATE INDEX idx_sys_user_employee_id ON sys_user(employee_id)');
    $db->exec('CREATE INDEX idx_sys_user_company_id ON sys_user(company_id)');
    $db->exec('CREATE INDEX idx_sys_user_store_id ON sys_user(store_id)');
    
    // sys_employee 表索引
    $db->exec('CREATE INDEX idx_sys_employee_user_id ON sys_employee(user_id)');
    $db->exec('CREATE INDEX idx_sys_employee_company_id ON sys_employee(company_id)');
    $db->exec('CREATE INDEX idx_sys_employee_department_id ON sys_employee(department_id)');
    
    // sys_user_profile 表索引
    $db->exec('CREATE UNIQUE INDEX idx_sys_user_profile_user_id ON sys_user_profile(user_id)');
    
    // sys_user_role 表索引
    $db->exec('CREATE INDEX idx_sys_user_role_user_id ON sys_user_role(user_id)');
    $db->exec('CREATE INDEX idx_sys_user_role_role_id ON sys_user_role(role_id)');
    
    // sys_user_store 表索引
    $db->exec('CREATE INDEX idx_sys_user_store_user_id ON sys_user_store(user_id)');
    $db->exec('CREATE INDEX idx_sys_user_store_store_id ON sys_user_store(store_id)');
    
    echo '索引添加成功！' . PHP_EOL;
    
    echo '数据库表结构修改完成！' . PHP_EOL;
    
} catch (PDOException $e) {
    echo '错误：' . $e->getMessage() . PHP_EOL;
    exit(1);
}
?>