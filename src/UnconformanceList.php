<?php
/**
 * Squille Cave (https://github.com/jairhumberto/Cave)
 *
 * MIT License
 *
 * Copyright (c) 2018 jairhumberto
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Squille\Cave;

class UnconformanceList extends \DOMDocument
{
    protected $itens;
    protected $initmess;

    public function __construct()
    {
        $this->itens = array();
    }

    public function length()
    {
        return count($this->itens);
    }

    public function item($index)
    {
        return $this->itens[$index];
    }

    public function addItem(Unconformance $item)
    {
        $this->itens[] = $item;
    }

    public function getItens()
    {
        return $this->itens;
    }

    public function initMessage()
    {
        $this->loadXML("<?xml version='1.0' encoding='ISO-8859-1'?><errors></errors>");
        $this->initmess = true;
    }

    public function addMessage($message)
    {
        if ($this->initmess) {
            $error = $this->createElement("error");
            $messagenode = $this->createElement("message");
            $messagenode->appendChild($this->createTextNode($message));
            $error->appendChild($messagenode);
            $error->appendChild($this->createElement("solutions"));
            $this->firstChild->appendChild($error);
            return $this->firstChild->childNodes->length - 1;
        }
    }

    public function addSolution($solution, $errorid)
    {
        if ($this->initmess) {
            $solutionnode = $this->createElement("solution");
            $solutionnode->appendChild($this->createTextNode($solution));
            $this->firstChild->childNodes->item($errorid)->childNodes->item(1)->appendChild($solutionnode);
        }
    }
}
