<?php
require 'vendor/autoload.php';

$db = new PDO('mysql:host=localhost;dbname=mydream', 'root', '123456');
$stmt = $db->query('DESCRIBE sys_employee');
echo 'sys_employee table structure:' . PHP_EOL;
echo '-----------------------------------' . PHP_EOL;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo 'Field: ' . $row['Field'] . ', Type: ' . $row['Type'] . PHP_EOL;
}
echo '-----------------------------------' . PHP_EOL;
?>