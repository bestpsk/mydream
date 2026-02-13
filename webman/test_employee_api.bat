@echo off

setlocal

REM 生成唯一的用户名
set "username=testuser%time:~6,5%"

REM 准备测试数据
set "testData={
    "username": "%username%",
    "employeeName": "测试员工",
    "companyId": 1,
    "deptId": 1,
    "position": "测试职位",
    "storeId": 1,
    "superiorId": 0,
    "roles": [1],
    "storeIds": [1, 2],
    "phone": "13800138000",
    "email": "test@example.com",
    "birthdaySolar": "1990-01-01",
    "birthdayLunar": "庚午年冬月十五",
    "idCard": "110101199001011234",
    "address": "北京市朝阳区",
    "emergencyContact": "紧急联系人",
    "emergencyPhone": "13900139000",
    "entryDate": "2024-01-01",
    "leaveDate": null
}"

REM 保存测试数据到文件
>test_data.json echo %testData%

REM 测试员工新增功能
echo 开始测试员工新增功能...
echo 使用用户名: %username%

echo 发送请求...
curl -X POST "http://localhost:8787/api/enterprise/addEmployee" ^
  -H "Content-Type: application/json" ^
  -d @test_data.json

echo.
echo 测试完成！

REM 清理临时文件
del test_data.json

endlocal
pause