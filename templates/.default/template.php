<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;
?>

<table>
    <tr>
        <th><?=Loc::getMessage("SODL_DOMAIN")?></th>
        <th><?=Loc::getMessage("SODL_QUANTITY")?></th>
    </tr>
    <?foreach ($arResult["ITEMS"] as $arItem):?>
        <tr>
            <td><?=$arItem["DOMAIN"]?></td>
            <td><?=$arItem["CNT"]?></td>
        </tr>
    <?endforeach;?>
</table>

