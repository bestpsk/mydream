<?php
// 数据库连接信息
$host = '127.0.0.1';
$port = 3306;
$dbname = 'mydream';
$username = 'root';
$password = '123456';
$charset = 'utf8mb4';

// 连接数据库
try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "数据库连接成功！\n";
} catch (Exception $e) {
    echo "数据库连接失败：" . $e->getMessage() . "\n";
    exit(1);
}

// 生成唯一的会员卡号
function generateMemberCard($pdo, $storeId) {
    do {
        $memberCard = 'MC' . $storeId . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM cust_customer WHERE member_card = ?');
        $stmt->execute([$memberCard]);
        $count = $stmt->fetchColumn();
    } while ($count > 0);
    return $memberCard;
}

// 生成唯一的手机号
function generatePhone($pdo) {
    do {
        $phone = '13' . rand(0, 9) . str_pad(rand(1, 999999999), 9, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM cust_customer WHERE phone = ?');
        $stmt->execute([$phone]);
        $count = $stmt->fetchColumn();
    } while ($count > 0);
    return $phone;
}

// 生成唯一的档案编号
function generateArchiveNumber($pdo, $storeId, $departmentId) {
    do {
        $archiveNumber = 'AR' . $storeId . $departmentId . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM cust_customer WHERE archive_number = ?');
        $stmt->execute([$archiveNumber]);
        $count = $stmt->fetchColumn();
    } while ($count > 0);
    return $archiveNumber;
}

// 生成随机生日
function generateBirthday() {
    $year = rand(1980, 2005);
    $month = rand(1, 12);
    $day = rand(1, 28);
    return "$year-$month-$day";
}

// 客户级别
$levels = ['普通客户', '银卡客户', '金卡客户', '钻石客户'];

// 客户来源
$sources = ['门店推荐', '朋友介绍', '网络推广', '线下活动', '其他'];

// 为顺义店（ID: 1）添加5条测试数据
echo "为顺义店添加测试客户数据...\n";
for ($i = 1; $i <= 5; $i++) {
    $storeId = 1;
    $departmentId = rand(1, 2); // 1:美发部，2:美容部
    $memberCard = generateMemberCard($pdo, $storeId);
    $name = "顺义客户$i";
    $gender = rand(1, 2); // 1:男，2:女
    $phone = generatePhone($pdo);
    $birthday = generateBirthday();
    $birthdayType = rand(1, 2); // 1:阳历，2:阴历
    $points = rand(0, 1000);
    $registerTime = date('Y-m-d H:i:s');
    $source = $sources[array_rand($sources)];
    $avatar = '';
    $archiveNumber = generateArchiveNumber($pdo, $storeId, $departmentId);
    $level = $levels[array_rand($levels)];
    $serviceStaffId = null; // 暂时为空
    $managerId = null; // 暂时为空
    $remark = "顺义店测试客户$i";
    $status = 1;
    $createdAt = date('Y-m-d H:i:s');
    $updatedAt = date('Y-m-d H:i:s');
    
    try {
        $stmt = $pdo->prepare('INSERT INTO cust_customer (store_id, department_id, member_card, name, gender, phone, birthday, birthday_type, points, register_time, source, avatar, archive_number, level, service_staff_id, manager_id, remark, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$storeId, $departmentId, $memberCard, $name, $gender, $phone, $birthday, $birthdayType, $points, $registerTime, $source, $avatar, $archiveNumber, $level, $serviceStaffId, $managerId, $remark, $status, $createdAt, $updatedAt]);
        echo "添加顺义客户$i 成功！\n";
    } catch (Exception $e) {
        echo "添加顺义客户$i 失败：" . $e->getMessage() . "\n";
    }
}

// 为肇嘉浜店（ID: 2）添加5条测试数据
echo "\n为肇嘉浜店添加测试客户数据...\n";
for ($i = 1; $i <= 5; $i++) {
    $storeId = 2;
    $departmentId = rand(1, 2); // 1:美发部，2:美容部
    $memberCard = generateMemberCard($pdo, $storeId);
    $name = "肇嘉浜客户$i";
    $gender = rand(1, 2); // 1:男，2:女
    $phone = generatePhone($pdo);
    $birthday = generateBirthday();
    $birthdayType = rand(1, 2); // 1:阳历，2:阴历
    $points = rand(0, 1000);
    $registerTime = date('Y-m-d H:i:s');
    $source = $sources[array_rand($sources)];
    $avatar = '';
    $archiveNumber = generateArchiveNumber($pdo, $storeId, $departmentId);
    $level = $levels[array_rand($levels)];
    $serviceStaffId = null; // 暂时为空
    $managerId = null; // 暂时为空
    $remark = "肇嘉浜店测试客户$i";
    $status = 1;
    $createdAt = date('Y-m-d H:i:s');
    $updatedAt = date('Y-m-d H:i:s');
    
    try {
        $stmt = $pdo->prepare('INSERT INTO cust_customer (store_id, department_id, member_card, name, gender, phone, birthday, birthday_type, points, register_time, source, avatar, archive_number, level, service_staff_id, manager_id, remark, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$storeId, $departmentId, $memberCard, $name, $gender, $phone, $birthday, $birthdayType, $points, $registerTime, $source, $avatar, $archiveNumber, $level, $serviceStaffId, $managerId, $remark, $status, $createdAt, $updatedAt]);
        echo "添加肇嘉浜客户$i 成功！\n";
    } catch (Exception $e) {
        echo "添加肇嘉浜客户$i 失败：" . $e->getMessage() . "\n";
    }
}

echo "\n测试客户数据添加完成！\n";

// 关闭连接
unset($pdo);
?>