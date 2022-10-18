<?php
// Class HtmlView extends BaseHtmlView. The BaseHtmlView is the base class for a Joomla! View. The view gets the data from the model to be output by the layout file.

namespace GHSVS\Component\GhsThing\Administrator\View\Ghsthing;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
	public $form;

	public function display($tpl = null)
	{
		$this->form = $this->get('Form');
		$this->addToolBar();
		parent::display($tpl);
	}

	protected function addToolBar()
	{
		ToolbarHelper::title(Text::_('COM_GHSVSTHING_GHSVSTHING_ADD'));
		ToolbarHelper::apply('ghsvsthing.apply');
	}
}
