# TODO

## Full text search
https://www.mongodb.com/docs/v4.4/reference/operator/query/text/#std-label-text-query-examples

Dans le CheckDatabase :
> db.vertex.createIndex({title:"text", content:"text"}, {default_language:"french", weights: {title:5}, name:'FullTextSearch' })

Dans le VertexRepository::filterBy
> db.vertex.find({$text: {$search:'kalinda'}}, {content:false, score: {$meta:"textScore"} }).sort({score: {$meta:"textScore"} })

## Other
* Le show de Timeline ajoute une fonctionnalité AJAX qui permet de barrer un élement <LI> de la liste timeline
* Artefact entity ?
* templates de Timeline avec des Form différents d'une simple liste
* Action Fork des Transhuman
