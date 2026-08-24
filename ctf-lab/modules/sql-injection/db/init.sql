-- ============================================
-- ctf 公共库：si1（万能密码）/ si8（读文件）/ si9（宽字节）使用
-- ============================================
CREATE DATABASE IF NOT EXISTS ctf DEFAULT CHARSET=utf8mb4;
USE ctf;
CREATE TABLE IF NOT EXISTS users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(64),
  password VARCHAR(64)
) DEFAULT CHARSET=utf8mb4;
INSERT INTO users (username, password) VALUES ('admin', 'Admin@123'), ('guest', 'guest123');

CREATE TABLE IF NOT EXISTS products (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(64),
  price INT
) DEFAULT CHARSET=utf8mb4;
INSERT INTO products (id, name, price) VALUES (1, '苹果', 5), (2, '香蕉', 3), (3, '西瓜', 10);

-- ============================================
-- 每道注入题一个独立库：只有 products + secret_table(data)
-- 表名和列名都不会在题目提示里出现——必须用 information_schema 自己找
-- ============================================
CREATE DATABASE IF NOT EXISTS ctf2 DEFAULT CHARSET=utf8mb4;
USE ctf2;
CREATE TABLE IF NOT EXISTS products (id INT PRIMARY KEY AUTO_INCREMENT, name VARCHAR(64), price INT) DEFAULT CHARSET=utf8mb4;
INSERT INTO products (id, name, price) VALUES (1, '苹果', 5), (2, '香蕉', 3), (3, '西瓜', 10);
CREATE TABLE IF NOT EXISTS secret_table (id INT, data VARCHAR(128)) DEFAULT CHARSET=utf8mb4;
INSERT INTO secret_table VALUES (1, 'flag{si2_union}');

CREATE DATABASE IF NOT EXISTS ctf3 DEFAULT CHARSET=utf8mb4;
USE ctf3;
CREATE TABLE IF NOT EXISTS products (id INT PRIMARY KEY AUTO_INCREMENT, name VARCHAR(64), price INT) DEFAULT CHARSET=utf8mb4;
INSERT INTO products (id, name, price) VALUES (1, '苹果', 5), (2, '香蕉', 3), (3, '西瓜', 10);
CREATE TABLE IF NOT EXISTS secret_table (id INT, data VARCHAR(128)) DEFAULT CHARSET=utf8mb4;
INSERT INTO secret_table VALUES (1, 'flag{si3_error}');

CREATE DATABASE IF NOT EXISTS ctf4 DEFAULT CHARSET=utf8mb4;
USE ctf4;
CREATE TABLE IF NOT EXISTS products (id INT PRIMARY KEY AUTO_INCREMENT, name VARCHAR(64), price INT) DEFAULT CHARSET=utf8mb4;
INSERT INTO products (id, name, price) VALUES (1, '苹果', 5), (2, '香蕉', 3), (3, '西瓜', 10);
CREATE TABLE IF NOT EXISTS secret_table (id INT, data VARCHAR(128)) DEFAULT CHARSET=utf8mb4;
INSERT INTO secret_table VALUES (1, 'flag{si4_comment}');

CREATE DATABASE IF NOT EXISTS ctf5 DEFAULT CHARSET=utf8mb4;
USE ctf5;
CREATE TABLE IF NOT EXISTS products (id INT PRIMARY KEY AUTO_INCREMENT, name VARCHAR(64), price INT) DEFAULT CHARSET=utf8mb4;
INSERT INTO products (id, name, price) VALUES (1, '苹果', 5), (2, '香蕉', 3), (3, '西瓜', 10);
CREATE TABLE IF NOT EXISTS flag5_table (flag VARCHAR(128)) DEFAULT CHARSET=utf8mb4;
INSERT INTO flag5_table VALUES ('flag{si5_boolean}');

CREATE DATABASE IF NOT EXISTS ctf6 DEFAULT CHARSET=utf8mb4;
USE ctf6;
CREATE TABLE IF NOT EXISTS products (id INT PRIMARY KEY AUTO_INCREMENT, name VARCHAR(64), price INT) DEFAULT CHARSET=utf8mb4;
INSERT INTO products (id, name, price) VALUES (1, '苹果', 5), (2, '香蕉', 3), (3, '西瓜', 10);
CREATE TABLE IF NOT EXISTS flag6_table (flag VARCHAR(128)) DEFAULT CHARSET=utf8mb4;
INSERT INTO flag6_table VALUES ('flag{si6_time}');

CREATE DATABASE IF NOT EXISTS ctf7 DEFAULT CHARSET=utf8mb4;
USE ctf7;
CREATE TABLE IF NOT EXISTS products (id INT PRIMARY KEY AUTO_INCREMENT, name VARCHAR(64), price INT) DEFAULT CHARSET=utf8mb4;
INSERT INTO products (id, name, price) VALUES (1, '苹果', 5), (2, '香蕉', 3), (3, '西瓜', 10);
CREATE TABLE IF NOT EXISTS secret_table (id INT, data VARCHAR(128)) DEFAULT CHARSET=utf8mb4;
INSERT INTO secret_table VALUES (1, 'flag{si7_stacked}');

CREATE DATABASE IF NOT EXISTS ctf10 DEFAULT CHARSET=utf8mb4;
USE ctf10;
CREATE TABLE IF NOT EXISTS products (id INT PRIMARY KEY AUTO_INCREMENT, name VARCHAR(64), price INT) DEFAULT CHARSET=utf8mb4;
INSERT INTO products (id, name, price) VALUES (1, '苹果', 5), (2, '香蕉', 3), (3, '西瓜', 10);
CREATE TABLE IF NOT EXISTS secret_table (id INT, data VARCHAR(128)) DEFAULT CHARSET=utf8mb4;
INSERT INTO secret_table VALUES (1, 'flag{si10_long_flag_chunked_extraction_42}');

