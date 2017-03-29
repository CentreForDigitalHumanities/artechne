# ARTECHNE Drupal Bootstrap sub-theme

This repository contains the [Drupal Bootstrap](https://www.drupal.org/project/bootstrap) sub-theme for the [ARTECHNE database](http://artechne.hum.uu.nl/). It consists of four folders:

* `css`: contains the SCSS for this theme, which can be compiled with [Sass](http://sass-lang.com/).
* `js`: contains the JavaScript for this theme.
* `scripts`: contains [Drush](http://www.drush.org/) scripts that have been used to clean the database.
* `templates`: contains additional templates for this theme.

Please see the READMEs of the respective folders for more information.

This folder (the root) contains the following files: 

* `artechne.info`: Drupal theme specification. States that this is a sub-theme of Drupal Bootstrap, defines regions, and specifies which CSS- and JavaScript-files are included.
* `favicon.ico`: The favicon displayed in the browser address bar.
* `Gruntfile.js`: Grunt task definitions, see below for more information.
* `logo.png`: The logo for this theme.
* `package.json`: Node dependencies, see below for more information.
* `screenshot.png`: A screenshot for this theme.
* `template.php`: This is mainly where the magic happens: contains the code to alter certain aspects of the website.

## Compiling SCSS/JavaScript

The SCSS and JavaScript files can be compiled/minified using [Grunt](https://gruntjs.com/). Grunt requies [Node](https://nodejs.org/) and [npm](https://www.npmjs.com/) to be installed, and compiling of SCSS requires the [Ruby gem "sass"](http://sass-lang.com/install) to be installed. After that, local dependencies (from `package.json`) can be installed using `npm install`, and either `grunt` or `grunt watch` (to watch for changes) will do the compilation/minification.
