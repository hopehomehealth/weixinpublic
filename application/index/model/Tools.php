<?php
/**
 * Created by PhpStorm.
 * User: LENOVO
 * Date: 2017/11/21
 * Time: 16:08
 */

/**
 * Class Tools
 * @param Array $data
 * @param Int $pid
 * @param Int $count
 * $param Array $treeList
 */
class Tools{
    static public $treeList = array();

    static public function tree(&$data,$pid=0,$count=0){
        foreach ($data as $key => $value){
            if($value['Pid'] == $pid){
                $value["Count"] = $count;
                self::$treeList[] = $value;
                unset($data[$key]);
                self::tree($data,$value['Id'],$count+1);
            }
        }
        return self::$treeList;
    }
}