<?php
$reservation_statuses = __('reservation_statuses', true);
$map_statuses = array('Confirmed' => 'ok', 'Pending' => 'pe', 'Cancelled' => 'ca');
$has_update_booking = pjAuth::factory('pjAdminReservations', 'pjActionUpdate')->hasAccess();
$can_res_index = pjAuth::factory('pjAdminReservations', 'pjActionIndex')->hasAccess();
$can_res_add = pjAuth::factory('pjAdminReservations', 'pjActionCreate')->hasAccess();
$can_vch_add = pjObject::getPlugin('pjVouchers') !== NULL && pjAuth::factory('pjVouchers', 'pjActionCreate')->hasAccess();
$self = $_SERVER['PHP_SELF'];
$nightsOf = function ($val) use ($tpl) {
	$n = (int) (new DateTime($val['date_to']))->diff(new DateTime($val['date_from']))->format('%a');
	if (isset($tpl['calendar_option_arr'][$val['calendar_id']]) && $tpl['calendar_option_arr'][$val['calendar_id']]['o_price_based_on'] == 'days') { $n += 1; }
	return $n;
};
$initials = function ($name) {
	$name = trim((string) $name);
	if ($name === '') { return '?'; }
	$p = preg_split('/\s+/', $name);
	$s = mb_substr($p[0], 0, 1);
	if (count($p) > 1) { $s .= mb_substr(end($p), 0, 1); } else { $s .= mb_substr($p[0], 1, 1); }
	return mb_strtoupper($s);
};
$money = function ($val) use ($tpl) {
	$cur = isset($tpl['calendar_option_arr'][$val['calendar_id']]) ? $tpl['calendar_option_arr'][$val['calendar_id']]['o_currency'] : NULL;
	return pjCurrency::formatPrice($val['amount'] + $val['tax'], " ", NULL, $cur);
};
$dn = __('days', true); $mn = __('months', true); ksort($mn);
$todayLabel = (is_array($dn) && isset($dn[date('w')]) && is_array($mn) && isset($mn[date('n')]))
	? $dn[date('w')] . ', ' . date('j') . ' ' . $mn[date('n')] . ' ' . date('Y')
	: date($tpl['option_arr']['o_date_format']);
