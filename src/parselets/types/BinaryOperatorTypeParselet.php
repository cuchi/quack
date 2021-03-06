<?php
/**
 * Quack Compiler and toolkit
 * Copyright (C) 2016 Marcelo Camargo <marcelocamargo@linuxmail.org> and
 * CONTRIBUTORS.
 *
 * This file is part of Quack.
 *
 * Quack is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Quack is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Quack.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace QuackCompiler\Parselets\Types;

use \QuackCompiler\Ast\Types\OperatorType;
use \QuackCompiler\Parser\TypeParser;
use \QuackCompiler\Lexer\Token;
use \QuackCompiler\Parselets\InfixParselet;

class BinaryOperatorTypeParselet implements InfixParselet
{
    public $precedence;
    public $is_right;

    public function __construct($precedence, $is_right)
    {
        $this->precedence = $precedence;
        $this->is_right = $is_right;
    }

    public function parse($parser, $left, Token $token)
    {
        $right = $parser->_type($this->precedence - (int) $this->is_right);
        return new OperatorType($left, $token->getTag(), $right);
    }

    public function getPrecedence()
    {
        return $this->precedence;
    }
}
