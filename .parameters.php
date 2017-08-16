<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main,
    Bitrix\Sale;

$siteId = isset($_REQUEST['src_site']) && is_string($_REQUEST['src_site']) ? $_REQUEST['src_site'] : '';
$siteId = substr(preg_replace('/[^a-z0-9_]/i', '', $siteId), 0, 2) ?: "s1";

if (!Main\Loader::includeModule("sale") || !Main\Loader::includeModule("main")) {
    ShowError(GetMessage("SODL_SALE_MODULE_NOT_INSTALLED"));
    die();
}

//Группа пользователей
$arUserGroups = [];

$dbUserGroups = Main\GroupTable::getList([
    "order" => [
        "ID"   => "ASC"
    ],
    "filter" => [
        "ACTIVE" => "Y"
    ]
]);
while($userGroup = $dbUserGroups->fetch())
    $arUserGroups[$userGroup["ID"]] = $userGroup["NAME"];


//Тип плательщика
$arPersonTypes = [];

$dbPersonTypes = Sale\PersonType::load($siteId);
foreach ($dbPersonTypes as $personType)
    $arPersonTypes[$personType["ID"]] = $personType["NAME"];


$arComponentParameters = [
    "PARAMETERS" => [
        "USER_GROUPS" => [
            "NAME"     => GetMessage("SODL_ACCESS_USER_GROUPS"),
            "TYPE"     => "LIST",
            "MULTIPLE" => "Y",
            "DEFAULT"  => [array_keys($arUserGroups)[0]],
            "VALUES"   => $arUserGroups,
        ],
        "PERSON_TYPE" => [
            "NAME"     => GetMessage("SODL_PERSON_TYPE"),
            "TYPE"     => "LIST",
            "DEFAULT"  => array_keys($arPersonTypes)[0],
            "VALUES"   => $arPersonTypes,
        ]
    ]
];

