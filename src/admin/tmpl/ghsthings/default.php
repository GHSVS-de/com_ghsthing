<?php
defined('_JEXEC') or die;


use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

$user = Factory::getUser();
$userId = $user->id;
?>
<h2>Welcome to My GhsThings!</h2>

<form action="<?php echo Route::_('index.php?option=com_ghsthing'); ?>" method="post" name="adminForm" id="adminForm">

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
	$canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || is_null($item->checked_out);
	$canEditOwn = $user->authorise('core.edit.own', 'com_contact.category.' . $item->catid) && $item->created_by == $userId;
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

<input type="hidden" name="task" value=""/>
<input type="hidden" name="boxchecked" value="0"/>
<?php echo HTMLHelper::_('form.token'); ?>

</form>
