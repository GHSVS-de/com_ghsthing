<?php

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

$wa = $this->document->getWebAssetManager();
$wa->useStyle('com_ghsthing.css.backend');
$wa->useStyle('com_ghsthing.css.backend-edit');
$wa->useScript('keepalive');
$wa->useScript('form.validate');
?>
<form action="<?php echo Route::_('index.php?option=com_ghsthing&layout=edit&id=' . (int) $this->item->id); ?>"
  method="post" name="adminForm" id="item-form" class="form-validate">

    <?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

		<div class="main-card">
			<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'details', 'recall' => true, 'breakpoint' => 768)); ?>


				<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', empty($this->item->id) ? Text::_('COM_CONTACT_NEW_CONTACT') : Text::_('COM_GHSTHING')); ?>
				<div class="row">
					<div class="col-lg-9">
						<div class="row">
							<div class="col-12">
								<?php echo $this->form->renderField('articletext'); ?>
							</div>
						</div>
					</div>
					<div class="col-lg-3">
						<?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
					</div>
				</div>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>




			<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
		</div><!--/main-card-->

    <input type="hidden" name="task" value="ghsthing.edit" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
