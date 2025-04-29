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

Dans le répertoire "[docker-buildroot](tree/main/docker-buildroot)",
se trouve un script "[build.sh](blob/main/docker-buildroot/build.sh)"
qui sert à générer les différents containers du projet.

```bash
cd docker-buildroot
./build.sh
```

**Remarque** : un certificat autosigné et une clef privée (qui serviront pour la connexion https)
sont générés et placés dans le sous-répertoire "[conf/web](tree/main/docker-buildroot/conf/web)"
du répertoire courant (RaspiBuildrootServer/docker-buildroot).
Ils peuvent être remplacés par une clef privée à mettre dans un fichier nommé "server.key" et
un certificat à mettre dans un fichier nommé "server.cer".

## Lancement de RaspiBuilrootServer

Pour lancer les containers, un script "[up.sh](blob/main/docker-buildroot/up.sh)"
est mis à disposition. Il provoque le lancement des containers et configure Docker pour le
redémarrage automatique en cas de reboot de la machine virtuelle hébergeant le logiciel.

```bash
./up.sh
```

### Configuration du login administrateur

Lors d'une première connexion https, via un navigateur, on obtient l'interface de connexion
demandant un couple, sous la forme d'un email et d'un mot de passe, qui sera utilisé comme
accès administrateur initial.

## Arrêt complet du logiciel

Pour demander l'arrêt complet des containers, un script "[down.sh](blob/main/docker-buildroot/down.sh)"
est fourni.

```bash
./down.sh
```

**Remarque** : Les containers base de données (utilisant "[Mariadb](https://mariadb.org)" et
"[Git](https://git-scm.com/)") étant arrêtés, et comme il n'y a pas de persistance des données de mis
en place, l'intégralité de la configuration et des données utilisateurs sont alors perdus.

