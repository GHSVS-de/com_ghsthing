<?php

namespace GHSVS\Component\GhsThing\Administrator\Model;

defined('_JEXEC') or die;

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

class GhsthingModel extends AdminModel
{
	use VersionableModelTrait;

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
		/* NOCH UNKLAR. V.a. das 'content':
		$config['events_map'] = $config['events_map'] ?? [];

		$config['events_map'] = array_merge(
				['featured' => 'content'],
				$config['events_map']
		); */

		parent::__construct($config, $factory, $formFactory);

		// Set the featured status change events
		$this->event_before_change_featured = $config['event_before_change_featured'] ?? $this->event_before_change_featured;
		$this->event_before_change_featured = $this->event_before_change_featured ?? 'onContentBeforeChangeFeatured';
		$this->event_after_change_featured  = $config['event_after_change_featured'] ?? $this->event_after_change_featured;
		$this->event_after_change_featured  = $this->event_after_change_featured ?? 'onContentAfterChangeFeatured';
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

	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_ghsthing.ghsthing', 'ghsthing', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
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

			$item->articletext = ($item->fulltext !== null && trim($item->fulltext) != '') ? $item->introtext . '<hr id="system-readmore">' . $item->fulltext : $item->introtext;

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
					->from($db->quoteName('#__ghsthing_frontpage'))
					->where($db->quoteName('ghsthing_id') . ' = :id')
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
			return true;
		}

		return false;
	}
}
