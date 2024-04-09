# TODO

## Other
* Artefact entity ?
* templates de Timeline avec des Form scenaristiques différents d'une simple liste
* Action Créer un Fork de Transhuman
* Redo pixabay autocomplete avec Alpine
* Autocomplete pour le namespace 'ep' dans les wikitext contents (ajax search to fandom, réactif ?)

## Info NMAP
nmap 192.168.44.2-254 -p9090 --open -oX - -sT
XPath: //host/ports/port[@protocol="tcp"][@portid="9090"]/ancestor::host/address[@addrtype="ipv4"]/@addr

## Report on migration to symfony 7
* mediawiki-services-parsoid use of Psr\Log should be updates to 2.* or 3.*
* mediawiki-services-parsoid must use dev-main for wikimedia/json-codec because the current release 2.2.1 is using an old Psr\Container
* strangelovebundle should be updated for symfony 7.0 components and a new Psr\Log above 1.1
* in strangelovebundle, signature of getConfigTreeBuilder() method in class src/DependencyInjection/Configuration.php should return TreeBuilder
* of course "minimum-stability" should be set to "dev", which is not acceptable
