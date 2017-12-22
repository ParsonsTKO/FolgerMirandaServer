README

When preparing the server update, just leave the previously-imported luna files in place.
Drop the database, then rebuild it. 
Then do imports - don't just use our database dump - as there are two records with URIs that should be updated.
Make sure to rebuild the elasticsearch indices using the provided script.

Notes on Imports:
* Put the binary files in /var/folger/storage/shrew/ and /var/folger/storage/trecento
* update the RemoteUniqueID in the files audio-example-Florence-Christmas-Music-Trecento.json and video-example-taming-shrew-1908.json from the local dev server's URI http://dapdev.dev/ to the "live" one : http://dap.parsonstko.com/