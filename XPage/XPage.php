<?php
/**
 * Description of XPage
 *
 * @author lordmatt
 */

require_once("AdvElement.php");
require_once("AEWrapper.php");

class XPage {

    protected $debugMode = FALSE;
    protected $debug_messages = array();
    protected $logs = array();
    
    // the XML object reprisenting the page
    protected $page;
    
    // A list of pointers
    protected $namedZones = array();
    protected $meta = array();
    protected $keywords = array();

    /**
     * Takes a HTML document (as string) or a path to a XHTML document
     * @param string $page
     * @param boolean $load_file
     */
    public function __construct($page,$load_file=false) {
        // AdvElement
        $doc = new DOMDocument();
        //$doc->strictErrorChecking = FALSE;
        libxml_use_internal_errors(true); // deal with less than valid HTML
        if($load_file){
            $doc->load($page);
        }else{
            // Encoding problem quick fixes
            $page = str_replace("\r\n", "\n", $page); // windows -> unix
            $page = str_replace("\r", "\n", $page);   // remaining -> unix

            if (!$doc->loadHTML(mb_convert_encoding($page,'UTF-8'))) {
                foreach (libxml_get_errors() as $error) {
                    // handle errors here
                    $this->log_error($this->display_xml_error($error));
                }
                libxml_clear_errors();
            }
        }
        $obj = simplexml_import_dom($doc,'AdvElement');
        if( !is_object($obj)){
            // Error from generated HTML
            $this->log_error("Page contained possible bad HTML");
            return FALSE;
        }        
        $this->page = new AEWrapper($obj,$this);
    }
    
    /**
     * attributes should be in key value pairs in $value as an array or value 
     * should be a string.
     * @param string $what
     * @param mixed $value
     * @return \XPage 
     */
    public function &setMeta($what,$value){
        $method_name = "_meta_{$what}";
        if(method_exists($this, $method_name)){
            $this->$method_name($value);
        }else{
            if(is_array($values)){
                $this->meta[]=array('type'=>$what,'values'=>$value);
            }else{
                $this->meta[]=array('type'=>$what,'values'=>array('tcontent'=>$value));
            }
        }
        return $this;
    }
    
    /**
     * Function for specifying meta tag data where the attributes are name and 
     * content only.
     * @param string $name
     * @param string $content
     * @return \XPage 
     */
    public function &setMetaName($name,$content){
        $this->meta[]=array('type'=>'meta','values'=>array('name'=>$name,'content'=>$content));
        return $this;
    }
    /**
     * Sets the title content
     * @param string $newTitle
     * @return \XPage 
     */
    public function &updateTitle($newTitle){
        $this->meta['title']=array('type'=>'title','values'=>array('tcontent'=>$newTitle));
        return $this;
    }
    
    public function &addKeyword($word){
        if($this->keywords===false){
            return $this;
        }
        $this->keywords[$word]=true;
        return $this;
    }
    
    public function &dropKeyword($word){
        if($this->keywords===false){
            return $this;
        }
        unset($this->keywords[$word]);
        return $this;
    }
    
    public function getKeywords(){
        return array_keys($this->keywords);
    }
    
    protected function _meta_title($value){
        $this->updateTitle($value);
    }
    
    protected function _meta_set_meta($pointless=null){
        $xml = $this->page->actual_simpleXML_obj_please();
        foreach($this->meta as $meta){
            $newMeta = null;
            if(isset($meta['values']['tcontent'])){
                $newMeta = $xml->head->addChild($meta['type'],$meta['values']['tcontent']);
                unset($meta['values']['tcontent']);
            }else{
                $newMeta = $xml->head->addChild($meta['type']);
            }
            if(count($meta['values'])>0){
                foreach($meta['values'] as $a=>$b){
                    $newMeta->addAttribute($a,$b);
                }
            }
        }
        unset($this->meta);
    }
    
    protected function keywords_to_meta(){
        if($this->keywords===false){
            return false;
        }
        $metaStr = implode(',',$this->keywords);
        $this->meta[] = array('type'=>'meta','values'=>array('name'=>'keywords','content'=>$metaStr));
        $this->keywords=false;
        return true;
    }
    
