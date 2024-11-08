# 1ere Approche
* une table (donc un jdr) = une database
* la table contient une config pour le système JDR utilisé
* Partir de Wikitext et utiliser des templates le plus possible
* Chaque JDR est paramétré avec sa liste de template wikitext et d'entités gérés : on peut en faire un bundle
* dans chaque bundle, on a une table 2D : entité × (show, edit, delete, row)
* peut-être qu'il est plus simple d'utiliser des templates twig mais utiliser des templates wikitext permet de partager du code

# 2e approche
* Bundle-iser la partie commune du mondèle avec des abstract, les controllers generiques et templates
* Faire un appli par RPG (mais il reste le problème pour la table)
