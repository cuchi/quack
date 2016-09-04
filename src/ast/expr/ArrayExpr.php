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
namespace QuackCompiler\Ast\Expr;

use \QuackCompiler\Parser\Parser;
use \QuackCompiler\Scope\ScopeError;
use \QuackCompiler\Types\NativeQuackType;
use \QuackCompiler\Types\Type;

class ArrayExpr extends Expr
{
    public $items;
    private $memoized_type;

    public function __construct($items)
    {
        $this->items = $items;
        $this->memoized_type = null;
    }

    public function format(Parser $parser)
    {
        $source = '{';
        if (sizeof($this->items) > 0) {
            $source .= ' ';
            $source .= implode('; ',
                array_map(function ($item) use ($parser) {
                    return $item->format($parser);
                }, $this->items)
            );
            $source .= ' ';
        }
        $source .= '}';

        return $this->parenthesize($source);
    }

    public function injectScope(&$parent_scope)
    {
        foreach ($this->items as $item) {
            $item->injectScope($parent_scope);
        }
    }

    public function getType()
    {
        if (null !== $this->memoized_type) {
            return $this->memoized_type;
        }

        $newtype = new Type(NativeQuackType::T_LIST);
        $list_size = sizeof($this->items);

        if (0 === $list_size) {
            $newtype->subtype = new Type(NativeQuackType::T_LAZY);
            return $newtype;
        }

        $newtype->subtype = $this->items[0]->getType();

        foreach (array_slice($this->items, 1) as $item) {
            $type = $item->getType();

            if (!$type->isCompatibleWith($newtype->subtype)) {
                throw new ScopeError(['message' => "Cannot add element of type `{$type}' to `{$newtype}'"]);
            }
        }

        // Apply Liskov substitution principle
        if ($newtype->hasSubtype()) {
            $out = new \stdClass;
            Type::getDeepestSubtype($newtype, $out);

            $subtype_list = array_map(function ($item) {
                $ref = new \stdClass;
                Type::getDeepestSubtype($item->getType(), $ref);
                return $ref->{'*'};
            }, $this->items);

            $base_type = Type::getBaseType($subtype_list);

            $out->{'*'}->importFrom($base_type);
        }

        $this->memoized_type = &$newtype;
        return $newtype;
    }
}
