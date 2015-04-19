Run FindMeEvidence Docker Container
-----------------------------------

1.  build the image (optional): `docker build -t gpetz/find-me-evidence .`
`
2.  start the container: `docker run -d -p 8080:8080 -p 80:80 -t gpetz/find-me-evidence` 

3.  start a shell session inside the running container: `docker exec -it <CONTAINER ID> bash`

4.  run inside the container: `root@:<CONTAINER ID>/opt/find-me-evidence-1.1/retrieval_and_indexing# php wikipedia_translations_to_solr_xml.php` to index the translations to the second dictionary Solr core

5.  access to FindMeEvidence: &lt;Docker Host IP&gt;:80 and Solr: &lt;Docker Host IP&gt;:8080