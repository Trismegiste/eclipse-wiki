# Eclipse Phase TTRPG Live Gamemaster Wiki Manager Application

## What 
It's a webapp for building and running sessions for the tabletop-role-playing game Eclipse Phase.
But for the record, this app use my conversion of the (far-too-complicated) original rules to the much-simplier Savage Worlds system.
The detail of this conversion can be found on a Fandom french mediawiki, since Eclipse Phase is a Creative Common RPG.

## Who
This app is intended for the GM. It helps you to create and build new scenario with a limited wikitext language and can stores your images.
It also contains a minimalistic battlemap manager and a **Websocket** broadcaster view for players which can watch pictures, 
battlemaps and game handouts on their smartphones (a QR code reader on the smartphone could be useful).

## Why
Though I prefer playing around a table with friends, you lack some fancy features from VTTRPG websites (Roll20 for exemple). 
This app is intended to fill this gap : playing around a table and keeping the fancy features of computer. 
Why not using Roll20 ? Well, first all your players must have an account. Furthermore, Roll20 is slow, ugly, old and 
totally fails when it comes to improvisation. You cannot create on the fly new character, new battlemaps and so on, the UX is a major failure.

This app comes with NPC name generator, avatar generator, battlemaps generator, Handouts generator, Love letters manager, and most important : 
a NPC profile generator for the 7 in-game social networks. Since your PC in the game can watch thoses profiles in Augmented Reality, the
GM have to generate a lot of these profiles anytime PCs encountered a new NPC.

## How
With Symfony 5.4, MongoDb 4+ and a mediawiki from Fandom. It uses AlpineJs, PureCSS, SvgJs and many javascript components (frozen in the app, since
javascript ecosystem is an insane nuclear chaos where BC is unknown concept).

## Where
Run this app anywhere with :
```bash
$ docker compose up
```

