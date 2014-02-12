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
 *        <canvas[:rgraph|:jqplot] chartid width,height>
 *         ... javascript ...
 *        </canvas>
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

    protected $entry_pattern = '<canvas.*?>(?=.*?</canvas>)';
    protected $exit_pattern  = '</canvas>';

    function getType()  { return 'protected'; }
    //function getPType() { return 'block'; }
    function getSort()  { return 160; }
    function connectTo($mode) {
      $this->Lexer->addEntryPattern($this->entry_pattern, $mode,
          implode('_', array('plugin',$this->getPluginName(),$this->getPluginComponent()))
      );
      $this->Lexer->addExitPattern($this->exit_pattern,
          implode('_', array('plugin',$this->getPluginName(),$this->getPluginComponent()))
      );
    }

 /**
  * handle syntax
  */
    public function handle($match, $state, $pos, &$handler){

        global $conf;
        // check whether inlinejs plugin exists
        if (plugin_isdisabled('inlinejs')) return false;
        $inlinejs =& plugin_load('syntax', 'inlinejs');
        if ($inlinejs->getConf('follow_htmlok') && !$conf['htmlok']) return false;

        switch ($state) {
            case DOKU_LEXER_ENTER:
                // at least cid one delimiter required to have unique canvas id.
                if (strpos($match,' ') === false) return false;
                return array($state, $match);

            case DOKU_LEXER_UNMATCHED:
                // javascript code
                return array($state, $match);

            case DOKU_LEXER_EXIT:
                return array($state, '');
        }
        return false;
    }

 /**
  * Render
  */
    public function render($mode, &$renderer, $indata) {

        if (empty($indata)) return false;
        list($state, $data) = $indata;

        if ($mode != 'xhtml') return false;

        switch ($state) {
            case DOKU_LEXER_ENTER:
                // get canvas type and id
                if ( substr($data, 1, 7) == 'canvas:') {
                    $match = trim(substr($data, 8, -1));
                } else {
                    $match = trim(substr($data, 1, -1));
                }
                list($ctype, $cid, $cparam) = explode($match, ' ', 3);
                $param = $this->getArguments($cparam, 'width');

                // prepare canvas
                $renderer->doc.= $this->_htmlCanvas($cid, $ctype, $param);
                // open script tag to output embedded javascript
                $renderer->doc.= '<script type="text/javascript">'.NL.'/*<![CDATA[*/';
                break;

            case DOKU_LEXER_UNMATCHED:
                // output javascript
                $renderer->doc.= $data;
                break;

            case DOKU_LEXER_EXIT:
                // close script tag
                $renderer->doc.=  '/*!]]>*/'.NL.'</script>'.NL;
                break;
        }
        return true;
    }


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
        if (!array_key_exists('width', $opts)) $opts['width']  = '300px';
        if (!array_key_exists('height',$opts)) $opts['height'] = '150px';

        // prepare plot container
        switch ($ctype) {
            case "jqplot":
                // see its project page https://bitbucket.org/cleonello/jqplot/overview 
                // jqPlot is currently available for use in all personal or commercial projects 
                // under both the MIT and GPL version 2.0 licenses. This means that you can 
                // choose the license that best suits your project and use it accordingly. 
                $html.= '<div class="jqplot-target"';
                $html.= ' id="'.$cid.'"';
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
                $html.= ' id="'.$cid.'"';
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
                $html.= ' id="'.$cid.'"';
                $html.= ' width="'.substr($opts['width'],0,-2).'"';
                $html.= ' height="'.substr($opts['height'],0,-2).'"';
                $html.= '>'.'[No canvas support]'.'</canvas>'.NL;
                break;
        }
        return $html;
    }


    /* ---------------------------------------------------------
     * get each named/non-named arguments as array variable
     *
     * Named arguments is to be given as key="value" (quoted).
     * Non-named arguments is assumed as boolean.
     *
     * @param $args (string) arguments
     * @param $singlekey (string) key name if single numeric value was given
     * @return (array) parsed arguments in $arg['key']=value
     * ---------------------------------------------------------
     */
    protected function getArguments($args='', $singlekey='height') {
        $arg = array();
        // get named arguments (key="value"), ex: width="100"
        // value must be quoted in argument string.
        $val = "([\"'`])(?:[^\\\\\"'`]|\\\\.)*\g{-1}";
        $pattern = "/(\w+)\s*=\s*($val)/";
        preg_match_all($pattern, $args, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $arg[$match[1]] = substr($match[2], 1, -1); // drop quates from value string
            $args = str_replace($match[0], '', $args); // remove parsed substring
        }

        // get named numeric value argument, ex width=100
        // numeric value may not be quoted in argument string.
        $val = '\d+';
        $pattern = "/(\w+)\s*=\s*($val)/";
        preg_match_all($pattern, $args, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $arg[$match[1]] = (int)$match[2];
            $args = str_replace($match[0], '', $args); // remove parsed substring
        }

        // get width and/or height, specified as non-named arguments
        $unit = 'px';
        $pattern = '/(?:^| )(\d+(%|em|pt|px)?)\s*([,xX]?(\d+(%|em|pt|px)?))?(?: |$)/';
        if (preg_match($pattern, $args, $matches)) {
            if ($matches[4]) {
                // width and height with unit was given
                $arg['width'] = $matches[1];
                if (!$matches[2]) $arg['width'].= $unit;
                $arg['height'] = $matches[4];
                if (!$matches[5]) $arg['height'].= $unit;
            } elseif ($matches[2]) {
                // width or height(=assumed as default) with unit was given
                // preferred key name given as second parameter of this function
                $arg[$singlekey] = $matches[1];
                if (!$matches[2]) $arg[$singlekey].= $unit;
            } elseif ($matches[1]) {
                // numeric token is assumed as width or height
                $arg[$singlekey] = $matches[1].$unit;
            }
            $args = str_replace($matches[0], '', $args); // remove parsed substring
        }

        // get flags or non-named arguments, ex: showdate, no-showfooter
        $tokens = preg_split('/\s+/', $args);
        foreach ($tokens as $token) {
            if (preg_match('/^(?:!|not?)(.+)/',$token, $matches)) {
                // denyed/negative prefixed token
                $arg[$matches[1]] = false;
            } elseif (preg_match('/^[A-Za-z]/',$token)) {
                $arg[$token] = true;
            }
        }
        return $arg;
    }

}