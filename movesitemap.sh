echo $PWD
if [ "$PWD" = "/var/www/dap" ]
then
	cp web/sitemap.xml ../dap-client/web/sitemap.xml
fi	
if [ "$PWD" = "/home/vagrant/Code/dapdev" ]
then
	cp web/sitemap.xml ../dap-client/web/sitemap.xml
fi