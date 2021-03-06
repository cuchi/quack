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
namespace QuackCompiler\Ast\Types;

use \QuackCompiler\Types\NativeQuackType;

class LiteralType extends TypeNode
{
    public $code;

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function __toString()
    {
        $map = [
            NativeQuackType::T_STR    => 'string',
            NativeQuackType::T_NUMBER => 'number',
            NativeQuackType::T_BOOL   => 'boolean',
            NativeQuackType::T_REGEX  => 'regex',
            NativeQuackType::T_BLOCK  => 'block',
            NativeQuackType::T_UNIT   => 'unit',
            NativeQuackType::T_NIL    => 'nil',
            NativeQuackType::T_BYTE   => 'byte',
            NativeQuackType::T_ATOM   => 'atom'
        ];

        return $this->parenthesize(
            array_key_exists($this->code, $map)
                ? $map[$this->code]
                : 'unknown'
        );
    }

    public function check(TypeNode $other)
    {
        if (!($other instanceof LiteralType)) {
            // Fallback for atom check
            return $other instanceof AtomType && NativeQuackType::T_ATOM === $this->code;
        }

        return $this->code === $other->code;
    }
}
