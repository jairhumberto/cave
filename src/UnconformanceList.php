<?php
/**
 * Squille Cave (https://github.com/jairhumberto/Cave)
 * 
 * @copyright Copyright (c) 2018 Squille
 * @license   this software is distributed under MIT license, see the
 *            LICENSE file.
 */

namespace Squille\Cave;

class UnconformanceList extends DOMDocument
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
