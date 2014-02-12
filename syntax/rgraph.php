<?php
/**
 * DokuWiki Syntax Plugin Canvas rgraph
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Sahara Satoshi <sahara.satoshi@gmail.com>
 *
 * REMARK: depends on function renderInlineJsHtml()
 *         of Helper component of InlineJS plugin
 * SYNTAX:
 *        <rgraph chartid width,height>
 *         ... javascript ...
 *        </rgraph>
 */
require_once DOKU_INC.'lib/plugins/canvas/syntax.php';

class syntax_plugin_canvas_rgraph extends syntax_plugin_canvas {

    protected $entry_pattern = '<rgraph.*?>(?=.*?</rgraph>)';
    protected $exit_pattern  = '</rgraph>';

    function connectTo($mode) {
        $this->Lexer->addEntryPattern($this->entry_pattern, $mode,
            implode('_', array('plugin',$this->getPluginName(),$this->getPluginComponent() ))
        );
    }
    function postConnect() {
        $this->Lexer->addExitPattern($this->exit_pattern,
            implode('_', array('plugin',$this->getPluginName(),$this->getPluginComponent() ))
        );
    }

}