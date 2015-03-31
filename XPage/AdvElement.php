<?php


/**
 * AdvElement provides additional simpleXML actions related to HTML(5).
 * AdvElement builds on the extension created for AdvAdmin - advAdmin_simpleXML 
 * Taken from advAdmin 1.1
 *
 * @author lordmatt
 */
class AdvElement extends SimpleXMLElement {

    /**
     * Adds the child to the start of the list. Enjoy.
     * 
     * See also: http://stackoverflow.com/questions/2092012/simplexml-how-to-prepend-a-child-in-a-node
     * 
     * @param string $name
     * @param string $value
     * @return AdvElement (SimpleXMLElement)
     */
    public function prependChild($name, $value='')
    {
        $dom = dom_import_simplexml($this);

        $new = $dom->insertBefore(
            $dom->ownerDocument->createElement($name, $value),
            $dom->firstChild
        );

        return simplexml_import_dom($new, get_class($this));
    }
    
    /**
     * Imports an XML element from elsewhere and adds it to the tree using DOM.
     * 
     * See also: http://stackoverflow.com/questions/3418019/simplexml-append-one-tree-to-another
     * 
     * @param simpleXML $child
     * @return AdvElement
     */
    public function importChild($child){
        // in case some idiot gives us a string
        if(is_string($child)){
            $this->addChild($child);
        }
        
        $domBit = dom_import_simplexml($child);
        if($this->getName()==''){
            // hustan we have a problem
            return false;
        }
        $Dself   = dom_import_simplexml($this);    
        
        $domBit = $Dself->ownerDocument->importNode($domBit, TRUE);
        $Dself->appendChild($domBit);
        
        return simplexml_import_dom($Dself, get_class($this));

    }
    

    /** 
     * Copy the given SimpleXMLElement into this one itteratively 
     * FROM: http://us2.php.net//manual/en/simplexmlelement.addchild.php
     * @param SimpleXMLElement $append 
     */ 
    public function appendXML($append) 
    { 
        if ($append) { 
            if (strlen(trim((string) $append))==0) { 
                $xml = $this->addChild($append->getName()); 
                foreach($append->children() as $child) { 
                    $xml->appendXML($child); 
                } 
            } else { 
                $xml = $this->addChild($append->getName(), (string) $append); 
            } 
            foreach($append->attributes() as $n => $v) { 
                $xml->addAttribute($n, $v); 
            } 
        } 
    } 
    
    

    
    
    
    
}