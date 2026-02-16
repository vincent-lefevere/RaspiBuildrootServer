# RaspiBuildrootServer

This software is a web server designed to provide access to [Buildroot](https://buildroot.org)  
for learning purposes, using a [Raspberry Pi](https://www.raspberrypi.com) as the target hardware.

It enables learners to work in groups (*up to 30 active groups simultaneously*)  
on projects while reducing the compilation time during practical work,  
thanks to optimized configuration. A shared precompilation of the development toolchain  
and a number of packages also allows for faster image generation for each group.

## Required Hardware and Software Configuration

The software is intended to be installed on a virtual machine (or dedicated server) with the following minimum specifications:
- 8 GB of RAM,
- 40 GB of hard disk space,
- 1 CPU core per two practical work groups using the server simultaneously,
- An Ethernet network card to ensure internet access for the server and to allow users  
  to connect to the software via HTTPS on port 443 and via SFTP on ports 2201 to 2230.

The software has been tested on a minimal installation of [Debian](https://www.debian.org) 12,  
with [Docker](https://www.docker.com) installed and configured using a provided script (see next section).

## List of dependencies

| Produits  | How  |
|-----------|------|
| [Docker](https://www.docker.com) | downloaded |
| [Debian](https://www.debian.org) | downloaded docker image |
| [Apache](https://httpd.apache.org/) | downloaded docker image |
| [php](https://www.php.net) | downloaded docker image |
| [mariadb](https://mariadb.org/) | downloaded docker image |
| [git](https://git-scm.com/) | downloaded |
| [mosquitto](https://mosquitto.org/) | downloaded |
| [telegraf](https://www.influxdata.com/time-series-platform/telegraf/) | downloaded |
| [proftpd](http://www.proftpd.org/) | downloaded |
| [wsssh](https://github.com/vincent-lefevere/wsssh) | downloaded |
| [xterm.js](https://xtermjs.org/) | directly included : [xterm.js](../../blob/main/docker-buildroot/html/js/xterm.js) [xterm.css](../../blob/main/docker-buildroot/html/css/xterm.css) |

## Prerequisite Installation

This server requires [Docker](https://www.docker.com) to be installed beforehand,  
using the script named `docker-install.sh` (to be run with root privileges).

```bash
git clone https://github.com/vincent-lefevere/RaspiBuildrootServer.git
cd RaspiBuildrootServer
./docker-install.sh
```

## Building the Containers

In the "[docker-buildroot](../../tree/main/docker-buildroot)" directory,  
there is a script called "[build.sh](../../blob/main/docker-buildroot/build.sh)"  
used to generate the project's containers.

```bash
cd docker-buildroot
./build.sh
```

**Note**: A self-signed certificate and a private key (used for HTTPS connections)  
are generated and placed in the subdirectory  
"[conf/web](../../tree/main/docker-buildroot/conf/web)"  
of the current directory ("RaspiBuildrootServer/docker-buildroot").  
They can be replaced by a private key saved as "server.key"  
and a certificate saved as "server.cer".

## Customisation du logo centrale

The software's web interface features a banner with the Buildroot logo on the left,
the Raspberry PI logo on the right and a space in the centre for the institutional
logo of the establishment using the software. To change this central logo, you need
to place your logo in the "[html/img](../../tree/main/docker-buildroot/html/img)"
directory under the name "logo-enterprise.png".

## Launching RaspiBuildrootServer

To start the containers, use the provided script  
"[up.sh](../../blob/main/docker-buildroot/up.sh)".  
It launches the containers and configures Docker to automatically restart them  
in case the host virtual machine is rebooted.

```bash
./up.sh
```

### Administrator Login Configuration

During the first HTTPS connection via a web browser,  
a login screen will appear asking for an email and password.  
These credentials will be used to create the initial administrator account.

**Note** : The surname/first name field associated with this account will
be set to "FIRST ADMIN" (which can be changed later if you wish).

## Software configuration

To access the settings window, click on the icon ![of the wheel](../../blob/main/docker-buildroot/html/img/config.png)
in front of "**Hello FIRST ADMIN**". This window offers 6 parameter settings.

![settings window](../../blob/main/documentation/img_en/conf_rubs.png)

### User management

The first section, at the top left of the window, provides access
to download a ".csv" file containing the list of users to be created,
modified, or deleted.

![user management section](../../blob/main/documentation/img_en/conf_rub1.png)

The ".csv" file must be encoded in UTF-8 with 4 columns (separated by semicolons):

- In the first column, enter a value of 0 (for a student account) or 1 (for a teacher account).

- In the second column, enter the account login email.

- In the third column, enter the initial password (in clear text).
This can be changed by the user (and will be stored in the database in encrypted format).
If the password is empty, the account is deleted (but you cannot delete your own account).

- In the fourth column, enter the user's First Name and Last Name.

Click on the "Choose a file" button to select the ".csv" file to be processed on your computer.
Once the file has been validated, processing begins immediately:
new accounts are created, existing accounts with empty passwords are deleted
and the type and the first names/surnames of existing accounts are updated.

**Note** : *The [documentation/exemple_en] directory (../../tree/main/documentation/exemple_en) contains an example file for creating 4 accounts (1 teacher account and 3 student accounts).
This file is called [list.csv](../.../blob/main/documentation/example_en/list.csv)*

| teacher | login email             | password  | surname/first name  |
| ------- |:------------------------|:----------|:--------------------|
| no      | student1@institution.uk | student1  | English Student n°1 |
| no      | student2@institution.uk | student2  | English Student n°2 |
| no      | student3@institution.uk | student3  | English Student n°3 |
| yes     | teacher1@institution.uk | teacher1  | English Teahcer n°1 |

### Download Buildroot software

The second section, at the top right of the window, provides access to download
from [the official website](https://buildroot.org/downloads/), via the internet,
the version of Buildroot that we want to use.

![Buildroot download section](../../blob/main/documentation/img_en/conf_rub2.png)

To retrieve the buildroot-2025.02.1.tar.gz file, for example, just enter **2025.02.1** and
click on the "**Add the indicated version**" button.
Once successfully downloaded, the version will appear in the list of choices in the next section.

### Toolchain generation

The third section, in the middle left of the window, allows you to generate the toolchain
(or compilation chain) by selecting the Raspberry Pi model you wish to use from the first
drop-down list.
The contents of this drop-down list are automatically constructed from the Raspberry Pi
models available for the versions of Buildroot you have downloaded.

The following list allows you to select a version of Buildroot compatible with the type
of Raspberry Pi selected.

**Note**: When a ![delete](../../blob/main/docker-buildroot/html/img/del.png) icon appears
to the right of this list of options, you can click on
it to request the deletion of the selected buildroot version. If the icon is not present,
it means that a toolchain is using it and that you must first delete that toolchain.

![toolchain generation section](../../blob/main/documentation/img_en/conf_rub3.png)

While a toolchain is being generated, the Buildroot logo at the top left of the web page flashes.
You can take advantage of this to request the generation of another toolchain, but this will be queued.
Queued toolchains appear on an orange background in the list of toolchain choices in the next section,
and the toolchain currently being generated appears on a flashing orange and green background.
Finally, those that have been completed are highlighted in green.

### Creating new speedup lists

The fourth section, in the middle right of the window, allows you to create new acceleration lists
that can then be integrated when generating a precompiled Buildroot image.

![acceleration list creation section](../../blob/main/documentation/img_en/conf_rub5.png)

The "List title" field is used to specify the name given to the new list.

Note: The name given to a new list must be different from names previously used.

The text box labelled "List packages to precompile" is used to specify the list of packages.
You will find an example corresponding to the definition of the predefined list named
"**GrovePi in Python**" in the file [speedup.txt](../../blob/main/documentation/example_en/speedup.txt)

Place one package per line in the list, distinguishing between those whose names begin with "host-",
which are just integrated into the compilation virtual machine, and those that will be compiled for the target.
For the latter, the package name must be followed by the name of the environment variable used by Buildroot,
separated by a comma.

### Generating the precompiled Buildroot image

The fifth section, at the bottom left of the window, allows you to generate a precompiled Buildroot image
by choosing:

- firstly the toolchain which will be used to compile,

**Note**: when a ![delete](../../blob/main/docker-buildroot/html/img/del.png) icon appears to the right of this list of choices, you can click on it to request
the deletion of the selected toolchain. If the icon is not present, this means that a precompiled Buildroot
image is using it and that you will first need to delete that image.

- secondly, the Buildroot version (which by default will be the same as the one used to generate the toolchain),

- thirdly, a list of packages that will be precompiled in order to reduce compilation times for users.

The software comes with 2 predefined lists of packages:

- "**Empty**": an empty list.

- "**GrovePi in Python**": a list of packages to speed up compilation of packages (and their dependencies)
when using GrovePi HAT in Python.

**Note**: The ![delete](../../blob/main/docker-buildroot/html/img/del.png) icon is available to delete an added list when it is selected.

![pre-copiled image generation section](../../blob/main/documentation/img_en/conf_rub4.png)

To confirm your choices and request generation of the precompiled image, click on the button:
"**Start creating the image for Virtual Machines**".

**Note**: The button is disabled when the selections match an image that has already been generated
or is currently being generated.

While a precompiled image is being generated, the Buildroot logo at the top left of the web page flashes.
You can take advantage of this to request the generation of another image, but this will be queued
with the toolchain generation requests.

**Note**: Generating a toolchain takes priority over generating an image to get out of this queue.

### List of generated images

The sixth section, at the bottom left of the window, shows the list of images for Virtual Machines.
The ![delete](../../blob/main/docker-buildroot/html/img/del.png) icon to the right of each image label allows you to request the destruction of that image.

### Exit the settings window

While the generation of toolchains and images is underway, we can already create project categories
in which users can then create their own projects.
To do this, first exit the **Software settings** window by clicking on the close cross at the top
right of the window, and then return to the project list.

## Creating project categories

The main window managing projects by category begins by displaying, at the top, a banner grouping
together the projects in which you are participating (whatever the category in which they were
created) like a list of shortcuts.

Below are the banners for the different categories, created by the teachers.
Clicking on the category title in the banner opens the selected category and closes the previously
open category.

![category creation](../../blob/main/documentation/img_en/proj_rub1.png)

The last banner, which only appears for teachers, allows you to create a new category by clicking
on the blue cross. A popup asks for the title of the category to be created.

**Note** : *As soon as an image is finished being built, a pictogram of a large blue cross will
appear in each project category, enabling users to create projects there.*

## Restarting and shutting down the server

### Restarting the server

To restart the server, if necessary, simply use the Linux command "**reboot**".
After restarting, the configuration and all projects will be restored to their previous states.
Of course, any commands running in the various virtual machines executing
buildroot will have been stopped and will need to be restarted by users if necessary.

### Shutting down the server

To shut down the server, simply use the Linux command "**halt**". The next time you restart,
you will find the configuration and all projects in their states as if you had used
the "**reboot**" command.

### Full Shutdown of the Software

Pour demander l'arrêt complet des containers (et la destruction des données mémorisées),
un script "[down.sh](../../blob/main/docker-buildroot/down.sh)" est fourni.

```bash
./down.sh
```
