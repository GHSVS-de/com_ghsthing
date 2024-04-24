<?php

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

// Shortcut for constants from trait MY_CON.php
$C = $this->MY_CON;

$wa = $this->document->getWebAssetManager();
$wa->useStyle($C->option . '.css.backend');
$wa->useStyle($C->option . '.css.backend-edit');
$wa->getRegistry()->addExtensionRegistryFile('com_contenthistory');
$wa->useScript('keepalive')
		->useScript('form.validate')
		->useScript('com_contenthistory.admin-history-versions');

// Create shortcut to parameters.
$params = clone $this->state->get('params');
$params->merge(new Registry($this->item->params));
?>
<form action="<?php echo Route::_('index.php?option=' . $C->option
	. '&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" name="adminForm" id="item-form" class="form-validate">

	<?php #echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div class="main-card">
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab',
			['active' => 'details', 'recall' => true, 'breakpoint' => 768]); ?>

			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_GHSTHING')); ?>

				<div class="row">
					<div class="col-12 col-lg-12">
						<div class="row">
							<div class="col-12">
								<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

								<div>
									<?php
									/*
									Notwendige Zwischenspeicherung, um später wieder rücksetzen zu können.
									Ist durch Joomla verursacht, dass nötig.
									*/
									$fieldsSafe = $this->fields;
									$hiddenfieldsSafe = $this->hidden_fields;

									$fromJsonData = $this->MY_CONgetEditFormDataFromJson(
										'ghsthing.edit|global'
									);

									echo LayoutHelper::render('ghsvs.editFormFields',
									[
										'fromJsonData' => new Registry($fromJsonData),
										'form' => $this->getForm()
									]);

									/*
									Aus Zwischengepeicherten wieder rücksetzen.
									*/
									$this->fields = $fieldsSafe;
									$this->hidden_fields = $hiddenfieldsSafe;
									?>
								</div>
							</div>
						</div>
					</div>
					<div class="col-lgssssssssss-3">

					</div>
				</div><!--/.row-->
			<?php echo HTMLHelper::_('uitab.endTab'); ?>

			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'content', Text::_('COM_GHSTHING')); ?>

				<div class="row">
							<div class="col-12">
								<?php echo $this->form->renderField('articletext'); ?>
							</div>
				</div><!--/.row-->
			<?php echo HTMLHelper::_('uitab.endTab'); ?>


<?php if ($params->get('show_publishing_options', 1) == 1) : ?>
	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publishing',
		Text::_('JGLOBAL_FIELDSET_PUBLISHING')); ?>

		<div class="row">
			<div class="col-12 col-lg-12">
				<fieldset id="fieldset-publishingdata" class="options-form">
					<legend><?php echo Text::_('JGLOBAL_FIELDSET_PUBLISHING'); ?></legend>
					<div>
						<?php
						/*
						Notwendige Zwischenspeicherung, um später wieder rücksetzen zu können.
						Ist durch Joomla verursacht, dass nötig.
						*/
						$fieldsSafe = $this->fields;
						$hiddenfieldsSafe = $this->hidden_fields;

						$fromJsonData = $this->MY_CONgetEditFormDataFromJson(
							'ghsthing.edit|publishingdata'
						);

						echo LayoutHelper::render('ghsvs.editFormFields',
						[
							'fromJsonData' => new Registry($fromJsonData),
							'form' => $this->getForm()
						]);

						/*
						Aus Zwischengepeicherten wieder rücksetzen.
						*/
						$this->fields = $fieldsSafe;
						$this->hidden_fields = $hiddenfieldsSafe;
						?>
					</div>
				</fieldset>
			</div>
		</div>
	<?php echo HTMLHelper::_('uitab.endTab'); ?>

	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'images',
		Text::_('GHSVS_IMAGES')); ?>

		<div class="row">
			<div class="col-12 col-lg-12">
				<?php foreach (['image-intro', 'image-full'] as $fieldset) : ?>
				<fieldset id="fieldset-<?php echo $fieldset; ?>" class="options-form">
				<legend><?php echo Text::_($this->form->getFieldsets()[$fieldset]->label); ?></legend>
				<div>
				<?php echo $this->form->renderFieldset($fieldset); ?>
				</div>
				</fieldset>
				<?php endforeach; ?>
			</div>
		</div>

	<?php echo HTMLHelper::_('uitab.endTab'); ?>

	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'gallery',
		Text::_('GHSVS_IMAGES_GALLERY')); ?>

		<div class="row">
			<div class="col-12 col-lg-12">
				<?php foreach (['fotos'] as $fieldset) : ?>
				<fieldset id="fieldset-<?php echo $fieldset; ?>" class="options-form">
				<legend><?php echo Text::_($this->form->getFieldsets()[$fieldset]->label); ?></legend>
				<div>
				<?php echo $this->form->renderFieldset($fieldset); ?>
				</div>
				</fieldset>
				<?php endforeach; ?>
			</div>
		</div>

	<?php echo HTMLHelper::_('uitab.endTab'); ?>

	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'metadata',
		Text::_('JGLOBAL_FIELDSET_METADATA_OPTIONS')); ?>
		<div class="row">
			<div class="col-12 col-lg-6">
				<fieldset id="fieldset-metadata" class="options-form">
				<legend><?php echo Text::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'); ?></legend>
					<div>
						<?php echo LayoutHelper::render('joomla.edit.metadata', $this); ?>
					</div>
				</fieldset>
			</div>
		</div>
	<?php echo HTMLHelper::_('uitab.endTab'); ?>
<?php endif; ?>


			<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
		</div><!--/main-card-->

		<input type="hidden" name="task" value="<?php echo $C->vSingle; ?>.edit" />
		<?php echo HTMLHelper::_('form.token'); ?>
</form>
