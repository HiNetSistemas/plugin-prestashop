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

<script>
	$(document).ready(function() {
		$("#refund-tp-button").click(function(){
			$('#message').html("");
			
			$.ajax({
				type: "POST",
				accepts: "application/json",
				data: { 
			        'amount': $("#input-amount-dev").val(),
			        'order': {$order_id},
			        'orderOperation': {$orderIdTPOperation},
			    },
			 	url: "{$url_refund}",
			 	beforeSend: function(){

			 	},
			 	success: function(data){
			 		if(data.length != null){
			 			response = $.parseJSON(data);
			 			$('#message').html(response.StatusMessage);
			 		}else{
			 			$('#message').html("Error de servicio, vuelva a intentarlo en unos minutos");
			 		}
			 		
			    },
			    error: function(data){
			    	
			    }
			});

		});

	});	
</script>
<style>
	#refund-tp-button{
		margin-top: 5px 0 5px 0 !important;
	}
	#wrapper-devolution{
		padding:17px 0px 15px 23px !important;
		margin:0 0 10px 0 !important;
	}
	.input-dev{
		margin:0 0 10px 0;
	}
	.tr-content-dev{
    	border-bottom: solid 1px #EAEDEF !important;
	}
	.table-tr{
		margin:2px 0 2px 0;
	}
	.table-td{
		padding:4px 25px 4px 0;
	}
	#input-amount-dev{
		width:150px;
	}
	#message{
		margin:5px 0 0 0;
		color:red;
	}
</style>
<!-- Tab Contifico -->
<div class="tab-pane" id="contifico">
	<b> Estado de la orden: </b> <br>
	
	{$status}
</div>
<div class="tab-pane" id="contifico-documento">
	<b> Devolucion total o parcial: </b><br><br>
	<div class="panel panel-total" id="wrapper-devolution">
		<div class="table-responsive">
			<table>
				<tr class="table-tr">
					<td class="table-td">Precio: </td>
					<td class="table-td">{$precio} USD</td>
				</tr>
				<tr class="table-tr">
					<td class="table-td">Transporte:</td>
					<td class="table-td">{$envio} USD</td>
				</tr>
				<tr class="table-tr">
					<td class="table-td"><strong>Total:<strong></td>
					<td class="table-td"><strong>{$total} USD</strong></td>
				</tr>
			</table>
		</div>
	</div>
	<div>
		<td class="partial_refund_fields current-edit" style="">
			<div class="input-group input-dev">
				<div class="input-group-addon">
					 USD
				</div>
				<input type="text" name="partialRefundShippingCost" value="0" id="input-amount-dev">
			</div>
		</td>
		<a href="#refund-devoluciones" class="btn btn-default" id="refund-tp-button">
			<i class="icon-exchange"></i>
			Reembolso
		</a>

		<div id="message"></div>
	</div>	
</div>
