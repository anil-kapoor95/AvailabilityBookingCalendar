<?php
/**
 * Modern design: booking progress bar ("Step 2 of 4" + Dates / Details / Review / Done).
 * Expects $modern_step (1-4). Printed only when the calendar uses the Modern design.
 * Texts are editable language labels (modern_step_*); English is used if a label is missing.
 */
$stepOpt = isset($tpl['option_arr']) && is_array($tpl['option_arr']) ? $tpl['option_arr'] : (isset($controller->option_arr) ? $controller->option_arr : array());
if (isset($stepOpt['o_theme']) && $stepOpt['o_theme'] == 'modern' && isset($modern_step))
{
	$stepDefaults = array(
		'modern_step_dates' => 'Dates',
		'modern_step_details' => 'Details',
		'modern_step_review' => 'Review',
		'modern_step_done' => 'Done',
		'modern_step_of' => 'Step %s of %s'
	);
	$stepText = array();
	foreach ($stepDefaults as $stepKey => $stepDefault)
	{
		$t = __($stepKey, true);
		$t = is_string($t) ? trim($t) : '';
		$stepText[$stepKey] = ($t === '' || $t === $stepKey || preg_match('/^[?\s]+$/u', $t)) ? $stepDefault : $t;
	}
	$stepNames = array($stepText['modern_step_dates'], $stepText['modern_step_details'], $stepText['modern_step_review'], $stepText['modern_step_done']);
	$stepOf = @sprintf($stepText['modern_step_of'], $modern_step, 4);
	if ($modern_step < 2)
	{
		# The calendar step shows no progress bar. The marker switches on the compact calendar layout (single-month view only)
		$calView = isset($controller) && $controller->_get->check('view') ? $controller->_get->toInt('view') : 1;
		if ($calView <= 1)
		{
			$calText = array('modern_cal_stay' => 'Your stay', 'modern_cal_none' => 'No dates selected', 'modern_cal_clear' => 'Clear');
			foreach ($calText as $calKey => $calDefault)
			{
				$t = __($calKey, true);
				$t = is_string($t) ? trim($t) : '';
				$calText[$calKey] = ($t === '' || $t === $calKey || preg_match('/^[?\s]+$/u', $t)) ? $calDefault : $t;
			}
			$calIso = 'en';
			if (isset($tpl['locale_arr']) && is_array($tpl['locale_arr']) && isset($controller) && method_exists($controller, 'pjActionGetLocale'))
			{
				$calLocale = $controller->pjActionGetLocale();
				foreach ($tpl['locale_arr'] as $calL)
				{
					if ((int) $calL['id'] === (int) $calLocale && !empty($calL['language_iso']))
					{
						$calIso = $calL['language_iso'];
						break;
					}
				}
			}
			$calDays = isset($stepOpt['o_price_based_on']) && $stepOpt['o_price_based_on'] == 'days';
			$calCont = __('bf_continue', true);
			$calCont = (is_string($calCont) && trim($calCont) !== '' && $calCont !== 'bf_continue' && !preg_match('/^[?\s]+$/u', $calCont)) ? $calCont : 'Continue';
			$calUnits = array();
			foreach (array('bf_day' => 'day', 'bf_days' => 'days', 'bf_night' => 'night', 'bf_nights' => 'nights') as $uk => $ud)
			{
				$t = __($uk, true);
				$calUnits[$uk] = (is_string($t) && trim($t) !== '' && $t !== $uk && !preg_match('/^[?\s]+$/u', $t)) ? $t : $ud;
			}
			?>
			<i class="abModernCal" hidden
				data-lang="<?php echo htmlspecialchars($calIso); ?>"
				data-tz="<?php echo (int) date('Z'); ?>"
				data-days="<?php echo $calDays ? 1 : 0; ?>"
				data-one="<?php echo htmlspecialchars($calDays ? $calUnits['bf_day'] : $calUnits['bf_night']); ?>"
				data-many="<?php echo htmlspecialchars($calDays ? $calUnits['bf_days'] : $calUnits['bf_nights']); ?>"
				data-stay="<?php echo htmlspecialchars($calText['modern_cal_stay']); ?>"
				data-none="<?php echo htmlspecialchars($calText['modern_cal_none']); ?>"
				data-clear="<?php echo htmlspecialchars($calText['modern_cal_clear']); ?>"
				data-continue="<?php echo htmlspecialchars($calCont); ?>"></i>
			<?php
		}
	} else {
	?>
	<div class="abProg" role="group" aria-label="<?php echo htmlspecialchars($stepOf); ?>">
		<div class="abProgT"><b><?php echo htmlspecialchars($stepOf); ?></b></div>
		<div class="abProgSeg"><?php foreach ($stepNames as $i => $n) { ?><i class="<?php echo $i < $modern_step ? 'on' : ''; ?>"></i><?php } ?></div>
		<div class="abProgLab"><?php foreach ($stepNames as $i => $n) { ?><span class="<?php echo $i < $modern_step ? 'on' : ''; ?><?php echo $i + 1 == $modern_step ? ' cur' : ''; ?>"><?php echo htmlspecialchars($n); ?></span><?php } ?></div>
	</div>
	<?php
		# Section titles of the details and review pages (editable language labels, English if missing)
		$secDefaults = array(
			'modern_sec_contact' => 'Contact', 'modern_sec_address' => 'Address', 'modern_sec_confirm' => 'Confirm',
			'modern_rv_stay' => 'Your stay', 'modern_rv_details' => 'Your details', 'modern_rv_price' => 'Price', 'modern_rv_edit' => 'Edit'
		);
		$secText = array();
		foreach ($secDefaults as $secKey => $secDefault)
		{
			$t = __($secKey, true);
			$t = is_string($t) ? trim($t) : '';
			$secText[$secKey] = ($t === '' || $t === $secKey || preg_match('/^[?\s]+$/u', $t)) ? $secDefault : $t;
		}
		?>
	<i class="abModernSec" hidden
		data-contact="<?php echo htmlspecialchars($secText['modern_sec_contact']); ?>"
		data-address="<?php echo htmlspecialchars($secText['modern_sec_address']); ?>"
		data-confirm="<?php echo htmlspecialchars($secText['modern_sec_confirm']); ?>"
		data-stay="<?php echo htmlspecialchars($secText['modern_rv_stay']); ?>"
		data-details="<?php echo htmlspecialchars($secText['modern_rv_details']); ?>"
		data-price="<?php echo htmlspecialchars($secText['modern_rv_price']); ?>"
		data-edit="<?php echo htmlspecialchars($secText['modern_rv_edit']); ?>"></i>
	<?php
	}
}
?>