$sentence = function ($plural, $single, $n) {
	return sprintf(__($n > 1 ? $plural : $single, true), '<strong>' . $n . '</strong>');
};
?>
<style>
.dbx{--b:#5b2a86;--bd:#3f1a61;--bl:#f1e9f8;--ink:#1f1a2b;--mut:#7a7488;--line:#ebe8f0;--r:14px;--sh:0 1px 2px rgba(31,26,43,.05),0 6px 18px rgba(31,26,43,.05);color:var(--ink)}
.dbx *{box-sizing:border-box}
.dbx a{text-decoration:none}
.dbx-head{display:flex;justify-content:space-between;align-items:flex-end;gap:16px;flex-wrap:wrap;margin:6px 0 22px}
.dbx-head h1{font-size:26px;font-weight:700;margin:0;letter-spacing:-.2px}
.dbx-eyebrow{display:inline-flex;align-items:center;gap:7px;background:var(--bl);color:var(--b);font-weight:700;font-size:12px;padding:5px 12px;border-radius:99px;margin-bottom:10px}
.dbx-pills{display:flex;gap:10px;flex-wrap:wrap;margin-top:12px}
.dbx-pill{display:inline-flex;align-items:center;gap:8px;background:#fff;border:1px solid var(--line);border-radius:99px;padding:6px 14px;font-size:13px;color:var(--mut)}
.dbx-pill strong{color:var(--ink)}
.dbx-pill i{width:8px;height:8px;border-radius:50%;background:#17a673}.dbx-pill.out i{background:#c7791a}
.dbx-head p{color:var(--mut);margin:4px 0 0}
.dbx-acts{display:flex;gap:10px;flex-wrap:wrap}
.dbx-btn{display:inline-flex;align-items:center;gap:8px;border:1px solid var(--line);background:#fff;color:var(--ink);padding:10px 16px;border-radius:10px;font-weight:600;font-size:13px;cursor:pointer;line-height:1.2}
.dbx-btn:hover{color:var(--ink);background:#faf9fc}
.dbx-btn.p{background:var(--b);border-color:var(--b);color:#fff}
.dbx-btn.p:hover{background:var(--bd);color:#fff}
.dbx-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-bottom:20px}
.dbx-kpi{background:#fff;border-radius:var(--r);padding:18px 20px;box-shadow:var(--sh);position:relative;color:var(--ink);display:block}
.dbx-kpi:hover{color:var(--ink)}
.dbx-kpi .ic{width:38px;height:38px;border-radius:11px;display:grid;place-items:center;background:var(--bl);color:var(--b);margin-bottom:14px}
.dbx-kpi .ic svg{width:19px;height:19px}
.dbx-kpi .lb{color:var(--mut);font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.5px}
.dbx-kpi .val{font-size:32px;font-weight:700;margin:2px 0 4px;letter-spacing:-.5px;line-height:1.2}
.dbx-kpi .sub{color:var(--mut);font-size:13px;min-height:19px}
.dbx-kpi .sub b{color:var(--ink)}
.dbx-kpi .tag{position:absolute;top:18px;right:18px;font-size:11px;font-weight:700;color:var(--b);background:var(--bl);padding:3px 10px;border-radius:99px}
.dbx-kpi.hero{background:linear-gradient(135deg,var(--b),var(--bd));color:#fff}
.dbx-kpi.hero .ic{background:rgba(255,255,255,.16);color:#fff}
.dbx-kpi.hero .lb,.dbx-kpi.hero .sub{color:#e2d3f1}.dbx-kpi.hero .sub b{color:#fff}
.dbx-kpi.hero .tag{background:rgba(255,255,255,.18);color:#fff}
.dbx-grid{display:grid;grid-template-columns:minmax(0,1.7fr) minmax(0,1fr);gap:20px;align-items:start}
.dbx-card{background:#fff;border-radius:var(--r);box-shadow:var(--sh);overflow:hidden}
.dbx-ch{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:18px 20px 14px}
.dbx-ch h2{font-size:17px;font-weight:700;margin:0}
.dbx-ch small{color:var(--mut);display:block;margin-top:3px;font-size:12.5px}
.dbx-link{font-weight:600;font-size:13px}
.dbx-scroll{overflow-x:auto}
.dbx table{width:100%;border-collapse:collapse}
.dbx th{font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:var(--mut);text-align:left;padding:10px 12px;background:#faf9fc;border-top:1px solid var(--line);border-bottom:1px solid var(--line);white-space:nowrap}
.dbx td{padding:12px;border-bottom:1px solid var(--line);vertical-align:middle;white-space:nowrap}
.dbx tr:last-child td{border-bottom:0}
.dbx th:first-child,.dbx td:first-child{padding-left:20px}.dbx th:last-child,.dbx td:last-child{padding-right:20px}
.dbx tbody tr:hover{background:#fcfbfe}
.dbx .cl{display:flex;align-items:center;gap:11px}
.dbx .cl .a{width:34px;height:34px;border-radius:50%;background:var(--bl);color:var(--b);display:grid;place-items:center;font-weight:700;font-size:12px;flex:none}
.dbx .cl b{display:block;font-weight:600;color:var(--ink)}
.dbx .cl span{color:var(--mut);font-size:12px}
.dbx .mut{color:var(--mut)}
.dbx .amt{font-weight:700;text-align:right}
.dbx .chip{display:inline-block;padding:3px 11px;border-radius:99px;font-size:11.5px;font-weight:700}
.dbx .chip.ok{background:#e3f6ee;color:#17a673}.dbx .chip.pe{background:#fdf0dc;color:#c7791a}.dbx .chip.ca{background:#fde8e8;color:#d64545}
.dbx-stack{display:grid;gap:20px}
.dbx-tl{padding:2px 20px 18px;display:grid;gap:12px}
.dbx-ev{display:grid;grid-template-columns:44px minmax(0,1fr) auto;gap:12px;align-items:center;padding:12px;border:1px solid var(--line);border-radius:12px;color:var(--ink)}
.dbx-ev:hover{color:var(--ink);background:#fcfbfe}
.dbx-ev .d{width:44px;height:48px;border-radius:10px;background:var(--bl);color:var(--b);text-align:center;font-weight:700;line-height:1.1;padding-top:7px;font-size:16px}
.dbx-ev .d small{display:block;font-size:9.5px;letter-spacing:.5px;text-transform:uppercase;font-weight:700}
.dbx-ev b{display:block;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.dbx-ev span.t{color:var(--mut);font-size:12.5px;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.dbx-ev .r{text-align:right;font-size:12.5px}.dbx-ev .r strong{display:block;margin-top:5px}
.dbx-count{background:var(--b);color:#fff;border-radius:99px;font-size:11px;font-weight:700;padding:2px 9px;margin-left:8px;vertical-align:2px}
.dbx-count.in{background:#17a673}.dbx-count.out{background:#c7791a}
.dbx-empty{padding:6px 20px 24px;color:var(--mut)}
.dbx a:focus-visible,.dbx-btn:focus-visible{outline:2px solid var(--b);outline-offset:2px}
.dbx .dbx-link,.dbx .dbx-link:focus{color:var(--b)}
.dbx .dbx-link:hover{color:var(--bd);text-decoration:underline}
.dbx .dbx-btn:not(.p){color:var(--b);border-color:var(--b)}
.dbx .dbx-btn:not(.p):hover,.dbx .dbx-btn:not(.p):focus{color:var(--b);border-color:var(--b);background:var(--bl)}
.dbx .cl:hover b,.dbx .cl:focus b{color:var(--b)}
.dbx .dbx-ev:hover,.dbx .dbx-ev:focus{border-color:var(--b);color:var(--ink)}
.dbx .dbx-kpi:hover{color:var(--ink)}
@media(max-width:1200px){.dbx-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.dbx-grid{grid-template-columns:minmax(0,1fr)}}
@media(max-width:560px){.dbx-kpis{grid-template-columns:minmax(0,1fr)}}
</style>
<div class="wrapper wrapper-content animated fadeInRight">
<div class="dbx">
	<span id="dbxProbe" class="btn btn-primary" style="display:none"></span>
	<div class="dbx-head">
		<div>
			<div class="dbx-eyebrow"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg><?php echo $todayLabel; ?></div>
			<h1><?php __('menuDashboard'); ?></h1>
			<div class="dbx-pills">
				<span class="dbx-pill in"><i></i><span><?php echo $sentence('dash_you_have_bookings_arriving_today', 'dash_you_have_booking_arriving_today', count($tpl['arriving_today_arr'])); ?></span></span>
				<span class="dbx-pill out"><i></i><span><?php echo $sentence('dash_you_have_bookings_leaving_today', 'dash_you_have_booking_leaving_today', count($tpl['leaving_today_arr'])); ?></span></span>
			</div>
		</div>
		<div class="dbx-acts">
			<?php if ($can_vch_add) { ?>
			<a class="dbx-btn" href="<?php echo $self; ?>?controller=pjVouchers&amp;action=pjActionCreate"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="8" width="18" height="13" rx="2"/><path d="M12 8v13M3 13h18"/></svg><?php __('plugin_vouchers_btn_add_voucher'); ?></a>
			<?php } ?>
			<?php if ($can_res_add) { ?>
			<a class="dbx-btn p" href="<?php echo $self; ?>?controller=pjAdminReservations&amp;action=pjActionCreate"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M12 5v14M5 12h14"/></svg><?php __('btnAddReservation'); ?></a>
			<?php } ?>
		</div>
	</div>

	<div class="dbx-kpis">
		<div class="dbx-kpi hero">
			<span class="tag"><?php __('dash_today'); ?></span>
			<div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg></div>
			<div class="lb"><?php __('dash_new_reservations'); ?></div>
			<div class="val"><?php echo (int) @$tpl['cnt_bookings_today']; ?></div>
			<div class="sub"><b><?php echo pjCurrency::formatPrice($tpl['total_amount_today']); ?></b></div>
		</div>
		<div class="dbx-kpi">
			<span class="tag"><?php __('dash_this_month'); ?></span>
			<div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg></div>
			<div class="lb"><?php __('dash_total_reservations'); ?></div>
			<div class="val"><?php echo (int) $tpl['cnt_bookings_this_month']; ?></div>
			<div class="sub"><b><?php echo pjCurrency::formatPrice($tpl['total_amount_this_month']); ?></b></div>
		</div>
		<div class="dbx-kpi">
			<div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="8" r="3.5"/><path d="M2 20a7 7 0 0 1 14 0M17 4.5a3.5 3.5 0 0 1 0 7M22 20a6 6 0 0 0-4-5.6"/></svg></div>
			<div class="lb"><?php __('lblDashUsers'); ?></div>
			<div class="val"><?php echo (int) $tpl['cnt_users']; ?></div>
			<div class="sub">&nbsp;</div>
		</div>
		<div class="dbx-kpi">
			<div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M3 9h18"/></svg></div>
			<div class="lb"><?php __('lblDashCalendars'); ?></div>
			<div class="val"><?php echo (int) $tpl['cnt_calendars']; ?></div>
			<div class="sub">&nbsp;</div>
		</div>
	</div>

	<div class="dbx-grid">
		<div class="dbx-card">
			<div class="dbx-ch">
				<div>
					<h2><?php __('lblDashLatestReservations'); ?></h2>
					<small><?php echo $sentence('dash_you_received_bookings', 'dash_you_received_booking', count($tpl['latest_reservation_arr'])); ?></small>
				</div>
				<?php if ($can_res_index) { ?><a class="dbx-btn" href="<?php echo $self; ?>?controller=pjAdminReservations&amp;action=pjActionIndex"><?php __('dash_view_all'); ?></a><?php } ?>
			</div>
			<?php if ($tpl['latest_reservation_arr']) { ?>
			<div class="dbx-scroll">
			<table>
				<thead><tr>
					<th><?php __('lblReservationClient'); ?></th><th><?php __('lblReservationCalendar'); ?></th><th><?php __('lblReservationDates'); ?></th><th><?php __('lblDashNights'); ?></th><th><?php __('lblStatus'); ?></th><th style="text-align:right"><?php __('lblReservationTotalPrice'); ?></th>
				</tr></thead>
				<tbody>
				<?php foreach ($tpl['latest_reservation_arr'] as $val) {
					$url = $has_update_booking ? $self . '?controller=pjAdminReservations&amp;action=pjActionUpdate&amp;id=' . $val['id'] : 'javascript:void(0);'; ?>
					<tr>
						<td><a class="cl" href="<?php echo $url; ?>"><div class="a"><?php echo pjSanitize::html($initials($val['c_name'])); ?></div><div><b><?php echo pjSanitize::html($val['c_name']); ?></b><span><?php echo pjSanitize::html($val['uuid']); ?></span></div></a></td>
						<td><?php echo pjSanitize::html($val['calendar_name']); ?></td>
						<td class="mut"><?php echo date($tpl['option_arr']['o_date_format'], strtotime($val['date_from'])); ?> &ndash; <?php echo date($tpl['option_arr']['o_date_format'], strtotime($val['date_to'])); ?></td>
						<td><?php echo $nightsOf($val); ?></td>
						<td><span class="chip <?php echo @$map_statuses[$val['status']]; ?>"><?php echo @$reservation_statuses[$val['status']]; ?></span></td>
						<td class="amt"><?php echo $money($val); ?></td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
			</div>
			<?php } else { ?>
				<p class="dbx-empty"><?php __('dashboard_reservations_empty'); ?></p>
			<?php } ?>
		</div>

		<div class="dbx-stack">
		<?php
		$blocks = array(
			array('arriving_today_arr', 'dash_reservations_arriving_today', 'dash_you_have_bookings_arriving_today', 'dash_you_have_booking_arriving_today', 'in'),
			array('leaving_today_arr', 'dash_reservations_leaving_today', 'dash_you_have_bookings_leaving_today', 'dash_you_have_booking_leaving_today', 'out'),
		);
		foreach ($blocks as $b) { $list = $tpl[$b[0]]; ?>
			<div class="dbx-card">
				<div class="dbx-ch">
					<div>
						<h2><?php __($b[1]); ?><span class="dbx-count <?php echo $b[4]; ?>"><?php echo count($list); ?></span></h2>
						<small><?php echo $sentence($b[2], $b[3], count($list)); ?></small>
					</div>
					<?php if ($can_res_index) { ?><a class="dbx-link" href="<?php echo $self; ?>?controller=pjAdminReservations&amp;action=pjActionIndex"><?php __('dash_view_all'); ?></a><?php } ?>
				</div>
				<?php if ($list) { ?>
				<div class="dbx-tl">
				<?php foreach ($list as $val) {
					$url = $has_update_booking ? $self . '?controller=pjAdminReservations&amp;action=pjActionUpdate&amp;id=' . $val['id'] : 'javascript:void(0);';
					$ts = strtotime($b[4] == 'in' ? $val['date_from'] : $val['date_to']); ?>
					<a class="dbx-ev" href="<?php echo $url; ?>">
						<div class="d"><small><?php echo date('M', $ts); ?></small><?php echo date('j', $ts); ?></div>
						<div><b><?php echo pjSanitize::html($val['c_name']); ?></b><span class="t"><?php echo pjSanitize::html($val['calendar_name']); ?> &middot; <?php echo pjSanitize::html(__('lblDashNights', true)); ?>: <?php echo $nightsOf($val); ?></span></div>
						<div class="r"><span class="chip <?php echo @$map_statuses[$val['status']]; ?>"><?php echo @$reservation_statuses[$val['status']]; ?></span><strong><?php echo $money($val); ?></strong></div>
					</a>
				<?php } ?>
				</div>
				<?php } else { ?>
					<p class="dbx-empty"><?php __('dashboard_reservations_empty'); ?></p>
				<?php } ?>
			</div>
		<?php } ?>
		</div>
	</div>
</div>
</div>
<script>
// follow the admin theme colour (same source as the primary buttons)
(function () {
	function brand() {
		var probe = document.getElementById('dbxProbe');
		if (!probe) { return; }
		var c = getComputedStyle(probe).backgroundColor;
		var m = c.match(/\d+(\.\d+)?/g);
		if (!m || m.length < 3) { return; }
		var rgb = [+m[0], +m[1], +m[2]];
		if (m[3] !== undefined && +m[3] === 0) { return; }
		var root = document.querySelector('.dbx');
		root.style.setProperty('--b', 'rgb(' + rgb.join(',') + ')');
		root.style.setProperty('--bd', 'rgb(' + rgb.map(function (v) { return Math.round(v * .72); }).join(',') + ')');
		root.style.setProperty('--bl', 'rgba(' + rgb.join(',') + ',.12)');
	}
	brand();
	window.addEventListener('load', brand);
})();
</script>
