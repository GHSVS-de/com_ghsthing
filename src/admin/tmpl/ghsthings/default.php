<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

?>
<h2>Welcome to My GhsThings!</h2>
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
                <td><?php echo $row->title; ?></td>
                <td><?php echo $row->id; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<input type="hidden" name="task" value=""/>
<input type="hidden" name="boxchecked" value="0"/>
<?php echo HTMLHelper::_('form.token'); ?>
