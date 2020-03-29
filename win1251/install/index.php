<?php
/**
 * @author dev2fun (darkfriend)
 * @copyright darkfriend
 * @version 1.0.0
 */
if (class_exists("dev2fun_mandrill")) return;

IncludeModuleLangFile(__FILE__);

use Bitrix\Main\ModuleManager,
    Bitrix\Main\EventManager;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc,
    Bitrix\Main\IO\Directory,
    Bitrix\Main\Config\Option;

Loader::registerAutoLoadClasses(
    "dev2fun.mandrill",
    [
        'Dev2fun\\Mandrill\\Base' => 'include.php',
        'Dev2fun\\Mandrill\\Config' => 'classes/general/Config.php',
//        'Dev2fun\MultiDomain\SubDomain' => 'classes/general/SubDomain.php',
//        'Dev2fun\MultiDomain\HLHelpers' => 'lib/HLHelpers.php',
    ]
);

class dev2fun_mandrill extends CModule
{
    var $MODULE_ID = "dev2fun.mandrill";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_GROUP_RIGHTS = 'Y';

    public function dev2fun_mandrill()
    {
        include(__DIR__ . '/version.php');
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];

        $this->MODULE_NAME = Loc::getMessage('D2F_MODULE_NAME_MANDRILL');
        $this->MODULE_DESCRIPTION = Loc::getMessage('D2F_MODULE_DESCRIPTION_MANDRILL');
        $this->PARTNER_NAME = 'dev2fun';
        $this->PARTNER_URI = 'https://dev2fun.com';
    }

    public function DoInstall()
    {
        global $APPLICATION;
        if (!check_bitrix_sessid()) return;
        try {
            $this->installDB();
            $this->registerEvents();
            ModuleManager::registerModule($this->MODULE_ID);
            \CAdminNotify::Add([
                'MESSAGE' => Loc::getMessage('D2F_MANDRILL_NOTICE_THANKS'),
                'TAG' => $this->MODULE_ID . '_install',
                'MODULE_ID' => $this->MODULE_ID,
            ]);
        } catch (Exception $e) {
            $GLOBALS['D2F_MANDRILL_ERROR'] = $e->getMessage();
            $GLOBALS['D2F_MANDRILL_ERROR_NOTES'] = Loc::getMessage('D2F_MANDRILL_INSTALL_ERROR_NOTES');
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage('D2F_MANDRILL_STEP_ERROR'),
                __DIR__ . '/error.php'
            );
            return false;
        }
        $APPLICATION->IncludeAdminFile(Loc::getMessage('D2F_MANDRILL_STEP1'), __DIR__ . '/step1.php');
    }

    public function installDB()
    {
        Option::set($this->MODULE_ID, 'enabled', 'Y');
        Option::set($this->MODULE_ID, 'track_opens', 'Y');
        Option::set($this->MODULE_ID, 'track_clicks', 'Y');
        return true;
    }

    public function registerEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandler(
            'main',
            'OnBeforeMailSend',
            $this->MODULE_ID,
            'Dev2fun\\Mandrill\\Base',
            'OnBeforeMailSend'
        );
        return true;
    }

    public function DoUninstall()
    {
        global $APPLICATION;
        if (!check_bitrix_sessid()) return;
        try {
            $this->unInstallDB();
            $this->unRegisterEvents();
            \CAdminNotify::Add([
                'MESSAGE' => Loc::getMessage('D2F_MANDRILL_NOTICE_WHY'),
                'TAG' => $this->MODULE_ID . '_uninstall',
                'MODULE_ID' => $this->MODULE_ID,
            ]);
            ModuleManager::unRegisterModule($this->MODULE_ID);
        } catch (Exception $e) {
            $GLOBALS['D2F_COMPRESSIMAGE_ERROR'] = $e->getMessage();
            $GLOBALS['D2F_COMPRESSIMAGE_ERROR_NOTES'] = Loc::getMessage('D2F_MANDRILL_UNINSTALL_ERROR_NOTES');
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage('D2F_MANDRILL_STEP_ERROR'),
                __DIR__ . '/error.php'
            );
            return false;
        }

        $APPLICATION->IncludeAdminFile(GetMessage('D2F_MANDRILL_UNSTEP1'), __DIR__ . '/unstep1.php');
    }

    public function unInstallDB()
    {
        Option::delete($this->MODULE_ID);
        return true;
    }

    public function unRegisterEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            'main',
            'OnBeforeMailSend',
            $this->MODULE_ID
        );
        return true;
    }
}
