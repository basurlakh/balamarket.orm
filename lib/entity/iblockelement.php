<?php
/**
 * Created by PhpStorm.
 * User: Anton
 * Date: 15.11.2014
 * Time: 16:19
 */

namespace Balamarket\Orm\Entity;

use Bitrix\Iblock\IblockSiteTable;
use Bitrix\Iblock\IblockTable;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\NotImplementedException;

abstract class IblockElement extends DataManager
{
    private static $arEnums = array();

    /**
     * @abstract
     * @return int
     * @throws ArgumentNullException
     * @throws \Bitrix\Main\NotImplementedException
     */
    public static function getIblockId()
    {
        global $CACHE_MANAGER;
        if (strlen(static::getIblockCode()) <= 0)
        {
            throw new ArgumentNullException("Метод getIblockCode() вернул пустую строку.");
        }

        $arIblock = array();
        $obCache = new \CPHPCache;
        $cacheId = md5(get_called_class() . " ::" . __FUNCTION__);
        if ($obCache->InitCache(36000, $cacheId, "/"))
        {
            $vars = $obCache->GetVars();
            $arIblock = $vars["arIblock"];
        }
        elseif ($obCache->StartDataCache())
        {
            if ($arIblock = IblockTable::getList(array(
                "select" => array("ID"),
                "filter" => array("=CODE" => static::getIblockCode()),
                'limit' => 1
            ))->fetch())
            {
                $CACHE_MANAGER->StartTagCache("/");
                $CACHE_MANAGER->RegisterTag("iblock_id_" . $arIblock["ID"]);
                $CACHE_MANAGER->EndTagCache();

                $obCache->EndDataCache(array("arIblock" => $arIblock));
            }
            else
            {
                $obCache->AbortDataCache();
            }
        }

        return $arIblock["ID"];
    }

