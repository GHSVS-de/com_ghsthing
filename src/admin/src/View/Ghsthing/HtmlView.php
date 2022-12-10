<?php
// Class HtmlView extends BaseHtmlView. The BaseHtmlView is the base class for a Joomla! View. The view gets the data from the model to be output by the layout file.

namespace GHSVS\Component\GhsThing\Administrator\View\Ghsthing;

defined('_JEXEC') or die;

use GHSVS\Component\GhsThing\Administrator\Traits\MY_CON;

use Joomla\CMS\Log\Log;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
	use MY_CON;

	/**
	* The \JForm object
	*
	* @var  \Joomla\CMS\Form\Form
	*/
	protected $form;

	/**
	* The active item
	*
	* @var  object
	*/
	protected $item;

	/**
		* The model state
		*
		* @var  object
		*/
	protected $state;

	/**
		* The model state
		*
		* @var  object
		*/
	protected $fields;

	public function display($tpl = null)
	{
		// Trait things for Layouts.
		$this->init_MY_CON();

		$this->state = $this->get('State');
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		$this->addToolBar();
		parent::display($tpl);
	}

	protected function addToolBar()
	{
		ToolbarHelper::title(Text::_('COM_GHSTHING_GHSTHING_ADD'));
		ToolbarHelper::apply('ghsthing.apply');
		ToolbarHelper::save('ghsthing.save');
		ToolbarHelper::cancel('ghsthing.cancel', 'JTOOLBAR_CLOSE');

		// ToDo. Development-Krücke!
		$itemEditable = 1;

		if (ComponentHelper::isEnabled('com_contenthistory')
			&& $this->state->params->get('save_history', 1) && $itemEditable) {
				ToolbarHelper::versions('com_ghsthing.ghsthing', $this->item->id);
		}

		ToolbarHelper::inlinehelp();
	}
}
