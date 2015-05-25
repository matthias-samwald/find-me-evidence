FROM    ubuntu:trusty
MAINTAINER Georg

ENV SOLR_VERSION 4.10.4
ENV FME_VERSION 1.1.1
ENV SOLR solr-$SOLR_VERSION
ENV FME find-me-evidence-$FME_VERSION

RUN apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get -yq install \
        php5 \
	curl \
	wget \
	unzip \
	default-jdk \
	tomcat7 \
	php5-curl

RUN mkdir -p /opt && \
    wget -nv --output-document=/opt/$SOLR.tgz http://archive.apache.org/dist/lucene/solr/$SOLR_VERSION/$SOLR.tgz && \
    wget -nv --output-document=/opt/v$FME_VERSION.tar.gz https://github.com/matthias-samwald/find-me-evidence/archive/v$FME_VERSION.tar.gz && \
    tar -C /opt --extract --file /opt/$SOLR.tgz && \
    tar -C /opt --extract --file /opt/v$FME_VERSION.tar.gz && \
    rm /opt/$SOLR.tgz && \
    rm /opt/v$FME_VERSION.tar.gz && \
    cp -r /opt/$FME/www/* /var/www/html/ &&\
    rm /var/www/html/index.html &&\
    ln -s /opt/$SOLR /opt/solr && \
    ln -s /opt/solr/dist/$SOLR.war /opt/solr/dist/solr.war 

COPY start.sh /opt/start.sh
RUN chmod 777 /opt/start.sh

RUN cp /opt/solr/example/lib/ext/*.jar /usr/share/tomcat7/lib

COPY solr.xml /etc/tomcat7/Catalina/localhost/solr.xml

RUN cp /opt/$FME/solr_configuration/collection1/conf/solrconfig.xml /opt/solr/example/solr/collection1/conf/ && \
    cp /opt/$FME/solr_configuration/collection1/conf/schema.xml /opt/solr/example/solr/collection1/conf/ && \
    cp /opt/$FME/solr_configuration/collection1/conf/synonyms.txt  /opt/solr/example/solr/collection1/conf/ && \
    cp -r /opt/$FME/solr_configuration/collection2  /opt/solr/example/solr/ && \
    chmod -R 777 /opt/$SOLR/example

EXPOSE 80
EXPOSE 8080
CMD ["/bin/bash", "/opt/start.sh"]
