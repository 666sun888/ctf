FROM php:8.2-apache
COPY src/ /var/www/html/
# ① 扩展 Apache 解析范围：黑名单题需要 .pht / .PhP 等扩展名也能被解析（真实环境常配多扩展名）
RUN printf '<FilesMatch "(?i)\\.(php|php3|php4|php5|phtml|pht)$">\nSetHandler application/x-httpd-php\n</FilesMatch>\n' > /etc/apache2/conf-available/zz-extensions.conf \
 && a2enconf zz-extensions \
 && echo 'allow_url_include=On' > /usr/local/etc/php/conf.d/zz-upload.ini \
 && chown -R www-data:www-data /var/www/html/up1/uploads /var/www/html/up2/uploads /var/www/html/up3/uploads /var/www/html/up4/uploads \
 && echo 'flag{up1_no_check}' > /flag_up1.txt \
 && echo 'flag{up2_blacklist_case}' > /flag_up2.txt \
 && echo 'flag{up3_mime_forged}' > /flag_up3.txt \
 && echo '<?php $flag = "flag{up4_image_horse_include}";' > /flag_up4.php \
 && echo 'flag{fi1_traversal}' > /flag_fi1.txt \
 && echo 'flag{fi2_suffix_absorbed}' > /flag_fi2.php \
 && echo 'flag{fi4_wrapper_rce}' > /flag_fi4.txt
