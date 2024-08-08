# TODO

## Other
* Artefact entity ?
* templates de Timeline avec des Form scenaristiques différents d'une simple liste
* Action Créer un Fork de Transhuman
* Redo pixabay autocomplete avec Alpine

## Report on migration to symfony 7
* mediawiki-services-parsoid use of Psr\Log should be updates to 2.* or 3.*
* mediawiki-services-parsoid must use dev-main for wikimedia/json-codec because the current release 2.2.1 is using an old Psr\Container
* strangelovebundle should be updated for symfony 7.0 components and a new Psr\Log above 1.1
* in strangelovebundle, signature of getConfigTreeBuilder() method in class src/DependencyInjection/Configuration.php should return TreeBuilder
* of course "minimum-stability" should be set to "dev", which is not acceptable
