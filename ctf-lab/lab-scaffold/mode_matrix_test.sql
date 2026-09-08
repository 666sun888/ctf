-- 2x2 矩阵 x 双 sql_mode 实测(临时表,会话级,自动消失)
SELECT '===== A. 严格模式 STRICT_TRANS_TABLES =====' AS step;
SET SESSION sql_mode='STRICT_TRANS_TABLES';
CREATE TEMPORARY TABLE t(ua varchar(64));

SELECT '--- A1 假前缀+AND: ''x'' and extractvalue ---' AS step;
INSERT INTO t VALUES('x' and extractvalue(1,concat(0x7e,(select 1))));
SELECT 'A1 executed' AS step;

SELECT '--- A2 真前缀+OR: ''1'' or extractvalue ---' AS step;
INSERT INTO t VALUES('1' or extractvalue(1,concat(0x7e,(select 1))));
SELECT 'A2 executed' AS step;

SELECT '--- A3 纯函数值: extractvalue ---' AS step;
INSERT INTO t VALUES(extractvalue(1,concat(0x7e,(select 1))));
SELECT 'A3 executed' AS step;

SELECT '--- A4 数字前缀+AND: 1 and extractvalue ---' AS step;
INSERT INTO t VALUES(1 and extractvalue(1,concat(0x7e,(select 1))));
SELECT 'A4 executed' AS step;

SELECT '--- 严格模式表内状态 ---' AS step;
SELECT COUNT(*) AS rows_in_t FROM t;

SELECT '===== B. 宽松模式 sql_mode=空 =====' AS step;
SET SESSION sql_mode='';
DROP TEMPORARY TABLE t;
CREATE TEMPORARY TABLE t2(ua varchar(64));

SELECT '--- B1 假前缀+AND ---' AS step;
INSERT INTO t2 VALUES('x' and extractvalue(1,concat(0x7e,(select 1))));
SELECT 'B1 executed' AS step;

SELECT '--- B2 真前缀+OR ---' AS step;
INSERT INTO t2 VALUES('1' or extractvalue(1,concat(0x7e,(select 1))));
SELECT 'B2 executed' AS step;

SELECT '--- B3 纯函数值 ---' AS step;
INSERT INTO t2 VALUES(extractvalue(1,concat(0x7e,(select 1))));
SELECT 'B3 executed' AS step;

SELECT '--- 宽松模式表内状态 ---' AS step;
SELECT ua, COUNT(*) AS c FROM t2 GROUP BY ua;
SHOW WARNINGS;
