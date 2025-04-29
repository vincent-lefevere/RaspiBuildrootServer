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

In the "[docker-buildroot](tree/main/docker-buildroot)" directory,  
there is a script called "[build.sh](blob/main/docker-buildroot/build.sh)"  
used to generate the project's containers.

```bash
cd docker-buildroot
./build.sh
```

**Note**: A self-signed certificate and a private key (used for HTTPS connections)  
are generated and placed in the subdirectory  
"[conf/web](tree/main/docker-buildroot/conf/web)"  
of the current directory (`RaspiBuildrootServer/docker-buildroot`).  
They can be replaced by a private key saved as `server.key`  
and a certificate saved as `server.cer`.

## Launching RaspiBuildrootServer

To start the containers, use the provided script  
"[up.sh](blob/main/docker-buildroot/up.sh)".  
It launches the containers and configures Docker to automatically restart them  
in case the host virtual machine is rebooted.

```bash
./up.sh
```

### Administrator Login Configuration

During the first HTTPS connection via a web browser,  
a login screen will appear asking for an email and password.  
These credentials will be used to create the initial administrator account.

## Full Shutdown of the Software

To completely stop the containers, a script named  
"[down.sh](blob/main/docker-buildroot/down.sh)" is provided.

```bash
./down.sh
```

**Note**: The database containers (using [MariaDB](https://mariadb.org) and  
[Git](https://git-scm.com/)) will be stopped. Since no data persistence mechanism is set up,  
all configuration and user data will be lost upon shutdown.