find-me-evidence
================

An open-source medical search engine

Installation
------------

1.  Download Apache Tomcat and Apache Solr

2.  Copy /example/solr to your SOLR_HOME and replace solrconfig.xml, schema.xml and synonyms.txt

3.  Copy solr_configuration/solr.xml to CATALINA_HOME/conf/Catalina/localhost (point to your SOLR_HOME and Solr WebApp)

4.  Copy the jars from solr/example/lib/ext into your container's main lib directory

Create Solr Index
-----------------

The folder retrieval_and_indexing contains scripts for fetching external content, indexing it in Solr, as well 
as creating synonym mappings. The scripts should be called from the command line (e.g.,
with a command such as "php start_crawl.php").

###Fetching and indexing PubMed

1.  Run `pubmed_fetch.php`:
pubmed_fetch.php downloads all PubMed entries that are the result of a specific PubMed 
query. You can edit the script to change the PubMed query used. The default query aims
to cover all content in PubMed that is of relevance for medical decision making.

2.  Run `pubmed_xml_to_solr_xml.php`:
pubmed_xml_to_solr_xml.php iterates through the PubMed XML files downloaded by 
pubmed_fetch.php, reads PubMed entries, and writes extracted content to the Solr index.

###Fetching and indexing Wikipedia

1.  optional
    *  Run `wikipedia_create_list_of_relevant_articles.php`

    * Run `wikipedia_langlinks_translate.php` to use the Wikipedia langlink as the
translation of the article

    *  Run `wikipedia_translate.php <max_number_of_yandex_translation>` to 
translate the remaining article titles with Yandex 
([Powered by Yandex.Translate](http://translate.yandex.com/))

2.  Run `wikipedia_fetch.php`

3.  Run `wikipedia_xml_to_solr_xml.php`:
wikipedia_xml_to_solr_xml.php iterates through the Wikipedia XML files downloaded by 
wikipedia_fetch.php, reads Wikipedia entries, and writes extracted content to the Solr index.

###Creating synonyms from Wikipedia

1.  Run `create_synonyms_from_wikipedia.php`:
This creates a synonym mapping (for improving quality of search results) based on
page redirects in Wikipedia. To do this, it calls the DBpedia server (an open
database where content from Wikipedia can be queried). The script writes synonyms to a file
in the ./synonyms subfolder. This file must be placed into the Solr directory of the Solr 
collection containing the index., e.g., as [SOLR_HOME]/collection1/conf/synonyms.txt

###Cleaning the index

1.  Run `clean_index.php`:
This simple script removes document from the index that match a certain Solr query.
Can be used to clean up unwanted stuff that slipped through in earlier indexing
steps.