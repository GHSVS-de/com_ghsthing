<?php
\defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Layout\LayoutHelper;

$data = new Registry($displayData);
$startEnd = $data->get('startEnd', 'start');

if ($startEnd === 'start') { ?>
<div class="row">
	<div class="col-md-12">
		<div id="j-main-container" class="j-main-container">
		<?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $data->get('myThis')]);?>
<?php } else { ?>
		</div><!--/j-main-container-->
	</div><!--/col-md-12-->
</div><!--/row-->
<?php }
