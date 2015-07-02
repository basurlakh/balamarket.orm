<?php
/**
 * Created by PhpStorm.
 * User: Anton
 * Date: 10.03.2015
 * Time: 18:33
 */

namespace Balamarket\Orm\Entity;

use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\NotImplementedException;

class IblockSectionTable extends SectionTable
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

    public static function getList(array $parameters = array())
    {
        if (!empty($parameters))
        {
            $parameters["filter"]["IBLOCK_ID"] = static::getIblockId();
        }

        return parent::getList($parameters);
    }

    public static function getMap()
    {
        $arMap = parent::getMap();
        $arMap['PARENT_SECTION'] = array(
            'data_type' => get_called_class(),
            'reference' => array('=this.IBLOCK_SECTION_ID' => 'ref.ID'),
        );

        $arMap = array_merge($arMap, static::getUrlTemplateMap($arMap));

        return $arMap;
    }


    /**
     * Полуим expression для ссылки на детальную страницу
     *
     * @param array $modelMap - текущий map
     *
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\NotImplementedException
     */
    private static function getUrlTemplateMap(array $modelMap = array())
    {
        global $CACHE_MANAGER;
        $arMap = array();
        $obCache = new \CPHPCache;
        $cacheId = md5(get_called_class() . " ::" . __METHOD__);

        if ($obCache->InitCache(36000, $cacheId, "/"))
        {
            $arMap = $obCache->GetVars();
        }

        elseif ($obCache->StartDataCache())
        {
            $obIblock = IblockTable::getList(array(
                "select" => array(
                    "LIST_PAGE_URL",
                    "SECTION_PAGE_URL"
                ),
                "filter" => array(
                    "ID" => static::getIblockId()
                )
            ));

            if ($arIblock = $obIblock->fetch())
            {
                $templateUrl = $arIblock["SECTION_PAGE_URL"];
                $expressionFields = array();
                preg_match_all('/#([^#]+)#/ui', $templateUrl, $match);
                if (!empty($match[1]))
                {
                    foreach ($match[1] as $kid => $fieldName)
                    {
                        if (array_key_exists($fieldName, $modelMap))
                        {
                            $templateUrl = str_replace($match[0][$kid], "', %s,'", $templateUrl);
                            $expressionFields[] = $fieldName;
                        }
                    }
                }

                array_unshift($expressionFields, "CONCAT('" . $templateUrl . "')");
                $arMap["DETAIL_PAGE_URL"] = array(
                    "data_type" => "string",
                    "expression" => $expressionFields
                );
            }

            $CACHE_MANAGER->StartTagCache("/");
            $CACHE_MANAGER->RegisterTag("iblock_id_" . static::getIblockId());
            $CACHE_MANAGER->EndTagCache();
            $obCache->EndDataCache($arMap);
        }

        return $arMap;
    }
}