<?php
defined('_JEXEC') or die;


use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

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
        <?php foreach ($this->items as $i => $row) : ?>
            <tr>
                <td><a href="/<?php echo Route::_('index.php?option=com_ghsthing&task=ghsthing.edit&id=' . $row->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape($row->title); ?>">
        <?php echo $this->escape($row->title); ?>
    </a></td>
                <td><?php echo $row->id; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<input type="hidden" name="task" value=""/>
<input type="hidden" name="boxchecked" value="0"/>
<?php echo HTMLHelper::_('form.token'); ?>

</form>
