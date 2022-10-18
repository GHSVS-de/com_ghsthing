<?php
/*
ListModel is a Model class for handling lists of items. It extends BaseDatabaseModel.
BaseDatabaseModel is Base class for a database aware Joomla Model. It extends BaseModel.
This model uses DatabaseAwareTrait, MVCFactoryAwareTrait, DispatcherAwareTrait.
It work as a base class by initializing the database driver object and the table object.
*/
namespace GHSVS\Component\GhsThing\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Table\Table;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

/**
 * Methods supporting a list of contact records.
 *
 * @since  1.6
 */
class GhsthingsModel extends ListModel
{
	/**
	* Constructor.
	*
	* @param   array  $config  An optional associative array of configuration settings.
	*
	* @since   1.6
	*/
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'alias', 'a.alias',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'catid', 'a.catid', 'category_id', 'category_title',
				'user_id', 'a.user_id',
				'published', 'a.published',
				'access', 'a.access', 'access_level',
				'created', 'a.created',
				'created_by', 'a.created_by',
				'ordering', 'a.ordering',
				'featured', 'a.featured',
				'language', 'a.language', 'language_title',
				'publish_up', 'a.publish_up',
				'publish_down', 'a.publish_down',
				'ul.name', 'linked_user',
				'tag',
				'level', 'c.level',
			);

			if (Associations::isEnabled()) {
				$config['filter_fields'][] = 'association';
			}
		}

		parent::__construct($config);
	}

	/**
	* Method to auto-populate the model state.
	*
	* Note. Calling getState in this method will result in recursion.
	*
	* @param   string  $ordering   An optional ordering field.
	* @param   string  $direction  An optional direction (asc|desc).
	*
	* @return  void
	*
	* @since   1.6
	*/
	protected function populateState($ordering = 'a.title', $direction = 'asc')
	{
		$app = Factory::getApplication();

		$forcedLanguage = $app->input->get('forcedLanguage', '', 'cmd');

		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout')) {
			$this->context .= '.' . $layout;
		}

		// Adjust the context to support forced languages.
		if ($forcedLanguage) {
			$this->context .= '.' . $forcedLanguage;
		}

		// List state information.
		parent::populateState($ordering, $direction);

		// Force a language.
		if (!empty($forcedLanguage)) {
			$this->setState('filter.language', $forcedLanguage);
		}
	}

	/**
		* Method to get a store id based on model configuration state.
		*
		* This is necessary because the model is used by the component and
		* different modules that might need different sets of data or different
		* ordering requirements.
		*
		* @param   string  $id  A prefix for the store id.
		*
		* @return  string  A store id.
		*
		* @since   1.6
		*/
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . serialize($this->getState('filter.category_id'));
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.language');
		$id .= ':' . serialize($this->getState('filter.tag'));
		$id .= ':' . $this->getState('filter.level');

		return parent::getStoreId($id);
	}

	protected function getListQuery()
	{
			$db = Factory::getDbo();
			$query = $db->getQuery(true);

			// Select statement
			$query->select('*')
					->from($db->quoteName('#__ghsthing'));

			// Order by
			$query->order('id DESC');
			return $query;
	}
}
