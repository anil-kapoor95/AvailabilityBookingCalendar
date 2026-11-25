<?php 
if (isset($tpl['status']) && $tpl['status'] == 'IP_BLOCKED') {
	?>
	<div class="abForm abPaymentForm">
		<div class="abBox abGray">
			<span class="abError"><?php __('front_ip_address_blocked');?></span>
		</div>
	</div>
	<?php
} else {
	$modern_step = 4;
	include dirname(__FILE__) . '/elements/steps.php';
	?>
	<div class="abForm abPaymentForm">
		<div class="abBox abGray">
			<?php
			if (isset($stepOpt['o_theme']) && $stepOpt['o_theme'] == 'modern' && !empty($tpl['booking_arr']['uuid']))
			{
				$refLabel = __('modern_done_ref', true);
				$refLabel = (is_string($refLabel) && trim($refLabel) !== '' && $refLabel !== 'modern_done_ref' && !preg_match('/^[?\s]+$/u', $refLabel)) ? $refLabel : 'Booking ID';
				?><span class="abRef"><?php echo htmlspecialchars($refLabel . ': ' . $tpl['booking_arr']['uuid']); ?></span><?php
			}
			if (isset($tpl['get']['payment_method']))
			{
				$status = __('front_booking_status', true);
				if(isset($tpl['params']['plugin']) && !empty($tpl['params']['plugin']))
				{
					$payment_messages = __('payment_plugin_messages');
					?>
					<div>
						<?php echo isset($payment_messages[$tpl['booking_arr']['payment_method']]) ? $payment_messages[$tpl['booking_arr']['payment_method']]: ''; ?><br/>
						<?php
						if (pjObject::getPlugin($tpl['params']['plugin']) !== NULL)
						{
							$controller->requestAction(array('controller' => $tpl['params']['plugin'], 'action' => 'pjActionForm', 'params' => $tpl['params']));
						}
						?>
					</div>
					<?php
				}else{
					?>
					<div>
						<?php
						switch ($tpl['booking_arr']['payment_method'])
						{
							case 'bank':
								echo $status[1] . '<br/>' .  nl2br(pjSanitize::html($tpl['bank_account']));
								break;
							case 'creditcard':
							case 'cash':
							default:
								echo $status[1];
								break;
						}
						?>
					</div>
					<div>
						<button type="button" class="abButton abButtonDefault abStartOver <?php echo $controller->_get->check('multi') ? ($controller->_get->toInt('multi') == 1 ? 'abReturnToAvailability' : 'abReturnToCalendar') : 'abReturnToCalendar';?>"><?php __('front_start_over', false, true); ?></button>
					</div>
					<?php
				}
			}
			?>
		</div>
	</div>
<?php } ?>