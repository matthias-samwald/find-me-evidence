Run FindMeEvidence Docker Container
-----------------------------------

1.  build the image (optional): `docker build -t msamwald/find-me-evidence .`

2.  start the container: `docker run --cap-add SYS_PTRACE --security-opt=apparmor:unconfined -d -p 8080:8080 -p 80:80 -t msamwald/find-me-evidence` (if you have already an existing Solr Index: `docker run -v //c/Users/user/path_to_index/solr:/opt/solr/example/solr -d -p 8080:8080 -p 80:80 -t msamwald/find-me-evidence`) 

3.  load and reload `<Docker Host IP>:8080/solr` in your Browser to add [collection2]

4.  start a shell session inside the running container: `docker exec -it <CONTAINER ID> bash`

5.  run inside the container: `root@:<CONTAINER ID>/opt/find-me-evidence-1.1/retrieval_and_indexing# php wikipedia_translations_to_solr_xml.php` to index the translations to the second dictionary Solr core (if [collection2] is not available, try to add it manually, run https://github.com/matthias-samwald/find-me-evidence/blob/master/docker/start.sh#L4)

6.  access to FindMeEvidence: &lt;Docker Host IP&gt;:80 and Solr: &lt;Docker Host IP&gt;:8080/solr

Run Seperate Docker Container For Solr And Apache
-------------------------------------------------

1. run Solr Container: `docker run --restart=always -v /home/path_to_index/solr4.10.4/solr:/opt/solr/example/solr -d --cap-add SYS_PTRACE --security-opt=apparmor:unconfined --name solr_instance -p 8081:8080 fme_solr`

2. run Apache Container: `docker run --restart=always -d -p 81:80 --link solr_instance:solr fme_apache`
