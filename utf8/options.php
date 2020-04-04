<?php
/**
 * @author dev2fun (darkfriend)
 * @copyright darkfriend
 * @version 1.0.0
 */
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;


if (!$USER->isAdmin()) {
    $APPLICATION->authForm('Nope');
}
$app = Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();
$curModuleName = 'dev2fun.mandrill';
//Loc::loadMessages($context->getServer()->getDocumentRoot()."/bitrix/modules/main/options.php");
Loc::loadMessages(__FILE__);

$aTabs = [
    [
        "DIV" => "edit1",
        "TAB" => Loc::getMessage("MAIN_TAB_SET"),
        "ICON" => "main_settings",
        "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_SET"),
    ],
//    [
//        "DIV" => "edit2",
//        "TAB" => Loc::getMessage("D2F_MANDRILL_TAB_2"),
//        "ICON" => "main_settings",
//        "TITLE" => Loc::getMessage("D2F_MANDRILL_TAB_2_TITLE_SET"),
//    ],
//    [
//        "DIV" => "edit3",
//        "TAB" => Loc::getMessage("D2F_MANDRILL_TAB_3"),
//        "ICON" => "main_settings",
//        "TITLE" => Loc::getMessage("D2F_MANDRILL_TAB_3_TITLE_SET"),
//    ],
//    [
//        "DIV" => "edit4",
//        "TAB" => Loc::getMessage("D2F_MANDRILL_TAB_4"),
//        "ICON" => "main_settings",
//        "TITLE" => Loc::getMessage("D2F_MANDRILL_TAB_4_TITLE_SET"),
//    ],
    //	array(
    //		"DIV" => "edit5",
    //		"TAB" => Loc::getMessage("D2F_MANDRILL_TAB_5"),
    //		"ICON" => "main_settings",
    //		"TITLE" => Loc::getMessage("D2F_MANDRILL_TAB_5_TITLE_SET")
    //	),
    //    array("DIV" => "edit8", "TAB" => GetMessage("MAIN_TAB_8"), "ICON" => "main_settings", "TITLE" => GetMessage("MAIN_OPTION_EVENT_LOG")),
    //    array("DIV" => "edit5", "TAB" => GetMessage("MAIN_TAB_5"), "ICON" => "main_settings", "TITLE" => GetMessage("MAIN_OPTION_UPD")),
    //    array("DIV" => "edit2", "TAB" => GetMessage("MAIN_TAB_RIGHTS"), "ICON" => "main_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_RIGHTS")),
];

//$tabControl = new CAdminTabControl("tabControl", array(
//    array(
//        "DIV" => "edit1",
//        "TAB" => Loc::getMessage("MAIN_TAB_SET"),
//        "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_SET"),
//    ),
//));

$tabControl = new CAdminTabControl("tabControl", $aTabs);

if ($request->isPost() && check_bitrix_sessid()) {

    $arFields = $request->getPost('options');
    if(empty($arFields['enabled'])) {
        $arFields['enabled'] = 'N';
    }
    if(empty($arFields['trackOpens'])) {
        $arFields['trackOpens'] = 'N';
    }
    if(empty($arFields['trackClicks'])) {
        $arFields['trackClicks'] = 'N';
    }
    foreach ($arFields as $k => $arField) {
        Option::set($curModuleName, $k, $arField);
    }

}
$msg = new CAdminMessage([
    'MESSAGE' => Loc::getMessage("D2F_MANDRILL_DONATE_MESSAGES", ['#LINK#' => 'http://yasobe.ru/na/thankyou_bitrix']),
    'TYPE' => 'OK',
    'HTML' => true,
]);
echo $msg->Show();
$tabControl->begin();
//$assets = \Bitrix\Main\Page\Asset::getInstance();
//$assets->addJs('/bitrix/js/' . $curModuleName . '/script.js');
?>

<form
    method="post"
    action="<?= sprintf('%s?mid=%s&lang=%s', $request->getRequestedPage(), urlencode($mid), LANGUAGE_ID) ?>&<?= $tabControl->ActiveTabParam() ?>"
    enctype="multipart/form-data"
    name="editform"
    class="editform"
>
    <?php
    echo bitrix_sessid_post();
    $tabControl->beginNextTab();
    ?>
    <!--    <tr class="heading">-->
    <!--        <td colspan="2"><b>--><? //echo GetMessage("D2F_COMPRESS_HEADER_SETTINGS")?><!--</b></td>-->
    <!--    </tr>-->
    <tr>
        <td width="40%">
            <label for="options[enabled]">
                <?= Loc::getMessage("D2F_MANDRILL_LABEL_ENABLE") ?>:
            </label>
        </td>
        <td width="60%">
            <?php
            $enabled = Option::get($curModuleName, 'enabled', 'Y') === 'Y';
            ?>
            <input type="checkbox" value="Y" name="options[enabled]" <?=$enabled?'checked':''?>>
        </td>
    </tr>
    <tr>
        <td width="40%">
            <label for="apiKey">
                <?= Loc::getMessage("D2F_MANDRILL_LABEL_API_KEY") ?>:
            </label>
        </td>
        <td width="60%">
            <?php
            $apiKey = Option::get($curModuleName, 'apiKey', '');
            ?>
            <input type="text" value="<?=$apiKey?>" name="options[apiKey]">
        </td>
    </tr>
    <tr>
        <td width="40%">
            <label for="options_fromName">
                <?= Loc::getMessage("D2F_MANDRILL_LABEL_FROM_NAME") ?>:
            </label>
        </td>
        <td width="60%">
            <?php
            $fromName = Option::get($curModuleName, 'fromName', '');
            ?>
            <input type="text" value="<?=$fromName?>" name="options[fromName]">
        </td>
    </tr>
    <tr>
        <td width="40%">
            <label for="options[trackOpens]">
                <?= Loc::getMessage("D2F_MANDRILL_LABEL_TRACK_OPENS") ?>:
            </label>
        </td>
        <td width="60%">
            <?php
            $trackOpens = Option::get($curModuleName, 'trackOpens', 'Y') === 'Y';
            ?>
            <input type="checkbox" value="Y" name="options[trackOpens]" <?=$trackOpens?'checked':''?>>
        </td>
    </tr>
    <tr>
        <td width="40%">
            <label for="options[trackClicks]">
                <?= Loc::getMessage("D2F_MANDRILL_LABEL_TRACK_CLICKS") ?>:
            </label>
        </td>
        <td width="60%">
            <?php
            $trackClicks = Option::get($curModuleName, 'trackClicks', 'Y') === 'Y';
            ?>
            <input type="checkbox" value="Y" name="options[trackClicks]" <?=$trackClicks?'checked':''?>>
        </td>
    </tr>

    <?php
    $tabControl->Buttons([
        "btnSave" => true,
        "btnApply" => true,
        "btnCancel" => true,
        "back_url" => $APPLICATION->GetCurUri(),
    ]);
    $tabControl->End();
    ?>
</form>