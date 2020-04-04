FROM php:7.4-fpm
WORKDIR "/application"

# Install selected extensions and other stuff
RUN apt-get update \
    && apt-get -y --no-install-recommends install wget tzdata locales iputils-ping git vim openssl zip unzip zlib1g-dev libzip-dev libpq-dev\
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*\
    && pecl install xdebug \
    && docker-php-ext-enable xdebug\
    && echo "error_reporting=E_ALL" > /usr/local/etc/php/conf.d/custom-xdebug.ini \
    && echo "xdebug.default_enable=0" >> /usr/local/etc/php/conf.d/custom-xdebug.ini \
    && echo "display_startup_errors=On" >> /usr/local/etc/php/conf.d/custom-xdebug.ini \
    && echo "display_errors=On" >> /usr/local/etc/php/conf.d/custom-xdebug.ini \
    && echo "xdebug.remote_enable=1" >> /usr/local/etc/php/conf.d/custom-xdebug.ini \
    && echo "xdebug.remote_connect_back=off" >> /usr/local/etc/php/conf.d/custom-xdebug.ini \
    && echo "xdebug.idekey=PHPSTORM" >> /usr/local/etc/php/conf.d/custom-xdebug.ini \
    && echo "xdebug.remote_port=9008" >> /usr/local/etc/php/conf.d/custom-xdebug.ini

RUN echo 'memory_limit=512M' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini;

RUN docker-php-ext-install pdo pgsql pdo_pgsql zip opcache
RUN export PATH="$HOME/.symfony/bin:$PATH"

RUN groupadd dev -g 999
RUN useradd dev -g dev -d /home/dev -m

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# to install serverless
RUN apt-get update \
    && apt-get -y install git vim wget python3 python3-dev python3-venv python3-pip \
    && pip3 install --upgrade pip \
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

ENV AWS_DEFAULT_REGION=us-east-2

ENV AWS_ACCESS_KEY_ID=a
ENV AWS_SECRET_ACCESS_KEY=a

RUN pip3 --no-cache-dir install --upgrade awscli
RUN pip3 install --user aws-sam-cli

ENV PATH="${PATH}:/home/dev/.local/bin"
ENV PATH="${PATH}:/root/.local/bin"

ENV LC_ALL=C.UTF-8
ENV LANG=C.UTF-8

ENV TZ America/Sao_Paulo
