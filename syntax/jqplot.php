<?php
/**
 * DokuWiki Syntax Plugin Canvas jqplot
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Sahara Satoshi <sahara.satoshi@gmail.com>
 *
 * SYNTAX:
 *        <jqplot chartid width,height>
 *         ... javascript ...
 *        </jqplot>
 */
require_once DOKU_PLUGIN.'canvas/syntax/canvas.php';

class syntax_plugin_canvas_jqplot extends syntax_plugin_canvas_canvas {

    protected $entry_pattern = '<jqplot.*?>(?=.*?</jqplot>)';
    protected $exit_pattern  = '</jqplot>';

}
