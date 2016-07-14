<?php
/**
 * DokuWiki Syntax Plugin Canvas rgraph
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Sahara Satoshi <sahara.satoshi@gmail.com>
 *
 * SYNTAX:
 *        <rgraph chartid width,height>
 *         ... javascript ...
 *        </rgraph>
 */
require_once DOKU_PLUGIN.'canvas/syntax/canvas.php';

class syntax_plugin_canvas_rgraph extends syntax_plugin_canvas_canvas {

    protected $entry_pattern = '<rgraph\b.*?>(?=.*?</rgraph>)';
    protected $exit_pattern  = '</rgraph>';

}
