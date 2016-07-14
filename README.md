DokuWiki plugin Canvas
======================

Provide HTML5 Canvas functionality for DokuWiki page.
The content of the canvas element of Wiki text is JavaScript code to draw on the canvas, which is different from HTML manner.

Dependency
----------
In order to check whether embedding HTML is allowed in the Wiki text, this plugin depends on other plugin component of Inline JavaScript ([inlinejs](https://www.dokuwiki.org/plugin:inlinejs)) plugin, which need to be enabled in your DokuWiki site.


Syntax
------

    <canvas cid width,height>
    ... javascript to draw on canvas identified by cid...
    </canvas>

----
Licensed under the GNU Public License (GPL) version 2

More information is available:
  * https://www.dokuwiki.org/plugin:canvas

(c) 2014-2016 Satoshi Sahara \<sahara.satoshi@gmail.com>
 