Google Map API [![Packagist](https://img.shields.io/packagist/dt/jasin755/Maps.svg)](https://packagist.org/packages/jasin755/Maps/stats) [![Build Status](https://travis-ci.org/jasin755/Maps.svg?branch=master)](https://travis-ci.org/jasin755/Maps)
=========
This component is stated for Nette framework and it simlifies working with a Google map.

Requirements
============
* Nette Framework 2.1+

Installation
============

	composer require jasin755/maps:dev-master

and now the component can be registered in extensions in a neon config

```
extensions:
    map: Jashin\GoogleAPI\MapApiExtension
```
    	
The last step is to link `client-side/googleMapAPI.js` to your page.

[Documentation](https://github.com/jasin755/Maps/blob/master/docs/en)