    protected function _meta_css($value){
        $values=array('rel'=>'stylesheet','type'=>'text/css','href'=>$value);
        if(is_array($value)){
            if(!isset($value['href'])){
                return false;
            }
            foreach($value as $a=>$b){
                $values[$a]=$b;
            }
        }
        $this->meta[]=array('type'=>'link','values'=>$values);
    }
    
    public function register_as($name,$what){
        $this->setZone($what,$name);
    }
    
    public function register_with_xpath($xpath,$name){
        $zones = $this->page->actual_simpleXML_obj_please()->xpath($xpath);
        foreach($zones as $zone){
            $this->setZone($zone, $name);
        }
    }
    
    // end of the line
    public function &go_back_up_by_one(){
        return $this;
    }
    
    // nope this is the XPage factory, mate
    public function this_is_XML(){
        return false;
    }
    
    public function setZone($pointer,$name){
        if(!isset($this->namedZones[$name])){
            $this->namedZones[$name]=$pointer;
        }elseif(!is_array($this->namedZones[$name])){
            $temp = $this->namedZones[$name];
            $this->namedZones[$name] = array();
            $this->namedZones[$name][] = $temp;
            $this->namedZones[$name][] = $pointer; 
        }else{
            $this->namedZones[$name][] = $pointer; 
        }
    }
    
    public function &getZone($name){
        return $this->namedZones[$name];
    }
    
    public function page(){
        return $this->page;
    }
    /*
    public function set_char_encoding(){
        if(is_object($this->page->head->meta[0])){
            $this->page->head->meta[0]->attributes()->content="text/html; charset=UTF-8";
        }
    }
    */

    public function asHTML($forceDoctype=true){
        $this->keywords_to_meta();
        $this->_meta_set_meta(null);
        $doc = dom_import_simplexml($this->page->actual_simpleXML_obj_please());
        $doc->ownerDocument->encoding = 'iso-8859-1';
        $doc->ownerDocument->preserveWhiteSpace = false;
        $doc->ownerDocument->formatOutput = true;        
        $output = $doc->ownerDocument->saveXML($doc);
        if($forceDoctype){
            $output = "<!DOCTYPE html>\n" . $output;     
        }
        return $output;
    }
    
    public function get_error_log(){
        return $this->logs;
    }
    
    public function get_last_error(){
        $i = count($this->logs)-1;
        if($i < 1){
            return '';
        }
        $msg = "{$this->logs[$i]['timestamp']}: {$this->logs[$i]['location']} says {$this->logs[$i]['message']}";
        return $msg;
    }
    
    protected function log_error($error){
        $this->debug($error);
        $trace  = debug_backtrace();
        $caller = $trace[1];
        $trigger = '';
        if (isset($caller['class'])){
            $trigger .= "{$caller['class']}::";   
        }
        $trigger .= "{$caller['function']}";
        $report = array();
        $report['timestamp'] = time();
        $report['location']  = $trigger;
        $report['message']   = $error;
        $this->logs[]=$report;
    }
    
    /**
     * Get and empty the error log
     * @return array
     */
    public function flush_error_log(){
        $error_log = $this->get_error_log();
        $this->logs=array();
        return $error_log;
    }
    
    protected function debug($message,$subcall=false){
        $trace=debug_backtrace();
        $n = 1;
        if($subcall){
            $n++;
        }
        $caller=$trace[$n];
        $trigger = '';
        if (isset($caller['class'])){
            $trigger .= "{$caller['class']}::";   
        }
        $trigger .= "{$caller['function']}";
        if($this->debugMode){
            echo "<p>{$trigger} says <q>{$message}</q></p>";
        }
    }
    
    // a whole bunch of mostly pointless pass through methods
    // really only good for development stuff
    
    public function debug_mode_report(){
        $msg = "debug is " .  ($this->debugMode ? 'ON' : 'OFF');
        return $msg;
    }
    
    public function echo_debug_mode_report(){
        echo $this->debug_mode_report();
        return $this;
    }
    
    public function toggle_debug(){
        $this->debugMode = ($this->debugMode ? FALSE : TRUE);
        return $this;
    }
    
    public function debug_on(){
        $this->debugMode = TRUE;
        return $this;
    }
    
    public function debug_off(){
        $this->debugMode = FALSE;
        return $this;
    }
    
    
}
