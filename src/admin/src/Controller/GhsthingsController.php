<?php
namespace GHSVS\Component\GhsThing\Administrator\Controller;

\defined('_JEXEC') or die;

use GHSVS\Component\GhsThing\Administrator\Traits\MY_CON;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

class GhsthingsController extends AdminController
{
	use MY_CON;

	/**
		* Constructor.
		*
		* @param   array                $config   An optional associative array of configuration settings.
		* Recognized key values include 'name', 'default_task', 'model_path', and
		* 'view_path' (this list is not meant to be comprehensive).
		* @param   MVCFactoryInterface  $factory  The factory.
		* @param   CMSApplication       $app      The Application for the dispatcher
		* @param   Input                $input    Input
		*
		* @since   3.0
		*/
	public function __construct($config = [], MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);
		$this->registerTask('unfeatured', 'featured');

		$this->init_MY_CON();
	}

	/**
		* Method to toggle the featured setting of a list of items.
		*
		* @return  void
		*
		* @since   1.6
		*/
	public function featured()
	{
		// Shortcut for constants from trait MY_CON.php
		$C = $this->MY_CON;

		/*
		Check for request forgeries.
		Geht über libraries\src\MVC\Controller\BaseController::checkToken()
		*/
		$this->checkToken();
		$user = $this->app->getIdentity();
		$taskValues = [
			'featured' => 1,
			'unfeatured' => 0
		];

		$task = $this->getTask();
		$value = ArrayHelper::getValue($taskValues, $task, 0, 'int');
		$ids = (array) $this->input->get('cid', [], 'int');
		$redirectUrl = Route::_('index.php?option=' . $C->option . '&view=' . $this->view_list
			. $this->getRedirectToListAppend(), false);

		// Remove zero value resulting from input filter
		$ids = array_filter($ids);

		foreach ($ids as $i => $id) {
			if (!$user->authorise('core.edit.state', $C->context . '.' . (int) $id)) {
				unset($ids[$i]);
				$this->app->enqueueMessage(
					Text::sprintf('GHSVS_EDITSTATE_NOT_PERMITTED', $id), 'warning');
			}
		}

		if (empty($ids)) {
			$this->app->enqueueMessage(Text::_('JERROR_NO_ITEMS_SELECTED'), 'error');
			$this->setRedirect($redirectUrl);
			return;
		}

		$model = $this->getModel();
		if (!$model->featured($ids, $value)) {
			$this->setRedirect($redirectUrl, $model->getError(), 'error');
			return;
		}

		$message = Text::sprintf('GHSVS_N_ITEMS_' . strtoupper($task), count($ids));

		$this->setRedirect($redirectUrl, $message);
	}

	public function getModel($name = '', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		if (empty($name)) {
			$name = $this->MY_CON->vSingle;
		}

		return parent::getModel($name, $prefix, $config);
	}
}
