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
require_once DOKU_INC.'lib/plugins/canvas/canvas.php';

class syntax_plugin_canvas_rgraph extends syntax_plugin_canvas_canvas {

    protected $entry_pattern = '<rgraph.*?>(?=.*?</rgraph>)';
    protected $exit_pattern  = '</rgraph>';

    /* ---------------------------------------------------------
     * build html of canvas
     *
     * @param $cid   (string) canvas id
     * @param $ctype (string) canvas type
     * @param $opts  (array)  canvas options (width, height, ...)
     * ---------------------------------------------------------
     */
    protected function _htmlCanvas($cid, $ctype, $opts) {

        // check whether canvas id is given?
        if (empty($cid)) return false;

        // set default canvas size
        if (!array_key_exists('width', $opts)) $opts['width']  = '600px';
        if (!array_key_exists('height',$opts)) $opts['height'] = '300px';

        // prepare plot container
        switch ($ctype) {
            case "rgraph":
                // see http://www.rgraph.net/license
                // RGraph can be used free-of-charge by both commercial and non-commercial 
                // entities (eg business, personal, charity, educational etc) on either 
                // internal or external websites or in software that they make under the terms 
                // of the Creative Commons Attribution 3.0 This means that you may use RGraph 
                // for both commercial and non-commercial purposes as long as you link back to 
                // this website (eg underneath the chart).
                $html.= '<canvas class="canvasbox"';
                $html.= ' id="'.$cid.'"';
                $html.= ' width="'.substr($opts['width'],0,-2).'"';
                $html.= ' height="'.substr($opts['height'],0,-2).'"';
                $html.= '>'.'[No canvas support]'.'</canvas>'.NL;
                $html.= '<div class="rgraph-license-note"';
                $html.= ' style="width: '.$opts['width'].'">';
                $html.= '<a href="http://www.rgraph.net/" title="Powered by RGraph">Powered by RGraph</a>';
                $html.= '</div>'.NL;
                break;
        }
        return $html;
    }

}
