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

## Customisation du logo centrale

L'interface web du logiciel présente un bandeau avec à gauche le logo de Buildroot,
à droite le logo de Raspberry PI et au centre une place pour le logo d'institutionnel
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

**Remarque** : Le champ nom/prénom associé à ce compte sera initialisé la valeur "FIRST ADMIN"
(que l'on pourra changer par la suite si on le désire).

## Paramètrage du logiciel

Pour accéder à la fenêtre de paramètrage, vous cliquez sur l'icone ![de la roue](../../blob/main/docker-buildroot/html/img/config.png)
située devant "**Bonjour FIRST ADMIN**". Cette fenêtre offre 5 rubriques de paramètrage.

**Remarque** : La 5ème et dernière rubrique (en bas à droite n'est pas fonctionnelle dans cette
version du logiciel).

### Gestion des utilisateurs

La première rubrique en haut de la fenêtre sur toute la largeur offre un accès de téléchargement d'un fichier ".csv" contenant la liste des utilisateurs à créer, à modifier, ou à détruire.

![rubrique gestion des utilisateurs](../../blob/main/docker-buildroot/documentation/img_fr/conf_rub1.png)

Le fichier ".csv" doit être codé en UTF-8 avec 4 colonnes (séparées par des points virgules) :

- En première colonne, mettre une valeur 0 (pour le compte d'un étudiant) ou 1 (pour le compte d'un professeur).

- En seconde colonne, mettre l'email de connexion au compte.

- En troisième colonne, mettre le mot de passe initial (en clair).
Ce dernier pourra être changé par l'utilisateur (et sera conservé dans la base de données
sous un format chiffré). Si le mot de passe est vide, le compte est supprimé
(mais on ne peut pas supprimer son propre compte).

- En Quatrième colonne, mettre le Prénom et le Nom de l'utilisateur.

En cliquant sur le bouton "choisir un fichier", on sélectionne sur son ordinateur le fichier ".csv" à traiter.
Une fois, validé, le traitement du fichier commence tout de suite : Les nouveaux comptes sont créés,
les comptes existant ayant un mot de passe vide sont supprimés enfin le type et les Prénom/Nom des comptes existant
sont mis à jour.

### Téléchargement du logiciel Buildroot

### Génération du toolchain

### Génération de l'image Buildroot précompilée

## Arrêt complet du logiciel

Pour demander l'arrêt complet des containers, un script "[down.sh](../../blob/main/docker-buildroot/down.sh)"
est fourni.

```bash
./down.sh
```

**Remarque** : Les containers base de données (utilisant "[Mariadb](https://mariadb.org)" et
"[Git](https://git-scm.com/)") étant arrêtés, et comme il n'y a pas de persistance des données de mis
en place, l'intégralité de la configuration et des données utilisateurs sont alors perdus.