    /**
     * @abstract
     * @return string
     * @throws \Bitrix\Main\NotImplementedException
     */
    public static function getIblockCode()
    {
        throw new NotImplementedException("Method getIblockCode() must be implemented by successor.");
    }

    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_iblock_element';
    }


    public static function getMap()
    {
        $arMap = array(
            'ID' => array(
                'data_type' => 'integer',
                'primary' => true,
                'autocomplete' => true,
            ),
            'NAME' => array(
                'data_type' => 'string'
            ),
            'IBLOCK_ID' => array(
                'data_type' => 'integer'
            ),
            'IBLOCK' => array(
                'data_type' => '\Bitrix\Iblock\Iblock',
                'reference' => array('=this.IBLOCK_ID' => 'ref.ID')
            ),
            "TIMESTAMP_X" => array(
                "data_type" => "datetime"
            ),
            "DATE_CREATE" => array(
                "data_type" => "datetime"
            ),
            'ACTIVE' => array(
                'data_type' => 'boolean',
                'values' => array('N', 'Y')
            ),
            "ACTIVE_FROM" => array(
                "data_type" => "datetime"
            ),
            "ACTIVE_TO" => array(
                "data_type" => "datetime"
            ),
            "CODE" => array(
                "data_type" => "string"
            ),
            "PREVIEW_TEXT" => array(
                "data_type" => "string"
            ),
            "DETAIL_TEXT" => array(
                "data_type" => "string"
            ),
            "SORT" => array(
                "data_type" => "integer"
            ),
            "IBLOCK_SECTION_ID" => array(
                "data_type" => "integer"
            ),
            "DETAIL_PICTURE" => array(
                "data_type" => "integer"
            ),
            "PREVIEW_PICTURE" => array(
                "data_type" => "integer"
            ),
            "XML_ID" => array(
                "data_type" => "string"
            ),
            "SHOW_COUNTER" => array(
                "data_type" => "integer"
            ),
            "MODIFIED_BY" => array(
                "data_type" => "integer"
            ),
            "CREATED_BY" => array(
                "data_type" => "integer"
            )
        );

        $propertySimpleClassName = str_replace("table", "", strtolower(get_called_class())) . "PropSimpleTable";
        if (class_exists($propertySimpleClassName))
        {
            $arMap["PROPERTY_SIMPLE"] = array(
                "data_type" => $propertySimpleClassName,
                "reference" => array(
                    "=this.ID" => "ref.IBLOCK_ELEMENT_ID"
                )
            );
        }

        $sectionClassName = str_replace("table", "", strtolower(get_called_class())) . "SectionTable";
        if (class_exists($sectionClassName))
        {
            $arMap["SECTION"] = array(
                "data_type" => $sectionClassName,
                "reference" => array(
                    "=this.IBLOCK_SECTION_ID" => "ref.ID"
                )
            );

            $arMap["SECTION_ELEMENT"] = array(
                "data_type" => '\Balamarket\Orm\Entity\SectionElementTable',
                "reference" => array(
                    "=this.ID" => "ref.IBLOCK_ELEMENT_ID"
                )
            );

            $arMap["SECTIONS"] = array(
                "data_type" => $sectionClassName,
                "reference" => array(
                    "=this.SECTION_ELEMENT.IBLOCK_SECTION_ID" => "ref.ID"
                )
            );
        }

        $arMap = array_merge($arMap, static::getPropertyMultipleMap());
        $arMap = array_merge($arMap, static::getUrlTemplateMap($arMap));

        return $arMap;
    }

    protected static function getPropertyMultipleMap()
    {
        global $CACHE_MANAGER;
        $arProperties = array();
        $propertyMultipleClassName = str_replace("table", "", strtolower(get_called_class())) . "PropMultipleTable";
        if (class_exists($propertyMultipleClassName))
        {
            $obCache = new \CPHPCache;
            $cacheId = md5(get_called_class() . " ::" . __METHOD__);
            if ($obCache->InitCache(36000, $cacheId, "/"))
            {
                $vars = $obCache->GetVars();
                $arProperties = $vars["arProperties"];
            }
            elseif ($obCache->StartDataCache())
            {
                $arFilter = array(
                    "IBLOCK_ID" => static::getIblockId(),
                    "MULTIPLE" => "Y"
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

                    $arProperties["PROPERTY_MULTIPLE_" . $arProperty["CODE"]] = array(
                        "data_type" => $propertyMultipleClassName,
                        "reference" => array(
                            "=this.ID" => "ref.IBLOCK_ELEMENT_ID",
                            "ref.IBLOCK_PROPERTY_ID" => new SqlExpression('?i', $arProperty["ID"])
                        )
                    );
                }

                $CACHE_MANAGER->StartTagCache("/");
                $CACHE_MANAGER->RegisterTag("property_iblock_id_" . static::getIblockId());
                $CACHE_MANAGER->EndTagCache();
                $obCache->EndDataCache(array("arProperties" => $arProperties));
            }
        }

        return $arProperties;
    }

    public static function getList(array $parameters = array())
    {
        $parameters["filter"]["IBLOCK_ID"] = static::getIblockId();

        return parent::getList($parameters);
    }

    /**
     * Вернет значение value у множественного свойства
     *
     * @param $id - id значения свойства
     * @param $propertyCode - символьный код свойства
     *
     * @return mixed null|String
     * @throws \Bitrix\Main\NotImplementedException
     */
    public static function getEnumValueById($id, $propertyCode)
    {
        $arProperty = self::getEnums();
        foreach ($arProperty[static::getIblockId()][$propertyCode] as $xmlId => $arEnumValue)
        {
            if ($id == $arEnumValue["ID"])
            {
                return $arEnumValue["VALUE"];
            }
        }

        return null;
    }

    /**
     * Вернет id значения множественного свойтсва по XML_ID
     *
     * @param $xml - xml_id значения свойства
     * @param $propertyCode - символьный код свойства
     *
     * @return mixed
     * @throws \Bitrix\Main\NotImplementedException
     */
    public static function getEnumIdByXmlId($xml, $propertyCode)
    {
        $arProperty = self::getEnums();

        return $arProperty[static::getIblockId()][$propertyCode][$xml]["ID"];
    }

    /**
     * Вернет xml_id множественного свойтсва по id
     *
     * @param $id
     * @param $propertyCode
     *
     * @return int|null|string
     * @throws \Bitrix\Main\NotImplementedException
     */
    public static function getXmlIdById($id, $propertyCode)
    {
        $arProperty = self::getEnums();
        foreach ($arProperty[static::getIblockId()][$propertyCode] as $xmlId => $arEnumValue)
        {
            if ($id == $arEnumValue["ID"])
            {
                return $xmlId;
            }
        }

        return null;
    }

    private static function getEnums()
    {
        if (!self::$arEnums)
        {
            self::$arEnums = array();
            $sCacheId = __CLASS__ . "::" . __FUNCTION__;

            $oCache = new \CPHPCache;
            $oCache->InitCache(36000, $sCacheId, "/");
            if (!$arData = $oCache->GetVars())
            {
                $oProperties = \CIBlockProperty::getList(array("ID" => "ASC"),
                    array(
                        "ACTIVE" => "Y",
                        "PROPERTY_TYPE" => "L"
                    ));
                $arProperty2IblockID = array();
                while ($arProperty = $oProperties->Fetch())
                {
                    self::$arEnums[$arProperty["IBLOCK_ID"]][$arProperty["CODE"]] = array();
                    $arProperty2IblockID[$arProperty["ID"]] = $arProperty["IBLOCK_ID"];
                }
                $oEnumProperties = \CIBlockPropertyEnum::getList(array("ID" => "ASC"));
                while ($arEnumProperty = $oEnumProperties->Fetch())
                {
                    self::$arEnums[$arProperty2IblockID[$arEnumProperty["PROPERTY_ID"]]][$arEnumProperty["PROPERTY_CODE"]][$arEnumProperty["XML_ID"]] = array(
                        "ID" => intval($arEnumProperty["ID"]),
                        "VALUE" => $arEnumProperty["VALUE"]
                    );
                }
                if ($oCache->StartDataCache())
                {
                    $oCache->EndDataCache(array("arProperties" => self::$arEnums));
                }
            }
            else
            {
                self::$arEnums = $arData["arProperties"];
            }
        }

        return self::$arEnums;
    }

    protected static function getProperties()
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
        elseif ($obCache->StartDataCache())
        {
            $arFilter = array(
                "IBLOCK_ID" => static::getIblockId(),
            );
            $rsProperty = \CIBlockProperty::GetList(array(),
                $arFilter);
            while ($arProperty = $rsProperty->Fetch())
            {
                if (empty($arProperty["CODE"]))
                {
                    continue;
                }
                $arProperties[$arProperty["CODE"]] = $arProperty;
            }

            $CACHE_MANAGER->StartTagCache("/");
            $CACHE_MANAGER->RegisterTag("property_iblock_id_" . static::getIblockId());
            $CACHE_MANAGER->EndTagCache();
            $obCache->EndDataCache(array("arProperties" => $arProperties));
        }

        return $arProperties;
    }

    /**
     * Вернет символьный код свойства по id
     *
     * @param $id - id свойства
     *
     * @return null|string
     */
    public static function getPropertyCodeById($id)
    {
        foreach (static::getProperties() as $code => $arProperty)
        {
            if ($arProperty["ID"] == $id)
            {
                return $code;
            }
        }

        return null;
    }

    /**
     * Вернет id свойства по его символьному коду
     *
     * @param $code - символьный код свойства
     *
     * @return null|int
     */
    public static function getPropertyIdByCode($code)
    {
        $arProperty = static::getProperties();

        return $arProperty[$code]["ID"];
    }

    /**
     * Полуим expression для ссылки на детальную страницу
     *
     * @param array $modelMap - текущий map
     *
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\NotImplementedException
     */
    private static function getUrlTemplateMap(array $modelMap = array())
    {
        global $CACHE_MANAGER;
        $arMap = array();
        $obCache = new \CPHPCache;
        $currentAdminPage = ((defined("ADMIN_SECTION") && ADMIN_SECTION===true) || !defined("BX_STARTED"));
        $cacheId = md5(get_called_class() . " ::" . __METHOD__ . $currentAdminPage);

        if ($obCache->InitCache(36000, $cacheId, "/"))
        {
            $arMap = $obCache->GetVars();
        }
        elseif ($obCache->StartDataCache())
        {
            $obIblock = IblockSiteTable::getList(array(
                "select" => array(
                    "DETAIL_PAGE_URL" => "IBLOCK.DETAIL_PAGE_URL",
                    "SITE_ID",
                    "DIR" => "SITE.DIR",
                    "SERVER_NAME" => "SITE.DIR",
                ),
                "filter" => array(
                    "IBLOCK_ID" => static::getIblockId()
                ),
                "limit" => 1
            ));

            if ($arIblock = $obIblock->fetch())
            {
                $templateUrl = $arIblock["DETAIL_PAGE_URL"];

                if($currentAdminPage)
                {
                    $templateUrl = str_replace(
                        array("#SITE_DIR#", "#SERVER_NAME#"),
                        array($arIblock["DIR"], $arIblock["SERVER_NAME"]),
                        $templateUrl
                    );
                }
                else
                {
                    $templateUrl = str_replace(
                        array("#SITE_DIR#", "#SERVER_NAME#"),
                        array(SITE_DIR, SITE_SERVER_NAME),
                        $templateUrl
                    );
                }

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