# RaspiBuildrootServer
Ce logiciel est un Serveur Web destiné à donner l'accès à [Buildroot](https://buildroot.org)
pour l'apprentissage de l'outil en utilisant une cible matériel de type [Raspberry PI](https://www.raspberrypi.com).

Cela permet à des apprenants de travailler en groupe (*30 groupes maximum actifs en même temps*)
sur des projets en diminuant les temps de compilation à supporter pendant les travaux pratiques
via une optimisation du paramètrage. La précompilation commune pour tous les projets de la chaîne
de développement (toolchain) et d'un certain nombre de packages, permet également d'offir un
temps de génération des images réduites pour chaque groupe.

## Configuration matériel et logiciel requise

Le logiciel est prévu pour être installé dans une machine virtuelle (ou un serveur dédié) pourvu
avec :
- un minimum de mémoire centrale de 8 Giga,
- un minimum de disque dur de 40 Giga,
- un minimum de 1 coeur de calcul pour deux groupes de travaux pratiques utilisant simultannément le serveur.
- une carte réseau Ethernet pour que le logiciel dispose d'un accès internet et pour que les utilisateurs 
puissent avoir accès au logiciel en https sur le port 443 et en sftp sur les ports de 2201 à 2230.

Le logiciel a été testé sur une installation minimaliste de [Debian](https://www.debian.org) 12 sur
laquelle [Docker](https://www.docker.com) sera installé et paramétré via un script fourni
(voir paragraphe suivant).

## Liste des dépendances

| Produits  | comment    |
|-----------|------------|
| [Docker](https://www.docker.com) | téléchargé |
| [Debian](https://www.debian.org) | image docker téléchargée |
| [Apache](https://httpd.apache.org/) | image docker téléchargée |
| [php](https://www.php.net) | image docker téléchargée |
| [mariadb](https://mariadb.org/) | image docker téléchargée |
| [mosquitto](https://mosquitto.org/) | téléchargé |
| [telegraf](https://www.influxdata.com/time-series-platform/telegraf/) | téléchargé |
| [wsssh](https://github.com/vincent-lefevere/wsssh) | téléchargé |
| [xterm.js](https://xtermjs.org/) | directement inclus : [xterm.js](../../blob/main/docker-buildroot/html/js/xterm.js) [xterm.css](../../blob/main/docker-buildroot/html/css/xterm.css) |

## Installation préalable

Ce serveur nécessite l'installation préalable de [Docker](https://www.docker.com) via le script nommé 
"docker-install.sh" (exécuté avec les droits root).

```bash
git clone https://github.com/vincent-lefevere/RaspiBuildrootServer.git
cd RaspiBuildrootServer
./docker-install.sh
```

## Construction des containers 

Dans le répertoire "[docker-buildroot](../../tree/main/docker-buildroot)",
se trouve un script "[build.sh](../../blob/main/docker-buildroot/build.sh)"
qui sert à générer les différents containers du projet.

```bash
cd docker-buildroot
./build.sh
```

**Remarque** : un certificat autosigné et une clef privée (qui serviront pour la connexion https)
sont générés et placés dans le sous-répertoire "[conf/web](../../tree/main/docker-buildroot/conf/web)"
du répertoire courant (RaspiBuildrootServer/docker-buildroot).
Ils peuvent être remplacés par une clef privée à mettre dans un fichier nommé "server.key" et
un certificat à mettre dans un fichier nommé "server.cer".

## Customisation du logo central

L'interface web du logiciel présente un bandeau avec à gauche le logo de Buildroot,
à droite le logo de Raspberry PI et au centre une place pour le logo institutionnel
de l'établissement utilisant le logiciel. Pour changer ce logo central, vous devez
placer votre logo dans le répertoire "[html/img](../../tree/main/docker-buildroot/html/img)"
sous le nom "logo-enterprise.png".

## Lancement de RaspiBuilrootServer

Pour lancer les containers, un script "[up.sh](../../blob/main/docker-buildroot/up.sh)"
est mis à disposition. Il provoque le lancement des containers et configure Docker pour le
redémarrage automatique en cas de reboot de la machine virtuelle hébergeant le logiciel.

```bash
./up.sh
```

### Configuration du login administrateur

Lors d'une première connexion https, via un navigateur, on obtient l'interface de connexion
demandant un couple, sous la forme d'un email et d'un mot de passe, qui sera utilisé comme
accès administrateur initial.

**Remarque** : Le champ nom/prénom associé à ce compte sera initialisé avec la valeur "FIRST ADMIN"
(que l'on pourra changer par la suite si on le désire).

## Paramètrage du logiciel

Pour accéder à la fenêtre de paramètrage, vous cliquez sur l'icone ![de la roue](../../blob/main/docker-buildroot/html/img/config.png)
située devant "**Bonjour FIRST ADMIN**". Cette fenêtre offre 5 rubriques de paramètrage.

**Remarque** : La 5ème et dernière rubrique (en bas à droite) n'est pas fonctionnelle dans cette
version du logiciel.

### Gestion des utilisateurs

La première rubrique, en haut de la fenêtre sur toute la largeur, offre un accès de téléchargement d'un fichier ".csv" contenant la liste des utilisateurs à créer, à modifier, ou à détruire.

![rubrique gestion des utilisateurs](../../blob/main/documentation/img_fr/conf_rub1.png)

Le fichier ".csv" doit être codé en UTF-8 avec 4 colonnes (séparées par des points virgules) :

- En première colonne, mettre une valeur 0 (pour le compte d'un étudiant) ou 1 (pour le compte d'un professeur).

- En seconde colonne, mettre l'email de connexion au compte.

- En troisième colonne, mettre le mot de passe initial (en clair).
Ce dernier pourra être changé par l'utilisateur (et sera conservé dans la base de données
sous un format chiffré). Si le mot de passe est vide, le compte est supprimé
(mais on ne peut pas supprimer son propre compte).

- En quatrième colonne, mettre le Prénom et le Nom de l'utilisateur.

En cliquant sur le bouton "choisir un fichier", on sélectionne sur son ordinateur le fichier ".csv" à traiter.
Une fois, validé, le traitement du fichier commence tout de suite : Les nouveaux comptes sont créés,
les comptes existant ayant un mot de passe vide sont supprimés enfin le type et les Prénom/Nom des comptes existant
sont mis à jour.

**Remarque** : *Dans le répertoire [documentation/exemple_fr](../../tree/main/documentation/exemple_fr)
se trouve un fichier d'exemple de création de 4 comptes (1 compte enseignant et 3 comptes étudiant).
Ce fichier s'appelle [list.csv](../../blob/main/documentation/exemple_fr/list.csv)*

| professeur | email de login          | mot de passe  | nom/prénom              |
| ---------- |:------------------------|:--------------|:------------------------|
| non        | etudiant1@institut.fr   | etudiant1     | Etudiant Français n°1   |
| non        | etudiant2@institut.fr   | etudiant2     | Etudiant Français n°2   |
| non        | etudiant3@institut.fr   | etudiant3     | Etudiant Français n°3   |
| oui        | professeur1@institut.fr | professeur1   | Professeur Français n°1 |

### Téléchargement du logiciel Buildroot

La seconde rubrique, au milieu à gauche de la fenêtre, offre un accès pour récupérer
depuis [le site officiel](https://buildroot.org/downloads/), via l'accès internet,
la version de Buildroot que nous voulons utiliser.

![rubrique téléchargement de Buildroot](../../blob/main/documentation/img_fr/conf_rub2.png)

Pour récupérer, par exemple, le fichier buildroot-2025.02.1.tar.gz, on indique seulement **2025.02.1**
et on clique sur le bouton "**Ajouter la version indiquée**".
Une fois téléchargé avec succès, on voit la version apparaître dans la liste de choix de la rubrique suivante.

### Génération du toolchain

La troisième rubrique, au milieu à droite de la fenêtre, permet la génération de la toolchain
(ou chaine de compilation) en choisissant dans la première liste déroulante le modèle de Raspberry Pi
que l'on souhaite utiliser.
Le contenu de cette liste de choix est automatiquement construite à partir des modèles de Raspberry Pi
disponibles en fonction des versions de Buildroot que l'on a téléchargées.
La liste de choix suivante permet de sélectionner une version de Buildroot compatible avec le type de
Raspberry Pi sélectionné.

![rubrique génération du toolchain](../../blob/main/documentation/img_fr/conf_rub3.png)

Pendant la génération d'une toolchain, le logo Buildroot en haut à gauche de la page web clignotte.
On peut en profiter pour demander la génération d'une autre toolchain mais celle-ci sera mise en file d'attente.
Les toolchains en attente apparaissent, dans la liste de choix des toolchains de la rubrique suivante,
sur fond orange, celle qui est en cours de génération sur fond orange et vert clignotant.
Enfin celles qui sont terminées sur fond vert.

### Génération de l'image Buildroot précompilée

La quatrième rubrique en bas à gauche de la fenêtre, permet la génération d'une image précompilée de Buildroot,
en choisissant :

- premièrement la toolchain qui sera utilisée pour compiler,

- deuxièmement la version de Buildroot (qui par défaut sera la même que celle utilisée pour générer la toolchain),

- troisièmement une liste de packages qui seront précompilés afin de diminuer les temps de compilation,
par la suite, pour les utilisateurs.

Le logiciel est pourvu de 2 listes prédéfinies de packages :

- "**Empty**" : une liste vide.

- "**GrovePi in Python**" : une liste de packages permettant d'accélérer la compilation des packages
(et de leurs dépendances) lors de l'usage du HAT GrovePi en Python.

![rubrique génération de l'image précompilée](../../blob/main/documentation/img_fr/conf_rub4.png)

Pour valider ses choix et demander la génération de l'image précompilée, on clique sur le bouton
"**Lancer la création de l'image pour les Machines Virtuelles**".

Pendant la génération d'une image précompilée, le logo Buildroot en haut à gauche de la page web clignotte.
On peut en profiter pour demander la génération d'une autre image mais celle-ci sera mise
en file d'attente avec les demandes de génération des toolchains.
**Remarque** : La génération d'une toolchain est prioritaire sur la génération d'une image pour sortir
de cette file d'attente.

### Ressortir de la fenêtre de paramétrage

Pendant que les générations de toolchains et d'images se déroulent, on peut déjà créer des catégories
de projets dans lesquels les utilisateurs pourront ensuite y créer leur projet.
Pour cela il faut d'abord quitter la fenêtre de **Paramétrage du logiciel** en cliquant sur la croix
de fermeture située en haut à droite de la fenêtre et ainsi retourner dans la liste des projet.

## Création des catégories de projets

La fenêtre principale gérant les projets par catégorie commence par présenter, en haut, un bandeau
regroupant les projets auxquels vous participez (quelque soit la catégorie dans laquelle ils ont
été créés) comme une liste de raccourcies.

En dessous, on trouve les bandeaux des différentes catégories qui ont été créés par les enseignants.
Si on clique sur le titre de la catégorie dans son bandeau, la catégorie sélectionnée s'ouvre tandis
que la catégorie précédemment ouverte se referme.

![création de catégorie](../../blob/main/documentation/img_fr/proj_rub1.png)

Le dernier bandeau, apparaissant uniquement pour les enseignants, permet la création d'une nouvelle
catégorie en cliquant sur la croix bleue. Un popup demande alors le titre de la catégorie à créer.

**Remarque** : *Dès qu'une image sera terminée d'être construite, dans chaque catégorie de projets
apparaîtra un pictogramme constitué d'une grande croix bleu permettant aux utilisateurs d'y créer
des projets.*

## Arrêt complet du logiciel

Pour demander l'arrêt complet des containers, un script "[down.sh](../../blob/main/docker-buildroot/down.sh)"
est fourni.

```bash
./down.sh
```

**Remarque** : Les containers base de données (utilisant "[Mariadb](https://mariadb.org)" et
"[Git](https://git-scm.com/)") étant arrêtés, et comme il n'y a pas de persistance des données de mise
en place, l'intégralité de la configuration et des données utilisateurs sont alors perdues.

