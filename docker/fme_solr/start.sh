#!/bin/bash

service tomcat7 start
curl 'http://127.0.0.1:8080/solr/admin/cores?wt=json&indexInfo=false&action=CREATE&name=collection2&instanceDir=%2Fopt%2Fsolr%2Fexample%2Fsolr%2Fcollection2%2F&dataDir=%2Fopt%2Fsolr%2Fexample%2Fsolr%2Fcollection2%2Fdata&config=solrconfig.xml'
/usr/sbin/apache2ctl -D FOREGROUND
