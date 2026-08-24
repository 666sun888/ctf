FROM php:8.2-apache
RUN apt-get update && apt-get install -y --no-install-recommends iputils-ping && rm -rf /var/lib/apt/lists/*
COPY src/ /var/www/html/
RUN echo 'flag{ci1_direct}' > /first.txt \
 && echo 'flag{ci2_pipe}' > /second.txt \
 && echo 'flag{ci3_nospace}' > /flag.txt \
 && echo 'flag{ci4_backtick}' > /fourth.txt
