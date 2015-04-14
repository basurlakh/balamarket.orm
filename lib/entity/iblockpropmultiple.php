<?php
/**
 * Created by PhpStorm.
 * User: Anton
 * Date: 15.11.2014
 * Time: 17:10
 */

namespace Balamarket\Orm\Entity;


use Bitrix\Main\Entity\DataManager;

class IblockPropMultiple extends DataManager
{
    /**
     * @abstract
     */
    public static function getIblockId()
    {
        throw new \Bitrix\Main\NotImplementedException("Method getIblockId() must be implemented by successor.");
    }
    public static function getTableName()
    {
        return 'b_iblock_element_prop_m' . static::getIblockId();
    }
    public static function getMap()
    {
        $arMap = array(
            "ID" => array(
                "data_type" => "integer",
                "primary" => true,
                "autocomplete" => true
            ),
            "IBLOCK_ELEMENT_ID" => array(
                "data_type" => "integer",
                "primary" => true
            ),
            "IBLOCK_PROPERTY_ID" => array(
                "data_type" => "integer"
            ),
            "VALUE" => array(
                "data_type" => "string"
            ),
            "DESCRIPTION" => array(
                "data_type" => "string"
            ),
            "VALUE_NUM" => array(
                "data_type" => "float"
            )
        );
        return $arMap;
    }
} 