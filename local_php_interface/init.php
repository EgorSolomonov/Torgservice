<?
// 
// Ловим событие на почтовый шаблон нового заказа
// 
AddEventHandler("sale", "OnOrderNewSendEmail", "bxModifySaleMails");

function bxModifySaleMails($orderID, &$eventName, &$arFields)
{
    if (CModule::IncludeModule("sale") && CModule::IncludeModule("iblock")) {

        $strCustomOrderList = "";
        $phone = "";
        //-- разбираем корзину и получаем артикул товара дополнительно
        $dbBasketItems = CSaleBasket::GetList(
            array("NAME" => "ASC"),
            array("ORDER_ID" => $orderID),
            false,
            false,
            array("PRODUCT_ID", "NAME", "QUANTITY")
        );

        while ($arProps = $dbBasketItems->Fetch()) {
            // получаем артикул по "PRODUCT_ID"
            $productId = $arProps['PRODUCT_ID'];

            $arProduct = CIBlockElement::GetByID($productId)->GetNext();
            $article = "";

            $dbProperty = CIBlockElement::GetProperty($arProduct['IBLOCK_ID'], $arProduct['ID'], array(), array('CODE' => 'CML2_ARTICLE'));
            if ($arProp = $dbProperty->Fetch()) {
                $article = $arProp['VALUE'];
            }

            $strCustomOrderList .= $arProps['NAME'] . " Кол-во: " . $arProps['QUANTITY'] . " Артикул: " . $article . "<br/>";
        }
        //-- получаем телефон
        $order_props = CSaleOrderPropsValue::GetOrderProps($orderID);
        while ($arProps = $order_props->Fetch()) {
            if ($arProps["CODE"] == "PHONE") {
                $phone = htmlspecialchars($arProps["VALUE"]);
                break;
            }
        }
        //-- добавляем новые поля в массив результатов
        $arFields["PHONE"] = $phone;
        $arFields["ORDER_TABLE_ITEMS"] = $strCustomOrderList;
    }
}
