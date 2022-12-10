<?php
/*
ListModel is a Model class for handling lists of items. It extends BaseDatabaseModel.
BaseDatabaseModel is Base class for a database aware Joomla Model. It extends BaseModel.
This model uses DatabaseAwareTrait, MVCFactoryAwareTrait, DispatcherAwareTrait.
It work as a base class by initializing the database driver object and the table object.
*/
namespace GHSVS\Component\GhsThing\Administrator\Model;

defined('_JEXEC') or die;

use GHSVS\Component\GhsThing\Administrator\Traits\MY_CON;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Table\Table;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;
use Joomla\Registry\Registry;

/**
 * Methods supporting a list of contact records.
 *
 * @since  1.6
 */
class GhsthingsModel extends ListModel
{
	use MY_CON;

	/**
	* Constructor.
	*
	* @param   array  $config  An optional associative array of configuration settings.
	*
	* @since   1.6
	*/
	public function __construct($config = [])
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = [
				'id', 'a.id',
				'title', 'a.title',
				'alias', 'a.alias',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'catid', 'a.catid', 'category_title',
				'state', 'a.state',
				'access', 'a.access', 'access_level',
				'created', 'a.created',
				'modified', 'a.modified',
				'created_by', 'a.created_by',
				'ordering', 'a.ordering',
				'featured', 'a.featured',
				'featured_up', 'fp.featured_up',
				'featured_down', 'fp.featured_down',
				'language', 'a.language',
				'publish_up', 'a.publish_up',
				'publish_down', 'a.publish_down',
				'published', 'a.published',
				'author_id',
				'category_id',
				'level',
				'tag',
			];
		}

		parent::__construct($config);

		// Trait things.
		$this->init_MY_CON();
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
	protected function populateState($ordering = 'a.id', $direction = 'desc')
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
		$id .= ':' . serialize($this->getState('filter.access'));
		$id .= ':' . $this->getState('filter.state');
		$id .= ':' . serialize($this->getState('filter.category_id'));
		$id .= ':' . serialize($this->getState('filter.author_id'));
		$id .= ':' . $this->getState('filter.language');
		$id .= ':' . serialize($this->getState('filter.tag'));

		return parent::getStoreId($id);
	}

	/**
	* Build an SQL query to load the list data.
	*
	* @return  \Joomla\Database\DatabaseQuery
	*
	* @since   1.6
	*/
	protected function getListQuery()
	{
		// Shortcut for constants from trait MY_CON.php
		$C = $this->MY_CON;
		$db = $this->getDatabase();
		$query = $db->getQuery(true);

		//$user = Factory::getApplication()->getIdentity();
		$user = $this->getCurrentUser();

		$params = ComponentHelper::getParams($C->option);
		$query->select(
			$this->getState('list.select',
			[
				$db->qn('a.id'), $db->qn('a.asset_id'), $db->qn('a.title'),
				$db->qn('a.alias'), $db->qn('a.checked_out'), $db->qn('a.checked_out_time'),
				$db->qn('a.catid'), $db->qn('a.state'), $db->qn('a.access'),
				$db->qn('a.created'), $db->qn('a.created_by'), $db->qn('a.modified'),
				$db->qn('a.ordering'), $db->qn('a.featured'), $db->qn('a.language'),
				$db->qn('a.publish_up'), $db->qn('a.publish_down'), $db->qn('a.introtext'),
				$db->qn('a.fulltext'), $db->qn('a.note'), $db->qn('a.images'),
				$db->qn('a.metakey'), $db->qn('a.metadesc'), $db->qn('a.metadata'),
				$db->qn('a.version'),
			]
			)
		)
		->select(
			[
				$db->qn('fp.featured_up'),
				$db->qn('fp.featured_down'),
				$db->qn('l.title', 'language_title'),
				$db->qn('l.image', 'language_image'),
				// editor ist der User, der ggf. gerade ausgecheckt hat.
				$db->qn('uc.name', 'editor'),
				$db->qn('ag.title', 'access_level'),
				$db->qn('c.title', 'category_title'),
				$db->qn('c.created_user_id', 'category_uid'),
				$db->qn('c.level', 'category_level'),
				$db->qn('c.published', 'category_published'),
				$db->qn('parent.title', 'parent_category_title'),
				$db->qn('parent.id', 'parent_category_id'),
				$db->qn('parent.created_user_id', 'parent_category_uid'),
				$db->qn('parent.level', 'parent_category_level'),
				$db->qn('ua.name', 'author_name'),
			]
		)
		->from($db->qn($C->table, 'a'))
		->join('LEFT', $db->qn('#__languages', 'l'), $db->qn('l.lang_code') . ' = '
			. $db->qn('a.language'))
		->join('LEFT', $db->qn($C->tableFeatured, 'fp'), $db->qn('fp.content_id')
			. ' = ' . $db->qn('a.id'))
		->join('LEFT', $db->qn('#__users', 'uc'), $db->qn('uc.id') . ' = '
			. $db->qn('a.checked_out'))
		->join('LEFT', $db->qn('#__viewlevels', 'ag'), $db->qn('ag.id') . ' = '
			. $db->qn('a.access'))
		->join('LEFT', $db->qn('#__categories', 'c'), $db->qn('c.id') . ' = '
			. $db->qn('a.catid'))
		->join('LEFT', $db->qn('#__categories', 'parent'), $db->qn('parent.id') . ' = '
			. $db->qn('c.parent_id'))
		->join('LEFT', $db->qn('#__users', 'ua'), $db->qn('ua.id') . ' = '
			. $db->qn('a.created_by'));

		$access = $this->getState('filter.access');

		if (is_numeric($access)) {
			$access = (int) $access;
			$query->where($db->qn('a.access') . ' = :access')
				->bind(':access', $access, ParameterType::INTEGER);
		} elseif (is_array($access)) {
			$access = ArrayHelper::toInteger($access);
			$query->whereIn($db->qn('a.access'), $access);
		}

		$featured = (string) $this->getState('filter.featured');

		if (\in_array($featured, ['0','1'])) {
			$featured = (int) $featured;
			$query->where($db->qn('a.featured') . ' = :featured')
				->bind(':featured', $featured, ParameterType::INTEGER);
		}

		if (!$user->authorise('core.admin')) {
			$groups = $user->getAuthorisedViewLevels();
			$query->whereIn($db->qn('a.access'), $groups);
			$query->whereIn($db->qn('c.access'), $groups);
		}

		$published = (string) $this->getState('filter.state');

		if ($published !== '*') {
			if (is_numeric($published)) {
				$state = (int) $published;
				$query->where($db->qn('a.state') . ' = :state')
					->bind(':state', $state, ParameterType::INTEGER);
			}
		}

		$categoryId = $this->getState('filter.category_id', []);

		if (!is_array($categoryId)) {
			$categoryId = $categoryId ? [$categoryId] : [];
		}

		if (count($categoryId)) {
			$categoryId = ArrayHelper::toInteger($categoryId);
			$categoryTable = Table::getInstance('Category');
			$subCatItemsWhere = [];

			foreach ($categoryId as $key => $filter_catid) {
				$categoryTable->load($filter_catid);

				/*
				Because values to $query->bind() are passed by reference, using
				$query->bindArray() here instead to prevent overwriting.
				*/
				$valuesToBind = [$categoryTable->lft, $categoryTable->rgt];

				// Bind values and get parameter names.
				$bounded = $query->bindArray($valuesToBind);

				$categoryWhere = $db->qn('c.lft') . ' >= ' . $bounded[0] . ' AND '
					. $db->qn('c.rgt') . ' <= ' . $bounded[1];

				$subCatItemsWhere[] = '(' . $categoryWhere . ')';
			}

			$query->where('(' . implode(' OR ', $subCatItemsWhere) . ')');
		}

		$authorId = $this->getState('filter.author_id');

		if (is_numeric($authorId)) {
			$authorId = (int) $authorId;
			$type = $this->getState('filter.author_id.include', true) ? ' = ' : ' <> ';
			$query->where($db->qn('a.created_by') . $type . ':authorId')
				->bind(':authorId', $authorId, ParameterType::INTEGER);
		} elseif (is_array($authorId)) {
			if (\in_array('by_me', $authorId)) {
				$authorId['by_me'] = $user->id;
			}

			$authorId = ArrayHelper::toInteger($authorId);
			$query->whereIn($db->qn('a.created_by'), $authorId);
		}

		$search = $this->getState('filter.search');

		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$search = (int) substr($search, 3);
				$query->where($db->qn('a.id') . ' = :search')
					->bind(':search', $search, ParameterType::INTEGER);
			} elseif (stripos($search, 'author:') === 0) {
				$search = '%' . substr($search, 7) . '%';
				$query->where('(' . $db->qn('ua.name') . ' LIKE :search1 OR '
					. $db->qn('ua.username') . ' LIKE :search2)')
					->bind([':search1', ':search2'], $search);
			} elseif (stripos($search, 'content:') === 0) {
				$search = '%' . substr($search, 8) . '%';
				$query->where('(' . $db->qn('a.introtext') . ' LIKE :search1 OR '
					. $db->qn('a.fulltext') . ' LIKE :search2)')
					->bind([':search1', ':search2'], $search);
			} else {
				$search = '%' . str_replace(' ', '%', trim($search)) . '%';
				$query->where(
					'(' . $db->qn('a.title') . ' LIKE :search1 OR ' . $db->qn('a.alias')
					. ' LIKE :search2' . ' OR ' . $db->qn('a.note') . ' LIKE :search3)'
				)
				->bind([':search1', ':search2', ':search3'], $search);
			}
		}

		if ($language = $this->getState('filter.language')) {
			$query->where($db->qn('a.language') . ' = :language')
				->bind(':language', $language);
		}

		$tag = $this->getState('filter.tag');

		if (\is_array($tag) && \count($tag) === 1) {
			$tag = $tag[0];
		}

		if ($tag && \is_array($tag)) {
			$tag = ArrayHelper::toInteger($tag);

			$subQuery = $db->getQuery(true)
			->select('DISTINCT ' . $db->qn('content_item_id'))
			->from($db->qn('#__contentitem_tag_map'))
			->where(
				[
					$db->qn('tag_id') . ' IN (' . implode(',', $query->bindArray($tag)) . ')',
					$db->qn('type_alias') . ' = ' . $db->quote($C->context),
				]
			);

			$query->join('INNER',
				'(' . $subQuery . ') AS ' . $db->qn('tagmap'),
				$db->qn('tagmap.content_item_id') . ' = ' . $db->qn('a.id')
			);
		} elseif ($tag = (int) $tag) {
			$query->join('INNER',
				$db->qn('#__contentitem_tag_map', 'tagmap'),
				$db->qn('tagmap.content_item_id') . ' = ' . $db->qn('a.id')
			)
			->where(
				[
				$db->qn('tagmap.tag_id') . ' = :tag',
				$db->qn('tagmap.type_alias') . ' = ' . $db->quote($C->context),
				]
			)
			->bind(':tag', $tag, ParameterType::INTEGER);
		}

		$orderCol  = $this->state->get('list.ordering', 'a.id');
		$orderDirn = $this->state->get('list.direction', 'DESC');
