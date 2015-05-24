<?php
/**
 * Created by PhpStorm.
 * User: Anton
 * Date: 15.11.2014
 * Time: 16:19
 */

namespace Balamarket\Orm\Entity;

use Bitrix\Main\DB\SqlExpression;
use \Bitrix\Main\Entity\DataManager;

abstract class IblockElement extends DataManager
{
    private static $arEnums = array();
    private static $arProperties;

    /**
     * @abstract
     * @return int
     * @throws \Bitrix\Main\NotImplementedException
     */
    public static function getIblockId()
    {
        throw new \Bitrix\Main\NotImplementedException("Method getIblockId() must be implemented by successor.");
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
            'TIMESTAMP_X' => array(
                'data_type' => 'datetime',
            ),
            'MODIFIED_BY' => array(
                'data_type' => 'integer',
            ),
            'DATE_CREATE' => array(
                'data_type' => 'datetime',
            ),
            'CREATED_BY' => array(
                'data_type' => 'integer',
            ),
            'IBLOCK_ID' => array(
                'data_type' => 'integer',
                'required' => true,
            ),
            'IBLOCK_SECTION_ID' => array(
                'data_type' => 'integer',
            ),
            'ACTIVE' => array(
                'data_type' => 'boolean',
                'values' => array('N', 'Y'),
            ),
            'ACTIVE_FROM' => array(
                'data_type' => 'datetime',
            ),
            'ACTIVE_TO' => array(
                'data_type' => 'datetime',
            ),
            'SORT' => array(
                'data_type' => 'integer',
            ),
            'NAME' => array(
                'data_type' => 'string',
                'required' => true,
            ),
            'PREVIEW_PICTURE' => array(
                'data_type' => 'integer',
            ),
            'PREVIEW_TEXT' => array(
                'data_type' => 'text',
            ),
            'PREVIEW_TEXT_TYPE' => array(
                'data_type' => 'enum',
                'values' => array('text', 'html'),
            ),
            'DETAIL_PICTURE' => array(
                'data_type' => 'integer',
            ),
            'DETAIL_TEXT' => array(
                'data_type' => 'text',
            ),
            'DETAIL_TEXT_TYPE' => array(
                'data_type' => 'enum',
                'values' => array('text', 'html'),
            ),
            'SEARCHABLE_CONTENT' => array(
                'data_type' => 'text',
            ),
            'WF_STATUS_ID' => array(
                'data_type' => 'integer',
            ),
            'WF_PARENT_ELEMENT_ID' => array(
                'data_type' => 'integer',
            ),
            'WF_NEW' => array(
                'data_type' => 'string',
            ),
            'WF_LOCKED_BY' => array(
                'data_type' => 'integer',
            ),
            'WF_DATE_LOCK' => array(
                'data_type' => 'datetime',
            ),
            'WF_COMMENTS' => array(
                'data_type' => 'text',
            ),
            'IN_SECTIONS' => array(
                'data_type' => 'boolean',
                'values' => array('N', 'Y'),
            ),
            'XML_ID' => array(
                'data_type' => 'string',
            ),
            'CODE' => array(
                'data_type' => 'string',
            ),
            'ELEMENT_CODE' => array(
                'data_type' => 'string',
                'expression' => array('%s', 'CODE')
            ),
            'TAGS' => array(
                'data_type' => 'string',
            ),
            'TMP_ID' => array(
                'data_type' => 'string',
            ),
            'WF_LAST_HISTORY_ID' => array(
                'data_type' => 'integer',
            ),
            'SHOW_COUNTER' => array(
                'data_type' => 'integer',
            ),
            'SHOW_COUNTER_START' => array(
                'data_type' => 'datetime',
            ),
            'IBLOCK' => array(
                'data_type' => '\Bitrix\Iblock\Iblock',
                'reference' => array('=this.IBLOCK_ID' => 'ref.ID'),
            ),
            'WF_PARENT_ELEMENT' => array(
                'data_type' => get_called_class(),
                'reference' => array('=this.WF_PARENT_ELEMENT_ID' => 'ref.ID'),
            ),
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
            $arMap["IBLOCK_SECTION"] = array(
                "data_type" => $sectionClassName,
                "reference" => array(
                    "=this.IBLOCK_SECTION_ID" => "ref.ID"
                )
            );

            $arMap["SECTION_CODE"] = array(
                "data_type" => "string",
                "expression" => array("%s", "IBLOCK_SECTION.CODE")
            );

            $arMap["SECTION_ID"] = array(
                "data_type" => "string",
                "expression" => array("%s", "IBLOCK_SECTION_ID")
            );
        }

        $arMap = array_merge($arMap, static::getPropertyMultipleMap());
        $arMap = array_merge($arMap, static::getUrlTemplateMap($arMap));

        return $arMap;
    }

    protected static function getPropertyMultipleMap()
    {
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
            elseif (\Bitrix\Main\Loader::includeModule("iblock") && $obCache->StartDataCache())
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
            $sCacheId = md5(__CLASS__ . "::" . __FUNCTION__);

            $oCache = new \CPHPCache;
            $oCache->InitCache(36000, $sCacheId, "/");
            if (!$arData = $oCache->GetVars())
            {
                \Bitrix\Main\Loader::includeModule("iblock");
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
        $obCache = new \CPHPCache;
        $cacheId = md5(get_called_class() . " ::" . __METHOD__);
        self::$arProperties = array();
        if ($obCache->InitCache(36000, $cacheId, "/"))
        {
            $vars = $obCache->GetVars();

            self::$arProperties = $vars["arProperties"];
        }
        elseif (\Bitrix\Main\Loader::includeModule("iblock") && $obCache->StartDataCache())
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
                self::$arProperties[$arProperty["CODE"]] = $arProperty;
            }

            $obCache->EndDataCache(array("arProperties" => self::$arProperties));
        }

        return self::$arProperties;
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
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\NotImplementedException
     */
    private static function getUrlTemplateMap(array $modelMap = array())
    {
        $arMap = array();
        $obCache = new \CPHPCache;
        $currentAdminPage = ((defined("ADMIN_SECTION") && ADMIN_SECTION===true) || !defined("BX_STARTED"));
        $cacheId = md5(get_called_class() . " ::" . __METHOD__ . $currentAdminPage . SITE_ID);

        if ($obCache->InitCache(36000, $cacheId, "/"))
        {
            $arMap = $obCache->GetVars();
        }
        elseif (\Bitrix\Main\Loader::includeModule("iblock") && $obCache->StartDataCache())
        {
            $obIblock = \Bitrix\Iblock\IblockSiteTable::getList(array(
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
            $obCache->EndDataCache($arMap);
        }

        return $arMap;
    }
}