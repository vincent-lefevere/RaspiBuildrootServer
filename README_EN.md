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
of the current directory (`RaspiBuildrootServer/docker-buildroot`).  
They can be replaced by a private key saved as `server.key`  
and a certificate saved as `server.cer`.

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
située devant "**Bonjour FIRST ADMIN**". Cette fenêtre offre 5 rubriques de paramètrage.

**Note** : The 5th and last section (bottom right) is not functional
in this version of the software.

### User management

The first heading at the top of the full-width window provides access
to download a ".csv" file containing the list of users to be created,
modified or deleted.

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

### Download Buildroot software

The second section, in the middle left of the window, provides access to retrieve the buildroot
version we want to use from [the official site](https://buildroot.org/downloads/),
via Internet access.

![Buildroot download section](../../blob/main/documentation/img_en/conf_rub2.png)

To retrieve the buildroot-2025.02.1.tar.gz file, for example, just enter **2025.02.1** and
click on the "**Add the indicated version**" button.
Once successfully downloaded, the version will appear in the list of choices in the next section.

### Toolchain generation

The third section, in the middle right of the window, is used to generate the toolchain
(or compilation chain) by choosing the Raspberry Pi model you wish to use from the first
drop-down list.
The contents of this drop-down list are automatically constructed from the Raspberry Pi
models available for the versions of Buildroot you have downloaded.
The following list allows you to select a version of Buildroot compatible with the type
of Raspberry Pi selected.

![toolchain generation section](../../blob/main/documentation/img_en/conf_rub3.png)

While a toolchain is being generated, the Buildroot logo at the top left of the web page flashes.
You can take advantage of this to request the generation of another toolchain, but this will be queued.
Queued toolchains appear on an orange background in the list of toolchain choices in the next section,
and the toolchain currently being generated appears on a flashing orange and green background.
Finally, those that have been completed are highlighted in green.

### Generating the precompiled Buildroot image

The fourth section, at the bottom left of the window, allows you to generate a precompiled image of Buildroot,
by choosing:

- firstly the toolchain which will be used to compile,

- secondly, the Buildroot version (which by default will be the same as the one used to generate the toolchain),

- thirdly, a list of packages that will be precompiled in order to reduce compilation times for users.

The software comes with 2 predefined lists of packages:

- "**Empty**": an empty list.

- "**GrovePi in Python**": a list of packages to speed up compilation of packages (and their dependencies)
when using GrovePi HAT in Python.

![pre-copiled image generation section](../../blob/main/documentation/img_en/conf_rub4.png)

To confirm your choices and request generation of the precompiled image, click on the button:
"**Start creating the image for Virtual Machines**"

While a precompiled image is being generated, the Buildroot logo at the top left of the web page flashes.
You can take advantage of this to request the generation of another image, but this will be queued
with the toolchain generation requests.

**Note**: Generating a toolchain takes priority over generating an image to get out of this queue.

## Full Shutdown of the Software

To completely stop the containers, a script named  
"[down.sh](../../blob/main/docker-buildroot/down.sh)" is provided.

```bash
./down.sh
```

**Note**: The database containers (using [MariaDB](https://mariadb.org) and  
[Git](https://git-scm.com/)) will be stopped. Since no data persistence mechanism is set up,  
all configuration and user data will be lost upon shutdown.