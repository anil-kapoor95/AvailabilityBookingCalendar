<?php
/**
 * Modern design: stay summary chip (calendar badge + dates + nights + change link).
 * Needs $STORAGE (start_dt / end_dt) and $tpl['option_arr']; printed only for the Modern design.
 */
if (isset($tpl['option_arr']['o_theme']) && $tpl['option_arr']['o_theme'] == 'modern' && !empty($STORAGE['start_dt']) && !empty($STORAGE['end_dt']))
{
	$stayNights = ceil(($STORAGE['end_dt'] - $STORAGE['start_dt']) / 86400);
	if ($tpl['option_arr']['o_price_based_on'] == 'days')
	{
		$stayNights += 1;
		$stayLen = sprintf("%u %s", $stayNights, $stayNights > 1 ? __('bf_days', true) : __('bf_day', true));
	} else {
		$stayLen = sprintf("%u %s", $stayNights, $stayNights > 1 ? __('bf_nights', true) : __('bf_night', true));
	}
	?>
	<div class="abStay">
		<div class="abStayCal"><small><?php
			$stay_months = __('months', true);
			$stay_m = (int) date('n', $STORAGE['start_dt']);
			$stay_name = (is_array($stay_months) && !empty($stay_months[$stay_m])) ? $stay_months[$stay_m] : date('F', $STORAGE['start_dt']);
			echo htmlspecialchars(function_exists('mb_substr') ? mb_substr($stay_name, 0, 3, 'UTF-8') : substr($stay_name, 0, 3));
		?></small><b><?php echo date('j', $STORAGE['start_dt']); ?></b></div>
		<div class="abStayTx">
			<b><?php echo date($tpl['option_arr']['o_date_format'], $STORAGE['start_dt']); ?> &ndash; <?php echo date($tpl['option_arr']['o_date_format'], $STORAGE['end_dt']); ?></b>
			<span><?php echo htmlspecialchars($stayLen); ?></span>
		</div>
		<a href="#" class="abSelectorChangeDates"><?php __('lblChangeDates'); ?></a>
	</div>
	<?php
}
?>
