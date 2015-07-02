<?php
/**
 * Created by PhpStorm.
 * User: Anton
 * Date: 15.11.2014
 * Time: 17:04
 */

namespace Balamarket\Orm\Entity;


use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Loader;
use Bitrix\Main\NotImplementedException;

abstract class IblockPropSimple extends DataManager
{
    /**
     * @abstract
     * @return int
     * @throws \Bitrix\Main\NotImplementedException
     */
    public static function getIblockId()
    {
        throw new NotImplementedException("Method getIblockId() must be implemented by successor.");
    }

    public static function getTableName()
    {
        return 'b_iblock_element_prop_s' . static::getIblockId();
    }
    public static function getMap()
    {
        $arMap = array(
            'IBLOCK_ELEMENT_ID' => array(
                'data_type' => 'integer',
                'primary' => true
            ),
            'IBLOCK_ELEMENT' => array(
                "data_type" => str_replace('PropSimple', '', get_called_class()),
                "reference" => array(
                    "=this.IBLOCK_ELEMENT_ID" => "ref.ID"
                )
            )
        );
        $arMap = array_merge($arMap, self::getPropertyMap());
        return $arMap;
    }
    private static function getPropertyMap()
    {
        global $CACHE_MANAGER;
        $obCache = new \CPHPCache;
        $cacheId = md5(get_called_class() . " ::" . __METHOD__);
        $arProperties = array();
        if ($obCache->InitCache(36000, $cacheId, "/"))
        {
            $vars = $obCache->GetVars();

            $arProperties = $vars["arProperties"];
        }
        elseif (Loader::includeModule("iblock") && $obCache->StartDataCache())
        {
            $arFilter = array(
                "IBLOCK_ID" => static::getIblockId(),
                "MULTIPLE" => "N"
            );
            $rsProperty = \CIBlockProperty::GetList(
                array(),
                $arFilter
            );
            while ($arProperty = $rsProperty->Fetch())
            {
                if (empty($arProperty["CODE"]))
                {
                    continue;
                }

                $arColumn = array(
                    "expression" => array(
                        "%s",
                        "PROPERTY_" . $arProperty["ID"]
                    )
                );
                switch ($arProperty["PROPERTY_TYPE"])
                {
                    case 'L':
                    case 'F':
                    case 'G':
                    case 'E':
                    case 'S:UserID':
                    case 'E:EList':
                    case 'S:FileMan':
                        $arColumn["data_type"] = "integer";
                        break;

                    case 'S:DateTime':
                        $arColumn["data_type"] = "datetime";
                        break;

                    case 'N':
                        $arColumn["data_type"] = "float";
                        break;
                    case 'S':
                    default:
                        $arColumn["data_type"] = "string";

                        if ($arProperty["USER_TYPE"] == "HTML")
                        {
                            $arColumn["data_type"] = "text";
                            $arColumn["serialized"] = true;
                        }

                        break;
                }

                $arProperties[$arProperty["CODE"]] = $arColumn;
                $arProperties["PROPERTY_" . $arProperty["ID"]] = array(
                    "data_type" => $arColumn["data_type"]
                );
            }

            $CACHE_MANAGER->StartTagCache("/");
            $CACHE_MANAGER->RegisterTag("property_iblock_id_" . static::getIblockId());
            $CACHE_MANAGER->EndTagCache();

            $obCache->EndDataCache(array("arProperties" => $arProperties));
        }

        return $arProperties;
    }
}