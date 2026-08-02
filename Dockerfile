FROM wordpress:latest

RUN set -eux; \
	# 替换 Debian 源为国内镜像（构建环境网络限制）
	sed -i 's|http://deb.debian.org/debian|https://mirrors.tuna.tsinghua.edu.cn/debian|g' /etc/apt/sources.list.d/debian.sources /etc/apt/sources.list 2>/dev/null || true; \
	sed -i 's|http://deb.debian.org/debian-security|https://mirrors.tuna.tsinghua.edu.cn/debian-security|g' /etc/apt/sources.list.d/debian.sources /etc/apt/sources.list 2>/dev/null || true; \
	apt-get update; \
	apt-get install -y --no-install-recommends libsqlite3-dev; \
	rm -rf /var/lib/apt/lists/*; \
	docker-php-ext-install pdo_sqlite; \
	a2enmod headers
