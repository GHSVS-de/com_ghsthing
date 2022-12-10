<?php

namespace GHSVS\Component\GhsThing\Administrator\Model;

defined('_JEXEC') or die;

use GHSVS\Component\GhsThing\Administrator\Traits\MY_CON;

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Versioning\VersionableModelTrait;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Registry\Registry;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\Database\ParameterType;
use Joomla\Component\Categories\Administrator\Helper\CategoriesHelper;
use Joomla\CMS\Form\Form;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Event\AbstractEvent;

class GhsthingModel extends AdminModel
{
	use VersionableModelTrait;
	use MY_CON;

	/**
		* The prefix to use with controller messages.
		*
		* @var    string
		* @since  1.6
		*/
	protected $text_prefix = 'COM_GHSTHING';

	/**
		* The type alias for this content type (for example, 'com_content.article').
		*
		* @var    string
		* @since  3.2
		*/
	public $typeAlias = 'com_ghsthing.ghsthing';

	/**
		* The event to trigger before changing featured status one or more items.
		*
		* @var    string
		* @since  4.0.0
		*/
	protected $event_before_change_featured = null;

	/**
		* The event to trigger after changing featured status one or more items.
		*
		* @var    string
		* @since  4.0.0
		*/
	protected $event_after_change_featured = null;

	/**
		* Constructor.
		*
		* @param   array                 $config       An array of configuration options (name, state, dbo, table_path, ignore_request).
		* @param   MVCFactoryInterface   $factory      The factory.
		* @param   FormFactoryInterface  $formFactory  The form factory.
		*
		* @since   1.6
		* @throws  \Exception
		*/
	public function __construct($config = array(), MVCFactoryInterface $factory = null, FormFactoryInterface $formFactory = null)
	{
		/*
		NOCH UNKLAR. V.a. das 'content'.
		Antwort: content ist der Plugin-Ordner. plugins/content. featured der Pluginname, was es im Core aber
		gar nicht gibt.
		*/
		$config['events_map'] = $config['events_map'] ?? [];

		$config['events_map'] = array_merge(
				['featured' => 'content'],
				$config['events_map']
		);

		parent::__construct($config, $factory, $formFactory);

		// Set the featured status change events
		$this->event_before_change_featured = $config['event_before_change_featured'] ?? $this->event_before_change_featured;
		$this->event_before_change_featured = $this->event_before_change_featured ?? 'onContentBeforeChangeFeatured';
		$this->event_after_change_featured  = $config['event_after_change_featured'] ?? $this->event_after_change_featured;
		$this->event_after_change_featured  = $this->event_after_change_featured ?? 'onContentAfterChangeFeatured';

		// Trait things.
		$this->init_MY_CON();
	}

	/**
	* Is the user allowed to create an on the fly category?
	*
	* @return  boolean
	*
	* @since   3.6.1
	*/
	private function canCreateCategory()
	{
		return Factory::getUser()->authorise('core.create', 'com_ghsthing');
	}

	protected function canEditState($record)
	{
		//$user = Factory::getApplication()->getIdentity();
		$user = $this->getCurrentUser();

		if (!empty($record->id)) {
			return $user->authorise('core.edit.state', 'com_ghsthing.ghsthing.'
				. (int) $record->id);
		}

		if (!empty($record->catid)) {
			return $user->authorise('core.edit.state', 'com_ghsthing.category.'
				. (int) $record->catid);
		}

		return parent::canEditState($record);
	}

