<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=mydream', 'root', '123456');

// Check sys_employee table
$stmt = $pdo->query('DESCRIBE sys_employee');
echo "sys_employee table structure:\n";
echo "-----------------------------------\n";
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Field: {$row['Field']}, Type: {$row['Type']}, Null: {$row['Null']}, Key: {$row['Key']}, Default: {$row['Default']}, Extra: {$row['Extra']}\n";
}
echo "-----------------------------------\n\n";

// Check sys_user table
$stmt = $pdo->query('DESCRIBE sys_user');
echo "sys_user table structure:\n";
echo "-----------------------------------\n";
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Field: {$row['Field']}, Type: {$row['Type']}, Null: {$row['Null']}, Key: {$row['Key']}, Default: {$row['Default']}, Extra: {$row['Extra']}\n";
}
echo "-----------------------------------\n\n";

// Check sys_user_role table
$stmt = $pdo->query('DESCRIBE sys_user_role');
echo "sys_user_role table structure:\n";
echo "-----------------------------------\n";
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Field: {$row['Field']}, Type: {$row['Type']}, Null: {$row['Null']}, Key: {$row['Key']}, Default: {$row['Default']}, Extra: {$row['Extra']}\n";
}
echo "-----------------------------------\n\n";

// Check sys_user_store table
$stmt = $pdo->query('DESCRIBE sys_user_store');
echo "sys_user_store table structure:\n";
echo "-----------------------------------\n";
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Field: {$row['Field']}, Type: {$row['Type']}, Null: {$row['Null']}, Key: {$row['Key']}, Default: {$row['Default']}, Extra: {$row['Extra']}\n";
}
echo "-----------------------------------\n\n";

// Check if sys_employee_role table exists
$stmt = $pdo->query('SHOW TABLES LIKE "sys_employee_role"');
if ($stmt->rowCount() > 0) {
    $stmt = $pdo->query('DESCRIBE sys_employee_role');
    echo "sys_employee_role table structure:\n";
    echo "-----------------------------------\n";
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Field: {$row['Field']}, Type: {$row['Type']}, Null: {$row['Null']}, Key: {$row['Key']}, Default: {$row['Default']}, Extra: {$row['Extra']}\n";
    }
    echo "-----------------------------------\n\n";
}
?>