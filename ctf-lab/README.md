# 🎯 CTF Web 定制靶场

为课程同步打造的自研靶场（不是 DVWA 那种通用靶场）。**每题自带源码**，先读源码再做题，做完题才能看 `writeups/`。

## 快速开始

```bash
cd ctf-lab
docker compose up -d --build
```

访问 http://localhost:8080/ 查看 HTTP 基础模块题目列表。停止：`docker compose down`。

## 端口分配

| 端口 | 模块 | 状态 |
|------|------|------|
| 8080 | http-basics（HTTP 基础） | ✅ 已上线 |
| 8082 | cmd-injection（命令注入） | ✅ 已上线 |
| 8083 | sql-injection（SQL 注入，16 题） | ✅ 已上线 |
| 8084 | xss（XSS，4 题） | ✅ 已上线 |
| 8081 | cmd-injection（命令注入） | ✅ 已上线 |

## 做题规则

1. 先读 `modules/<模块>/src/` 下的源码
2. 卡住 30 分钟以上再问老师
3. 做完 → 看 `writeups/` 复盘 → 在 `course/progress.md` 打勾