<?php
/**
 * Created by PhpStorm.
 * User: Anton
 * Date: 10.03.2015
 * Time: 18:33
 */

namespace Balamarket\Orm\Entity;

\Bitrix\Main\Loader::includeModule("iblock");

class IblockSectionTable extends \Bitrix\Iblock\SectionTable
{
    /**
     * @abstract
     * @return int
     * @throws \Bitrix\Main\NotImplementedException
     */
    public static function getIblockId()
    {
        throw new \Bitrix\Main\NotImplementedException("Method getIblockId() must be implemented by successor.");
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
        $arMap = array();
        $obCache = new \CPHPCache;
        $cacheId = md5(get_called_class() . " ::" . __METHOD__);

        if ($obCache->InitCache(36000, $cacheId, "/"))
        {
            $arMap = $obCache->GetVars();
        }

        elseif (\Bitrix\Main\Loader::includeModule("iblock") && $obCache->StartDataCache())
        {
            $obIblock = \Bitrix\Iblock\IblockTable::getList(array(
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
                $templateUrl = $arIblock["LIST_PAGE_URL"] . $arIblock["SECTION_PAGE_URL"];
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
            $obCache->EndDataCache($arMap);
        }

        return $arMap;
    }
}