FROM php:8.2-apache
COPY src/ /var/www/html/
# ① 扩展名解析（与基础模块一致）
RUN printf '<FilesMatch "(?i)\\.(php|php3|php4|php5|phtml|pht)$">\nSetHandler application/x-httpd-php\n</FilesMatch>\n' > /etc/apache2/conf-available/zz-extensions.conf \
 && a2enconf zz-extensions \
# ② up8 解析漏洞：老式 AddHandler 让 shell.php.jpg 也被当 PHP（真实环境 Apache 配置背刺的经典成因）
 && printf 'AddHandler application/x-httpd-php .php\n' > /etc/apache2/conf-available/zz-addhandler.conf \
 && a2enconf zz-addhandler \
# ③ up5 前提：允许 uploads/ 下的 .htaccess 覆盖服务器配置
 && sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf \
# ④ fi5/fi8 靶点：访问日志落两个文件（fi8 的故意以 .php 结尾——出题背景：运维给日志浏览插件用的命名）
#    注意：vhost 内的 CustomLog 会覆盖 server 级配置，必须写进 000-default 虚拟主机
 && sed -i '/access.log combined/a\CustomLog /var/log/visit.log combined\nCustomLog /var/log/fi8log.php combined' /etc/apache2/sites-available/000-default.conf \
 && touch /var/log/visit.log /var/log/fi8log.php && chmod 644 /var/log/visit.log /var/log/fi8log.php \
# ⑤ session 落盘路径固定（fi6 用）
 && printf 'session.save_path=/tmp\n' > /usr/local/etc/php/conf.d/zz-adv.ini \
 && chown -R www-data:www-data /var/www/html/up5/uploads /var/www/html/up6/uploads /var/www/html/up7/uploads /var/www/html/up8/uploads \
 && echo 'flag{up5_htaccess_takeover}' > /flag_up5.txt \
 && echo 'flag{up6_double_write}' > /flag_up6.txt \
 && echo 'flag{up7_race_window}' > /flag_up7.txt \
 && echo 'flag{up8_parse_vuln}' > /flag_up8.txt \
 && echo 'flag{fi5_log_poison}' > /flag_fi5.txt \
 && echo 'flag{fi6_session_include}' > /flag_fi6.txt \
 && echo 'flag{fi7_pearcmd_rce}' > /flag_fi7.txt \
 && echo 'flag{fi8_absorb_log_combo}' > /flag_fi8.txt
