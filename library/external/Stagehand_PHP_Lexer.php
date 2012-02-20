<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2009 KUMAKURA Yousuke <kumatch@gmail.com>,
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    stagehand-php-lexer
 * @copyright  2009 KUMAKURA Yousuke <kumatch@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      File available since Release 0.1.0
 */

// {{{ Stagehand_PHP_Lexer

/**
 * A class for PHP lex.
 *
 * @package    stagehand-php-lexer
 * @copyright  2009 KUMAKURA Yousuke <kumatch@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class Stagehand_PHP_Lexer
{
    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access protected
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    private $_position;
    private $_tokens = array();
    private $_docComments = array();
    private $_latestDocComment;

    private $_isDeclarStep;

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ __construct()

    /**
     * Loads a PHP script file.
     *
     * @param string $filename  PHP script filename.
     */
    public function __construct($filename)
    {
        $this->_position = 0;
        $this->_tokens = token_get_all(file_get_contents($filename));
    }

    // }}}
    // {{{ yylex()

    /**
     * Lexs a PHP script.
     *
     * @param object $yylval
     * @return mixed
     */
    public function yylex(&$yylval)
    {
        while (1) {
            $currentPosition = $this->_position;
            $token = @$this->_tokens[$currentPosition];
            ++$this->_position;

            if (!$token) {
                return 0;
            }

            if (!is_array($token)) {
                $yylval = new Stagehand_PHP_Lexer_Token($token, $currentPosition);

                if ($yylval->getValue() === ';') {
                    $this->_isDeclarStep = false;
                }

                return ord($yylval);
            }

            $this->_catchDocComment($token);

            $name = token_name($token[0]);
            if ($this->_isIgnoreToken($name)) {
                continue;
            }

            $yylval = new Stagehand_PHP_Lexer_Token($token[1], $currentPosition);
            if ($name === 'T_DOUBLE_COLON') {
                return Stagehand_PHP_Parser::T_PAAMAYIM_NEKUDOTAYIM;
            }

            return constant("Stagehand_PHP_Parser::{$name}");
        }
    }

    // }}}
    // {{{ getTokens()

    /**
     * Gets tokens.
     *
     * @param integer $startPosition  number of start position
     * @param integer $endPosition    number of end position
     * @return array
     */
    public function getTokens($startPosition = 0, $endPosition = -1)
    {
        if ($endPosition < 0) {
            $endPosition = count($this->_tokens) - 1;
        }

        $tokens = array();
        for ($i = $startPosition; $i <= $endPosition; ++$i) {
            if (isset($this->_tokens[$i])) {
                array_push($tokens, $this->_tokens[$i]);
            }
        }

        return $tokens;
    }

    // }}}
    // {{{ getLatestDocComment()

    /**
     * Gets a latest doc comment.
     *
     * @return string
     */
    public function getLatestDocComment()
    {
        return array_pop($this->_docComments);
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _isIgnoreToken()

    /**
     * Returns whether the token is ignore or not.
     *
     * @param string $tokenName
     * @return array
     */
    private function _isIgnoreToken($tokenName)
    {
        $ignoreList = array('T_OPEN_TAG', 'T_CLOSE_TAG', 'T_WHITESPACE',
                            'T_COMMENT', 'T_DOC_COMMENT', 'T_INLINE_HTML', 
                            );

        if (in_array($tokenName, $ignoreList)) {
            return true;
        }

        return false;
    }

    // }}}
    // {{{ _catchDocDomment()

    /**
     * Catches a document comment.
     *
     * @param string $token    A token.
     * @return array
     */
    private function _catchDocComment($token)
    {
        $name = token_name($token[0]);

        if ($name === 'T_DOC_COMMENT') {
            $this->_latestDocComment = $token[1];
            return;
        }

        if ($this->_isMemberModifier($name)) {
            $this->_isDeclarStep = true;
        }

        if ($name === 'T_CLASS'
            || $name === 'T_INTERFACE'
            || $name === 'T_FUNCTION'
            ) {
            array_push($this->_docComments, $this->_latestDocComment);
            $this->_latestDocComment = null;
            $this->_isDeclarStep = false;
            return;
        }

        if ($this->_isDeclarStep && $name === 'T_VARIABLE') {
            array_push($this->_docComments, $this->_latestDocComment);
            $this->_latestDocComment = null;
            return;
        }
    }

    /**
     * Returns whether the token is member modifier or not.
     *
     * @param string $tokenName
     * @return array
     */
    private function _isMemberModifier($tokenName)
    {
        $memberModifiers = array('T_PUBLIC', 'T_PROTECTED', 'T_PRIVATE',
                                 'T_STATIC', 'T_ABSTRACT', 'T_FINAL',
                                 'T_VAR'
                                 );
        if (in_array($tokenName, $memberModifiers)) {
            return true;
        }

        return false;
    }

    /**#@-*/

    // }}}
}

// }}}

/*
 * Local Variables:
 * mode: php
 * coding: iso-8859-1
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * indent-tabs-mode: nil
 * End:
 */
