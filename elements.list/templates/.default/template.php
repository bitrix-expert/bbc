<?if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();?>

<?if ($arParams['DISPLAY_TOP_PAGER'] === 'Y') {?>
    <?=$arResult['NAV_STRING']?>
<?}?>

<?foreach ($arResult['ELEMENTS'] as $element) {?>

    <h1><a href="<?=$element['DETAIL_PAGE_URL']?>"><?=$element['NAME']?></a></h1>

<?}?>

<?if ($arParams['DISPLAY_BOTTOM_PAGER'] === 'Y') {?>
    <?=$arResult['NAV_STRING']?>
<?}?>