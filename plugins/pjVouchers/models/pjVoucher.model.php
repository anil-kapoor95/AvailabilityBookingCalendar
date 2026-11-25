<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjVoucherModel extends pjVouchersAppModel
{
	protected $primaryKey = 'id';

	protected $table = 'plugin_vouchers';

	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'code', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'type', 'type' => 'enum', 'default' => ':NULL'),
		array('name' => 'discount', 'type' => 'decimal', 'default' => ':NULL'),
		array('name' => 'valid', 'type' => 'enum', 'default' => ':NULL'),
		array('name' => 'valid_for', 'type' => 'enum', 'default' => 'stay'),
		array('name' => 'date_from', 'type' => 'date', 'default' => ':NULL'),
		array('name' => 'date_to', 'type' => 'date', 'default' => ':NULL'),
		array('name' => 'time_from', 'type' => 'time', 'default' => ':NULL'),
		array('name' => 'time_to', 'type' => 'time', 'default' => ':NULL'),
		array('name' => 'every', 'type' => 'enum', 'default' => ':NULL')
	);

    public static function factory($attr=array())
    {
        return new self($attr);
    }

    /**
     * Availability Booking Calendar: vouchers are validated by DATE only (check-in date),
     * the time columns are stored but ignored.
     */
    public function getVoucher($code, $date, $time = '00:00:00')
	{
		$result = array('status' => 'ERR', 'code' => '100');

		$arr = $this->reset()->where("t1.code", $code)->findAll()->getData();
		if (count($arr) == 1)
		{
			$arr = $arr[0];
			$result['code'] = '101';
			$check = function($d) use ($arr) {
				switch ($arr['valid'])
				{
					case 'period':
						return ($d >= $arr['date_from'] && $d <= $arr['date_to']);
					case 'fixed':
						return ($arr['date_from'] == $d);
					case 'recurring':
						return (date("l", strtotime($d)) == ucfirst($arr['every']));
				}
				return false;
			};
			// valid_for: stay = check-in date, booking = date the reservation is made (today), both = both
			$valid_for = isset($arr['valid_for']) ? $arr['valid_for'] : 'stay';
			$today = date('Y-m-d');
			switch ($valid_for)
			{
				case 'booking':
					$ok = $check($today);
					break;
				case 'both':
					$ok = $check($date) && $check($today);
					break;
				default:
					$ok = $check($date);
			}
			if ($ok)
			{
				$result['status'] = 'OK';
				$result['code'] = '200';
				$result['arr'] = $arr;
			}
		}

		return $result;
	}

	protected static function hoursToSeconds($hour)
	{
    	$parse = array();
		if (!preg_match ('#^(?<hours>[\d]{2}):(?<mins>[\d]{2}):(?<secs>[\d]{2})$#',$hour,$parse))
		{
			throw new RuntimeException ("Hour Format not valid");
		}
		return (int) $parse['hours'] * 3600 + (int) $parse['mins'] * 60 + (int) $parse['secs'];
	}
}
?>