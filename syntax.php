<?php
/**
 * DokuWiki Canvas Plugin (Syntax component)
 *
 *  html5 canvas functionality
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Sahara Satoshi <sahara.satoshi@gmail.com>
 *
 * REMARK: depends on function renderInlineJsHtml()
 *         of Helper component of InlineJS plugin
 * SYNTAX:
 *        <canvas:[rgraph|jqplot] chartid width,height>
 *         ... javascript ...
 *        </canvas>
 *        <rgraph chartid width,height > ... </rgraph>
 *        <jqplot chartid width,height > ... </jqplot>
 *
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once DOKU_PLUGIN.'syntax.php';

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_canvas extends DokuWiki_Syntax_Plugin {

    function getType()  { return 'substition'; }
    //function getPType() { return 'block'; }
    function getSort()  { return 160; }
    function connectTo($mode) {
      $this->Lexer->addSpecialPattern('<canvas.*?</canvas>',$mode,'plugin_canvas');
      $this->Lexer->addSpecialPattern('<jqplot .*?</jqplot>',$mode,'plugin_canvas');
      $this->Lexer->addSpecialPattern('<rgraph .*?</rgraph>',$mode,'plugin_canvas');
    }

 /**
  * handle syntax
  */
    public function handle($match, $state, $pos, &$handler){

        $opts = array( // set default
                     'canvastype' => 'rgraph',
                     'chartid' => '',
                     'width'   => '300px',
                     'height'  => '150px',
                     );

        list($params, $script) = explode('>',$match,2);

        // script part
        $p = strrpos($script, '<'); // get position of beginning of ending markup
        $script = substr($script, 0, $p-strlen($script)); // drop ending markup

        // param part
        // split the phrase by any number of space characters,
        // which include " ", \r, \t, \n and \f
        $tokens = preg_split('/\s+/', $params);

        //what markup used?
        $markup = array_shift($tokens);
        if (strpos($markup,':') !== false) { // '<canvas:canvastype'
            list($markup, $canvastype) = explode(':',$markup,2);
        } else { // '<jqplot' or '<rgraph'
            $canvastype = substr($markup,1); // drop '<'
        }
        $opts['canvastype'] = strtolower($canvastype);

        foreach ($tokens as $token) {

            // get width and height of iframe
            $matches=array();
            if (preg_match('/(\d+(px)?)\s*([,xX]\s*(\d+(px)?))?/',$token,$matches)){
                if ($matches[4]) {
                    // width and height was given
                    $opts['width'] = $matches[1];
                    if (!$matches[2]) $opts['width'].= 'px';
                    $opts['height'] = $matches[4];
                    if (!$matches[5]) $opts['height'].= 'px';
                    continue;
                } elseif ($matches[2]) {
                    // only height was given
                    $opts['height'] = $matches[1];
                    if (!$matches[2]) $opts['height'].= 'px';
                    continue;
                }
            }
            // get chartid, first match prioritized
            //restrict token characters to prevent any malicious chartid
            if (preg_match('/[^A-Za-z0-9_-]/',$token)) continue;
            if (empty($opts['chartid'])) $opts['chartid'] = $token;
        }
        return array($state, $opts, $script);
    }

 /**
  * Render
  */
    public function render($mode, &$renderer, $data) {

        if ($mode != 'xhtml') return false;

        list($state, $opts, $script) = $data;

        // check whether chartid defined?
        if (empty($opts['chartid'])) return false;

        // prepare plot container
        switch ($opts['canvastype']) {
            case "jqplot":
                // see its project page https://bitbucket.org/cleonello/jqplot/overview 
                // jqPlot is currently available for use in all personal or commercial projects 
                // under both the MIT and GPL version 2.0 licenses. This means that you can 
                // choose the license that best suits your project and use it accordingly. 
                $html.= '<div class="jqplot-target"';
                $html.= ' id="'.$opts['chartid'].'"';
                $html.= ' style="width: '.$opts['width'].'; height: '.$opts['height'].';"> ';
                $html.= '</div>'.NL;
                $html.= '<div class="jqplot-license-note"';
                $html.= ' style="width: '.$opts['width'].'">';
                $html.= '<a href="http://www.jqplot.com/" title="Powered by jqPlot">Powered by jQplot</a>';
                $html.= '</div>'.NL;
                break;
            case "rgraph":
                // see http://www.rgraph.net/license
                // RGraph can be used free-of-charge by both commercial and non-commercial 
                // entities (eg business, personal, charity, educational etc) on either 
                // internal or external websites or in software that they make under the terms 
                // of the Creative Commons Attribution 3.0 This means that you may use RGraph 
                // for both commercial and non-commercial purposes as long as you link back to 
                // this website (eg underneath the chart).
                $html.= '<canvas class="canvasbox"';
                $html.= ' id="'.$opts['chartid'].'"';
                $html.= ' width="'.substr($opts['width'],0,-2).'"';
                $html.= ' height="'.substr($opts['height'],0,-2).'"';
                $html.= '>'.'[No canvas support]'.'</canvas>'.NL;
                $html.= '<div class="rgraph-license-note"';
                $html.= ' style="width: '.$opts['width'].'">';
                $html.= '<a href="http://www.rgraph.net/" title="Powered by RGraph">Powered by RGraph</a>';
                $html.= '</div>'.NL;
                break;
            default:
                $html.= '<canvas class="canvasbox"';
                $html.= ' id="'.$opts['chartid'].'"';
                $html.= ' width="'.substr($opts['width'],0,-2).'"';
                $html.= ' height="'.substr($opts['height'],0,-2).'"';
                $html.= '>'.'[No canvas support]'.'</canvas>'.NL;
                break;
        }
        $renderer->doc.=$html;

        // prepare inline javascript using helper Component of InlineJS plugin
        if (empty($script)) return true;
        if(!plugin_isdisabled('inlinejs')) {
            $embedder = plugin_load('helper','inlinejs');
            $embedder->renderInlineJsHtml($renderer,$script);
        }
        return true;
    }
}