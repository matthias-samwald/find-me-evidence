#!/bin/bash

cp oa_articles.txt oa_articles_tmp.txt
cat oa_articles_tmp.txt | sort | uniq > oa_articles.txt
rm oa_articles_tmp.txt