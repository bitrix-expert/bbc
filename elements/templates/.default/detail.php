<?if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();?>

<?$APPLICATION->IncludeComponent('basis:elements.detail', '.default', array(
        'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],
        'IBLOCK_ID' => $arParams['IBLOCK_ID'],
        'ELEMENT_ID' => $arParams['ELEMENT_ID'],
        'SELECT_FIELDS' => $arParams['DETAIL_SELECT_FIELDS'],
        'SELECT_PROPS' => $arParams['DETAIL_SELECT_PROPS'],
        'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
        'CACHE_TYPE' => $arParams['CACHE_TYPE'],
        'CACHE_TIME' => $arParams['CACHE_TIME'],
        'SET_404' => $arParams['SET_404'],
        'DATE_FORMAT' => $arParams['DETAIL_DATE_FORMAT'],
        'SET_SEO_TAGS' => $arParams['SET_SEO_TAGS']
    ),
    $component
);?>