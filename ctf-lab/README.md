# 🎯 CTF Web 定制靶场

为课程同步打造的自研靶场（不是 DVWA 那种通用靶场）。**每题自带源码**，先读源码再做题，做完题才能看 `writeups/`。

## 快速开始

```bash
cd ctf-lab
docker compose up -d --build
```

访问 http://localhost:8001/ 查看 HTTP 基础模块题目列表。停止：`docker compose down`。

> 端口说明（2026-09-08 修正）：http-basics 由 8080 改为 **8001**——8080 与本机 Burp Suite 冲突。
> 抽题器 `tools/review_random.py` 里 ch1~ch4 用的也是 8001，两边现已一致。

端口全部只绑定 127.0.0.1（仅本机可访问）。复测前重置靶场状态（清空容器内的上传文件/留言/权限改动）：`docker compose up -d --force-recreate`。

## 端口分配

| 端口 | 模块 | 状态 |
|------|------|------|
| 8001 | http-basics（HTTP 基础） | ✅ 已上线 |
| 8082 | cmd-injection（命令注入） | ✅ 已上线 |
| 8083 | sql-injection（SQL 注入，15 题） | ✅ 已上线 |
| 8084 | xss（XSS，7 题） | ✅ 已上线 |
| 8085 | upload-lfi（上传/包含，8 题） | ✅ 已上线 |
| 8086 | upload-lfi-adv（上传/包含进阶，8 题） | ✅ 已上线 |

## 做题规则

1. 先读 `modules/<模块>/src/` 下的源码
2. 卡住 30 分钟以上再问老师
3. 做完 → 看 `writeups/` 复盘 → 在 `modules/course/progress.md` 打勾