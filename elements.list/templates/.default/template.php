<?if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();?>

<?if ($arParams['DISPLAY_TOP_PAGER'] === 'Y') {?>
    <?=$arResult['NAV_STRING']?>
<?}?>

<?foreach ($arResult['ELEMENTS'] as $element) {echo '<pre>'; print_r($element); echo '</pre>';?>

    <h1><?=$element['NAME']?></h1>

<?}?>

<?if ($arParams['DISPLAY_BOTTOM_PAGER'] === 'Y') {?>
    <?=$arResult['NAV_STRING']?>
<?}?>