-Develop python version:3.9.1
-Library installed:
    pip install pymssql


-Database
程式需要MySQL使用者,使用的帳號密碼為
使用者:ShiXun
密碼:password


1.若MySQL沒有使用者'ShiXun'請執行:
CREATE USER 'ShiXun'@'localhost' IDENTIFIED BY 'password';


2.若MySQL沒有Cacti_customize的database則執行:
CREATE DATABASE cacti_customize;
GRANT ALL PRIVILEGES ON cacti_customize.* TO 'ShiXun'@'localhost';


3.創建table
USE cacti_customize;

CREATE TABLE microsoftSQL (
IP VARCHAR(500) PRIMARY KEY,
conn_count FLOAT,
waiting_task_count FLOAT,
default_cpu FLOAT,
internal_cpu FLOAT,
delay_time FLOAT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);






msSQL筆記:
1.要開啟TCP(在SQL Server Management->)