-- ============================================
-- 权限
-- ============================================
GRANT ALL PRIVILEGES ON ctf.* TO 'ctf'@'%';
GRANT ALL PRIVILEGES ON ctf2.* TO 'ctf'@'%';
GRANT ALL PRIVILEGES ON ctf3.* TO 'ctf'@'%';
GRANT ALL PRIVILEGES ON ctf4.* TO 'ctf'@'%';
GRANT ALL PRIVILEGES ON ctf5.* TO 'ctf'@'%';
GRANT ALL PRIVILEGES ON ctf6.* TO 'ctf'@'%';
GRANT ALL PRIVILEGES ON ctf7.* TO 'ctf'@'%';
GRANT ALL PRIVILEGES ON ctf10.* TO 'ctf'@'%';
GRANT FILE ON *.* TO 'ctf'@'%';
-- ============================================
-- L2 变种题库（独立库，flag 表名/列名不提示）
-- ============================================
CREATE DATABASE IF NOT EXISTS ctf2b DEFAULT CHARSET=utf8mb4;
USE ctf2b;
CREATE TABLE IF NOT EXISTS products (id INT PRIMARY KEY AUTO_INCREMENT, name VARCHAR(64), price INT) DEFAULT CHARSET=utf8mb4;
INSERT INTO products (id, name, price) VALUES (1, '苹果', 5), (2, '香蕉', 3), (3, '西瓜', 10);
CREATE TABLE IF NOT EXISTS secret_table (id INT, data VARCHAR(128)) DEFAULT CHARSET=utf8mb4;
INSERT INTO secret_table VALUES (1, 'flag{si2b_case_bypass}');

CREATE DATABASE IF NOT EXISTS ctf3b DEFAULT CHARSET=utf8mb4;
USE ctf3b;
CREATE TABLE IF NOT EXISTS products (id INT PRIMARY KEY AUTO_INCREMENT, name VARCHAR(64), price INT) DEFAULT CHARSET=utf8mb4;
INSERT INTO products (id, name, price) VALUES (1, '苹果', 5), (2, '香蕉', 3), (3, '西瓜', 10);
CREATE TABLE IF NOT EXISTS secret_table (id INT, data VARCHAR(128)) DEFAULT CHARSET=utf8mb4;
INSERT INTO secret_table VALUES (1, 'flag{si3b_remember_extractvalue_is_two_args_47}');

CREATE DATABASE IF NOT EXISTS ctf5b DEFAULT CHARSET=utf8mb4;
USE ctf5b;
CREATE TABLE IF NOT EXISTS products (id INT PRIMARY KEY AUTO_INCREMENT, name VARCHAR(64), price INT) DEFAULT CHARSET=utf8mb4;
INSERT INTO products (id, name, price) VALUES (1, '苹果', 5), (2, '香蕉', 3), (3, '西瓜', 10);
CREATE TABLE IF NOT EXISTS flag5b_table (flag VARCHAR(128)) DEFAULT CHARSET=utf8mb4;
INSERT INTO flag5b_table VALUES ('flag{si5b_blind_comment_space}');

CREATE DATABASE IF NOT EXISTS ctf7b DEFAULT CHARSET=utf8mb4;
USE ctf7b;
CREATE TABLE IF NOT EXISTS users (id INT PRIMARY KEY AUTO_INCREMENT, username VARCHAR(64), role VARCHAR(16)) DEFAULT CHARSET=utf8mb4;
INSERT INTO users (username, role) VALUES ('guest', 'guest'), ('admin', 'admin');
CREATE TABLE IF NOT EXISTS products (id INT PRIMARY KEY AUTO_INCREMENT, name VARCHAR(64), price INT) DEFAULT CHARSET=utf8mb4;
INSERT INTO products (id, name, price) VALUES (1, '苹果', 5), (2, '香蕉', 3), (3, '西瓜', 10);

-- ============================================
-- L3 综合毕业考
-- ============================================
CREATE DATABASE IF NOT EXISTS ctfF DEFAULT CHARSET=utf8mb4;
USE ctfF;
CREATE TABLE IF NOT EXISTS tickets (id INT PRIMARY KEY AUTO_INCREMENT, title VARCHAR(64), status VARCHAR(32)) DEFAULT CHARSET=utf8mb4;
INSERT INTO tickets (id, title, status) VALUES (1, '网站打不开', '处理中'), (2, '账号被盗', '处理中'), (3, '充值没到账', '处理中');
CREATE TABLE IF NOT EXISTS fl4gs (d4ta VARCHAR(128)) DEFAULT CHARSET=utf8mb4;
INSERT INTO fl4gs VALUES ('flag{sif1n4l_blind_chain_gr4duat3d_45chars}');

GRANT ALL PRIVILEGES ON ctf2b.* TO 'ctf'@'%';
GRANT ALL PRIVILEGES ON ctf3b.* TO 'ctf'@'%';
GRANT ALL PRIVILEGES ON ctf5b.* TO 'ctf'@'%';
GRANT ALL PRIVILEGES ON ctf7b.* TO 'ctf'@'%';
GRANT ALL PRIVILEGES ON ctfF.* TO 'ctf'@'%';
