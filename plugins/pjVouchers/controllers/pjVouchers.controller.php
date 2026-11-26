<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjVouchers extends pjVouchersAppController
{
	public function pjActionCheckCode()
	{
		$this->setAjax(true);

		if ($this->isXHR())
		{
			$code = trim((string) $this->_get->toString('code'));
			if ($code === '')
			{
				echo 'false';
				exit;
			}
			$pjVoucherModel = pjVoucherModel::factory()->where('t1.code', $code);
			if ($this->_get->toInt('id'))
			{
				$pjVoucherModel->where('t1.id !=', $this->_get->toInt('id'));
			}
			echo $pjVoucherModel->findCount()->getData() == 0 ? 'true' : 'false';
		}
		exit;
	}

	public function pjActionCheckDate()
	{
		$this->setAjax(true);

		if ($this->isXHR())
		{
			$dt_from = strtotime(pjDateTime::formatDate($this->_post->toString('p_date_from'), $this->option_arr['o_date_format']));
			$dt_to = strtotime(pjDateTime::formatDate($this->_post->toString('p_date_to'), $this->option_arr['o_date_format']));
			echo $dt_to >= $dt_from ? 'true' : 'false';
		}
		exit;
	}

	public function pjActionCreate()
	{
		if (!pjAuth::factory()->hasAccess())
		{
			$this->sendForbidden();
			return;
		}

		if ($this->_post->check('voucher_create'))
		{
			$this->trimPostedValues();
			if (trim((string) $this->_post->toString('code')) === '' || trim((string) $this->_post->toString('discount')) === '')
			{
				// voucher code or discount is blank (or only spaces)
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjVouchers&action=pjActionIndex&err=AV09");
			}
			$data = array();

			$data['code'] = $this->_post->toString('code');
			$data['discount'] = $this->_post->toString('discount');
			$data['type'] = $this->_post->toString('type');
			$data['valid'] = $this->_post->toString('valid');
			if (!$this->isValidVoucherInput($data))
			{
				# invalid discount / type / validity / code length (server-side guard, the form validates the same on the client)
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjVouchers&action=pjActionIndex&err=AV04");
			}
			$data['valid_for'] = in_array($this->_post->toString('valid_for'), array('stay', 'booking', 'both')) ? $this->_post->toString('valid_for') : 'stay';
			if (($data['valid'] == 'fixed' && !$this->isRawDate($this->_post->toString('f_date'))) || ($data['valid'] == 'period' && (!$this->isRawDate($this->_post->toString('p_date_from')) || !$this->isRawDate($this->_post->toString('p_date_to')))))
			{
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjVouchers&action=pjActionIndex&err=AV04");
			}
			switch ($data['valid'])
			{
				case 'fixed':
					$data['date_from'] = pjDateTime::formatDate($this->_post->toString('f_date'), $this->option_arr['o_date_format']);
					$data['date_to'] = $data['date_from'];
					if (!$this->isRawDate($this->_post->toString('f_date')))
					{
						pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjVouchers&action=pjActionIndex&err=AV04");
					}
					if(strpos($this->option_arr['o_time_format'], 'a') > -1 || strpos($this->option_arr['o_time_format'], 'A') > -1)
					{
						$data['time_from'] = date('H:i:s', strtotime($data['date_from'] . ' ' .$this->_post->toString('f_hour_from') . ':' . $this->_post->toString('f_minute_from') . ' ' . strtoupper($this->_post->toString('f_ampm_from'))));
						$data['time_to'] =  date('H:i:s', strtotime($data['date_to'] . ' ' .$this->_post->toString('f_hour_to') . ':' . $this->_post->toString('f_minute_to') . ' ' . strtoupper($this->_post->toString('f_ampm_to'))));
					}else{
						$data['time_from'] = $this->_post->toString('f_hour_from') . ":" . $this->_post->toString('f_minute_from') . ":00";
						$data['time_to'] = $this->_post->toString('f_hour_to') . ":" . $this->_post->toString('f_minute_to') . ":00";
					}
					break;
				case 'period':
					$data['date_from'] = pjDateTime::formatDate($this->_post->toString('p_date_from'), $this->option_arr['o_date_format']);
					$data['date_to'] = pjDateTime::formatDate($this->_post->toString('p_date_to'), $this->option_arr['o_date_format']);
					if (!$this->isRawDate($this->_post->toString('p_date_to')) || !$this->isRawDate($this->_post->toString('p_date_from')) || strtotime($data['date_to']) < strtotime($data['date_from']))
					{
						# To date earlier than From date (server-side guard)
						pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjVouchers&action=pjActionIndex&err=AV04");
					}
					if(strpos($this->option_arr['o_time_format'], 'a') > -1 || strpos($this->option_arr['o_time_format'], 'A') > -1)
					{
						$data['time_from'] = date('H:i:s', strtotime($data['date_from'] . ' ' .$this->_post->toString('p_hour_from') . ':' . $this->_post->toString('p_minute_from') . ' ' . strtoupper($this->_post->toString('p_ampm_from'))));
						$data['time_to'] =  date('H:i:s', strtotime($data['date_to'] . ' ' .$this->_post->toString('p_hour_to') . ':' . $this->_post->toString('p_minute_to') . ' ' . strtoupper($this->_post->toString('p_ampm_to'))));
					}else{
						$data['time_from'] = $this->_post->toString('p_hour_from') . ":" . $this->_post->toString('p_minute_from') . ":00";
						$data['time_to'] = $this->_post->toString('p_hour_to') . ":" . $this->_post->toString('p_minute_to') . ":00";
					}
					break;
				case 'recurring':
					$data['every'] = $this->_post->toString('r_every');
					if(strpos($this->option_arr['o_time_format'], 'a') > -1 || strpos($this->option_arr['o_time_format'], 'A') > -1)
					{
						$data['time_from'] = date('H:i:s', strtotime(date('Y-m-d') . ' ' .$this->_post->toString('r_hour_from') . ':' . $this->_post->toString('r_minute_from') . ' ' . strtoupper($this->_post->toString('r_ampm_from'))));
						$data['time_to'] =  date('H:i:s', strtotime(date('Y-m-d') . ' ' .$this->_post->toString('r_hour_to') . ':' . $this->_post->toString('r_minute_to') . ' ' . strtoupper($this->_post->toString('r_ampm_to'))));
					}else{
						$data['time_from'] = $this->_post->toString('r_hour_from') . ":" . $this->_post->toString('r_minute_from') . ":00";
						$data['time_to'] = $this->_post->toString('r_hour_to') . ":" . $this->_post->toString('r_minute_to') . ":00";
					}
					break;
			}
			$id = pjVoucherModel::factory($data)->insert()->getInsertId();
			if ($id !== false && (int) $id > 0)
			{
				$err = 'AV03';
			} else {
				$err = 'AV04';
			}
			pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjVouchers&action=pjActionIndex&err=$err");
		} else {

			$this->appendCss('datepicker.css', PJ_THIRD_PARTY_PATH . 'bootstrap_datepicker/');
			$this->appendJs('bootstrap-datepicker.js', PJ_THIRD_PARTY_PATH . 'bootstrap_datepicker/');
			$this->appendJs('pjVouchers.js', $this->getConst('PLUGIN_JS_PATH'));
		}
	}

	/**
	 * True when the posted date really exists in the configured date format (no roll-over such as 31.02 or 99.99).
	 */
	private function isRawDate($raw)
	{
		$format = $this->option_arr['o_date_format'];
		foreach (array('.', '-', '/') as $lim)
		{
			if (strpos($format, $lim) !== false)
			{
				$d = explode($lim, trim((string) $raw));
				$f = explode($lim, $format);
				if (count($d) !== 3 || count($f) !== 3) return false;
				$map = array_flip($f);
				$m = isset($map['m']) ? $map['m'] : (isset($map['n']) ? $map['n'] : null);
				$dd = isset($map['d']) ? $map['d'] : (isset($map['j']) ? $map['j'] : null);
				$y = isset($map['Y']) ? $map['Y'] : null;
				if ($m === null || $dd === null || $y === null) return false;
				foreach (array($d[$m], $d[$dd], $d[$y]) as $part) if (!ctype_digit($part)) return false;
				return strlen($d[$y]) === 4 && checkdate((int) $d[$m], (int) $d[$dd], (int) $d[$y]);
			}
		}
		return false;
	}

	/**
	 * Server-side validation shared by create/update/inline edit.
	 * Normalizes $data['discount'] to a dot-decimal string with 2 decimals.
	 */
	private function isValidVoucherInput(&$data)
	{
		if (!in_array($data['type'], array('percent', 'amount'))) return false;
		if (!in_array($data['valid'], array('fixed', 'period', 'recurring'))) return false;
		if (mb_strlen((string) $data['code']) > 255) return false;
		$raw = trim((string) $data['discount']);
		if (!preg_match('/^\d{1,10}([.,]\d+)?$/', $raw)) return false;
		$vd = round((float) str_replace(',', '.', $raw), 2);
		if ($vd <= 0 || ($data['type'] == 'percent' && $vd > 100)) return false;
		$data['discount'] = number_format($vd, 2, '.', '');
		return true;
	}

	/**
	 * Codes (from the given voucher ids) that are linked to at least one reservation.
	 */
	private function getUsedVoucherCodes($ids)
	{
		$used = array();
		$ids = array_filter(array_map('intval', (array) $ids));
		if (empty($ids)) return $used;
		$rows = pjVoucherModel::factory()->whereIn('id', $ids)->findAll()->getData();
		foreach ($rows as $row)
		{
			$cnt = pjReservationModel::factory()->where('t1.voucher_code', $row['code'])->findCount()->getData();
			if ((int) $cnt > 0) $used[$row['id']] = array('code' => $row['code'], 'count' => (int) $cnt);
		}
		return $used;
	}

	private static function usedMessage($used)
	{
		$codes = array();
		foreach ($used as $u) $codes[] = $u['code'] . ' (' . $u['count'] . ')';
		return 'The voucher cannot be deleted because it is linked to a reservation: ' . join(', ', $codes) . '. Vouchers that were used in reservations must be kept.';
	}

	public function pjActionDeleteVoucher()
	{
		$this->setAjax(true);

		if ($this->isXHR())
		{
			if (!pjAuth::factory()->hasAccess())
			{
				self::jsonResponse(array('status' => 'ERR', 'code' => 100, 'text' => 'Access denied.'));
			}

			$used = $this->getUsedVoucherCodes(array($this->_get->toInt('id')));
			if (!empty($used))
			{
				self::jsonResponse(array('status' => 'ERR', 'code' => 409, 'text' => self::usedMessage($used)));
			}

			if (pjVoucherModel::factory()->setAttributes(array('id' => $this->_get->toInt('id')))->erase()->getAffectedRows() == 1)
			{
				self::jsonResponse(array('status' => 'OK', 'code' => 200, 'text' => 'Voucher has been deleted.'));
			} else {
				self::jsonResponse(array('status' => 'ERR', 'code' => 100, 'text' => 'Voucher has not been deleted.'));
			}
		}
		exit;
	}

	public function pjActionDeleteVoucherBulk()
	{
		$this->setAjax(true);

		if ($this->isXHR())
		{
			if (!pjAuth::factory()->hasAccess())
			{
				self::jsonResponse(array('status' => 'ERR', 'code' => 100, 'text' => 'Access denied.'));
			}

			$record = $this->_post->toArray('record');
			if (count($record))
			{
				$used = $this->getUsedVoucherCodes($record);
				$free = array_values(array_diff(array_map('intval', $record), array_keys($used)));
				if (!empty($free))
				{
					pjVoucherModel::factory()->whereIn('id', $free)->eraseAll();
				}
				if (!empty($used))
				{
					self::jsonResponse(array('status' => 'ERR', 'code' => 409, 'text' => self::usedMessage($used) . (empty($free) ? '' : ' The other selected vouchers were deleted.')));
				}
				
				self::jsonResponse(array('status' => 'OK', 'code' => 200, 'text' => 'Voucher(s) has been deleted.'));
			}

			self::jsonResponse(array('status' => 'ERR', 'code' => 100, 'text' => 'Reservation(s) has not been deleted.'));
		}
		exit;
	}

	public function pjActionExportVoucher()
	{
		$this->checkLogin();

		if (!pjAuth::factory()->hasAccess())
		{
			$this->sendForbidden();
			return;
		}

		$record = $this->_post->toArray('record');
		if (count($record))
		{
			$arr = pjVoucherModel::factory()->whereIn('id', $record)->findAll()->getData();
			foreach ($arr as &$item)
			{
				if (!empty($item['date_from']))
				{
					$item['date_from'] = pjDateTime::formatDate($item['date_from'], 'Y-m-d', $this->option_arr['o_date_format']);
				}
				if (!empty($item['date_to']))
				{
					$item['date_to'] = pjDateTime::formatDate($item['date_to'], 'Y-m-d', $this->option_arr['o_date_format']);
				}
				if (!empty($item['time_from']))
				{
					$item['time_from'] = pjDateTime::formatTime($item['time_from'], 'H:i:s', $this->option_arr['o_time_format']);
				}
				if (!empty($item['time_to']))
				{
					$item['time_to'] = pjDateTime::formatTime($item['time_to'], 'H:i:s', $this->option_arr['o_time_format']);
				}
			}
			$csv = new pjCSV();
			$csv
				->setHeader(true)
				->setName("Vouchers-".time().".csv")
				->process($arr)
				->download();
		}
		exit;
	}

	public function pjActionGetVoucher()
	{
		$this->setAjax(true);

		if ($this->isXHR())
		{
			$pjVoucherModel = pjVoucherModel::factory();

			if ($q = $this->_get->toString('q'))
			{
				$q = str_replace(array('%', '_'), array('\%', '\_'), trim($q));
				$pjVoucherModel->where('t1.code LIKE', "%$q%");
			}

			$column = 'code';
			$direction = 'ASC';
			if ($this->_get->check('column') && in_array(strtoupper($this->_get->toString('direction')), array('ASC', 'DESC')))
			{
				$column = $this->_get->toString('column');
				$direction = strtoupper($this->_get->toString('direction'));
			}

			$total = $pjVoucherModel->findCount()->getData();
			$rowCount = $this->_get->toInt('rowCount') ?: 10;
			$pages = ceil($total / $rowCount);
			$page = $this->_get->toInt('page') ?: 1;
			$offset = ((int) $page - 1) * $rowCount;
			if ($page > $pages)
			{
				$page = $pages;
			}

			$data = $pjVoucherModel
				->select('t1.id, t1.code, t1.discount, t1.type, t1.valid')
				->orderBy("$column $direction")->limit($rowCount, $offset)->findAll()->getData();
			
			$voucher_types = __('plugin_vouchers_types', true);
			$vouchers_valids = __('plugin_vouchers_valids', true, false);
			$currency_sign = pjCurrency::getCurrencySign($this->option_arr['o_currency'], false);
			foreach($data as $k => $v)
			{
			    $data[$k]['sign'] = $v['type'] == 'percent' ? '%' : $currency_sign; # shown after the discount in the grid
			    $data[$k]['type'] = $voucher_types[$v['type']];
			    $data[$k]['valid'] = $vouchers_valids[$v['valid']];
			}
			$data = pjSanitize::clean($data);

			pjAppController::jsonResponse(compact('data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction'));
		}
		exit;
	}

	public function pjActionIndex()
	{
		if (!pjAuth::factory($this->_get->toString('controller'))->hasAccess())
		{
			$this->sendForbidden();
			return;
		}
		$this->set('has_export', pjAuth::factory('pjVouchers', 'pjActionExportVoucher')->hasAccess());
		
		$this->appendJs('jquery.datagrid.js', PJ_FRAMEWORK_LIBS_PATH . 'pj/js/');
		$this->appendJs('pjVouchers.js', $this->getConst('PLUGIN_JS_PATH'));
		$this->appendJs('index.php?controller=pjBase&action=pjActionMessages', PJ_INSTALL_URL, true);
	}

	public function pjActionSaveVoucher()
	{
		$this->setAjax(true);

		if (!$this->isXHR())
		{
			self::jsonResponse(array('status' => 'ERR', 'code' => 100, 'text' => 'Missing headers.'));
		}
		
		if (!self::isPost())
		{
			self::jsonResponse(array('status' => 'ERR', 'code' => 101, 'text' => 'HTTP method not allowed.'));
		}
		
		if (!pjAuth::factory($this->_get->toString('controller'), 'pjActionUpdate')->hasAccess())
		{
			self::jsonResponse(array('status' => 'ERR', 'code' => 102, 'text' => 'Access denied.'));
		}
		
		$this->trimPostedValues();
		
		$arr = pjVoucherModel::factory()->find($this->_get->toInt('id'))->getData();
		if (!$arr)
		{
			self::jsonResponse(array('status' => 'ERR', 'code' => 103, 'text' => 'Voucher not found.'));
		}
		
		$column = $this->_post->toString('column');
		$value = $this->_post->toString('value');
		if (!in_array($column, array('code', 'discount')))
		{
			self::jsonResponse(array('status' => 'ERR', 'code' => 106, 'text' => 'This field can not be edited.'));
		}
		if (trim((string) $value) === '')
		{
			self::jsonResponse(array('status' => 'ERR', 'code' => 105, 'text' => 'This field is required.'));
		}
		if ($column == 'discount')
		{
			$chk = array('code' => $arr['code'], 'type' => $arr['type'], 'valid' => $arr['valid'], 'discount' => $value);
			if (!$this->isValidVoucherInput($chk))
			{
				$text = ($arr['type'] == 'percent' && (float) str_replace(',', '.', $value) > 100) ? 'Discount can not exceed 100%.' : 'Invalid discount value.';
				self::jsonResponse(array('status' => 'ERR', 'code' => 104, 'text' => $text));
			}
			$value = $chk['discount'];
		} else {
			if (mb_strlen($value) > 255)
			{
				self::jsonResponse(array('status' => 'ERR', 'code' => 107, 'text' => 'The code is too long.'));
			}
			$dup = pjVoucherModel::factory()->where('t1.code', $value)->where('t1.id !=', $arr['id'])->findCount()->getData();
			if ((int) $dup > 0)
			{
				self::jsonResponse(array('status' => 'ERR', 'code' => 108, 'text' => 'The code already exists.'));
			}
			if ($value !== $arr['code'] && !empty($this->getUsedVoucherCodes(array($arr['id']))))
			{
				self::jsonResponse(array('status' => 'ERR', 'code' => 409, 'text' => 'The code of a voucher that was used in reservations can not be changed.'));
			}
		}

		pjVoucherModel::factory()->set('id', $this->_get->toInt('id'))->modify(array($column => $value));

		self::jsonResponse(array('status' => 'OK', 'code' => 201, 'text' => 'Voucher has been updated.'));
	}

	public function pjActionUpdate()
	{
		if (!pjAuth::factory()->hasAccess())
		{
			$this->sendForbidden();
			return;
		}

		if ($this->_post->check('voucher_update'))
		{
			$this->trimPostedValues();
			if (trim((string) $this->_post->toString('code')) === '' || trim((string) $this->_post->toString('discount')) === '')
			{
				// voucher code or discount is blank (or only spaces)
				pjUtil::redirect(PJ_INSTALL_URL . "index.php?controller=pjVouchers&action=pjActionIndex&err=AV09");
			}
			$data = array();
			$data['code'] = $this->_post->toString('code');
			$data['discount'] = $this->_post->toString('discount');
			$data['type'] = $this->_post->toString('type');
			$data['valid'] = $this->_post->toString('valid');
			if (!$this->isValidVoucherInput($data))
			{
				# invalid discount / type / validity / code length (server-side guard, the form validates the same on the client)
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjVouchers&action=pjActionIndex&err=AV04");
			}
			$data['valid_for'] = in_array($this->_post->toString('valid_for'), array('stay', 'booking', 'both')) ? $this->_post->toString('valid_for') : 'stay';
			if (($data['valid'] == 'fixed' && !$this->isRawDate($this->_post->toString('f_date'))) || ($data['valid'] == 'period' && (!$this->isRawDate($this->_post->toString('p_date_from')) || !$this->isRawDate($this->_post->toString('p_date_to')))))
			{
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjVouchers&action=pjActionIndex&err=AV04");
			}
			switch ($data['valid'])
			{
				case 'fixed':
					$data['date_from'] = pjDateTime::formatDate($this->_post->toString('f_date'), $this->option_arr['o_date_format']);
					$data['date_to'] = $data['date_from'];
					if (!$this->isRawDate($this->_post->toString('f_date')))
					{
						pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjVouchers&action=pjActionIndex&err=AV04");
					}
					if(strpos($this->option_arr['o_time_format'], 'a') > -1 || strpos($this->option_arr['o_time_format'], 'A') > -1)
					{
						$data['time_from'] = date('H:i:s', strtotime($data['date_from'] . ' ' .$this->_post->toString('f_hour_from') . ':' . $this->_post->toString('f_minute_from') . ' ' . strtoupper($this->_post->toString('f_ampm_from'))));
						$data['time_to'] =  date('H:i:s', strtotime($data['date_to'] . ' ' .$this->_post->toString('f_hour_to') . ':' . $this->_post->toString('f_minute_to') . ' ' . strtoupper($this->_post->toString('f_ampm_to'))));
					}else{
						$data['time_from'] = $this->_post->toString('f_hour_from') . ":" . $this->_post->toString('f_minute_from') . ":00";
						$data['time_to'] = $this->_post->toString('f_hour_to') . ":" . $this->_post->toString('f_minute_to') . ":00";
					}
					$data['every'] = array('NULL');
					break;
				case 'period':
					$data['date_from'] = pjDateTime::formatDate($this->_post->toString('p_date_from'), $this->option_arr['o_date_format']);
					$data['date_to'] = pjDateTime::formatDate($this->_post->toString('p_date_to'), $this->option_arr['o_date_format']);
					if (!$this->isRawDate($this->_post->toString('p_date_to')) || !$this->isRawDate($this->_post->toString('p_date_from')) || strtotime($data['date_to']) < strtotime($data['date_from']))
					{
						# To date earlier than From date (server-side guard)
						pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjVouchers&action=pjActionIndex&err=AV04");
					}
					if(strpos($this->option_arr['o_time_format'], 'a') > -1 || strpos($this->option_arr['o_time_format'], 'A') > -1)
					{
						$data['time_from'] = date('H:i:s', strtotime($data['date_from'] . ' ' .$this->_post->toString('p_hour_from') . ':' . $this->_post->toString('p_minute_from') . ' ' . strtoupper($this->_post->toString('p_ampm_from'))));
						$data['time_to'] =  date('H:i:s', strtotime($data['date_to'] . ' ' .$this->_post->toString('p_hour_to') . ':' . $this->_post->toString('p_minute_to') . ' ' . strtoupper($this->_post->toString('p_ampm_to'))));
					}else{
						$data['time_from'] = $this->_post->toString('p_hour_from') . ":" . $this->_post->toString('p_minute_from') . ":00";
						$data['time_to'] = $this->_post->toString('p_hour_to') . ":" . $this->_post->toString('p_minute_to') . ":00";
					}
					$data['every'] = array('NULL');
					break;
				case 'recurring':
					$data['date_from'] = array('NULL');
					$data['date_to'] = array('NULL');
					$data['every'] = $this->_post->toString('r_every');
					if(strpos($this->option_arr['o_time_format'], 'a') > -1 || strpos($this->option_arr['o_time_format'], 'A') > -1)
					{
						$data['time_from'] = date('H:i:s', strtotime(date('Y-m-d') . ' ' .$this->_post->toString('r_hour_from') . ':' . $this->_post->toString('r_minute_from') . ' ' . strtoupper($this->_post->toString('r_ampm_from'))));
						$data['time_to'] =  date('H:i:s', strtotime(date('Y-m-d') . ' ' .$this->_post->toString('r_hour_to') . ':' . $this->_post->toString('r_minute_to') . ' ' . strtoupper($this->_post->toString('r_ampm_to'))));
					}else{
						$data['time_from'] = $this->_post->toString('r_hour_from') . ":" . $this->_post->toString('r_minute_from') . ":00";
						$data['time_to'] = $this->_post->toString('r_hour_to') . ":" . $this->_post->toString('r_minute_to') . ":00";
					}
					break;
			}

			$cur = pjVoucherModel::factory()->find($this->_post->toInt('id'))->getData();
			if (!$cur)
			{
				pjUtil::redirect(PJ_INSTALL_URL . "index.php?controller=pjVouchers&action=pjActionIndex&err=AV08");
			}
			if ($data['code'] !== $cur['code'] && !empty($this->getUsedVoucherCodes(array($cur['id']))))
			{
				# the code of a voucher that is linked to reservations must stay the same
				pjUtil::redirect(PJ_INSTALL_URL . "index.php?controller=pjVouchers&action=pjActionIndex&err=AV04");
			}

			pjVoucherModel::factory()->where('id', $this->_post->toInt('id'))->limit(1)->modifyAll($data);

			pjUtil::redirect(PJ_INSTALL_URL . "index.php?controller=pjVouchers&action=pjActionIndex&err=AV01");

		} else {
			$arr = pjVoucherModel::factory()->find($this->_get->toInt('id'))->getData();
			if (count($arr) === 0)
			{
				pjUtil::redirect(PJ_INSTALL_URL. "index.php?controller=pjVouchers&action=pjActionIndex&err=AV08");
			}
			$this->set('arr', $arr);

			$this->appendCss('datepicker.css', PJ_THIRD_PARTY_PATH . 'bootstrap_datepicker/');
			$this->appendJs('bootstrap-datepicker.js', PJ_THIRD_PARTY_PATH . 'bootstrap_datepicker/');
			$this->appendJs('pjVouchers.js', $this->getConst('PLUGIN_JS_PATH'));
		}
	}
}
?>