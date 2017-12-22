echo ''
echo '[INFO]  run this on the homestead virtual machine, or just use it as a reference for what commands to run'
echo ''
echo ''
curl -s -XDELETE http://localhost:9200/folgersdap/?pretty > /dev/null
echo 'deleted Elasticsearch index (if any)'
bin/console ongr:es:index:create
echo 'Used ONGR console command to create index'
curl -s -XGET http://dapdev.dev/dap/buildelasticindex?headless=1 > /dev/null
echo 'Populated index from Postgres DB via http://dapdev.dev/dap/buildelasticindex'