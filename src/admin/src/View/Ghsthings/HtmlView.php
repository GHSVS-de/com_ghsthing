<?php
// Class HtmlView extends BaseHtmlView. The BaseHtmlView is the base class for a Joomla! View. The view gets the data from the model to be output by the layout file.

namespace GHSVS\Component\GhsThing\Administrator\View\Ghsthings;

defined('_JEXEC') or die;

use GHSVS\Component\GhsThing\Administrator\Traits\MY_CON;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
	use MY_CON;
	/**
		* An array of items
		*
		* @var  array
		*/
	protected $items;

	/**
		* The pagination object
		*
		* @var  \Joomla\CMS\Pagination\Pagination
		*/
	protected $pagination;

	/**
		* The model state
		*
		* @var   \Joomla\CMS\Object\CMSObject
		*/
	protected $state;

	/**
		* Form object for search filters
		*
		* @var  \Joomla\CMS\Form\Form
		*/
	public $filterForm;

	/**
		* The active search filters
		*
		* @var  array
		*/
	public $activeFilters;

	public function display($tpl = null)
	{
		// Trait things for Layouts.
		$this->init_MY_CON();

		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');

		// Kümmert sich, ob Searchtools eingeblendet bleiben oder nicht.
		$this->activeFilters = $this->get('ActiveFilters');

		// Gibt die Möglichkeit, das Verhalten via z.B.
		//unset($this->activeFilters['language']);
		// zu deaktivieren

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
		* Add the page title and toolbar.
		*
		* @return  void
		*
		* @since   1.6
		*/
	protected function addToolbar()
	{
			$canDo = ContentHelper::getActions('com_ghsthing', 'category', $this->state->get('filter.category_id'));
			#$user  = Factory::getApplication()->getIdentity();
			$user = $this->getCurrentUser();

			// Get the toolbar object instance
			$toolbar = Toolbar::getInstance('toolbar');

			ToolbarHelper::title(Text::_('COM_GHSTHING_MANAGER_GHSTHINGS'), 'address-book ghsthing');

			if ($canDo->get('core.create') || \count($user->getAuthorisedCategories('com_ghsthing', 'core.create')) > 0) {
					$toolbar->addNew('ghsthing.add');
			}

			if ($canDo->get('core.edit.state')) {
					$dropdown = $toolbar->dropdownButton('status-group')
							->text('JTOOLBAR_CHANGE_STATUS')
							->toggleSplit(false)
							->icon('icon-ellipsis-h')
							->buttonClass('btn btn-action')
							->listCheck(true);

					$childBar = $dropdown->getChildToolbar();

					$childBar->publish('ghsthings.publish')->listCheck(true);

					$childBar->unpublish('ghsthings.unpublish')->listCheck(true);

					$childBar->standardButton('featured')
							->text('JFEATURE')
							->task('ghsthings.featured')
							->listCheck(true);
					$childBar->standardButton('unfeatured')
							->text('JUNFEATURE')
							->task('ghsthings.unfeatured')
							->listCheck(true);

					$childBar->archive('ghsthings.archive')->listCheck(true);

					if ($user->authorise('core.admin')) {
							$childBar->checkin('ghsthings.checkin')->listCheck(true);
					}

					if ($this->state->get('filter.state') != -2) {
							$childBar->trash('ghsthings.trash')->listCheck(true);
					}

					// Add a batch button
					if (
							$user->authorise('core.create', 'com_ghsthing')
							&& $user->authorise('core.edit', 'com_ghsthing')
							&& $user->authorise('core.edit.state', 'com_ghsthing')
					) {
							$childBar->popupButton('batch')
									->text('JTOOLBAR_BATCH')
									->selector('collapseModal')
									->listCheck(true);
					}
			}

			if ($this->state->get('filter.state') == -2 && $canDo->get('core.delete')) {
					$toolbar->delete('ghsthings.delete')
							->text('JTOOLBAR_EMPTY_TRASH')
							->message('JGLOBAL_CONFIRM_DELETE')
							->listCheck(true);
			}

			if ($user->authorise('core.admin', 'com_ghsthing') || $user->authorise('core.options', 'com_ghsthing')) {
					$toolbar->preferences('com_ghsthing');
			}

			//$toolbar->help('Ghsthings');
	}
}
