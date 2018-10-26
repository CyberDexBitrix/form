<?if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();?>

<?if(strlen($arResult["OK_MESSAGE"]) > 0){
	?><div class="alert alert-success"><?=$arResult["OK_MESSAGE"]?></div><?
}elseif($arParams['FIELDS_LIST']){?>    
    <form action="<?=POST_FORM_ACTION_URI?>" method="POST" enctype="multipart/form-data">
        
        <h2><?=$arParams['FORM_TITLE']?></h2>
        
        <?if(!empty($arResult["ERRORS"]['MESSAGE'])){?>
            <div class="alert alert-danger" role="alert"> 
                <?foreach($arResult["ERRORS"]['MESSAGE'] as $v) ShowError($v);?>
            </div>
        <?}?>
        
        <?=bitrix_sessid_post()?>
        
        <?foreach($arParams['FIELDS_LIST'] as $id => $name){?>
            <div class="form-group<?if($arResult['ERRORS']['FIELD'.$id]){?> has-feedback has-error<?}?>">
                
                <label for="field<?=$id?>" class="control-label"><?=$name?><?if(in_array($id,$arParams['REQUIRED_FIELDS'])){?>*<?}?></label>
                
                <?if($arParams['FIELD_'.$id.'_TYPE'] == 'textarea'){?>
                    <textarea class="form-control" placeholder="<?=$name?><?if(in_array($id,$arParams['REQUIRED_FIELDS'])){?>*" required="<?}?>" name="FIELDS[<?=$id?>]" id="FIELD<?=$id?>"><?=$_REQUEST["FIELDS"][$id]?></textarea>    
                <?}elseif($arParams['FIELD_'.$id.'_TYPE'] == 'list'){?>
                    <select class="js-select-style" name="FIELDS[<?=$id?>]">
                        <option value=""><?=$name?></option>
                        <?foreach($arParams['FIELD_'.$id.'_LIST'] as $key => $item){
                            if($item){?>
                                <option <?if($_REQUEST["FIELDS"][$id] == $item){?>selected="" <?}?>value="<?=$item?>"><?=$item?></option>
                            <?}?>
                        <?}?>
                    </select>
                <?}else{?>
                    <input class="form-control" placeholder="<?=$name?><?if(in_array($id,$arParams['REQUIRED_FIELDS'])){?>*" required="<?}?>" type="<?=$arParams['FIELD_'.$id.'_TYPE']?>" name="FIELDS[<?=$id?>]" id="FIELD<?=$id?>" value="<?=$_REQUEST["FIELDS"][$id]?>" />
                <?}?>
                
                <?if($arResult['ERRORS']['FIELD'.$id]){?>
                    <i style="" class="form-control-feedback bv-no-label glyphicon glyphicon-remove" data-bv-icon-for="Name"></i>
                    <small style="" class="help-block"><?=$arResult['ERRORS']['FIELD'.$id]?></small>
                <?}?>
            </div>
        <?}?>
    	
        <?if( $arParams['USE_RECAPTCHA'] == 'Y' ){?>
            <div class="form-group<?if($arResult['ERRORS']['CAPCHA']){?> has-feedback has-error<?}?>">
                <div class="g-recaptcha" data-sitekey="<?=RE_SITE_KEY?>"></div>
                <?if($arResult['ERRORS']['CAPCHA']){?>
                    <small style="" class="help-block"><?=$arResult['ERRORS']['CAPCHA']?></small>
                <?}?>
            </div>
            <script type="text/javascript">
                grecaptcha.render( $('.g-recaptcha')[0], { sitekey : '<?=RE_SITE_KEY?>' });
            </script>
        <?}?>
        
        <?if($arParams["USE_CAPTCHA"] == "Y"){?>
        	<div class="form-group<?if($arResult['ERRORS']['CAPCHA']){?> has-feedback has-error<?}?>">
        		<input type="hidden" name="captcha_sid" value="<?=$arResult["capCode"]?>" />
        		<img src="/bitrix/tools/captcha.php?captcha_sid=<?=$arResult["capCode"]?>" width="180" height="40" alt="CAPTCHA">        		
        		<input class="form-control" placeholder="Введите код с картинки" type="text" name="captcha_word" size="30" maxlength="50" value="" />
                
                <?if($arResult['ERRORS']['CAPCHA']){?>
                    <i style="" class="form-control-feedback bv-no-label glyphicon glyphicon-remove" data-bv-icon-for="Name"></i>
                    <small style="" class="help-block"><?=$arResult['ERRORS']['CAPCHA']?></small>
                <?}?>
            </div>
    	<?}?>
        
    	<input type="hidden" name="PARAMS_HASH" value="<?=$arResult["PARAMS_HASH"]?>" />
        
    	<input class="btn btn-default" type="submit" name="submit" value="Отправить" />
        
    </form>
<?}?>