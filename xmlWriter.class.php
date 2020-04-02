class xmlWritter{
    private static $dom;

    public static function init(){
        if(self::$dom){
            return;
        }
        self::$dom=new DomDocument('1.0', 'utf-8');
        self::$dom->formatOutput = true;
    }
    private static function reset(){
        self::$dom = null;
    }

    /*
     * attr:att1@val2|attr2@val2
     * */
    private static function _newElement($key, $val=''){
        if(strpos($key, ':')!==false){
            $params = explode(':', $key);
            $key = $params[0];
            $dom = self::$dom->createElement($key, $val);
            $attrs = explode('|', $params[1]);
            foreach($attrs as $attr){
                if(strpos($attr, '@')===false){
                    continue;
                }
                list($attr, $val) = explode('@', $attr);
                $dom->setAttribute($attr, $val);
            }
            return $dom;
        } else {
            //var_dump($val);
            try {
                return self::$dom->createElement($key, $val);
            }catch (Exception $e){
                die($key.'/'.var_export($val, true).'/'.$e->getMessage());
            }
        }
    }

    public static function formatXML($data, &$sub=null){
        self::init();
        $rootElement = null;
        $subElements = [];
        $counter  = 0;
        foreach($data as $key=>$val){
            if($key==='iteration') {
                foreach($val['items'] as $item){
                    $newDom = self::_newElement($val['key']);
                    self::formatXML($item, $newDom);
                    if(!$rootElement){
                        if(!$sub){
                            $rootElement = $newDom;
                        } else {
                            $rootElement = &$sub;
                        }
                    }
                    if($rootElement !== $newDom){
                        $rootElement->appendChild($newDom);
                    }
                }
            } else {
                if (is_array($val)) {
                    $newDom = self::_newElement($key);
                    self::formatXML($val, $newDom);
                } else {
                    $newDom = self::_newElement($key, $val);
                }

                if(!$rootElement){
                    if(!$sub){
                        $rootElement = $newDom;
                    } else {
                        $rootElement = &$sub;
                    }
                }
                if($rootElement !== $newDom){
                    $rootElement->appendChild($newDom);
                }
            }


        }
        if(!$sub){
            self::$dom->appendChild($rootElement);
        } else {
            //self::$dom->appendChild($sub);
            //$sub->appendChild($rootElement);
        }
    }

    public static function output(){
        header("Content-type: text/xml");
        echo self::$dom->saveXML();
        die();
    }
}