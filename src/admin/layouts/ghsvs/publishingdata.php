<?php
/*
Ist eine bearbeitete Kopie der layouts\joomla\edit\publishingdata.php.
1.) Manipuliere ich die $this-fields zuvor (siehe com_ghsthing_fields.json).
2.) Geht mir die Anordnung durch Joomla selbst seit jeher auf den Sack.
*/
defined('_JEXEC') or die;

$form = $displayData->getForm();

// Im Normalfall von mir verändert. Das array() ist lediglich ein Fallback.
$fields = $displayData->get('fields') ?: array(
	'publish_up',
	'publish_down',
	'featured_up',
	'featured_down',
	array('created', 'created_time'),
	array('created_by', 'created_user_id'),
	'created_by_alias',
	array('modified', 'modified_time'),
	array('modified_by', 'modified_user_id'),
	'version',
	'hits',
	'id'
);

$hiddenFields = $displayData->get('hidden_fields') ?: array();
?>
<div class="row">
	<?php
	foreach ($fields as $field) {
		foreach ((array) $field as $f) {
			if ($form->getField($f)) {
				if (in_array($f, $hiddenFields)) {
					$form->setFieldAttribute($f, 'type', 'hidden');
				} ?>
				<div class="col">
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
</div>
