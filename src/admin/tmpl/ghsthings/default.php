<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Utilities\ArrayHelper;

// Shortcut for constants from trait MY_CON.php
$C = $this->MY_CON;

$wa = $this->document->getWebAssetManager();
$wa->useStyle('com_ghsthing.css.backend');

$hasItems = !empty($this->items);
$this->document->getWebAssetManager()->useScript('table.columns')
	->useScript('multiselect');
$listOrder = $this->escape($this->state->get('list.ordering'));
$saveOrder = $listOrder === 'a.ordering';
$listDirn  = $this->escape($this->state->get('list.direction'));
$dateOrderingColumn = $this->getDateOrderingColumn($listOrder);
$tbodyAttrs = '';

if ($saveOrder && $hasItems) {
	$tbodyAttrs = ' ' . ArrayHelper::toString([
		'class' => 'js-draggable',
		'data-url' => $this->getSaveOrderingUrl(),
		'data-direction' => strtolower($listDirn),
		'data-nested' => 'true',
	]);
	HTMLHelper::_('draggablelist.draggable');
}

?>
<form action="<?php echo Route::_('index.php?option=' . $C->option
	. '&view=' . $C->vList); ?>" method="post" name="adminForm" id="adminForm">

<?php echo LayoutHelper::render('ghsvs.listview_j-maincontainer',
	['view' => $this, 'hasItems' => $hasItems]); ?>

<?php
if ($hasItems === true)
{
	$this->setItemRights($this->items);
?>
<table class="table itemList caption-top" id="<?php echo $C->vSingle; ?>List">
	<caption class="visually-hiddensss">
		<span class="h3"><?php echo Text::_($C->prefix . $C->vListUpper .'_TABLE_CAPTION'); ?></span>,
		<span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?></span>,
		<span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
	</caption>
	<thead>

<!--CHECKBOXES-->
		<td class="text-center">
			<?php echo HTMLHelper::_('grid.checkall'); ?>
		</td>

<!--ORDERING-->
		<th scope="col" class="text-center d-none d-md-table-cell">
			<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ORDERING',
				'a.ordering', $listDirn, $listOrder, null, 'asc'); ?>
		</th>

<!--TITLE, ALIAS, NOTE-->
		<th scope="col" class="th4title">
			<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title',
				$listDirn, $listOrder); ?>
		</th>

		<th scope="col" class="d-none d-md-table-cell">
			<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state',
				$listDirn, $listOrder); ?>
		</th>

		<th scope="col" class="d-none d-md-table-cell">
			<?php echo HTMLHelper::_('searchtools.sort', 'JFEATURED', 'a.featured',
				$listDirn, $listOrder); ?>
		</th>

		<th scope="col" class="th4category">
			<?php echo HTMLHelper::_('searchtools.sort', 'JCATEGORY', 'category_title',
				$listDirn, $listOrder); ?>
		</th>

		<th scope="col" class="d-none d-md-table-cell">
			<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'a.access',
				$listDirn, $listOrder); ?>
		</th>

		<th scope="col" class=" d-none d-md-table-cell">
			<?php echo HTMLHelper::_('searchtools.sort', 'JAUTHOR', 'a.created_by',
				$listDirn, $listOrder); ?>
		</th>
		<th scope="col" class="d-none d-md-table-cell text-center">
			<?php echo HTMLHelper::_('searchtools.sort',
				'GHSVS_' . strtoupper($dateOrderingColumn), 'a.' . $dateOrderingColumn,
				$listDirn, $listOrder); ?>
		</th>
		<th scope="col" class="d-none d-lg-table-cell">
			<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id',
				$listDirn, $listOrder); ?>
		</th>
	</thead>

	<tbody<?php echo $tbodyAttrs; ?>>
		<?php foreach ($this->items as $i => $item)
		{
			$this->setItemButtons($item, $i);
			?>
<tr class="row<?php echo $i % 2; ?>" data-draggable-group="<?php echo $item->catid; ?>">

<!--CHECKBOXES-->
	<td class="text-center">
		<?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb',
			$item->title); ?>
	</td>

<!--ORDERING-->
	<td class="text-center d-none d-md-table-cell">
		<?php
			$iconClass = '';

			if (!$item->UCanChange)
			{
				$iconClass = ' inactive';
			}
			elseif (!$saveOrder)
			{
				$iconClass = ' inactive" title="' . Text::_('JORDERINGDISABLED');
			}
		?>
		<span class="sortable-handler<?php echo $iconClass ?>">
			<span class="icon-ellipsis-v" aria-hidden="true"></span>
		</span>
		<?php if ($item->UCanChange && $saveOrder) { ?>
			<input type="text" name="order[]" size="5" value="<?php echo $item->ordering; ?>"
				class="text-area-order hidden">
		<?php } ?>
	</td>

<!--TITLE, ALIAS, NOTE-->
	<td>
		<?php echo $item->UCheckedoutButton; ?>

		<?php if ($item->UCanEdit || $item->UCanEditOwn)
		{
			$linkAttrs = ArrayHelper::toString([
				'href' => Route::_('index.php?option=' . $C->option . '&task='
					. $C->vSingle . '.edit&id=' . $item->id),
				'title' => Text::_('JACTION_EDIT') . ' ' . $item->titleEscaped,
			]); ?>
			<a <?php echo $linkAttrs; ?>><?php echo $item->titleEscaped; ?></a>
		<?php
		} else { ?>
			<span title="<?php echo Text::sprintf('JFIELD_ALIAS_LABEL', $item->aliasEscaped); ?>">
				<?php echo $item->titleEscaped; ?>
			</span>
		<?php
		} ?>

		<div>
			<?php
			echo '<span class=italic>' . Text::sprintf('JFIELD_ALIAS_LABEL')
				. ':</span> ' . $item->aliasEscaped;

			if (!empty($item->note)) {
				echo ', <span class=italic>' . Text::sprintf('JFIELD_NOTE_LABEL')
					. ':</span> ' .  $this->escape($item->note);
			} ?>
		</div>
	</td>

<!--STATE-->
	<td class="<?php echo $C->vSingle; ?>-status">
		<?php echo $item->UPublishedButton; ?>
	</td>

<!--FEATURED-->
	<td class="d-none d-md-table-cell">
		<?php echo $item->UFeaturedButton; ?>
	</td>

	<td class="d-none d-md-table-cell">
		<?php	echo $this->getCatInfo($item); ?>
	</td>

	<td class="d-none d-md-table-cell">
		<?php echo $this->escape($item->access_level); ?>
	</td>

	<td class="d-none d-md-table-cell">
		<?php echo $this->escape($item->author_name); ?>
	</td>

	<td class="d-none d-md-table-cell">
		<?php
		$date = $item->{$dateOrderingColumn};
		echo $date > 0 ? HTMLHelper::_('date', $date, Text::_('DATE_FORMAT_LC6')) : '-';
		?>
	</td>

	<td class="d-none d-lg-table-cell">
		<?php echo (int) $item->id; ?>
	</td>

</tr>
		<?php
		} //foreach this->items ?>
	</tbody>
</table>

<?php echo $this->pagination->getListFooter(); ?>

<?php
} //hasItems ?>

<input type="hidden" name="task" value="">
<input type="hidden" name="boxchecked" value="0">
<?php echo HTMLHelper::_('form.token'); ?>

<?php echo LayoutHelper::render('ghsvs.listview_j-maincontainer', ['startEnd' => 'end']); ?>
</form>
