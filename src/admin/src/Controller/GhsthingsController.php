<?php
namespace GHSVS\Component\GhsThing\Administrator\Controller;

\defined('_JEXEC') or die;

class GhsthingsController extends AdminController
{
	public function getModel($name = 'Ghsthings', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
}
