<?php
/**
 * Central request-input normalisation (runs before any controller).
 * - strips NUL / control characters and zero-width characters
 * - trims leading/trailing whitespace (incl. NBSP / unicode spaces) from every value
 * - "name-like" fields: collapses inner whitespace runs to one space; whitespace-only => ''
 * - "token-like" fields (codes, captcha, uuid ...): all whitespace removed
 * - email fields: trimmed only (inner whitespace is left so format validation rejects it)
 * - passwords / card data / tokens are never touched
 * - free text (descriptions, notes, content, templates): inner spaces and line breaks preserved
 */
if (!function_exists('pjCleanInputValue'))
{
	function pjCleanInputKind($key)
	{
		$k = strtolower((string) $key);
		if (preg_match('/pass|pwd|csrf|token|^hash$|cc_|captcha_hash|^json|^cur_/', $k)) return 'skip';
		if (preg_match('/email/', $k)) return 'email';
		if (preg_match('/(^|_)(code|uuid|captcha|alpha_2|alpha_3|key|secret|username|login_user|sms_key|api)(_|$)|^code$/', $k)) return 'token';
		if (preg_match('/(^|_)(name|first_name|last_name|company|city|state|country|title|subject)$/', $k) || $k === 'name') return 'name';
		if (preg_match('/phone|(^|_)zip$|^number$/', $k)) return 'phone';
		return 'text';
	}

	function pjCleanInputValue($value, $kind)
	{
		if (!is_string($value)) return $value;
		if ($kind === 'skip') return $value;
		// control chars (keep \t \n \r) and zero-width / BOM / soft hyphen
		$value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
		$v = @preg_replace('/[\x{200B}-\x{200D}\x{2060}\x{FEFF}\x{00AD}]/u', '', $value);
		if ($v !== null) $value = $v;
		$ws = '[\s\x{00A0}\x{1680}\x{2000}-\x{200A}\x{2028}\x{2029}\x{202F}\x{205F}\x{3000}]';
		$t = @preg_replace('/^' . $ws . '+|' . $ws . '+$/u', '', $value);
		if ($t === null) $t = trim($value); // invalid UTF-8: fall back
		$value = $t;
		switch ($kind)
		{
			case 'name':
			case 'phone':
				$c = @preg_replace('/' . $ws . '+/u', ' ', $value);
				if ($c !== null) $value = $c;
				break;
			case 'token':
				$c = @preg_replace('/' . $ws . '+/u', '', $value);
				if ($c !== null) $value = $c;
				break;
		}
		return $value;
	}

	function pjCleanInputArray(&$arr, $parentKind = 'text')
	{
		foreach ($arr as $k => &$v)
		{
			// array keys that are numeric (i18n locale ids, ids) inherit the parent's kind
			$kind = (is_int($k) || ctype_digit((string) $k)) ? $parentKind : pjCleanInputKind($k);
			if ($parentKind === 'skip') $kind = 'skip';
			if (is_array($v)) pjCleanInputArray($v, $kind);
			else $v = pjCleanInputValue($v, $kind);
		}
		unset($v);
	}

	/**
	 * Rejects (HTTP 422) POSTed person fields that are still invalid after cleaning:
	 * e-mail with inner whitespace / bad format, names with markup or control chars,
	 * phones with letters. Required-ness stays with each controller.
	 */
	function pjGuardInput($arr, $path = '', $parentKind = 'text', &$bad = array())
	{
		foreach ($arr as $k => $v)
		{
			$kind = (is_int($k) || ctype_digit((string) $k)) ? $parentKind : pjCleanInputKind($k);
			if ($parentKind === 'skip') $kind = 'skip';
			$key = strtolower((string) $k);
			if (is_array($v)) { pjGuardInput($v, $path . $k . '.', $kind, $bad); continue; }
			if (!is_string($v) || $v === '' || $kind === 'skip') continue;
			$isPlain = strpos($key, 'value-') !== 0 && strpos($key, 'login_') !== 0 && $key !== 'q';
			if (!$isPlain) continue;
			if ($kind === 'email' && preg_match('/(^|_)email$/', $key) && !filter_var($v, FILTER_VALIDATE_EMAIL)) $bad[] = $path . $k;
			elseif ($kind === 'name' && ($key === 'name' || preg_match('/_name$/', $key)) && preg_match('/[<>]/', $v)) $bad[] = $path . $k;
			elseif ($kind === 'phone' && preg_match('/phone$/', $key) && !preg_match('/^[0-9+\-().\/ ]{3,30}$/', $v)) $bad[] = $path . $k;
		}
		return $bad;
	}

	if (!empty($_POST)) pjCleanInputArray($_POST);
	if (!empty($_GET)) pjCleanInputArray($_GET);
	if (isset($_REQUEST)) { $_REQUEST = array_merge($_GET, $_POST); }

	if (!empty($_POST) && isset($_GET['controller']) && $_GET['controller'] !== 'pjFront')
	{
		$pjBad = pjGuardInput($_POST);
		// required (non-empty after trimming) fields for the admin create/update forms
		$pjMarker = false;
		foreach (array_keys($_POST) as $pk) { if (in_array($pk, array('calendar_create','calendar_update','owner_create','owner_update','user_create','user_update','country_create','country_update','voucher_create','voucher_update'), true)) $pjMarker = true; }
		if ($pjMarker)
		{
			$pjReq = array('name' => 'name', 'email' => 'email', 'code' => 'code');
			foreach ($pjReq as $rk => $rl)
			{
				if (array_key_exists($rk, $_POST) && !is_array($_POST[$rk]) && $_POST[$rk] === '') $pjBad[] = $rk . ' (required)';
			}
			if (isset($_POST['i18n']) && is_array($_POST['i18n']))
			{
				foreach ($_POST['i18n'] as $lid => $fields)
				{
					if (is_array($fields) && array_key_exists('name', $fields) && $fields['name'] === '') { $pjBad[] = 'i18n[' . $lid . '][name] (required)'; }
					break; // only the first (default) language is mandatory
				}
			}
		}
		if (!empty($pjBad))
		{
			@header('HTTP/1.1 422 Unprocessable Entity');
			$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
			if ($isAjax)
			{
				@header('Content-Type: application/json; charset=utf-8');
				echo json_encode(array('status' => 'ERR', 'code' => 422, 'text' => 'Invalid value in: ' . implode(', ', $pjBad), 'fields' => $pjBad));
			} else {
				echo '<!DOCTYPE html><meta charset="utf-8"><body style="font-family:sans-serif;padding:30px"><h3>Invalid input</h3><p>Please correct: <b>' . htmlspecialchars(implode(', ', $pjBad), ENT_QUOTES, 'UTF-8') . '</b></p><p><a href="javascript:history.back()">&laquo; Go back</a></p></body>';
			}
			exit;
		}
	}
}
