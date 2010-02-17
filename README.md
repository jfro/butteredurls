Lessn More
==========

Lessn is an extremely simple, personal url shortener 
	written in PHP with MySQL and mod_rewrite
	by Shaun Inman <http://shauninman.com/>.

Buttered URLs is a fork of Lessn by Jeremy Knope <http://github.com/jfro>.

Lessn More is a fork of Buttered URLs
	by Alan Hogan <http://github.com/alanhogan>.

<!-- 
  This document is written in Markdown,
  readable as text or convertible to HTML.
  Syntax: http://bit.ly/mkdnsyntax
  Converter & cheatsheet: http://tinyurl.com/mkdndingus 
  Editor w/ instant preview: http://tinyurl.com/mkdnwmd
  (TextMate: ⌃⌥⌘-P to preview as web page)
-->

Lessn is offered as-is, sans support and without warranty.
Copyright © 2009-10 Shaun Inman and contributors.


Requirements
-------------

* PHP 5.1+
* PHP's PDO
* MySQL or PostgreSQL or SQLite
* mod_rewrite or similar rewrite system (see .htaccess)


Installation
------------

Installation instructions are different depending on if you are upgrading or doing a fresh install.

### Fresh Install ###

**ONLY** follow these instructions if you are not upgrading!

1. Open /-/config.php in a plaintext editor and
	create a Lessn username and password then enter your
	database connection details.
	You may also choose other settings such as
	authentication salts and a default home page.

2. For the shortest URLs possible, upload the contents of this
	directory to your domain's root public folder.
	(For short urls when that is not possible, 
	upload the entire directory to your server  
	and rename to a single character. 
	Example: http://doma.in/x/)

3. Visit http://doma.in/-/ log in & start using!
	Be sure to grab the bookmarklets. (The required database table is created 
	automatically the first time you visit Lessn).

**NOTE:** If your Lessn'd urls aren't working you probably didn't
	upload the .htaccess file. Enable "Show invisible files" 
	in your FTP application. It's also possible that your host doesn't like
	the <IfModule>; try taking it out (this happens on 1and1).

### Upgrading ###

If you are upgrading from a previous version of Lessn or ButteredURLs:

#### Upgrading from Lessn 1.0.0 or 1.0.1

1. Using a tool like PhpMyAdmin or the MySQL CLI change the 
   checksum index type to INDEX (from UNIQUE).
2.	Continue below with "ALL VERSIONS"

#### ALL VERSIONS: Upgrading to ButteredURLs 2.0

1.	Note some old redirections so you can manually check they still work after upgrading (they should, but hey, it's important).
1.	Manually merge your old configs into the new config file.
	There will be new options you will want to make
	decisions about.
1.	Upload all lessn/BU files, excluding config.php, or making sure to use the new one.
1.	Go to http://doma.in/x/install.php?start=N where 
	N is 2 if upgrading from Lessen 1.0,or    
	N is 4 if upgrading from ButteredURLs 1.1.
1.	Test some old known working redirections
1.	Delete install.php.

<!-- Upgrading to LessnMore 2.1+ from ≤ 2.0 should start at install.php?start=4. -->

**Congratulations.** You are running the latest version of Lessn More.
