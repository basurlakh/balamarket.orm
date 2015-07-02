<?
IncludeModuleLangFile(__FILE__);
Class balamarket_orm extends CModule
{
	const MODULE_ID = 'balamarket.orm';
	var $MODULE_ID = 'balamarket.orm';
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $strError = '';

	function __construct()
	{
		$arModuleVersion = array();
		include(dirname(__FILE__)."/version.php");
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = GetMessage("balamarket.orm_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("balamarket.orm_MODULE_DESC");

		$this->PARTNER_NAME = GetMessage("balamarket.orm_PARTNER_NAME");
		$this->PARTNER_URI = GetMessage("balamarket.orm_PARTNER_URI");
	}

	function InstallDB($arParams = array())
	{
		return true;
	}

	function UnInstallDB($arParams = array())
	{
		return true;
	}

	function InstallEvents()
	{
        RegisterModuleDependences('iblock', 'OnAfterIBlockPropertyAdd', self::MODULE_ID, 'Balamarket\Orm\EventHandler\Property', 'OnAfterIBlockPropertyAdd');
        RegisterModuleDependences('iblock', 'OnAfterIBlockPropertyUpdate', self::MODULE_ID, 'Balamarket\Orm\EventHandler\Property', 'OnAfterIBlockPropertyUpdate');
        RegisterModuleDependences('iblock', 'OnBeforeIBlockPropertyDelete', self::MODULE_ID, 'Balamarket\Orm\EventHandler\Property', 'OnBeforeIBlockPropertyDelete');
		return true;
	}

	function UnInstallEvents()
	{
        UnRegisterModuleDependences('iblock', 'OnAfterIBlockPropertyAdd', self::MODULE_ID, 'Balamarket\Orm\EventHandler\Property', 'OnAfterIBlockPropertyAdd');
        UnRegisterModuleDependences('iblock', 'OnAfterIBlockPropertyUpdate', self::MODULE_ID, 'Balamarket\Orm\EventHandler\Property', 'OnAfterIBlockPropertyUpdate');
        UnRegisterModuleDependences('iblock', 'OnBeforeIBlockPropertyDelete', self::MODULE_ID, 'Balamarket\Orm\EventHandler\Property', 'OnBeforeIBlockPropertyDelete');
		return true;
	}

	function InstallFiles($arParams = array())
	{
		return true;
	}

	function UnInstallFiles()
	{
		return true;
	}

	function DoInstall()
	{
		global $APPLICATION;
		$this->InstallFiles();
		$this->InstallDB();
        $this->InstallEvents();
		RegisterModule(self::MODULE_ID);
	}

	function DoUninstall()
	{
		global $APPLICATION;
		UnRegisterModule(self::MODULE_ID);
		$this->UnInstallDB();
		$this->UnInstallFiles();
        $this->UnInstallEvents();
	}
}
