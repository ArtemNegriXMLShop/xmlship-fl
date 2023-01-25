FROM php:8.2-fpm

# Install Dependences
RUN apt-get update \
    && apt-get upgrade -yq \
    && apt-get install -yq apt-utils \
    && apt-get install -yq libpq-dev libmcrypt-dev libreadline-dev libxslt-dev libxml2-dev libicu-dev zlib1g-dev \
    && apt-get install -yq libssl-dev \
    && apt-get install -yq git \
    && apt-get install -yq libzip-dev \
    && apt-get install -yq libpng-dev imagemagick libmagickwand-dev \
    && apt-get install -yq unixodbc unixodbc-dev libonig-dev

# Install PHP
RUN apt-get update \
    && docker-php-ext-install bcmath \
    && docker-php-ext-install gettext \
    && docker-php-ext-install opcache \
    && docker-php-ext-configure opcache --enable-opcache \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install intl \
    && docker-php-ext-install zip \
    && docker-php-ext-install pcntl \
    && docker-php-ext-install sockets \
    && docker-php-ext-install gd \
    && docker-php-ext-install exif

RUN pecl install xdebug  && docker-php-ext-enable xdebug \
#    && pecl install mcrypt && docker-php-ext-enable mcrypt \ #8.2.1 doesn't have a version
    && pecl install msgpack && docker-php-ext-enable msgpack \
    && pecl install mongodb && docker-php-ext-enable mongodb \
    && pecl install imagick && docker-php-ext-enable imagick \

RUN yes "" | pecl install redis && docker-php-ext-enable redis \
    && rm -rf /tmp/pear

# Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

# Install other packages
RUN apt-get install -yq nano mc vim htop iotop default-mysql-client procps net-tools telnet zip unzip git-core curl \
    lsof cron mariadb-client \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    jpegoptim optipng pngquant gifsicle

# set locale to utf-8
RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen && locale-gen
ENV LANG='en_US.UTF-8' LANGUAGE='en_US:en' LC_ALL='en_US.UTF-8'

ARG USER_ID=1000
ARG GROUP_ID=1000
RUN userdel -f www-data && \
    if getent group www-data ; then groupdel www-data; fi && \
    groupadd -g ${GROUP_ID} www-data && \
    useradd -l -u ${USER_ID} -g www-data www-data && \
    install -d -m 0755 -o www-data -g www-data /home/www-data && \
    chown --changes --silent --no-dereference --recursive \
        --from=33:33 ${USER_ID}:${GROUP_ID} /var/www /home/www-data

# COPY config/ /

RUN chown -R www-data:www-data /home/www-data

USER www-data

## Install NodeJS
#RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.35.0/install.sh | bash \
#    && export NVM_DIR="$([ -z "${XDG_CONFIG_HOME-}" ] && printf %s "${HOME}/.nvm" || printf %s "${XDG_CONFIG_HOME}/nvm")" \
#    && [ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh" \
#    && nvm install v10.15.1 \
#    && npm install pm2@latest -g \
#    && npm install dotenv -g

COPY ./.bashrc /home/www-data
