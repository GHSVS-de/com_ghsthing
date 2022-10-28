<?php
/*
bind takes the array of data that comes from your form and then stores any data from it into the table class.

check then runs sanity checks on the data that you have binded into the table class

store then stores the binded data into the database table.
*/

namespace GHSVS\Component\GhsThing\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Tag\TaggableTableInterface;
use Joomla\CMS\Tag\TaggableTableTrait;
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\Database\DatabaseDriver;
use Joomla\String\StringHelper;


class GhsthingTable extends Table implements VersionableTableInterface, TaggableTableInterface
{
	use TaggableTableTrait;

	/**
		* Indicates that columns fully support the NULL value in the database
		*
		* @var    boolean
		* @since  4.0.0
		*/
	protected $_supportNullValue = true;

	/**
		* Ensure the params and metadata in json encoded in the bind method
		*
		* @var    array
		* @since  3.3
		*/
	protected $_jsonEncode = array('params', 'metadata');

	function __construct(DatabaseDriver $db)
	{
		$this->typeAlias = 'com_ghsthing.ghsthing';
		parent::__construct('#__ghsthing', 'id', $db);
		// $this->setColumnAlias('title', 'name');

		// Wichtig z.B. bei Klick auf publish buttons in Listen-View.
		$this->setColumnAlias('published', 'state');
	}

	public function bind($array, $ignore = '')
	{
		// Search for the {readmore} tag and split the text up accordingly.
		if (isset($array['articletext'])) {
			$pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
			$tagPos = preg_match($pattern, $array['articletext']);

			if ($tagPos == 0) {
				$this->introtext = $array['articletext'];
				$this->fulltext = '';
			} else {
				list ($this->introtext, $this->fulltext) = preg_split($pattern, $array['articletext'], 2);
			}
		}

		if (isset($array['params']) && \is_array($array['params'])) {
			$registry = new Registry($array['attribs']);
			$array['attribs'] = (string) $registry;
		}

		if (isset($array['metadata']) && \is_array($array['metadata'])) {
			$registry = new Registry($array['metadata']);
			$array['metadata'] = (string) $registry;
		}

		if (isset($array['rules']) && \is_array($array['rules'])) {
				$rules = new Rules($array['rules']);
				$this->setRules($rules);
		}

		return parent::bind($array, $ignore);
	}
	/**
		* Stores a Ghsthing.
		*
		* @param   boolean  $updateNulls  True to update fields even if they are null.
		*
		* @return  boolean  True on success, false on failure.
		*
		* @since   1.6
		*/
	public function store($updateNulls = true)
	{
			$date   = Factory::getDate()->toSql();
			$userId = Factory::getUser()->id;

			// Set created date if not set.
			if (!(int) $this->created) {
					$this->created = $date;
			}

			if ($this->id) {
					// Existing item
					$this->modified_by = $userId;
					$this->modified    = $date;
			} else {
					// Field created_by field can be set by the user, so we don't touch it if it's set.
					if (empty($this->created_by)) {
							$this->created_by = $userId;
					}

					if (!(int) $this->modified) {
							$this->modified = $date;
					}

					if (empty($this->modified_by)) {
							$this->modified_by = $userId;
					}
			}

			// Convert IDN urls to punycode
			if ($this->webpage !== null) {
					$this->webpage = PunycodeHelper::urlToPunycode($this->webpage);
			}

			// Verify that the alias is unique
			$table = Table::getInstance('GhsthingTable', __NAMESPACE__ . '\\', array('dbo' => $this->getDbo()));

			if ($table->load(array('alias' => $this->alias, 'catid' => $this->catid)) && ($table->id != $this->id || $this->id == 0)) {
					$this->setError(Text::_('COM_GHSTHING_ERROR_UNIQUE_ALIAS'));

					return false;
			}

			return parent::store($updateNulls);
	}

	/**
		* Overloaded check function
		*
		* @return  boolean  True on success, false on failure
		*
		* @see     \JTable::check
		* @since   1.5
		*/
	public function check()
	{
		try {
			parent::check();
		} catch (\Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}

		// Check for valid title
		if (trim($this->title) == '') {
			$this->setError(Text::_('COM_GHSTHING_WARNING_PROVIDE_VALID_TITLE'));

			return false;
		}

		// Generate a valid alias
		$this->generateAlias();

		// Check for a valid category.
		if (!$this->catid = (int) $this->catid) {
			$this->setError(Text::_('JLIB_DATABASE_ERROR_CATEGORY_REQUIRED'));

			return false;
		}

		if (trim(str_replace('&nbsp;', '', $this->fulltext)) == '') {
			$this->fulltext = '';
		}

		/**
			* Ensure any new items have compulsory fields set. This is needed for things like
			* frontend editing where we don't show all the fields or using some kind of API
			*/
		if (!$this->id) {
			// Images can be an empty json string
			if (!isset($this->images)) {
				$this->images = '{}';
			}

			// URLs can be an empty json string
			if (!isset($this->urls)) {
				$this->urls = '{}';
			}

			// Attributes (article params) can be an empty json string
			if (!isset($this->params)) {
				$this->params = '{}';
			}

			// Metadata can be an empty json string
			if (!isset($this->metadata)) {
				$this->metadata = '{}';
			}
		}

		if (!$this->publish_up) {
			$this->publish_up = null;
		}

		if (!$this->publish_down) {
			$this->publish_down = null;
		}

		if (!is_null($this->publish_up) && !is_null($this->publish_down) && $this->publish_down < $this->publish_up) {
			$temp = $this->publish_up;
			$this->publish_up = $this->publish_down;
			$this->publish_down = $temp;
		}

		if (!empty($this->metakey)) {
			$badCharacters = ["\n", "\r", "\"", '<', '>'];
			$afterClean = StringHelper::str_ireplace($badCharacters, '', $this->metakey);
			$keys = explode(',', $afterClean);
			$cleanKeys = [];

			foreach ($keys as $key) {
				if (trim($key)) {
					$cleanKeys[] = trim($key);
				}
			}

			// Put array back together delimited by ", "
			$this->metakey = implode(', ', $cleanKeys);
		} else {
			$this->metakey = '';
		}

		if ($this->metadesc === null) {
			$this->metadesc = '';
		}

################ Bis hier weitestgehend mit libraries\src\Table\Content.php::check() abgeglichen.




################ Altes Zeugs aus Banners oder Contacts
# Vergleiche das mit libraries\src\Table\Content.php, weil da diese Checks teils in anderen Methoden sind.
# Keine AHnung warum das so Kuddelmuddel ist.




		if (empty($this->params)) {
				$this->params = '{}';
		}

		if (empty($this->metadata)) {
				$this->metadata = '{}';
		}


		if (!$this->modified) {
				$this->modified = $this->created;
		}

		if (empty($this->modified_by)) {
				$this->modified_by = $this->created_by;
		}

		return true;
	}

	/**
		* Generate a valid alias from title / date.
		* Remains public to be able to check for duplicated alias before saving
		*
		* @return  string
		*/
	public function generateAlias()
	{
		if (empty($this->alias)) {
			$this->alias = $this->title;
		}

		$this->alias = ApplicationHelper::stringURLSafe($this->alias, $this->language);

		if (trim(str_replace('-', '', $this->alias)) == '') {
			$this->alias = Factory::getDate()->format('Y-m-d-H-i-s');
		}

		return $this->alias;
	}

	/**
		* Get the type alias for the history table
		*
		* @return  string  The alias as described above
		*
		* @since   4.0.0
		*/
	public function getTypeAlias()
	{
		return $this->typeAlias;
	}
}
