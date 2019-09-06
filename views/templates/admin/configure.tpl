{*
* 2019 Troyan Technology
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@troyantechnoloy.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade TroyanTechnology to newer
* versions in the future. If you wish to customize TroyanTechnology for your
* needs please refer to https://www.troyantechnoloy.com for more information.
*
*  @author    Troyan Technology <info@troyantechnoloy.com>
*  @copyright 2019 Troyan Technology
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Troyan Technology
*}

<script src="{$url_base}modules/hn24/js/fancybox/source/jquery.fancybox.js"></script>
<script src="{$url_base}modules/hn24/js/fancybox/lib/jquery.mousewheel-3.0.6.pack.js"></script>

<!-- Add jQuery library -->
<script type="text/javascript" src="{$url_base}modules/hn24/js/fancybox/lib/jquery-1.10.1.min.js"></script>

<!-- Add mousewheel plugin (this is optional) -->
<script type="text/javascript" src="{$url_base}modules/hn24/js/fancybox/lib/jquery.mousewheel-3.0.6.pack.js"></script>

<!-- Add fancyBox main JS and CSS files -->
<script type="text/javascript" src="{$url_base}modules/hn24/js/fancybox/source/jquery.fancybox.js?v=2.1.5"></script>

<!-- Add Thumbnail helper (this is optional) -->
<link rel="stylesheet" type="text/css" href="{$url_base}modules/hn24/js/fancybox/source/helpers/jquery.fancybox-thumbs.css?v=1.0.7" />
<script type="text/javascript" src="{$url_base}modules/hn24/js/fancybox/source/helpers/jquery.fancybox-thumbs.js?v=1.0.7"></script>

<!-- Add Media helper (this is optional) -->
<script type="text/javascript" src="{$url_base}modules/hn24/js/fancybox/source/helpers/jquery.fancybox-media.js?v=1.0.6"></script>

<!-- Add version compare js -->
<script type="text/javascript" src="{$url_base}modules/hn24/js/version_compare/version_compare.js"></script>

<!-- check_version js -->
<script type="text/javascript" src="{$url_base}modules/hn24/js/version_compare/check_version.js"></script>

<script type="text/javascript">
	$(document).ready(function() {
            
        check_last_version("{$version}");
                
		$("#fieldset_0_1_1").css("display","none");

		$('.fancybox').fancybox({
			'titleShow': false
		});

		//desabilita el boton credencial de produccion o test segun el ambiente habilitado
		if(ambienttype() == "production"){
			$("#fieldset_0_2_2").find("#cred-button").attr("disabled", "disabled");
		}else{
			$("#fieldset_0_3_3").find("#cred-button").attr("disabled", "disabled");
		}

		function ambienttype() {
			section = $("#fieldset_0").find(".prestashop-switch:eq(1)"); 	
			return (section.find("#modo_on").attr('checked') == "checked")? "production": "developer";
		}
});

</script>
<div class="tab-content panel">	
	<!-- Tab Configuracion -->
	<div id="general">
                <div id="panel_actualizacion_disponible" class="alert alert-warning">
                    <span class="text-error">Se encuentra disponible una versi&oacute;n m&aacute;s reciente del plugin de Contifico, puede consultarla desde <a target="_blank" href="#">aqu&iacute;</a></span>
                </div>
            
		<div class="panel">
			<div class="panel-heading">
				<i class="icon-cogs"></i>Versi&oacute;n utilizada
			</div>
			Utilizando la versi&oacute;n: {$version}
		</div>	
		{$config_general}		
	</div>
	<div class="panel">
   		<h3><i class="icon icon-tags"></i> {l s='Information' d='Modules.hn24.Admin'}</h3>
   		<p>
    	1. <strong>{l s='Automatically:' d='Modules.hn24.Admin'}</strong> {l s='Ask your hosting provider to setup a "Cron job" to load the following URL at the time you would like:' d='Modules.hn24.Admin'}
      	<a href="{$hn24_cron|escape:'htmlall':'UTF-8'}" target="_blank">{$hn24_cron|escape:'htmlall':'UTF-8'}</a><br>
      	{l s='It will automatically sincronize hn24.' d='Modules.hn24.Admin'}
   		</p>
	</div>
</div>
