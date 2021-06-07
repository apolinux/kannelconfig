<?php

namespace KannelConfig ;

class Config {

  const COMMENT = 1 ;
  const ITEM = 2 ;
  const GROUP = 3 ;
  const INCLUDE = 4 ;

  private $filename_base ;

  private $multiple_groups = [
    'smsc' ,
    'sms-service',
    'sendsms-user',
    'smpp-tlv',
  ] ;

  private static $parse_first = true ;

  public static function parseStringStatic($data){
    $config = new Config ;
    return $config->parseString($data);
  }

  public function parseString($data){
    $file_descriptor=fopen('php://temp/maxmemory:10M','rw') ;
    $resp=fwrite($file_descriptor,$data);
    if(false===$resp){
      throw new KannelConfigException("Can't write to memory");
    }
    fseek($file_descriptor,0) ;

    return $this->parseFd($file_descriptor);
  }

  public static function parseStatic($filename){
    $config = new Config ;
    return $config->parse($filename);
  }

  public function parse($filename){
    if(self::$parse_first){
      $this->filename_base = $filename ;
      $first = false ;
    }
    $this->checkFile($filename);

    $fp = fopen($filename,'r');
    $out = $this->parseFd($fp);

    fclose($fp);

    return $out ;
  }

  private function parseFd($fp){
    $data =[];
    $current_group = null;

    $cont_group =[];
    while(! feof($fp)){
      $line = trim(fgets($fp, 4096));
      list($type, $item, $value) = $this->parseLine($line);

      switch($type){
        case self::GROUP:
         $current_group=$value ;
         if($this->itemHasGroup($current_group)){
           if(! isset($cont_group[$current_group])) {
             $cont_group[$current_group] = 0 ;
           }else{
             $cont_group[$current_group] ++ ;
           }
           $data[$current_group][ $cont_group[$current_group] ] = [];
         }else{
           $data[$current_group] = [];
         }
        break;

        case self::ITEM:
          if($this->itemHasGroup($current_group)){
            // contador del grupo, ej, de smsc
            $cont_groupx=$cont_group[$current_group];
            $data[$current_group][$cont_groupx][$item] = $value ;
          }else{
            $data[$current_group][$item] = $value ;
          }
        break ;

        case self::INCLUDE:
          try{
            $filealt = $this->parseInclude($value) ;
            $dataf = $this->parse($filealt);
            $data = $this->mergeData($data, $dataf);
          }catch(FilenameException $e){
            // ignore error
            //echo 'error:' . $e->getMessage() ;
          }
        break ;

        case self::COMMENT :
      }
    }

    return $data ;
  }

  private function itemHasGroup($item){
    return in_array($item, $this->multiple_groups);
  }

  private function mergeData($array1, $array2){
    $out = [];
    foreach($array1 as $item1 => $value1){
      if(! $this->itemHasGroup($item1) ){
        $out[$item1] = $value1 ;
      }else{
        // $value1 es un array indexado
        // buscar en el otro array
        if(array_key_exists($item1, $array2)){
          $out[$item1] = array_merge( $array2[$item1] , $value1);
          unset($array2[$item1]);
        }else{
          $out[$item1] = $value1 ;
        }
      }
    }
    // add $array2 modified
    $out = array_merge($array2,$out) ;
    return $out ;
  }

  /*private function getBaseDir($filename){
    if($filename[0] == '/'){
      return $filename ;
    }

    $basedir = dirname($basefile) ;

    return $basedir .'/' . $filename ;
  }*/
  /**
   * return filename with a directory base if necessary
   */
  private function parseInclude($filename){
    if($filename[0] == '/'){
      return $filename ;
    }

    $basedir = dirname($this->filename_base) ;

    return $basedir .'/' . $filename ;
  }

  private function parseLine($line){
    if(! isset($line[0]) || $line[0] == '#'){
      return [self::COMMENT,null,null] ;
    }

    /*if('include ' === substr($line,0,8)){
      list(,$file) = explode(' ');
      $file=trim(trim($file),'"\'');
      return [self::INCLUDE, null, $file];
    }*/

    list($name, $value) = explode('=',$line,2);

    $name = trim($name);
    $value = trim(trim($value),'\'"');

    if($name == 'group'){
      return [self::GROUP, null, $value];
    }

    if($name == 'include'){
      return [self::INCLUDE, null, $value];
    }

    return [self::ITEM, $name, $value];
  }

  private function checkFile($filename){
    if(! file_exists($filename)){
      throw new FilenameException("filename ''$filename' does not exists.");
    }

    if(! is_readable($filename)){
      throw new FilenameException("filename ''$filename' is not readable.");
    }
  }
}
