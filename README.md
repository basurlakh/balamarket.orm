# balamarket.orm
Модуль bitrix для работы с инфоблоками через ORM
## Модель
В своем модуле создаете entity для конкретного инфоблока.
К примеру:

    /local/
        modules/
        <ваш модуль>/
            content/
                news.php - Класс инфоблока
                newspropsimple.php - Класс свойств инфоблока
                newspropmultiple.php - Класс множественных свойств инфоблока

Для того, чтоб классы могли знать друг о друге используется правило наименования:
    
    <Сущьность>Table
    <Сущьность>PropSimpleTable
    <Сущьность>PropMultipleTable

Все 3 класса обязательно должны лежать в одном `namespace`.

### Класс `NewsTable` инфоблока наследуем от `Balamarket\Orm\Entity\IblockElement`

    <?php
    namespace <ваш модуль>\Content;
    use Bitrix\Main\Loader;
    
    Loader::includeModule("balamarket.orm");

    class NewsTable extends \Balamarket\Orm\Entity\IblockElement
    {
        public static function getIblockId()
        {
            return 1;
        }
    }


### Класс `NewsPropSimpleTable` свойств инфоблока наследуем от `Balamarket\Orm\Entity\IblockPropSimple`

    <?php
    namespace <ваш модуль>\Content;
    use Bitrix\Main\Loader;
    
    Loader::includeModule("balamarket.orm");

    class NewsPropSimpleTable extends \Balamarket\Orm\Entity\IblockPropSimple
    {
        public static function getIblockId()
        {
            return NewsTable::getIblockId();
        }
    }

### Класс `NewsPropMultipleTable` множественных свойств инфоблока наследуем от `Balamarket\Orm\Entity\IblockPropMultiple`

    <?php
    namespace <ваш модуль>\Content;
    use Bitrix\Main\Loader;
    
    Loader::includeModule("balamarket.orm");

    class NewsPropMultipleTable extends \Balamarket\Orm\Entity\IblockPropSimple
    {
        public static function getIblockId()
        {
            return NewsTable::getIblockId();
        }
    }
    
## Выборка
Во время выборки можно использовать все поля указанные в методе `getMap()`
Для доступа к свойствам используются резервированные названия полей:

    PROPERTY_SIMPLE.<символьный код свойства>
    PROPERTY_MULTIPLE_<символьный код свойства>.VALUE

Примеры:

    use <ваш модуль>\Content\NewsTable;
    $obNews = NewsTable::getList(
        array(
            "select" => array(
                "ID",
                "NAME",
                "SOURCE_LINK" => "PROPERTY_SIMPLE.SOURCE_LINK"
            ),
            "filter" => array(
                "=ACTIVE" => "Y",
                "!PROPERTY_SIMPLE.SOURCE_LINK" => false
            ),
        )
    );
    
    while($arNew = $obNews->fetch()) {
        // code
    }
    
    
Примеры работы с множественными свойствами:

    use <ваш модуль>\Content\NewsTable;
    $obNews = NewsTable::getList(
        array(
            "select" => array(
                "ID",
                "NAME",
                "TAGS"
            ),
            "runtime" => array(
                "TAGS" => array(
                    "data_type" => "string",
                    "expression" => array(
                        "GROUP_CONCAT(%s)",
                        "PROPERTY_MULTIPLE_TAGS.VALUE"
                    )
                )
            ),
            "filter" => array(
                "=ACTIVE" => "Y",
                "!PROPERTY_MULTIPLE_TAGS.VALUE" => false
            ),
        )
    );
    
    while($arNew = $obNews->fetch()) {
        // code
    }