	public function getForm($data = [], $loadData = true)
	{
		$app = Factory::getApplication();

		$form = $this->loadForm('com_ghsthing.ghsthing', 'ghsthing', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		$record = new \stdClass();
		$itemIdFromInput = $app->isClient('site')
			? $app->input->getInt('a_id', 0)
			: $app->input->getInt('id', 0);

		// On edit ID from state. On save, data from input
		$id = (int) $this->getState('ghsthing.id', $itemIdFromInput);
		$record->id = $id;

		// Absolut keine Ahnung, was der folgende Scheiß soll.

		// For new items we load the potential state + associations
		if ($id == 0 && $formField = $form->getField('catid')) {
			$assignedCatids = $data['catid'] ?? $form->getValue('catid');
			$assignedCatids = is_array($assignedCatids)
				? (int) reset($assignedCatids)
				: (int) $assignedCatids;

			// Try to get the category from the category field
			if (empty($assignedCatids)) {
				$assignedCatids = $formField->getAttribute('default', null);

				if (!$assignedCatids) {
					// Choose the first category available
					$catOptions = $formField->options;

					if ($catOptions && !empty($catOptions[0]->value)) {
						$assignedCatids = (int) $catOptions[0]->value;
					}
				}
			}

			// Activate the reload of the form when category is changed
			$form->setFieldAttribute('catid', 'refresh-enabled', true);
			$form->setFieldAttribute('catid', 'refresh-cat-id', $assignedCatids);
			$form->setFieldAttribute('catid', 'refresh-section', 'ghsthing');

			// Store ID of the category uses for edit state permission check
			$record->catid = $assignedCatids;
		} else {
			if (!empty($data['catid'])) {
				$catId = (int) $data['catid'];
			} else {
				$catIds = $form->getValue('catid');
				$catId = is_array($catIds) ? (int) reset($catIds) : (int) $catIds;

				if (!$catId) {
					$catId = (int) $form->getFieldAttribute('catid', 'default', 0);
				}
			}

			$record->catid = $catId;
		}

		// Modify the form based on Edit State access controls.
		if (!$this->canEditState($record)) {
			// Disable fields for display.
			$form->setFieldAttribute('featured', 'disabled', 'true');
			$form->setFieldAttribute('featured_up', 'disabled', 'true');
			$form->setFieldAttribute('featured_down', 'disabled', 'true');
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('publish_up', 'disabled', 'true');
			$form->setFieldAttribute('publish_down', 'disabled', 'true');
			$form->setFieldAttribute('state', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is an item you can edit.
			$form->setFieldAttribute('featured', 'filter', 'unset');
			$form->setFieldAttribute('featured_up', 'filter', 'unset');
			$form->setFieldAttribute('featured_down', 'filter', 'unset');
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('publish_up', 'filter', 'unset');
			$form->setFieldAttribute('publish_down', 'filter', 'unset');
			$form->setFieldAttribute('state', 'filter', 'unset');
		}

		if (!Factory::getUser()->authorise('core.manage', 'com_users')) {
			$form->setFieldAttribute('created_by', 'filter', 'unset');
		}
		return $form;
	}

	/**
		* Method to toggle the featured setting of items and to save featured_up and featured_down.
		*
		* @param   array        $pks           The ids of the items to toggle.
		* @param   integer      $value         The value to toggle to.
		* @param   string|Date  $featuredUp    The date which item featured up.
		* @param   string|Date  $featuredDown  The date which item featured down.
		*
		* @return  boolean  True on success.
		*/
	public function featured($pks, $value = 0, $featuredUp = null, $featuredDown = null)
	{
		$pks = (array) $pks;
		$pks = array_filter(ArrayHelper::toInteger($pks));
		$value = (int) $value;
		$context = $this->option . '.' . $this->name;

		// Letztlich importPlugin('content')  im Ordner 'content'.
		PluginHelper::importPlugin($this->events_map['featured']);

		if ($featuredUp === '') {
			$featuredUp = null;
		}

		if ($featuredDown === '') {
			$featuredDown = null;
		}

		if (empty($pks)) {
			$this->setError(Text::_('JERROR_NO_ITEMS_SELECTED'));

			return false;
		}

		/*
		Macht er richtig. Auch ohne eigene getTable().
		GHSVS\Component\GhsThing\Administrator\Table\FeaturedTable
		*/
		$table = $this->getTable('Featured', 'Administrator');
		Log::add(get_class($table) . PHP_EOL . ' in ' . __METHOD__, Log::INFO,
			'ComGhsthingLog');
		#echo ' 4654sd48sa7d98sD81s8d71dsa <pre>' . print_r(get_object_vars($table), true) . '</pre>';exit;
		#echo ' 4654sd48sa7d98sD81s8d71dsa <pre>' . print_r(get_object_vars($this), true) . '</pre>';exit;

		$eventClass = 'GHSVS\Component\GhsThing\Administrator\Event\Model\FeatureEvent';

		/*
		Trigger the before change featured event.
		onContentBeforeChangeFeatured.
		Komplett komplizierter Bullshit im Vergleich zu früher.
		Wohl wegen Workflow(???)
		*/
		$eventResult = Factory::getApplication()->getDispatcher()->dispatch(
			$this->event_before_change_featured,
			AbstractEvent::create(
				$this->event_before_change_featured,
				[
					'eventClass' => $eventClass,
					'subject'    => $this,
					'extension'  => $context,
					'pks'        => $pks,
					'value'      => $value,
				]
			)
		);

		if ($eventResult->getArgument('abort', false)) {
			$this->setError(Text::_($eventResult->getArgument('abortReason')));
			return false;
		}

		$tableFeatured = '#__ghsthing_frontpage';

		try {
			$db = $this->getDatabase();
			$query = $db->getQuery(true)
				->update($db->quoteName('#__ghsthing'))
				->set($db->quoteName('featured') . ' = :featured')
				->whereIn($db->quoteName('id'), $pks)
				->bind(':featured', $value, ParameterType::INTEGER);
			$db->setQuery($query);
			$db->execute();

			if ($value === 0) {
				// Clear the existing featured entries.
				$query = $db->getQuery(true)
					->delete($db->quoteName($tableFeatured))
					->whereIn($db->quoteName('content_id'), $pks);
				$db->setQuery($query);
				$db->execute();
			} else {
				// First, we find out which of our new featured items are already featured.
				$query = $db->getQuery(true)
					->select($db->quoteName('content_id'))
					->from($db->quoteName($tableFeatured))
					->whereIn($db->quoteName('content_id'), $pks);
				$db->setQuery($query);
				$oldFeatured = $db->loadColumn();

				if (count($oldFeatured)) {
					$query = $db->getQuery(true)
						->update($db->quoteName($tableFeatured))
						->set(
							[
								$db->quoteName('featured_up') . ' = :featuredUp',
								$db->quoteName('featured_down') . ' = :featuredDown',
							]
						)
						->whereIn($db->quoteName('content_id'), $oldFeatured)
						->bind(':featuredUp', $featuredUp,
							$featuredUp ? ParameterType::STRING : ParameterType::NULL)
						->bind(':featuredDown', $featuredDown,
							$featuredDown ? ParameterType::STRING : ParameterType::NULL);
					$db->setQuery($query);
					$db->execute();
				}

				// We diff the arrays to get a list of the items that are newly featured
				$newFeatured = array_diff($pks, $oldFeatured);

				if ($newFeatured) {
					$query = $db->getQuery(true)
						->insert($db->quoteName($tableFeatured))
						->columns(
							[
								$db->quoteName('content_id'),
								$db->quoteName('ordering'),
								$db->quoteName('featured_up'),
								$db->quoteName('featured_down'),
							]
						);

					$dataTypes = [
						ParameterType::INTEGER,
						ParameterType::INTEGER,
						$featuredUp ? ParameterType::STRING : ParameterType::NULL,
						$featuredDown ? ParameterType::STRING : ParameterType::NULL,
					];

					foreach ($newFeatured as $pk) {
						$query->values(implode(',',
							$query->bindArray(
								[$pk, 0, $featuredUp, $featuredDown],
								$dataTypes
							)
						));
					}

					$db->setQuery($query);
					$db->execute();
				}
			}
		} catch (\Exception $e) {
			$this->setError($e->getMessage());
			return false;
		}
		$table->reorder();

		Factory::getApplication()->getDispatcher()->dispatch(
			$this->event_after_change_featured,
			AbstractEvent::create(
				$this->event_after_change_featured,
				[
					'eventClass' => $eventClass,
					'subject'    => $this,
					'extension'  => $context,
					'pks'        => $pks,
					'value'      => $value,
				]
			)
		);

		// Ohne eigene cleanCache() wird aller Cache gelöscht.
		// $this->cleanCache();

		return true;
	}

	/**
		* Method to get a single record.
		*
		* @param   integer  $pk  The id of the primary key.
		*
		* @return  mixed  Object on success, false on failure.
		*/
	public function getItem($pk = null)
	{
		// parent::getItem() führt eine DB-Abfrage aus.
		if ($item = parent::getItem($pk)) {
			$registry = new Registry($item->metadata);
			$item->metadata = $registry->toArray();
			$registry = new Registry($item->images);
			$item->images = $registry->toArray();

			$item->articletext = ($item->fulltext !== null && trim($item->fulltext) !== '')
				? $item->introtext . '<hr id="system-readmore">' . $item->fulltext
				: $item->introtext;

			if (!empty($item->id)) {
				$item->tags = new TagsHelper();
				$item->tags->getTagIds($item->id, $this->typeAlias);

				$item->featured_up   = null;
				$item->featured_down = null;

				if ($item->featured) {
					$db = $this->getDatabase();
					$query = $db->getQuery(true)
					->select(
						[
							$db->quoteName('featured_up'),
							$db->quoteName('featured_down'),
						]
					)
					->from($db->quoteName($this->MY_CON->tableFeatured))
					->where($db->quoteName('content_id') . ' = :id')
					->bind(':id', $item->id, ParameterType::INTEGER);

					$featured = $db->setQuery($query)->loadObject();

					if ($featured) {
						$item->featured_up   = $featured->featured_up;
						$item->featured_down = $featured->featured_down;
					}
				}
			}
		}
		return $item;
	}

	/**
	* Method to get the data that should be injected in the form.
	*
	* @return  mixed  The data for the form.
	*
	* @since   1.6
	*/
	protected function loadFormData()
	{
		$app = Factory::getApplication();

		// Check the session for previously entered form data.
		$data = $app->getUserState('com_ghsthing.edit.ghsthing.data', array());

		if (empty($data)) {
			$data = $this->getItem();

			// Prime some default values.
			if ($this->getState('ghsthing.id') == 0) {
				$data->set('catid', $app->input->get('catid', $app->getUserState('com_ghsthing.ghsthings.filter.category_id'), 'int'));
			}
		}

		// Ruft letztlich Plugin-Events. Der Core ruft onContentPrepareData.
		$this->preprocessData('com_ghsthing.ghsthing', $data);
		//Log::add('$data: ' . print_r($data, true) . PHP_EOL . ' in ' . __METHOD__ . PHP_EOL, Log::INFO, 'ComGhsthingLog');

		return $data;
	}

	/*
	Beim Laden der Edit-Form im Backend. Wohl nicht beim Speichern?
	Parent ist in diesem Fall FormBehaviorTrait bzw. FormModel, das das Trait lädt.
	Dort werden die Plugins der $group getriggert.
	$data-Objekt kann aber hier auch direkt manipuliert werden oder eigene Plugin-Trigger.
	$data: Joomla\CMS\Object\CMSObject Object
	*/
	protected function preprocessData($context, &$data, $group = 'ghsthing')
	{
		$data->ghstest = 'ghstest';
		/* PluginHelper::importPlugin($group);
		Factory::getApplication()->triggerEvent('onGhsthingPrepareData', array($context, &$data)); */

		// Das hat als default $group = 'content'.
		parent::preprocessData($context, $data);
	}

	/**
	* Allows preprocessing of the Form object.
	*
	* @param   Form    $form   The form object
	* @param   array   $data   The data to be merged into the form object
	* @param   string  $group  The plugin group to be executed
	*
	* @return  void
	*
	* @since   3.0
	*/
	protected function preprocessForm(Form $form, $data, $group = 'content')
	{
		// Muss in eigener save() abgehandelt werden.
		if ($this->canCreateCategory()) {
			$form->setFieldAttribute('catid', 'allowAdd', 'true');
			$form->setFieldAttribute('catid', 'customPrefix', '#new#');
		}

		parent::preprocessForm($form, $data, $group);
	}

	/**
	* Prepare and sanitise the table prior to saving.
	*
	* @param   \Joomla\CMS\Table\Table  $table  The Table object
	*
	* @return  void
	*
	* @since   1.6
	*/
	protected function prepareTable($table)
	{
		$date = Factory::getDate()->toSql();
		$table->title = htmlspecialchars_decode($table->title, ENT_QUOTES);
		// Ist GhsthingTable::generateAlias()
		$table->generateAlias();

		if (empty($table->id)) {
			$table->created = $date;
			// Ist eine Core-Methode in Table. Neues Item mit höchstem ordering.
			$table->reorder('catid = ' . (int) $table->catid . ' AND state >= 0');
		} else {
			$table->modified = $date;
			$table->modified_by = Factory::getUser()->id;
		}
		$table->version++;
	}

	/**
	* Method to save the form data.
	*
	* @param   array  $data  The form data.
	*
	* @return  boolean  True on success.
	*
	* @since   1.6
	*/
	public function save($data)
	{
		$app = Factory::getApplication();
		$input = $app->input;

		if (isset($data['images']) && is_array($data['images'])) {
			$registry = new Registry($data['images']);
			$data['images'] = (string) $registry;
		}

		$createCategory = true;

		if (is_null($data['catid'])) {
			$createCategory = false;
		}

		if (is_numeric($data['catid']) && $data['catid']) {
			$createCategory = !CategoriesHelper::validateCategoryId($data['catid'],
				'com_ghsthing');
		}

		if ($createCategory && $this->canCreateCategory()) {
			$category = [
				// Remove #new# prefix, if exists. See preprocessForm().
				'title' => strpos($data['catid'], '#new#') === 0
					? substr($data['catid'], 5) : $data['catid'],
				'parent_id' => 1,
				'extension' => 'com_ghsthing',
				'language'  => $data['language'],
				'published' => 1,
			];

			/** @var \Joomla\Component\Categories\Administrator\Model\CategoryModel $categoryModel */
			$categoryModel = Factory::getApplication()->bootComponent('com_categories')
			->getMVCFactory()->createModel('Category', 'Administrator', ['ignore_request' => true]);

			// Create new category.
			if (!$categoryModel->save($category)) {
				$this->setError($categoryModel->getError());

				return false;
			}

			$data['catid'] = $categoryModel->getState('category.id');
		}

		if (parent::save($data)) {
			// Hier wird featured_up featured_down gespeichert, damit beim nächsten Editaufruf immer noch da oder gelöscht.
			// $this->featured() ist die Methode in diesem Model, die das abwickelt.
			if (isset($data['featured'])) {
				if (!$this->featured($this->getState($this->getName() . '.id'),
					$data['featured'], $data['featured_up'] ?? null, $data['featured_down'] ?? null)
				) {
					return false;
				}
			}

			return true;
		}

		return false;
	}
}
