<?if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();?>

<h1>Index page</h1>

<?foreach ($arResult['ELEMENTS'] as $element) {?>
    <h2><a href="<?=$element['DETAIL_PAGE_URL']?>"><?=$element['NAME']?></a></h2>
<?}?>