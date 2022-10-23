<?php

namespace GHSVS\Component\GhsThing\Administrator\Traits;

\defined('_JEXEC') or die;

use Joomla\CMS\Session\Session;

trait MY_CON
{
	public $MY_CON = null;

	public function init_MY_CON()
	{
		if ($this->MY_CON === null)
		{
			$this->MY_CON = new \stdClass();
			$this->MY_CON->context = 'com_ghsthing.ghsthing';
			$this->MY_CON->option = 'com_ghsthing';
			$this->MY_CON->vSingle = 'ghsthing';
			$this->MY_CON->vList = 'ghsthings';
		}
	}

	public function getOrderingColumn($listOrder)
	{
		if (strpos($listOrder, 'publish_up') !== false) {
			return 'publish_up';
		} elseif (strpos($listOrder, 'publish_down') !== false) {
			return 'publish_down';
		} elseif (strpos($listOrder, 'modified') !== false) {
			return 'modified';
		} else {
			return 'created';
		}
	}

	public function getSaveOrderingUrl($task = null, $option = null)
	{echo ' 4654sd48sa7d98sD81s8d71dsa <pre>' . print_r($this->items, true) . '</pre>';exit;
		return 'index.php?option=' . ($option ?? $this->MY_CON->option)
			. '&task=' . ($task ?? $this->MY_CON->vList) . '.saveOrderAjax&tmpl=component&'
			. Session::getFormToken() . '=1';
	}
}
