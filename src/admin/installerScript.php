<?php
/*
 * Use in your extension manifest file (any tag is optional!!!!!):
 * <minimumPhp>7.0.0</minimumPhp>
 * <minimumJoomla>3.9.0</minimumJoomla>
 * Yes, use 999999 to match '3.9'. Otherwise comparison will fail.
 * <maximumJoomla>3.9.999999</maximumJoomla>
 * <maximumPhp>7.3.999999</maximumPhp>
 * <allowDowngrades>1</allowDowngrades>
 */
defined('_JEXEC') or die;

use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Log\Log;
//use Joomla\CMS\Table\ContentType;
use Joomla\CMS\Table\Table;

class com_ghsthingInstallerScript extends InstallerScript
{
	/**
	 * A list of files to be deleted with method removeFiles().
	 *
	 * @var    array
	 * @since  2.0
	 */
	protected $deleteFiles = [];

	/**
	 * A list of folders to be deleted with method removeFiles().
	 *
	 * @var    array
	 * @since  2.0
	 */
	protected $deleteFolders = [];

	public function preflight($type, $parent)
	{
		$manifest = @$parent->getManifest();

		if ($manifest instanceof SimpleXMLElement)
		{
			if ($type === 'update' || $type === 'install' || $type === 'discover_install')
			{
				$minimumPhp = trim((string) $manifest->minimumPhp);
				$minimumJoomla = trim((string) $manifest->minimumJoomla);

				// Custom
				$maximumPhp = trim((string) $manifest->maximumPhp);
				$maximumJoomla = trim((string) $manifest->maximumJoomla);

				$this->minimumPhp = $minimumPhp ? $minimumPhp : $this->minimumPhp;
				$this->minimumJoomla = $minimumJoomla ? $minimumJoomla : $this->minimumJoomla;

				if ($maximumJoomla && version_compare(JVERSION, $maximumJoomla, '>'))
				{
					$msg = 'Your Joomla version (' . JVERSION . ') is too high for this extension. Maximum Joomla version is: ' . $maximumJoomla . '.';
					Log::add($msg, Log::WARNING, 'jerror');
				}

				// Check for the maximum PHP version before continuing
				if ($maximumPhp && version_compare(PHP_VERSION, $maximumPhp, '>'))
				{
					$msg = 'Your PHP version (' . PHP_VERSION . ') is too high for this extension. Maximum PHP version is: ' . $maximumPhp . '.';

					Log::add($msg, Log::WARNING, 'jerror');
				}

				if (isset($msg))
				{
					return false;
				}
			}

			if (trim((string) $manifest->allowDowngrades))
			{
				$this->allowDowngrades = true;
			}
		}

		if (!parent::preflight($type, $parent))
		{
			return false;
		}

		return true;
	}

	/**
	 * Runs right after any installation action is preformed on the component.
	 *
	 * @param  string    $type   - Type of PostFlight action. Possible values are:
	 *                           - * install
	 *                           - * update
	 *                           - * discover_install
	 * @param  \stdClass $parent - Parent object calling object.
	 *
	 * @return void
	 */
	public function postflight($type, $parent)
	{
		if ($type === 'update') {
			$this->removeFiles();
		}

		$this->saveContentTypes();
	}

	private function saveContentTypes()
	{
		$contentType = [];
		$contentType['type_alias'] = 'com_ghsthing.ghsthing';

		$typesTable = Table::getInstance('ContentType', 'Joomla\\CMS\Table\\');
		$typesTable->load(['type_alias' => $contentType['type_alias']]);

		$contentType['type_id'] = empty($typesTable->type_id) ? 0 : $typesTable->type_id;
		$contentType['type_title'] = 'Ghsthing';
		$contentType['table'] = '{
			"special": {
			  "dbtable": "#__ghsthing",
			  "key": "id",
			  "type": "GhsthingTable",
			  "prefix": "GHSVS\\\\Component\\\\GhsThing\\\\Administrator\\\\Table\\\\",
			  "config": "array()"
			},
			"common": {
			  "dbtable": "#__ucm_content",
			  "key": "ucm_id",
			  "type": "Corecontent",
			  "prefix": "Joomla\\\\CMS\\\\Table\\\\",
			  "config": "array()"
			}
		}';
		$contentType['rules'] = '';
		$contentType['field_mappings'] = '{
			"common": {
				"core_content_item_id": "id",
				"core_title": "title",
				"core_alias": "alias",
				"core_body":"introtext",
				"core_state": "state",
				"core_catid": "catid",
				"core_created_time":"created",
				"core_modified_time":"modified",
				"core_publish_up":"publish_up",
				"core_publish_down":"publish_down",
				"core_images":"images",
				"core_urls":"urls",
				"core_params":"params",
				"core_version":"version",
				"core_ordering":"ordering",
				"core_metakey":"metakey",
				"core_metadesc":"metadesc",
				"core_access":"access",
				"core_metadata":"metadata",
				"core_featured":"featured",
				"core_language":"language",
				"note":"note",
				"asset_id":"asset_id"
			},
			"special": {
				"fulltext":"fulltext"
			}
		}';
		$contentType['rules'] = '';
		$contentType['router'] = 'GhsthingHelperRoute::getGhsthingRoute';
		$contentType['content_history_options'] = '{
			"formFile":"administrator\/components\/com_ghsthing\/forms\/ghsthing.xml",
			"hideFields":[
				"asset_id",
				"checked_out",
				"checked_out_time",
				"version"
			],
			"ignoreChanges":[
				"modified_by",
				"modified",
				"checked_out",
				"checked_out_time",
				"version",
				"ordering"
			],
			"convertToInt":[
				"publish_up",
				"publish_down",
				"featured",
				"ordering"
			],
			"displayLookup":[
				{
					"sourceColumn":"catid",
					"targetTable":"#__categories",
					"targetColumn":"id",
					"displayColumn":"title"
				},
				{
					"sourceColumn":"created_by",
					"targetTable":"#__users",
					"targetColumn":"id",
					"displayColumn":"name"},
				{
					"sourceColumn":"access",
					"targetTable":"#__viewlevels",
					"targetColumn":"id",
					"displayColumn":"title"
				},
				{
					"sourceColumn":"modified_by",
					"targetTable":"#__users",
					"targetColumn":"id",
					"displayColumn":"name"
				}
			]
		}';
		$typesTable->save($contentType);
	}
}
