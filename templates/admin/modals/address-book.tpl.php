<div class="modal modal-lg fade" id="addressBookModal" tabindex="-1" aria-labelledby="addressBookModalLabel" data-bs-backdrop="static" aria-hidden="true">
	<div class="modal-dialog modal-dialog-scrollable sendtrace">
		<form class="modal-content" id="addressBookModalForm" method="POST">
			<input type="hidden" id="action" value="add"/>
			<input type="hidden" id="type" value="<?php echo $type ?>"/>
			<input type="hidden" id="ab_id"/>
			<div class="modal-header">
				<h5 class="modal-title" id="addressBookModalLabel"> <span class="title-action">New </span> Address Book</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<?php
				if (!empty($form_fields)) {
					foreach ($form_fields as $field) {
						$field['group_class'] = 'mb-0';
						WPSTForm::gen_field($field, true);
					}
				}
				?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary px-3 py-2" data-bs-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary px-3 py-2 submit-btn">Add</button>
			</div>
		</form>
	</div>
</div>