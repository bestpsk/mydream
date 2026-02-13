<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=mydream', 'root', '123456');
$stmt = $pdo->query('DESCRIBE sys_store');
echo "sys_store table structure:\n";
echo "-----------------------------------\n";
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Field: {$row['Field']}, Type: {$row['Type']}, Null: {$row['Null']}, Key: {$row['Key']}, Default: {$row['Default']}, Extra: {$row['Extra']}\n";
}
echo "-----------------------------------\n";

// Also check if sys_store_department table exists
$stmt = $pdo->query('DESCRIBE sys_store_department');
echo "\nsys_store_department table structure:\n";
echo "-----------------------------------\n";
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Field: {$row['Field']}, Type: {$row['Type']}, Null: {$row['Null']}, Key: {$row['Key']}, Default: {$row['Default']}, Extra: {$row['Extra']}\n";
}
echo "-----------------------------------\n";
?>