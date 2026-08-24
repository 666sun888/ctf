#!/bin/sh
# secure_file_priv=/var/lib/mysql-files/，flag 必须放这个目录才能被 LOAD_FILE 读到
echo 'flag{si8_loadfile}' > /var/lib/mysql-files/flag.txt
chmod 644 /var/lib/mysql-files/flag.txt
