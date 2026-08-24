# 模块：xss（跨站脚本）

对应讲义：`course/lesson-04-XSS.md`
端口：8084

## 题目列表

| 路径 | 题目 | 考点 |
|------|------|------|
| /xs1/ | 搜索 | 反射型 XSS |
| /xs2/ | 留言板 | 存储型 XSS |
| /xs3/ | 欢迎页 | DOM 型 XSS |
| /xs4/ | 用户中心 | 偷 Cookie |

## 做题前必读

- 讲义 lesson-04 第 2、3 节（原理 + 三类型）
- 第 6 节（绕过 payload 库）
- xs4 需要浏览器测试（curl 不执行 JS）：用浏览器打开页面 → F12 看 Cookie → 观察 /xss/stolen.txt