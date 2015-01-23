#!/bin/bash

#generate sorted list of wikipedia terms
cat relevant_articles_credibility.txt | cut -d';' -f1 | sed 's/_/ /g' | sort > wikipedia_articles_sorted_tmp.txt
#generate sorted list of mesh terms
cat $1 | tail -n+5 | sort > mesh_descriptors_sorted_tmp.txt
#generate list of new terms
comm -23 --check-order mesh_descriptors_sorted_tmp.txt wikipedia_articles_sorted_tmp.txt > new_medical_terms.txt
#remove newline at end of ile
truncate -s -1 new_medical_terms.txt
#delete tmp files
rm wikipedia_articles_sorted_tmp.txt
rm mesh_descriptors_sorted_tmp.txt