#echo ' 4654sd48sa7d98sD81s8d71dsa <pre>' . print_r($orderCol, true) . '</pre>';exit;
		if ($orderCol === 'a.ordering' || $orderCol === 'category_title') {
			$ordering = [
				$db->qn('c.title') . ' ' . $db->escape($orderDirn),
				$db->qn('a.ordering') . ' ' . $db->escape($orderDirn),
			];
		} else {
			$ordering = $db->escape($orderCol) . ' ' . $db->escape($orderDirn);
		}

		$query->order($ordering);

		return $query;
	}

	/**
	* Method to get a list of items.
	* Overridden to add item type alias.
	*
	* @return  mixed  An array of data items on success, false on failure.
	*
	* @since   4.0.0
	*/
	public function getItems()
	{
		// Shortcut for constants from trait MY_CON.php
		$C = $this->MY_CON;

		$items = parent::getItems();

		foreach ($items as $item) {
			$item->typeAlias = $C->typeAlias;

			if (isset($item->metadata)) {
				$registry = new Registry($item->metadata);
				$item->metadata = $registry->toArray();
			}

			$item->titleEscaped = htmlspecialchars($item->title, ENT_QUOTES, 'utf-8');
			$item->aliasEscaped = htmlspecialchars($item->alias, ENT_QUOTES, 'utf-8');
			$item->parent_category_titleEscaped = htmlspecialchars($item->parent_category_title, ENT_QUOTES, 'utf-8');
			$item->category_titleEscaped = htmlspecialchars($item->category_title, ENT_QUOTES, 'utf-8');
		}

		return $items;
	}
}
