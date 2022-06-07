GVExport
========

This is a repository for a webtrees module known as gvexport. GVExport's goal is to produce an "all in one tree" of every person in a family tree and their relationships. It uses GraphViz to generate a tree, and if the GraphViz software is installed on the server it can produce output in multiple file formats such as JPG, SVG, PDF, and others.

Webtrees
--------

[Webtrees](https://webtrees.net/) is an online collaborative genealogy application. This can be hosted on your own server by following the [Install instructions](https://webtrees.net/install/). If you are familiar with Docker, you might like to install Webtrees using [this unofficial docker image](https://hub.docker.com/r/nathanvaughn/webtrees).

Installation of GVExport
------------

To install GVExport, first install webtrees (see above), then download all files into the `modules_v4/GVExport` directory (if using Docker, you'll want to map this to a host directory then place in that directory).

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

Help and support
----------------

If you need help with GVExport, please create an issue on GitHub.

If you find a bug or something isn't working, please create an issue.

If you want to request a new feature, please create an issue.

Older Versions
--------------

We attempt to keep up with the latest stable (non-prerelease) version of webtrees. You can see the latest release of webtrees [here](https://github.com/fisharebest/webtrees/releases). Older versions are not supported. *However*, as we progress through webtrees versions, we are releasing GVExport versions. These are listed on the [Releases page](https://github.com/Neriderc/GVExport/releases). If you are not using the latest version of webtrees, and the latest release of GVExport isn't working for you, you may want to try an older release.

For versions earlier than 2.0.21 you can try the [source repository](https://github.com/tillsc/GVExport), but note this DOES NOT work for 2.0.22 and later.

Contributing
------------

If you'd like to contribute to GVExport, great! You can contribute by:

- Creating issues for any new ideas you have
- Creating issues if you find bugs
- Contributing code - check out the Issues for things that need attention. If you have changes you want to make not listed in an issue, please create one, then you can link your pull request.
- Testing - it's all manual currently, please create issues for any bugs you find.

This repository
---------------
This repository was forked from [tillsc/GVExport](https://github.com/tillsc/GVExport) after community pull requests had not been merged for some time. [A post](https://www.webtrees.net/index.php/en/forum/4-customising/35801-display-complex-all-in-on-tree-with-gvexport) had been made on the webtrees forum stating that GVExport had been brought up to date to work with webtrees 2.0, but that the developer could not provide ongoing support. After a webtrees update broke the module, this fork was created to maintain compatibility.

Current maintainer:
 - https://github.com/Neriderc
(plus community contributions)

Past Authors:

 - Ferenc Kurucz <korbendallas1976@gmail.com>
 - https://github.com/IJMacD
 - https://github.com/pceres
 - Till Schulte-Coerne (https://github.com/tillsc)
