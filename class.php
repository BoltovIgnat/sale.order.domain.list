<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main,
    Bitrix\Sale;

class saleOrderDomainList extends CBitrixComponent {

    /**
     * Корректировать параметры
     * @param $arParams
     * @return array
     */
    public function onPrepareComponentParams($arParams) {

        if (empty($arParams["USER_GROUPS"]))
            $arParams["USER_GROUPS"] = [1];
        elseif (!is_array($arParams["USER_GROUPS"]))
            $arParams["USER_GROUPS"] = [$arParams["USER_GROUPS"]];

        $arParams["USER_GROUPS"] = array_map("intval", $arParams["USER_GROUPS"]);

        $arParams["PERSON_TYPE"] = (int) $arParams["PERSON_TYPE"] ?: 1;

        return parent::onPrepareComponentParams($arParams);

    }

    /**
     * Установить нужный модуль
     * @throws Main\SystemException
     */
    private function checkModule() {

        if (!Main\Loader::includeModule("sale"))
            throw new Main\SystemException(Main\Localization\Loc::getMessage("SODL_SALE_MODULE_NOT_INSTALLED"));

    }

    /**
     * Проверить права доступа
     * @throws Main\SystemException
     */
    private function chechAccess() {

        $userGroups = Main\UserTable::getUserGroupIds($this->getUserId());
        $intersect = array_intersect($userGroups, $this->arParams["USER_GROUPS"]);

        if (empty($intersect))
            throw new Main\SystemException(Main\Localization\Loc::getMessage("SODL_ACCESS_DENIED"));

    }

	public function executeComponent() {

        try {

            $this->checkModule();
            $this->chechAccess();

        } catch (Main\SystemException $e) {

            ShowError($e->getMessage());
            return;

        }

        $this->arResult["ITEMS"] = $this->getDomainList();

		$this->includeComponentTemplate();

	}

    /**
     * Список доменов с количеством
     * @return array|false
     */
    private function getDomainList() {

        //Определить свойства для E-mail
        $dbEmailProps = Sale\Internals\OrderPropsTable::getList([
            "filter" => [
                "PERSON_TYPE_ID" => $this->arParams["PERSON_TYPE"],
                "IS_EMAIL"       => "Y"
            ]
        ]);

        $arEmailPropID = 0;
        if($emailProp = $dbEmailProps->fetch())
            $arEmailPropID = $emailProp["ID"];

        if ($arEmailPropID <= 0) return [];

        $arResult = [];

        //Получаем список заказов группированный по домену
        $dbOrders = Sale\Internals\OrderTable::getList([
            "runtime" => [
                "EMAIL" => [
                    "data_type" => Sale\Internals\OrderPropsValueTable::getEntity(),
                    "reference" => array(
                        "=ref.ORDER_ID"      => "this.ID",
                        "=ref.ORDER_PROPS_ID" => new Main\DB\SqlExpression("?i", $arEmailPropID)
                    ),
                    "join_type" => "inner"
                ],
            ],
            "select" => [
                new Main\Entity\ExpressionField("DOMAIN", "SUBSTRING_INDEX(%s,'@',-1)", ["EMAIL.VALUE"]),
                new Main\Entity\ExpressionField("CNT", "COUNT(*)")
            ],
            "filter" => [
                "!DOMAIN" => false
            ],
            "order" => [
                "CNT" => "DESC"
            ]
        ]);

        while($arDomain = $dbOrders->fetch())
            $arResult[] = $arDomain;

        return $arResult;

    }

    /**
     * ИД текущего пользователя
     * @return int
     */
    private function getUserId() {

        global $USER;
        if (isset($USER) && $USER instanceof CUser)
            return (int)$USER->getId();

        return 0;

    }

}
