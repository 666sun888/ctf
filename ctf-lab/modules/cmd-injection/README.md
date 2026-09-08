# 模块：cmd-injection（命令注入）

对应速查：`course/命令注入速查.md`
端口：8082

## 题目列表

| 路径 | 题目 | 考点 |
|------|------|------|
| /ci1/ | 直接注入 | 原理 + 分号 |
| /ci2/ | 拦分号 | 分隔符绕过（管道符/换行） |
| /ci3/ | 拦空格和关键字 | IFS / tac / 通配符 |
| /ci4/ | 拦所有分隔符 | 命令替换 $( ) 与反引号 |

## 做题前必读

`src/ci1/index.php` 到 `src/ci4/index.php` 全部读一遍。flag 文件：/first.txt、/second.txt、/flag.txt、/fourth.txt