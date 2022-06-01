GVExport
========

This is a repository for a webtrees module known as gvexport. GVExport's goal is to produce an "all in one tree" of every person in a family tree and their relationships.

Current maintainer:
 - https://github.com/Neriderc

Past Authors:

 - Ferenc Kurucz <korbendallas1976@gmail.com>
 - https://github.com/IJMacD
 - https://github.com/pceres
 - Till Schulte-Coerne (https://github.com/tillsc)



Webtrees
--------

[Webtrees](https://webtrees.net/) is an online collaborative genealogy application. This can be hosted on your own server. If you are familiar with Docker, you might like to install Webtrees using [this unofficial docker image](https://hub.docker.com/r/nathanvaughn/webtrees).

Installation
------------

Download all files into the `modules_v4/GVExport` directory (if using Docker, you'll want to map this to a host directory then place in that directory).
In Webtrees, go to *My Pages -> Control Panel*, in the Modules section go to *Geneology -> Charts*, then check the *Enable* checkbox and click *Save* at the bottom of the page.

GraphViz
--------

The ouptut format of the module is known as DOT and is used by [GraphViz](http://www.graphviz.org/) which is a general pupose graphing tool. If GraphViz is installed on the server the module can use it to generate image files directly.

If you run a Debian based OS (like Ubuntu), you can install this by running the command `sudo apt install graphviz`.

If you're using Docker, you could update the Dockerfile apt-install process to include `graphviz`, or for a once off you could run:
````
docker exec -it webtrees apt update && docker exec -it webtrees apt install graphviz
````

However, note that while this command installs graphviz in the container, if you rebuild your container it will be lost and you'll have to do it again.

GVExport Module
---------------

Once installed, go to *Charts -> GVExport* to see the options.

Some further but non-exhaustive instructions can be found in [this forum post](https://www.webtrees.net/index.php/en/forum/4-customising/35801-display-complex-all-in-on-tree-with-gvexport?start=0) from the previous version.
