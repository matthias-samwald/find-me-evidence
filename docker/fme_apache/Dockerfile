FROM    ubuntu:trusty
MAINTAINER Georg

ENV SOLR_VERSION 4.10.4
ENV FME_VERSION 1.1
ENV SOLR solr-$SOLR_VERSION
ENV FME find-me-evidence-$FME_VERSION

RUN apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get -yq install \
        php5 \
	curl \
	wget \
	unzip \
	default-jdk \
	php5-curl

RUN mkdir -p /opt && \
    wget -nv --output-document=/opt/v$FME_VERSION.tar.gz https://github.com/matthias-samwald/find-me-evidence/archive/v$FME_VERSION.tar.gz && \
    tar -C /opt --extract --file /opt/v$FME_VERSION.tar.gz && \
    rm /opt/v$FME_VERSION.tar.gz && \
    cp -r /opt/$FME/www/* /var/www/html/ &&\
    rm /var/www/html/index.html

COPY config.php /var/www/html/config.php

EXPOSE 80
CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]
