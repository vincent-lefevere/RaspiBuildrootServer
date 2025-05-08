# RaspiBuildrootServer User Manual

## Preliminary assumption

- Let's assume that the server has been installed by the administrator of the educational
institution under the name: "**buildroot.institution.uk**".

- Let's assume that the accounts created (and used for the purposes of this user manual) are:
**student1@institution.uk**, **student2@institution.uk**, **student3@institution.uk**
and **teacher1@institution.uk**

- Finally, let's suppose that the project category created has the title "**Practical work**".

## Project group connection and access to the server:

1. Connect to the web server using the URL **https://buildroot.institution.uk/**

You start by choosing your language (French or English), then enter your login email
and the associated initial password that the teacher has given you.

**Note:** *We strongly advise you to change your password immediately.*

To do this, enter your password in the two fields at the bottom of the yellow block.
If the two passwords are identical, the "**login**" button will be deactivated and
the "**change**" button will be activated.
You can then click on it to change the connection password.
If the original password was the correct one, the new password is automatically
entered in the first password entry field above the "**login**" button.

![Login](img_en/login.png)

All you have to do is click on the "**login**" button.

2. In each group, the student, designated as the project leader, clicks on the
title of the category specified by the teacher, i.e. "**Practical work**".

All the projects in the category are then displayed under the category title.
Click on the pictogram (in the shape of a large blue +), corresponding to the
creation of a new project, located after the list of projects in this category.

When you create a project, the interface will ask you, via a popup, to give it
a title that will distinguish it from your other projects (without having
to remember the identification number that will be assigned to it).

Once created, you are automatically a member of the project and you will find it
in the list of your projects under the first heading.
Here you will find the number identifying the project, the title you have given
to the project and the list of first names and surnames involved in the project.

For the time being, you must be the only participant and your colleagues will
now find the project in the list of the "**Practical work**" category, whereas
you will find it both in this list (framed by a red border) and in the list of
your projects.

3. You can now select the project you wish to work on.

## Access to project settings

The project settings window that opens when you select a project is divided
into 5 blocks (4 permanent blocks and a 5th block for displaying the server's
internal git history).

### Project identification block

This block is located at the top left of the window. This block contains :
- the project number,
- the title, which can be changed by editing the input field

    *When the cursor leaves the input field, it is validated.*

- and a tick box.

    *This locks access to the project for student colleagues who are not members,
    by removing it from the list of its category.
    Teachers will still see the project in the list of its category and
    will still be able to access it.*

![bloc 1](img_en/set_cad1.png)

**Note**: *Obviously, any changes you make via this block will not be taken
into account if you are not a member of the project.*

### Buildroot version selection block

As it is possible for the teacher to install several versions of Buildroot
compiled with different hardware targets, this block at the top right
of the window gives us the option of choosing the Buildroot version
with which we currently want to work on the project.

**Note** : *When the virtual machine has been started, the image used will
depend on this choice and the drop-down list will be transformed into a
simple display field showing the version used. To change version, simply
switch off the virtual machine and take advantage of the inverse transformation
of the display field into a list of choices.*

![bloc 2](img_en/set_cad2.png)

This framework also gives teachers the option of giving "expert" rights
to use all bash commands from this project.

For a student, the message and the box to be ticked, which follows,
do not appear in this box. If a teacher has given the project "expert" rights,
a message in bold will indicate this in this box.
For most uses, the "expert" right is not necessary.

### Member management block

In this third block, located below the first block on the left of the window,
you will find the list of project members, with your login (or your login email
if you prefer) on the first line. The pictograms in front of each login will
allow you to manage the other members (those who have not created the project
and wish to participate).

#### Devenir membre d’un projet

- **Example** : **student1@institution.uk** having created project number 1 sees
  the frame like this.
  ![bloc 3 figure a](img_en/set_cad3a.png)

In the list of project members, your email address appears first in normal font
(indicating that you don't yet have access) with the
![add](../docker-buildroot/html/img/add.png) access request pictogram that you
can click on.

- **Example** : **student2@institution.uk** wants to be part of project number 1
  and therefore clicks on the access request pictogram when he sees the frame
  like this.

  ![bloc 3 figure b](img_en/set_cad3b.png)

  Once you have made the request, the frame display will look like this.

  ![bloc 3 figure c](img_en/set_cad3c.png)

The other members of the group (including the creator) will now see you in the
list of members. The ![valid](../docker-buildroot/html/img/valid.png) access icon
allows them to give you permission to enter and the
![supr](../docker-buildroot/html/img/supr.png) removal icon allows them to deny
you access by removing you from the list).

- **Example** : **student1@institution.uk** va pouvoir décider s'il valide 
  ou refuse la demande de participation de **student2@institution.uk**.

  ![bloc 3 figure d](img_en/set_cad3d.png)

  Après validation, l'affichage pour **student2@institution.uk** devient le suivant.

  ![bloc 3 figure e](img_en/set_cad3e.png)

**Note** : *Teachers do not need your approval to join your group.*

### Action block

In this fourth block, located on the right-hand side of the window under the
**version choice block**, once you are a member, you will find buttons for
deleting the project, starting or stopping the virtual machine running Buildroot
for our project and finally displaying the Buildroot control terminal.

#### Launching the virtual machine

To switch on the compilation server associated with the project,
press the following button:

![startvm](img_en/startvm.png)

An available Virtual Machine (which is independent of your project number and
can therefore change each time you restart) loads the configuration previously
saved for this project.

**Note** : *A message in bold indicates the sftp port number to be used
to retrieve the compiled image or to upload new business packages.*

#### Virtual machine access

Next, press the following button to access the Buildroot control terminal:
 
![viewvm](img_en/viewvm.png)

A new window (the terminal window) replaces the project settings window.

This window can be closed at any time (by clicking on the cross at the top right)
to return to the project settings window.

## Using the virtual machine terminal

In the terminal window, on the right-hand side are buttons for launching
the most important Buildroot commands in a shell and on the left-hand side
is the terminal for viewing, or interacting with, the execution of the command.

![term](img_en/term.png)

### Example of use : make menuconfig

To access the configuration system, we commonly use the "make menuconfig" command
from "buildroot". You can access this command via the button of the same name on
the interface.

After a few seconds (during which the compilation command scrolls across the screen),
the menu appears in the terminal on the left of the web interface.

![menuconfig](img_en/menuconfig.png)

To use the menu, click in the terminal (the cursor will be activated) and
use the arrows on the keyboard and the "enter" key, as you would for traditional
use of buildroot.

**Note**: *When you execute a command, the command buttons turn red and will turn
green again when the command is complete. Your project partners will be able
to see and interact with the command (as you do) by using the "**refresh terminal**"
button.*

They will start by seeing, in fast motion, the history of all the displays you have
had in your terminal before arriving at the display synchronous with the command
being executed. You can also use this button after logging out while a command is
running. You can then resume execution of the command (if it has not finished) or
simply view the result of the last command executed by buildroot.


