<?php

namespace GHSVS\Component\GhsThing\Administrator\Traits;

\defined('_JEXEC') or die;

use Joomla\CMS\Button\FeaturedButton;
use Joomla\CMS\Button\PublishedButton;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;


trait MY_CON
{
	public $MY_CON = null;
	public $itemsEmpty = null;

	public function init_MY_CON()
	{
		if ($this->MY_CON === null)
		{
			$this->MY_CON = new \stdClass();
			$this->MY_CON->context = $this->MY_CON->typeAlias = 'com_ghsthing.ghsthing';
			$this->MY_CON->contextCat = 'com_ghsthing.category';
			$this->MY_CON->option = 'com_ghsthing';
			$this->MY_CON->prefix = 'COM_GHSTHING_';
			$this->MY_CON->table = '#__ghsthing';
			$this->MY_CON->tableFeatured = '#__ghsthing_frontpage';
			$this->MY_CON->vSingle = 'ghsthing';
			$this->MY_CON->vList = 'ghsthings';
			$this->MY_CON->vListUpper = 'GHSTHINGS';
		}

		$this->loadGhsvsLangFiles();
	}

	public function loadGhsvsLangFiles(array $files = ['global'])
	{
		$lang = Factory::getApplication()->getLanguage();
		$path = JPATH_ADMINISTRATOR . '/components/' . $this->MY_CON->option;

		foreach ($files as $file)
		{
			$lang->load('ghsvs_' . $file, $path);
		}
	}

	public function getDateOrderingColumn($listOrder)
	{
		if (strpos($listOrder, 'publish_up') !== false) {
			return 'publish_up';
		} elseif (strpos($listOrder, 'publish_down') !== false) {
			return 'publish_down';
		} elseif (strpos($listOrder, 'modified') !== false) {
			return 'modified';
		} else {
			return 'created';
		}
	}

	public function getSaveOrderingUrl($task = null, $option = null)
	{
		return 'index.php?option=' . ($option ?? $this->MY_CON->option)
			. '&task=' . ($task ?? $this->MY_CON->vList) . '.saveOrderAjax&tmpl=component&'
			. Session::getFormToken() . '=1';
	}

	public function setItemRights(&$items, $context = null, $contextCat = null)
	{
		$context = ($context ?? $this->MY_CON->context) . '.';
		$contextCat = ($contextCat ?? $this->MY_CON->contextCat) . '.';
		$user = Factory::getApplication()->getIdentity();

		foreach ($items as $i => $item)
		{
			$item->UCanEdit = $user->authorise('core.edit', $context . $item->id);
			$item->UCanCheckin = $user->authorise('core.manage', 'com_checkin')
				|| $item->checked_out == $user->id || is_null($item->checked_out);
			$item->UCanEditOwn = $user->authorise('core.edit.own', $context . $item->id)
				 && $item->created_by == $user->id;
			$item->UCanChange = $user->authorise('core.edit.state', $context . $item->id)
				&& $item->UCanCheckin;
			$item->UCanEditCat = $user->authorise('core.edit', $contextCat . $item->catid);
			$item->UCanEditOwnCat = $user->authorise('core.edit.own',
				$contextCat . $item->catid) && $item->category_uid == $user->id;
			$item->UCanEditParCat = $user->authorise('core.edit', $contextCat .
				$item->parent_category_id);
			$item->UCanEditOwnParCat = $user->authorise('core.edit.own', $contextCat .
				$item->parent_category_id) && $item->parent_category_uid == $user->id;
		}
	}

	public function setItemButtons(&$item, $i, $task_prefix = null)
	{
		$task_prefix = ($task_prefix ?? $this->MY_CON->vList) . '.';
		$options = [
			'task_prefix' => $task_prefix,
			'disabled' => !$item->UCanChange,
			'id' => 'featured-' . $item->id,
		];

		$item->UFeaturedButton = (new FeaturedButton())->render(
			(int) $item->featured, $i, $options, $item->featured_up,
			$item->featured_down
		);

		$options['id'] = 'state-' . $item->id;
		$options['category_published'] = $item->category_published;

		$item->UPublishedButton = (new PublishedButton())->render(
			(int) $item->state, $i, $options, $item->publish_up,
			$item->publish_down
		);

		$item->UCheckedoutButton = '';

		if ($item->checked_out) {
			$item->UCheckedoutButton = HTMLHelper::_('jgrid.checkedout',
				$i, $item->editor, $item->checked_out_time, $task_prefix,
				$item->UCanCheckin);
		}
	}

	public function getCatInfo($item, $option = null)
	{
		$html = [];
		$option = ($option ?? $this->MY_CON->option);
		$ediCatUrl = 'index.php?option=com_categories&task=category.edit&extension='
			. $option . '&id=';
		$sepa = ' &#187; ';
		$editCatTxt = Text::_('GHSVS_EDIT_CATEGORY');
		$html[] = Text::_('JCATEGORY') . ': ';

		/*
		???? keine Ahnung.
		A: Da maximal 2 Kategorien angezeigt werden, symbolisiert das » davor,
		dass die Parent-Kategorie noch eine oder mehrere drüber hat.
		*/
		if ($item->category_level != '1') {
			if ($item->parent_category_level != '1') {
				$html[] = $sepa;
			}
		}

		if ($item->category_level != '1') {
			if ($item->UCanEditParCat || $item->UCanEditOwnParCat) {
				$html[] = '<a href="' . Route::_($ediCatUrl . $item->parent_category_id)
					. '" title="' . $editCatTxt . '">';
			}
			$html[] = $this->escape($item->parent_category_title);

			if ($item->UCanEditParCat || $item->UCanEditOwnParCat) {
				$html[] = '</a>';
			}
			$html[] = ' &#187; ';
		}

		if ($item->UCanEditCat || $item->UCanEditOwnCat) {
			$html[] = '<a href="' . Route::_($ediCatUrl . $item->catid)
				. '" title="' . $editCatTxt . '">';
		}
		$html[] = $this->escape($item->category_title);

		if ($item->UCanEditCat || $item->UCanEditOwnCat) {
			$html[] = '</a>';
		}

		if ($item->category_published < '1') {
			$html[] = '(';
			$html[] = $item->category_published == '0' ? Text::_('JUNPUBLISHED')
				: Text::_('JTRASHED');
			$html[] = ')';
		}
		return implode('', $html);

	}
}
