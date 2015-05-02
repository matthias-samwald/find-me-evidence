Run FindMeEvidence Docker Container
-----------------------------------

1.  build the image (optional): `docker build -t gpetz/find-me-evidence .`

2.  start the container: `docker run --cap-add SYS_PTRACE -d -p 8080:8080 -p 80:80 -t gpetz/find-me-evidence` 

3.  load and reload `<Docker Host IP>:8080/solr` in your Browser to add [collection2]

4.  start a shell session inside the running container: `docker exec -it <CONTAINER ID> bash`

5.  run inside the container: `root@:<CONTAINER ID>/opt/find-me-evidence-1.1/retrieval_and_indexing# php wikipedia_translations_to_solr_xml.php` to index the translations to the second dictionary Solr core (if [collection2] is not available, try to add it manually, run https://github.com/matthias-samwald/find-me-evidence/blob/master/docker/start.sh#L4)

6.  access to FindMeEvidence: &lt;Docker Host IP&gt;:80 and Solr: &lt;Docker Host IP&gt;:8080/solr
