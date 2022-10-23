<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;


// Shortcut for constants from trait MY_CON.php
$C = $this->MY_CON;
$this->document->getWebAssetManager()->useScript('table.columns')
	->useScript('multiselect');
$user = Factory::getApplication()->getIdentity();
$listOrder = $this->escape($this->state->get('list.ordering'));
$saveOrder = $listOrder === 'a.ordering';
$listDirn  = $this->escape($this->state->get('list.direction'));
$orderingColumn = $this->getOrderingColumn($listOrder);

if ($saveOrder && !empty($this->items)) {
	$saveOrderingUrl = $this->getSaveOrderingUrl();
	HTMLHelper::_('draggablelist.draggable');
}
?>
<h2>Welcome to My GhsThings!</h2>

<form action="<?php echo Route::_('index.php?option=' . $C->option
	. '&view=' . $C->vList); ?>" method="post" name="adminForm" id="adminForm">

<?php echo LayoutHelper::render('ghsvs.listview_j-maincontainer', ['view' => $this, 'itemsEmpty' => empty($this->items)]); ?>




<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>Title</th>
            <th>ID</th>
        </tr>
    </thead>
    <tbody>

<?php
foreach ($this->items as $i => $item) :
	$canCreate  = $user->authorise('core.create', 'com_contact.category.' . $item->catid);
	$canEdit    = $user->authorise('core.edit', 'com_contact.category.' . $item->catid);
	$canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $user->id || is_null($item->checked_out);
	$canEditOwn = $user->authorise('core.edit.own', 'com_contact.category.' . $item->catid) && $item->created_by == $user->id;
	$canChange  = $user->authorise('core.edit.state', 'com_contact.category.' . $item->catid) && $canCheckin;
	$item->cat_link = Route::_('index.php?option=com_categories&extension=com_contact&task=edit&type=other&id=' . $item->catid); ?>
            <tr>
		<td>
			<a href="<?php echo Route::_('index.php?option=com_ghsthing&task=ghsthing.edit&id=' . $item->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape($item->title); ?>">
				<?php echo $this->escape($item->title); ?>
			</a>
		</td>
		<td>
			<?php echo $item->id; ?>
		</td>
            </tr>
<?php endforeach; ?>
    </tbody>
</table>

<input type="hidden" name="task" value="">
<input type="hidden" name="boxchecked" value="0">
<?php echo HTMLHelper::_('form.token'); ?>

<?php echo LayoutHelper::render('ghsvs.listview_j-maincontainer', ['startEnd' => 'end']); ?>
</form>
