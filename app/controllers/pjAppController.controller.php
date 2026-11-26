<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjAppController extends pjBaseAppController
{
	public $models = array();

	/**
	 * Generate (or return existing) CSRF token for the current session.
	 */
	public static function getCsrfToken()
	{
		if (empty($_SESSION['csrf_token']))
		{
			$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
		}
		return $_SESSION['csrf_token'];
	}

	/**
	 * Validate CSRF token. pjInput nulls raw $_POST/$_GET, so read request objects first.
	 */
	public static function validateCsrfToken()
	{
		$token = '';
		$reg = pjRegistry::getInstance();
		$post = $reg->get('_post');
		$get = $reg->get('_get');
		if (is_object($post) && $post->check('csrf_token')) {
			$token = (string) $post->toString('csrf_token');
		} elseif (is_object($get) && $get->check('csrf_token')) {
			$token = (string) $get->toString('csrf_token');
		} elseif (!empty($_POST['csrf_token'])) {
			$token = $_POST['csrf_token'];
		} elseif (!empty($_GET['csrf_token'])) {
			$token = $_GET['csrf_token'];
		} elseif (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
			$token = $_SERVER['HTTP_X_CSRF_TOKEN'];
		}
		if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string) $token))
		{
			header('HTTP/1.1 403 Forbidden');
			exit('Invalid CSRF token.');
		}
	}

	/**
	 * Modern: default design tokens (must match app/web/css/themes/modern.css).
	 * Colour tokens are the keys of the "ocean" palette plus the availability status colours.
	 */
	public static function getModernPalettes()
	{
		return array(
			'coral' => array('Sunset Coral', array('page' => '#fbf0ea', 'card' => '#ffffff', 'soft' => '#f4dccf', 'line' => '#f1e0d8', 'accent' => '#e8836b', 'heading' => '#4a2a22', 'body' => '#674c45', 'muted' => '#7d6660', 'link' => '#ae3c22', 'btn' => '#c94e33', 'btn_text' => '#ffffff', 'btn_hover' => '#b73f24', 'btn2' => '#ffffff', 'btn2_text' => '#ae3c22')),
			'sage' => array('Sage', array('page' => '#f1f4ec', 'card' => '#ffffff', 'soft' => '#dde6d0', 'line' => '#e3eadb', 'accent' => '#8fa876', 'heading' => '#2f3a26', 'body' => '#505a49', 'muted' => '#697163', 'link' => '#4d6837', 'btn' => '#5f7d45', 'btn_text' => '#ffffff', 'btn_hover' => '#4d6837', 'btn2' => '#ffffff', 'btn2_text' => '#4d6837')),
			'ocean' => array('Ocean', array('page' => '#edf4fa', 'card' => '#ffffff', 'soft' => '#d3e5f4', 'line' => '#e0ecf6', 'accent' => '#4f93c9', 'heading' => '#1b3550', 'body' => '#3f556c', 'muted' => '#5b6e81', 'link' => '#245f93', 'btn' => '#2a6fa8', 'btn_text' => '#ffffff', 'btn_hover' => '#1f5a8c', 'btn2' => '#ffffff', 'btn2_text' => '#245f93')),
			'lavender' => array('Lavender', array('page' => '#f4f0fa', 'card' => '#ffffff', 'soft' => '#e3d9f2', 'line' => '#ebe4f5', 'accent' => '#9a7fd0', 'heading' => '#33284f', 'body' => '#544a6b', 'muted' => '#6c6480', 'link' => '#5b4396', 'btn' => '#6b4fb0', 'btn_text' => '#ffffff', 'btn_hover' => '#59409a', 'btn2' => '#ffffff', 'btn2_text' => '#5b4396')),
			'forest' => array('Forest', array('page' => '#eef3ef', 'card' => '#ffffff', 'soft' => '#d2e2d7', 'line' => '#e0eae3', 'accent' => '#3f8a5f', 'heading' => '#16301f', 'body' => '#3b5143', 'muted' => '#576a5e', 'link' => '#1a5c3b', 'btn' => '#1f6b44', 'btn_text' => '#ffffff', 'btn_hover' => '#17573a', 'btn2' => '#ffffff', 'btn2_text' => '#1a5c3b')),
			'gold' => array('Gold', array('page' => '#fbf6e9', 'card' => '#ffffff', 'soft' => '#f2e6bf', 'line' => '#f2ead0', 'accent' => '#d6a52a', 'heading' => '#3d3112', 'body' => '#5c5238', 'muted' => '#736b54', 'link' => '#855f05', 'btn' => '#946a08', 'btn_text' => '#ffffff', 'btn_hover' => '#7d5805', 'btn2' => '#ffffff', 'btn2_text' => '#855f05')),
			'rose' => array('Rose', array('page' => '#fbeff3', 'card' => '#ffffff', 'soft' => '#f6d6e0', 'line' => '#f6e2e9', 'accent' => '#e0708f', 'heading' => '#4a2130', 'body' => '#674551', 'muted' => '#7d5f6a', 'link' => '#a52e56', 'btn' => '#c23b66', 'btn_text' => '#ffffff', 'btn_hover' => '#a82f56', 'btn2' => '#ffffff', 'btn2_text' => '#a52e56')),
			'mint' => array('Mint', array('page' => '#ebf7f4', 'card' => '#ffffff', 'soft' => '#cdeee6', 'line' => '#dcf2ed', 'accent' => '#3fb8a0', 'heading' => '#103a33', 'body' => '#365a54', 'muted' => '#53716c', 'link' => '#0f6a5a', 'btn' => '#13806d', 'btn_text' => '#ffffff', 'btn_hover' => '#0e6a5a', 'btn2' => '#ffffff', 'btn2_text' => '#0f6a5a'))
		);
	}

	public static function getModernStatus()
	{
		return array('free' => '#dcefe6', 'free_text' => '#256a50', 'booked' => '#f3cfc4', 'booked_text' => '#a53a22', 'pending' => '#fbe5b5', 'pending_text' => '#8a5a00', 'past' => '#f6f0ec', 'past_text' => '#b8a8a1');
	}

	/**
	 * Modern: fonts the admin can choose from (key => array(label, CSS stack, Google Fonts family or NULL)).
	 */
	public static function getModernFonts()
	{
		return array(
			'poppins' => array('Poppins', "'Poppins',system-ui,sans-serif", 'Poppins'),
			'inter' => array('Inter', "'Inter',system-ui,sans-serif", 'Inter'),
			'dm_sans' => array('DM Sans', "'DM Sans',system-ui,sans-serif", 'DM+Sans'),
			'open_sans' => array('Open Sans', "'Open Sans',system-ui,sans-serif", 'Open+Sans'),
			'playfair' => array('Playfair Display', "'Playfair Display',Georgia,serif", 'Playfair+Display'),
			'georgia' => array('Georgia', "Georgia,'Times New Roman',serif", NULL),
			'system' => array('System UI (no external font)', "system-ui,-apple-system,'Segoe UI',Roboto,Arial,sans-serif", NULL)
		);
	}

	public static function getModernRadii()
	{
		return array(
			'sharp' => array('8px', '6px', '6px', '6px'),
			'soft' => array('18px', '12px', '10px', '12px'),
			'round' => array('26px', '999px', '14px', '50%')
		);
	}

	public static function getModernShadows()
	{
		return array(
			'none' => 'none',
			'soft' => '0 10px 30px color-mix(in srgb, var(--modern-heading) 10%, transparent)',
			'lifted' => '0 18px 44px color-mix(in srgb, var(--modern-heading) 22%, transparent)'
		);
	}

	public static function getModernDefaults()
	{
		$p = self::getModernPalettes();
		return array(
			'palette' => 'ocean',
			'colors' => array_merge($p['ocean'][1], self::getModernStatus()),
			'font' => 'poppins',
			'fs' => 14,
			'radius' => 'soft',
			'shadow' => 'soft'
		);
	}

	/**
	 * Modern: validates the saved JSON and returns ONLY the valid overrides (empty array = use CSS defaults).
	 * Every value is checked against a strict whitelist, so the result is safe to print inside a stylesheet.
	 */
	public static function getModernSettings($json)
	{
		$out = array();
		if (!is_string($json) || $json === '')
		{
			return $out;
		}
		$arr = json_decode($json, true);
		if (!is_array($arr))
		{
			return $out;
		}
		$defaults = self::getModernDefaults();
		if (isset($arr['palette']) && is_string($arr['palette']))
		{
			$palettes = self::getModernPalettes();
			if ($arr['palette'] === '' || isset($palettes[$arr['palette']]))
			{
				$out['palette'] = $arr['palette'];
			}
		}
		if (isset($arr['colors']) && is_array($arr['colors']))
		{
			foreach ($defaults['colors'] as $k => $default)
			{
				if (isset($arr['colors'][$k]) && is_string($arr['colors'][$k]) && preg_match('/^#[0-9a-fA-F]{6}$/', $arr['colors'][$k]))
				{
					$out['colors'][$k] = strtolower($arr['colors'][$k]);
				}
			}
		}
		if (isset($arr['font']) && is_string($arr['font']) && array_key_exists($arr['font'], self::getModernFonts()))
		{
			$out['font'] = $arr['font'];
		}
		if (isset($arr['fs']) && is_numeric($arr['fs']) && in_array((int) $arr['fs'], array(13, 14, 15, 16), true))
		{
			$out['fs'] = (int) $arr['fs'];
		}
		if (isset($arr['radius']) && is_string($arr['radius']) && array_key_exists($arr['radius'], self::getModernRadii()))
		{
			$out['radius'] = $arr['radius'];
		}
		if (isset($arr['shadow']) && is_string($arr['shadow']) && array_key_exists($arr['shadow'], self::getModernShadows()))
		{
			$out['shadow'] = $arr['shadow'];
		}
		return $out;
	}

	/**
	 * Modern: Google Fonts @import line for the effective font ('' when a system font is used).
	 */
	public static function getModernFontsImport($settings)
	{
		$cfg = array_merge(self::getModernDefaults(), is_array($settings) ? $settings : array());
		$fonts = self::getModernFonts();
		if (isset($fonts[$cfg['font']]) && $fonts[$cfg['font']][2] !== NULL)
		{
			return "@import url('https://fonts.googleapis.com/css2?family=" . $fonts[$cfg['font']][2] . ":wght@400;500;600;700&display=swap');\n";
		}
		return '';
	}

	/**
	 * Modern: builds the "<selector>{--modern-*:...}" block from validated settings.
	 */
	public static function getModernCssBlock($settings, $selector)
	{
		if (empty($settings))
		{
			return '';
		}
		$lum = function ($hex) {
			$c = array();
			foreach (array(1, 3, 5) as $i)
			{
				$v = hexdec(substr($hex, $i, 2)) / 255;
				$c[] = $v <= 0.03928 ? $v / 12.92 : pow(($v + 0.055) / 1.055, 2.4);
			}
			return 0.2126 * $c[0] + 0.7152 * $c[1] + 0.0722 * $c[2];
		};
		$vars = array();
		if (isset($settings['colors']))
		{
			foreach ($settings['colors'] as $k => $v)
			{
				$vars[] = '--modern-' . str_replace('_', '-', $k) . ':' . $v;
			}
		}
		$fonts = self::getModernFonts();
		if (isset($settings['font']))
		{
			$vars[] = '--modern-font-head:' . $fonts[$settings['font']][1];
			$vars[] = '--modern-font-body:' . $fonts[$settings['font']][1];
		}
		if (isset($settings['fs']))
		{
			$vars[] = '--modern-fs:' . (int) $settings['fs'] . 'px';
		}
		if (isset($settings['radius']))
		{
			$radii = self::getModernRadii();
			$r = $radii[$settings['radius']];
			$vars[] = '--modern-r:' . $r[0];
			$vars[] = '--modern-rb:' . $r[1];
			$vars[] = '--modern-ri:' . $r[2];
			$vars[] = '--modern-cell:' . $r[3];
		}
		if (isset($settings['shadow']))
		{
			$shadows = self::getModernShadows();
			$vars[] = '--modern-shadow:' . $shadows[$settings['shadow']];
			if ($settings['shadow'] == 'none')
			{
				$vars[] = '--modern-dock-shadow:none';
			}
		}
		return empty($vars) ? '' : "\n/* Modern customization */\n" . $selector . "{" . join(';', $vars) . ";}\n";
	}

	/**
	 * Modern: returns the stylesheet (defaults + the saved customization) for one container.
	 */
	public static function getModernCss($option_arr, $selector, $file)
	{
		$settings = self::getModernSettings(isset($option_arr['o_modern_colors']) ? $option_arr['o_modern_colors'] : '');
		$css = '';
		ob_start();
		@readfile(PJ_CSS_PATH . 'themes/' . $file);
		$string = ob_get_contents();
		ob_end_clean();
		if ($string !== FALSE && $string !== '')
		{
			$css = str_replace('[calendarContainer]', $selector, $string);
		}
		# tooltips and messages live in <body>, outside the calendar container: they need the same variables
		$blockSelector = $selector;
		if (preg_match('/^#pjWrapperABC_(\d+)$/', $selector, $m))
		{
			$blockSelector .= ',#abCalendarTooltip_' . $m[1] . ',#abCalendarMessage_' . $m[1];
		}
		return $css . self::getModernCssBlock($settings, $blockSelector);
	}

	/**
	 * Colour of a calendar's name in the all-calendars tables: the main colour chosen in that calendar's appearance
	 * (Modern: the link colour of its palette; Classic: its month bar colour).
	 */
	public static function getRowNameColor($option_arr)
	{
		$defaults = self::getModernDefaults();
		$color = $defaults['colors']['link'];
		if (isset($option_arr['o_theme']) && $option_arr['o_theme'] == 'modern')
		{
			$settings = self::getModernSettings(isset($option_arr['o_modern_colors']) ? $option_arr['o_modern_colors'] : '');
			if (isset($settings['colors']['link']))
			{
				$color = $settings['colors']['link'];
			}
		} elseif (isset($option_arr['o_background_month']) && is_string($option_arr['o_background_month']) && preg_match('/^#[0-9a-fA-F]{6}$/', $option_arr['o_background_month'])) {
			$color = strtolower($option_arr['o_background_month']);
		}
		return $color;
	}

	/**
	 * Modern: the "--modern-*" block of ONE calendar row of the all-calendars tables (admin overview, front availability).
	 * A Modern calendar uses its own palette, font, radius and shadow; a Classic calendar uses its own classic colours (square cells).
	 */
	public static function getModernRowCss($option_arr, $selector)
	{
		$cfg = self::getModernDefaults();
		unset($cfg['palette']);
		if (isset($option_arr['o_theme']) && $option_arr['o_theme'] == 'modern')
		{
			$settings = self::getModernSettings(isset($option_arr['o_modern_colors']) ? $option_arr['o_modern_colors'] : '');
			foreach ($settings as $k => $v)
			{
				if ($k == 'palette')
				{
					continue;
				}
				if ($k == 'colors')
				{
					$cfg['colors'] = array_merge($cfg['colors'], $v);
				} else {
					$cfg[$k] = $v;
				}
			}
		} else {
			foreach (array('free' => 'available', 'booked' => 'booked', 'pending' => 'pending', 'past' => 'past') as $token => $name)
			{
				$bg = isset($option_arr['o_background_' . $name]) ? $option_arr['o_background_' . $name] : '';
				$fg = isset($option_arr['o_color_' . $name]) ? $option_arr['o_color_' . $name] : '';
				if (is_string($bg) && preg_match('/^#[0-9a-fA-F]{6}$/', $bg))
				{
					$cfg['colors'][$token] = strtolower($bg);
				}
				if (is_string($fg) && preg_match('/^#[0-9a-fA-F]{6}$/', $fg))
				{
					$cfg['colors'][$token . '_text'] = strtolower($fg);
				}
			}
			$cfg['radius'] = 'sharp';
		}
		return self::getModernCssBlock($cfg, $selector);
	}

	/**
	 * Modern: the Google Fonts @import lines (unique) needed by all calendars that use the Modern design.
	 */
	public static function getModernFontsImportAll($option_arr_list)
	{
		$out = array();
		foreach ($option_arr_list as $option_arr)
		{
			if (isset($option_arr['o_theme']) && $option_arr['o_theme'] == 'modern')
			{
				$line = self::getModernFontsImport(self::getModernSettings(isset($option_arr['o_modern_colors']) ? $option_arr['o_modern_colors'] : ''));
				if ($line !== '' && !in_array($line, $out))
				{
					$out[] = $line;
				}
			}
		}
		return join('', $out);
	}

	public $defaultCalendarId = 'admin_calendar_id';
	
	public $allowSetPricePerGuests = FALSE;
		
	public function pjActionCheckInstall()
	{
		$this->setLayout('pjActionEmpty');
		
		$result = array('status' => 'OK', 'code' => 200, 'text' => 'Operation succeeded', 'info' => array());
		$folders = array('app/web/upload');
		foreach ($folders as $dir)
		{
			if (!is_writable($dir))
			{
				$result['status'] = 'ERR';
				$result['code'] = 101;
				$result['text'] = 'Permission requirement';
				$result['info'][] = sprintf('Folder \'<span class="bold">%1$s</span>\' is not writable. You need to set write permissions (chmod 777) to directory located at \'<span class="bold">%1$s</span>\'', $dir);
			}
		}
		
		return $result;
	}
	
	public function beforeFilter()
    {
    	parent::beforeFilter();
    	if(!$this->_get->isEmpty('controller') && !in_array($this->_get->toString('controller'), array('pjFront', 'pjInstaller')))
    	{
    		$pjCalendarModel = pjCalendarModel::factory();
    		if ($this->isOwner())
    		{
    			$pjCalendarModel->where('t1.user_id', $this->getUserId());
    		}
	    	$calendars = $pjCalendarModel
				->select("t1.*, IF(t2.content IS NULL OR t2.content = '', t3.content, t2.content) AS `name`")
				->join('pjMultiLang', "t2.model='pjCalendar' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left')
				->join('pjMultiLang', "t3.model='pjCalendar' AND t3.foreign_id=t1.id AND t3.field='name' AND t3.locale='".(int) $this->getDefaultLocaleId()."'", 'left')
				->orderBy('t1.id ASC')
				->findAll()->getDataPair('id');
			$this->set('calendars', $calendars);
			
			if ($this->getCalendarId() === false && count($calendars) > 0)
			{
				$keys = array_keys($calendars);
				$this->setCalendarId($keys[0]);
				
			}
			
			$base_option_arr = pjBaseOptionModel::factory()->getPairs(1);
	        $script_option_arr = pjOptionModel::factory()->getPairs($this->getCalendarId());
	        $this->option_arr = array_merge($base_option_arr, $script_option_arr);
	        $this->set('option_arr', $this->option_arr);
	        
	        pjRegistry::getInstance()->set('options', $this->option_arr);
	        
	        $this->appendJs('pjAdminCore.js');
    	}
    	return true;
    }
	
	public function afterFilter()
	{
		parent::afterFilter();
	    if(!in_array($this->_get->toString('controller'), array('pjFront', 'pjInstaller')))
	    {
	        $this->appendCss('admin.css');
	    }
	}
	
	public function pjActionAfterInstall()
	{
		$this->setLayout('pjActionEmpty');
		
		$result = array('status' => 'OK', 'code' => 200, 'text' => 'Operation succeeded', 'info' => array());
	    $pjAuthRolePermissionModel = pjAuthRolePermissionModel::factory();
	    $pjAuthUserPermissionModel = pjAuthUserPermissionModel::factory();
	    
	    $permissions = pjAuthPermissionModel::factory()->findAll()->getDataPair('key', 'id');
	    
	    $roles = array(1 => 'admin', 2 => 'editor', 3 => 'owner');
	    foreach ($roles as $role_id => $role)
	    {
	        if (isset($GLOBALS['CONFIG'], $GLOBALS['CONFIG']["role_permissions_{$role}"])
	        && is_array($GLOBALS['CONFIG']["role_permissions_{$role}"])
	        && !empty($GLOBALS['CONFIG']["role_permissions_{$role}"]))
	        {
	            $pjAuthRolePermissionModel->reset()->where('role_id', $role_id)->eraseAll();
	            
	            foreach ($GLOBALS['CONFIG']["role_permissions_{$role}"] as $role_permission)
	            {
	                if($role_permission == '*')
	                {
	                    // Grant full permissions for the role
	                    foreach($permissions as $key => $permission_id)
	                    {
	                        $pjAuthRolePermissionModel->setAttributes(compact('role_id', 'permission_id'))->insert();
	                    }
	                    break;
	                }
	                else
	                {
	                    $hasAsterix = strpos($role_permission, '*') !== false;
	                    if($hasAsterix)
	                    {
	                        $role_permission = str_replace('*', '', $role_permission);
	                    }
	                    
	                    foreach($permissions as $key => $permission_id)
	                    {
	                        if($role_permission == $key || ($hasAsterix && strpos($key, $role_permission) !== false))
	                        {
	                            $pjAuthRolePermissionModel->setAttributes(compact('role_id', 'permission_id'))->insert();
	                        }
	                    }
	                }
	            }
	        }
	    }
	    pjAuthRoleModel::factory()->setAttributes(array('id' => 3, 'role' => 'Owner', 'is_backend' => 'T', 'is_admin' => 'F'))->insert();
	    
	    // Grant full permissions to Admin
	    $user_id = 1; // Admin ID
	    $pjAuthUserPermissionModel->reset()->where('user_id', $user_id)->eraseAll();
	    foreach($permissions as $key => $permission_id)
	    {
	        $pjAuthUserPermissionModel->setAttributes(compact('user_id', 'permission_id'))->insert();
	    }
	    
		$id = pjCalendarModel::factory()->setAttributes(array('user_id' => 1, 'uuid' => pjUtil::uuid()))->insert()->getInsertId();
		if ($id !== false && (int) $id > 0)
		{
			pjMultiLangModel::factory()->saveMultiLang(array(
				1 => array('name' => 'Calendar 1')
			), $id, 'pjCalendar');
			
			$pjOptionModel = pjOptionModel::factory();
			$pjOptionModel->init($id);
			$pjOptionModel->initConfirmation($id, null);

			$data = $data = $pjOptionModel->reset()->getAllPairs($id);
			pjUtil::pjActionGenerateImages($id, $data);
		}
		
		return array('status' => 'OK', 'code' => 200, 'text' => 'Operation succeeded');
	}
    
	public function getCalendarId()
	{
	    if (isset($_SESSION[$this->defaultCalendarId]))
	    {
	        return $_SESSION[$this->defaultCalendarId];
	    }
	    return false;
	}
	
	public function setCalendarId($calendar_id)
	{
	    $_SESSION[$this->defaultCalendarId] = (int) $calendar_id;
	    return $this;
	}
	
    public static function getTokens($booking_arr, $option_arr, $locale_id)
    {
    	$payment_methods = __('payment_methods', true);
    	$na = __('lblNA', true, false);
    	$c_name = !empty($booking_arr['c_name']) ? @$booking_arr['c_name'] : $na;
    	$c_email = !empty($booking_arr['c_email']) ? @$booking_arr['c_email'] : $na;
    	$c_phone = !empty($booking_arr['c_phone']) ? @$booking_arr['c_phone'] : $na;
    	$c_adults = $booking_arr['c_adults'] != '' ? @$booking_arr['c_adults'] : $na;
    	$c_children = $booking_arr['c_children'] != '' ? @$booking_arr['c_children'] : $na;
    	$c_notes = !empty($booking_arr['c_notes']) ? @$booking_arr['c_notes'] : $na;
    	$c_address = !empty($booking_arr['c_address']) ? @$booking_arr['c_address'] : $na;
    	$c_city = !empty($booking_arr['c_city']) ? @$booking_arr['c_city'] : $na;
    	$country = !empty($booking_arr['country']) ? @$booking_arr['country'] : $na;
    	$c_state = !empty($booking_arr['c_state']) ? @$booking_arr['c_state'] : $na;
    	$c_zip = !empty($booking_arr['c_zip']) ? @$booking_arr['c_zip'] : $na;
    	$cc_type = !empty($booking_arr['cc_type']) ? @$booking_arr['cc_type'] : $na;
    	$cc_num = !empty($booking_arr['cc_num']) ? @$booking_arr['cc_num'] : $na;
    	$cc_exp_month = @$booking_arr['payment_method'] == 'creditcard' ? (!empty($booking_arr['cc_exp_month']) ? @$booking_arr['cc_exp_month'] : $na) : $na;
    	$cc_exp_year = @$booking_arr['payment_method'] == 'creditcard' ? (!empty($booking_arr['cc_exp_year']) ? @$booking_arr['cc_exp_year'] : $na) : $na;
    	$cc_code = !empty($booking_arr['cc_code']) ? @$booking_arr['cc_code'] : $na;
    	$payment_method = !empty($booking_arr['payment_method']) ? $payment_methods[$booking_arr['payment_method']] : $na;
    	
    	$tax = pjCurrency::formatPrice($booking_arr['tax'], " ", NULL, $option_arr['o_currency']);
    	# {Price} is shown BEFORE the voucher discount (amount is stored after it), matching the booking form breakdown
    	$amount = pjCurrency::formatPrice((float) $booking_arr['amount'] + (!empty($booking_arr['voucher_code']) && isset($booking_arr['discount']) ? (float) $booking_arr['discount'] : 0), " ", NULL, $option_arr['o_currency']);
		if (!isset($booking_arr['total'])) {
			$total =  $booking_arr['amount'] + $booking_arr['tax'];
		}
		else {
			$total = $booking_arr['total'];
		}
    	$total_price =  pjCurrency::formatPrice($total, " ", NULL, $option_arr['o_currency']);
    	$deposit = pjCurrency::formatPrice($booking_arr['deposit'], " ", NULL, $option_arr['o_currency']);
    	
    	$cancelURL = sprintf("%sindex.php?controller=pjFront&action=pjActionCancel&cid=%u&locale=%u&id=%u&hash=%s", PJ_INSTALL_URL, @$booking_arr['calendar_id'], $locale_id, @$booking_arr['id'], sha1(@$booking_arr['id'] . PJ_SALT));
    	$cancelURL = '<a href="'.$cancelURL.'">'.$cancelURL.'</a>';
    	
    	$calendar_name = '';
		if(isset($booking_arr['calendar_id']) && (int) $booking_arr['calendar_id'] > 0)
		{
			$calendar = pjCalendarModel::factory()
			->select("t1.*, IF(t2.content IS NULL OR t2.content = '', t3.content, t2.content) as name")
			->join('pjMultiLang', "t2.foreign_id = t1.id AND t2.model = 'pjCalendar' AND t2.locale = '".$locale_id."' AND t2.field = 'name'", 'left')
			->join('pjMultiLang', "t3.foreign_id = t1.id AND t3.model = 'pjCalendar' AND t3.locale = '".(int) (($dl = pjLocaleModel::factory()->where('t1.is_default', 1)->limit(1)->findAll()->getData()) ? $dl[0]['id'] : 0)."' AND t3.field = 'name'", 'left')
			->find($booking_arr['calendar_id'])->getData();
			$calendar_name = $calendar['name'];
		}
		
    	$search = array(
    		'{Name}', '{Email}', '{Phone}', '{Adults}', '{Children}',
    		'{Notes}', '{Address}', '{City}', '{Country}', '{State}',
    		'{Zip}', '{CCType}', '{CCNum}', '{CCExpMonth}', '{CCExpYear}',
    		'{CCSec}', '{PaymentMethod}', '{StartDate}', '{EndDate}', '{Deposit}',
    		'{Tax}', '{Price}', '{TotalPrice}','{CalendarID}', '{ReservationID}',
    		'{ReservationUUID}', '{CancelURL}', '{CalendarName}', '{Voucher}', '{Discount}');
		$replace = array(
			$c_name, $c_email, $c_phone, $c_adults, $c_children,
			$c_notes, $c_address, $c_city, $country, $c_state,
			$c_zip, $cc_type, $cc_num, $cc_exp_month, $cc_exp_year,
			$cc_code, $payment_method, date(@$option_arr['o_date_format'], strtotime(@$booking_arr['date_from'])), date(@$option_arr['o_date_format'], strtotime(@$booking_arr['date_to'])), @$deposit,
			$tax,  $amount, @$total_price, @$booking_arr['calendar_id'], @$booking_arr['id'],
			@$booking_arr['uuid'], $cancelURL, $calendar_name,
			!empty($booking_arr['voucher_code']) ? $booking_arr['voucher_code'] : $na,
			pjCurrency::formatPrice(isset($booking_arr['discount']) ? (float) $booking_arr['discount'] : 0, " ", NULL, $option_arr['o_currency'])
		);
		return compact('search', 'replace');
    }
    
	public static function getSubjectMessage($notification, $locale_id)
    {
    	$variant = $notification['variant'] == 'confirmation' ? 'confirm' : $notification['variant'];
        $field = $variant . '_tokens_' . $notification['recipient'];
        $pjMultiLangModel = pjMultiLangModel::factory();
        $lang_message = $pjMultiLangModel
        ->reset()
        ->select('t1.*')
        ->where('t1.foreign_id', $notification['id'])
        ->where('t1.model','pjNotification')
        ->where('t1.locale', $locale_id)
        ->where('t1.field', $field)
        ->limit(0, 1)
        ->findAll()
        ->getData();
        $field = $variant . '_subject_' . $notification['recipient'];
        $lang_subject = $pjMultiLangModel
        ->reset()
        ->select('t1.*')
        ->where('t1.foreign_id',  $notification['id'])
        ->where('t1.model','pjNotification')
        ->where('t1.locale', $locale_id)
        ->where('t1.field', $field)
        ->limit(0, 1)
        ->findAll()
        ->getData();
        return compact('lang_message', 'lang_subject');
    }
    
	public static function getSmsMessage($notification, $locale_id)
    {
    	$variant = $notification['variant'] == 'confirmation' ? 'confirm' : $notification['variant'];
        $field = $variant . '_sms_' . $notification['recipient'];
        $pjMultiLangModel = pjMultiLangModel::factory();
        $lang_message = $pjMultiLangModel
        ->reset()
        ->select('t1.*')
        ->where('t1.foreign_id', $notification['id'])
        ->where('t1.model','pjNotification')
        ->where('t1.locale', $locale_id)
        ->where('t1.field', $field)
        ->limit(0, 1)
        ->findAll()
        ->getData();
        return compact('lang_message');
    }

    public static function jsonDecode($str)
	{
		$Services_JSON = new pjServices_JSON();
		return $Services_JSON->decode($str);
	}
	
	public static function jsonEncode($arr)
	{
		$Services_JSON = new pjServices_JSON();
		return $Services_JSON->encode($arr);
	}
	
	public static function jsonResponse($arr)
	{
		header("Content-Type: application/json; charset=utf-8");
		echo pjAppController::jsonEncode($arr);
		exit;
	}

	public function isEditor()
	{
		return $this->getRoleId() == 2;
	}

	public function isOwner()
	{
		return $this->getRoleId() == 3;
	}
	
	public function isPriceReady()
	{
		return $this->isAdmin() || $this->isEditor() || $this->isOwner();
	}
	
	public function isPeriodReady()
	{
		return $this->isAdmin() || $this->isEditor() || $this->isOwner();
	}
	
	public function isInvoiceReady()
	{
		return $this->isAdmin() || $this->isEditor() || $this->isOwner();
	}
	
	public function isCountryReady()
	{
		return $this->isAdmin();
	}
	
	public function isOneAdminReady()
	{
		return $this->isAdmin();
	}

	# The ID of the default language (set in Options -> Languages); 0 when there is none
	public function getDefaultLocaleId()
	{
		$arr = pjLocaleModel::factory()->where('t1.is_default', 1)->limit(1)->findAll()->getData();
		return count($arr) === 1 ? (int) $arr[0]['id'] : 0;
	}

	public function getLocaleId()
	{
		return isset($_SESSION[$this->defaultLocale]) && (int) $_SESSION[$this->defaultLocale] > 0 ? (int) $_SESSION[$this->defaultLocale] : false;
	}
	public function setLocaleId($locale_id)
	{
		$_SESSION[$this->defaultLocale] = (int) $locale_id;
	}
	
	protected function pjActionCheckDt($date_from, $date_to, $calendar_id=NULL, $id=NULL, $backend=false)
	{
		$calendar_id = !empty($calendar_id) ? (int) $calendar_id : $this->getCalendarId();
		
		if ($backend && $calendar_id != $this->getCalendarId())
		{
			$option_arr = pjOptionModel::factory()->getPairs($calendar_id);
		} else {
			$option_arr = $this->option_arr;
		}
		$front_err_msg = __('front_err', true, true);
		$validate_booking_msg = array(1 => (string) @$front_err_msg['fully_booked'], 2 => (string) @$front_err_msg['fully_booked']);
		if ($option_arr['o_price_based_on'] == 'nights' && $date_from == $date_to && $option_arr['o_booking_behavior'] == 1)
		{
			return array('status' => 'ERR', 'code' => 100, 'text' => $validate_booking_msg[1]);
		}
		
		$pjReservationModel = pjReservationModel::factory();
		$pjLimitModel = pjLimitModel::factory();
		
		$info = $pjReservationModel
			->prepare(sprintf("SELECT `date_from`, `date_to` 
				FROM `%1\$s`
				WHERE `calendar_id` = :calendar_id
				%2\$s
				AND `status` != :status
				AND ((`date_from` BETWEEN :date_from AND :date_to)
				OR ( `date_to` BETWEEN :date_from AND :date_to)
				OR ( `date_from` <= :date_from AND `date_to` >= :date_to))",
				$pjReservationModel->getTable(), (!empty($id) ? " AND `id` != :id" : NULL),
				($option_arr['o_price_based_on'] == 'nights' ? '<' : '<='),
				($option_arr['o_price_based_on'] == 'nights' ? '>' : '>=')
			))
			->exec(array(
				'calendar_id' => $calendar_id,
				'status' => 'Cancelled',
				'date_from' => $date_from,
				'date_to' => $date_to,
				'id' => $id
			))
			->getData();
	
		$morning = array();
		$afternoon = array();
		$av_arr = array();
		$booked_arr = array();
		$nights_mode = false;
		if ($option_arr['o_price_based_on'] == 'nights')
		{ 
			$nights_mode = true;
		}
		if(isset($info) && count($info)  >0)
		{
			foreach ($info as $res)
			{
				$dt_from = strtotime($res['date_from']);
				$dt_to = strtotime($res['date_to']);
				for($i = $dt_from; $i <= $dt_to; $i = strtotime('+1 day', $i))
				{
					if(!empty($res['price_based_on']) && in_array($res['price_based_on'], array('nights', 'days')))
					{
						if($res['price_based_on'] == 'nights')
						{
							$nights_mode = true;
						}
					}
					if (!isset($afternoon[$i])) {
						$afternoon[$i] = 0;
					}
					if (!isset($morning[$i])) {
						$morning[$i] = 0;
					}
					if (!isset($booked_arr[$i])) {
						$booked_arr[$i] = 0;
					}
					if ($i == $dt_from && $nights_mode){
						$afternoon[$i] += 1;
					}elseif ($i == $dt_to && $nights_mode) {
						$morning[$i] += 1;
					}else {
						$booked_arr[$i] += 1;
					}
				}
			}
		}
		$s_from = strtotime($date_from);
		$s_to = strtotime($date_to);	
  		for($z = $s_from; $z <= $s_to; $z = strtotime('+1 day', $z))
		{
			if(isset($booked_arr[$z]) || isset($morning[$z]) || isset($afternoon[$z])) 
			{
				$booked_value = isset($booked_arr[$z]) ? $booked_arr[$z] : 0;
				$monring_value = isset($morning[$z]) ? $morning[$z] : 0;
				$afternoon_value = isset($afternoon[$z]) ? $afternoon[$z] : 0;
				
				$booked_value += min($monring_value,$afternoon_value);
				$morning[$z] -= min($monring_value, $afternoon_value);
				$afternoon[$z] -= min($monring_value, $afternoon_value);
				
				$av_arr[$z] = $booked_value;
				if($morning[$z] >= $afternoon[$z])
				{
					if($z > $s_from && $z <= $s_to)
					{
						$av_arr[$z] = $booked_value + ($morning[$z] );
					}
				}else{
					if($z >= $s_from && $z < $s_to)
					{
						$av_arr[$z] = $booked_value + ($afternoon[$z]);
					}
				}
			}else{
				$av_arr[$z] = 0;
			}
		}
		$cnt = max($av_arr);
		
		if(empty($id))
		{
			if ($cnt < (int) $option_arr['o_bookings_per_day'])
			{
				$result = array('status' => 'OK', 'code' => 200, 'text' => '');
			} else {
				$result = array('status' => 'ERR', 'code' => 100, 'text' => $validate_booking_msg[2]);
			}
		}else{
			if ($cnt < (int) $option_arr['o_bookings_per_day'])
			{
				$result = array('status' => 'OK', 'code' => 200, 'text' => '');
			} else {
				$result = array('status' => 'ERR', 'code' => 100, 'text' => $validate_booking_msg[2]);
			}
		}
		return $result;
	}
	
	/**
	 * True when a rate (price > 0) exists for the given stay. Used by the admin and front-end to refuse reservations without a rate.
	 */
	protected function pjActionHasRate($calendar_id, $start_dt, $end_dt, $adults, $children, $option_arr)
	{
		try {
			$p = $this->pjActionCalcPrices($calendar_id, $start_dt, $end_dt, $adults, $children, $option_arr, $this->getLocaleId());
		} catch (Exception $ex) {
			return false;
		}
		return (float) $p['price_before_discount'] > 0;
	}

	protected function pjActionCalcPrices($calendar_id, $start_dt, $end_dt, $adults, $children, $option_arr, $locale_id, $voucher_code = NULL)
	{
		$discount = 0;
		$voucher_status = NULL;
		$price_before_discount = 0;
		$amount = 0;
		$sub_total = 0;
		$tax = 0;
		$total = 0;
		$deposit = 0;
		$net = 0;
		$nights = $end_dt > $start_dt ? ceil(($end_dt - $start_dt) / 86400) : 0;
		$early_nights = ceil(($start_dt - time()) / 86400);
		if ($option_arr['o_price_based_on'] == 'days')
		{
			$nights += 1;
			$early_nights += 1;
		}
		
		$price_arr = array();
		if ($option_arr['o_price_plugin'] == 'price')
		{
			$price_arr = pjPriceModel::factory()->getPrice(
					$calendar_id,
					date("Y-m-d", $start_dt),
					date("Y-m-d", $end_dt),
					$this->option_arr,
					@$adults,
					(int) $option_arr['o_bf_children'] !== 1 ? @$children : 0
			);
		
		} elseif ($option_arr['o_price_plugin'] == 'period') {
			$price_arr = pjPeriodModel::factory()->getPrice(
					$calendar_id,
					date("Y-m-d", $start_dt),
					date("Y-m-d", $end_dt),
					$this->option_arr,
					@$adults,
					(int) $option_arr['o_bf_children'] !== 1 ? @$children : 0
			);
		}
		$net = $price_arr['net'];
		$amount = $price_arr['amount'];
		$price_before_discount = $amount;
		$voucher_code = trim((string) $voucher_code);
		if ($voucher_code !== '' && pjObject::getPlugin('pjVouchers') !== NULL)
		{
			$voucher = pjVoucherModel::factory()->getVoucher($voucher_code, date('Y-m-d', $start_dt));
			if ($voucher['status'] == 'OK' && $amount > 0)
			{
				$voucher_status = 'OK';
				if ($voucher['arr']['type'] == 'percent')
				{
					$discount = round($amount * (float) $voucher['arr']['discount'] / 100, 2);
				} else {
					$discount = (float) $voucher['arr']['discount'];
				}
				$discount = min($discount, $amount);
				$amount = round($amount - $discount, 2);
				$voucher_code = $voucher['arr']['code'];
			} else {
				$voucher_status = $voucher['status'] == 'OK' ? 'ERR' : ($voucher['code'] == '100' ? 'INVALID' : 'OUT_OF_RANGE');
				$voucher_code = '';
			}
		} else {
			$voucher_code = '';
		}
		if ($amount > 0 && isset($option_arr['o_tax']) && (float) $option_arr['o_tax'] > 0)
		{
			$tax = round(($amount * (float) $option_arr['o_tax']) / 100, 2);
		}
		$total = round($amount + $tax, 2);
		if (isset($option_arr['o_require_all_within']) && (int) $option_arr['o_require_all_within'] > 0 &&
				strtotime(date("Y-m-d")) + (int) $option_arr['o_require_all_within'] * 86400 >= $start_dt)
		{			
			$deposit = $total;				
		} elseif (isset($option_arr['o_deposit']) && (float) $option_arr['o_deposit'] > 0) {				
			switch ($option_arr['o_deposit_type'])
			{
				case 'percent':
					$deposit = round(($total * (float) $option_arr['o_deposit']) / 100, 2);
					break;
				case 'amount':
					$deposit = (float) $option_arr['o_deposit'];
					break;
			}
		}
		if ($deposit > $total)
		{
			# A deposit can never be more than the total price
			$deposit = $total;
		}		
		$result = compact('nights', 'early_nights', 'amount', 'deposit', 'tax', 'total', 'net', 'discount', 'voucher_code', 'voucher_status', 'price_before_discount');
		
		return $result;
	}
	static public function getFromEmail($option_arr)
	{
		$email = $option_arr['o_email_address'];
		if($email == '')
		{
			$arr = pjAuthUserModel::factory()
				->findAll()
				->orderBy("t1.id ASC")
				->limit(1)
				->getData();
			$email = !empty($arr) ? $arr[0]['email'] : null;
		}
		return $email;
	}
	static public function getOwnerEmail($calendar_id)
	{
		$arr = pjCalendarModel::factory()->select("t1.*, t2.email")->join("pjAuthUser", "t1.user_id=t2.id", 'left')->find($calendar_id)->getData();
		if(!empty($arr))
		{
			if(!empty($arr['email']))
			{
				return $arr['email'];
			}else{
				return null;
			}
		}else{
			return null;
		}
	}
	static public function getOwnerPhone($calendar_id)
	{
		$arr = pjCalendarModel::factory()->select("t1.*, t2.phone")->join("pjAuthUser", "t1.user_id=t2.id", 'left')->find($calendar_id)->getData();
		if(!empty($arr))
		{
			if(!empty($arr['phone']))
			{
				return $arr['phone'];
			}else{
				return null;
			}
		}else{
			return null;
		}
	}
	static public function getOwnerCalendarIds($owner_id)
	{
		$arr = pjCalendarModel::factory()->select("t1.id")->where('t1.user_id', $owner_id)->findAll()->getDataPair(null, 'id');
		return $arr;
	}
	
	public static function syncFeeds($feed_id)
    {
        $feed = pjFeedModel::factory()->find($feed_id)->getData();       
        if ($feed)
        {
        	$option_arr = pjOptionModel::factory()->getPairs($feed['calendar_id']);
            $pjReservationModel = pjReservationModel::factory();
            if (!empty($feed['url']))
            {
                if(!class_exists ('iCalEasyReader'))
                {
                    include ( PJ_COMPONENTS_PATH . 'iCalEasyReader.php' );
                }
                $ical = new iCalEasyReader();
                $fead_contents = file_get_contents($feed['url']);
                $lines = $ical->load( $fead_contents );
                
                if (!empty($lines) && isset($lines['VEVENT']) && !empty($lines['VEVENT'])) 
                {
                    $pjReservationModel
	                    ->reset()
	                    ->where('calendar_id', $feed['calendar_id'])
	                    ->where('provider_id', $feed['provider_id'])
	                    ->eraseAll();
                    $pjReservationModel->reset()->begin();
                    
                    foreach($lines['VEVENT'] as $item)
                    {
                        $booking = array();
                        $booking['calendar_id'] = $feed['calendar_id'];
                        $booking['provider_id'] = $feed['provider_id'];
                        $booking['price_based_on'] = $option_arr['o_price_based_on'];
                        $booking['status'] = 'Confirmed';
                        
                        if(isset($item['UID']) && !empty($item['UID']))
                        {
                            $booking['uuid'] = $item['UID'];
                        }else{
                            $booking['uuid'] = pjUtil::uuid();
                        }
                        if(isset($item['DTSTART']))
                        {
                            if(is_array($item['DTSTART']))
                            {
                                if(isset($item['DTSTART']['value']) && !empty($item['DTSTART']['value']))
                                {
                                    $booking['date_from'] = substr($item['DTSTART']['value'],0,4) . '-' . substr($item['DTSTART']['value'],4,2) . '-' . substr($item['DTSTART']['value'],6,2);
                                }
                            }else if(!empty($item['DTSTART'])){
                                $booking['date_from'] = substr($item['DTSTART'],0,4) . '-' . substr($item['DTSTART'],4,2) . '-' . substr($item['DTSTART'],6,2);
                            }
                        }
                        if(isset($item['DTEND']))
                        {
                            if(is_array($item['DTEND']))
                            {
                                if(isset($item['DTEND']['value']) && !empty($item['DTEND']['value']))
                                {
                                    $booking['date_to'] = substr($item['DTEND']['value'],0,4) . '-' . substr($item['DTEND']['value'],4,2) . '-' . substr($item['DTEND']['value'],6,2);
                                }
                            }else if(!empty($item['DTEND'])){
                                $booking['date_to'] = substr($item['DTEND'],0,4) . '-' . substr($item['DTEND'],4,2) . '-' . substr($item['DTEND'],6,2);
                            }
                        }
                        if(isset($item['SUMMARY']) && !empty($item['SUMMARY']))
                        {
                            if (in_array($feed['provider_id'], array(2)))
                            {
                                $booking['status'] = (preg_match('/Cancelled|cancelled/i', $item['SUMMARY'])) ? 'Cancelled' : 'Pending';
                            }
                            if (in_array($feed['provider_id'], array(3,4)))
                            {
                                $booking['status'] = (preg_match('/Reserved|Blocked/i', $item['SUMMARY'])) ? 'Confirmed' : 'Pending';
                            }
                            if (strpos($item['SUMMARY'], '-') == true) {
                                $name = explode('-', $item['SUMMARY']);
                                $booking['c_name'] = (!empty($name) && $name[1]) ? trim($name[1]) : trim($item['SUMMARY']);
                            } else {
                                $booking['c_name'] = $item['SUMMARY'];
                            }
                        }
                        if (!empty($booking['date_to']) && $booking['date_to'] >= date('Y-m-d') && $booking['status'] != 'Cancelled')
                        {
                            $pjReservationModel->reset()->setAttributes($booking)->insert();
                        }
                    }
                    $pjReservationModel->commit();
                }
            }
        }
        
        return array('status' => 'OK', 'code' => 200);
    }
}
?>