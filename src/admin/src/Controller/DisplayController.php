<?php
// This is a basic or general controller for the administrator part.
// This controller will display the 'ghsthings' view by default.
namespace GHSVS\Component\GhsThing\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

class DisplayController extends BaseController
{
 protected $default_view = 'ghsthings';

 public function display($cachable = false, $urlparams = array())
 {
   return parent::display($cachable, $urlparams);
 }
}
