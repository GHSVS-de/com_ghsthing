<?php
/*
Ist ein Layout für AUsgabe von Formfeldern in der Art wie layouts\joomla\edit\publishingdata.php.
1.) Manipuliere ich die $this-fields zuvor (siehe com_ghsthing_fields.json).
2.) Geht mir die Anordnung durch Joomla selbst seit jeher auf den Sack.
*/
defined('_JEXEC') or die;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Component\ComponentHelper;
/*
$fromJsonData Registry
$form
*/
extract($displayData);

$fields = $fromJsonData->get('fields', []);
$colClass = $fromJsonData->get('colClass', 'col');
$hiddenFields = $fromJsonData->get('hidden_fields', []);
$disabledFields = $fromJsonData->get('disabled_fields', []);

$saveHistory = ComponentHelper::getParams('com_ghsthing');
#echo ' 4654sd48sa7d98sD81s8d71dsawwwwww <pre>' . print_r($saveHistory, true) . '</pre>';exit;

if (!Multilanguage::isEnabled()) {
	$disabledFields[] = 'language';
	$form->setFieldAttribute('language', 'default', '*');
}
?>
<div class="row">
	<?php
	foreach ($fields as $field)
	{
		foreach ((array) $field as $f)
		{
			if ($form->getField($f))
			{
				if (in_array($f, $hiddenFields))
				{
					$form->setFieldAttribute($f, 'type', 'hidden');
				}

				if (in_array($f, $disabledFields))
				{
					$form->setFieldAttribute($f, 'disabled', 'disabled');
				}

				?>
				<div class="<?php echo $colClass; ?>">
					<?php
						echo $form->renderField($f, $group = null, $default = null,
							$options = ['class' => 'wurst']);
					?>
				</div>
				<?php
				break;
			}
		}
	} ?>
</div><!--/.row-->
