<?php
\defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Language\Text;

$startEnd = $displayData['startEnd'] ?? 'start';

if ($startEnd === 'start') { ?>
<div class="row">
	<div class="col-md-12">
		<div id="j-main-container" class="j-main-container">
		<?php echo LayoutHelper::render('joomla.searchtools.default',
			['view' => $displayData['view']]);?>

			<?php if ($displayData['itemsEmpty'] === true) { ?>
			<div class="alert alert-info">
				<span class="icon-info-circle" aria-hidden="true"></span>
				<span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
				<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
			<?php } ?>

<?php } else { ?>
		</div><!--/j-main-container-->
	</div><!--/col-md-12-->
</div><!--/row-->
<?php }
