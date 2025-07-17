<?php

require_once COMMENTING_BLOCK_DIR . 'admin/classes/class-commenting-block-permissions.php';
$permission_list = new Commenting_Block_Permissions();
$data            = $permission_list->cf_get_pp_roles();

$disabled       = 'disabled';
$disabled_class = 'cf_disabled_input';
delete_option( 'cf_permissions' );

?>
<form class="cf-settings-panel__repeater-body" id="cf_permissions" method="post"> 

<div id="cf-permissions-container">
	
			<div class="cf-settings-role-section cf-role-visible" data-role-index="1">
				<div class="cf-settings-role-section__header">
					<h3>Administrator</h3>
				                  </div>

				<!-- Comment Section -->
				<div class="cf-settings-row">
					<h4>Comment</h4>
					<div class="cf-setting-options">
						<label><input type="checkbox" name="cf_permissions[administrator][add_comment]" value="1"> Add</label>
						<label><input type="checkbox" name="cf_permissions[administrator][resolved_comment]" value="1"> Resolve</label>
						<label><input type="checkbox" name="cf_permissions[administrator][hide_comment]" value="1"> Disable</label>
					</div>
				</div>

				<!-- Suggestion Section -->
										<div class="cf-settings-row">
						<h4>Suggestion</h4>
						<div class="cf-setting-options">
							<label><input type="checkbox" name="cf_permissions[administrator][add_suggestion]" value="1"> Add</label>
							<label><input type="checkbox" name="cf_permissions[administrator][resolved_suggestion]" value="1"> Accept/Reject</label>
							<label><input type="checkbox" name="cf_permissions[administrator][hide_suggestion]" value="1"> Disable</label>
						</div>
					</div>

					<!-- Real-Time Editing -->
					<div class="cf-settings-row">
						<h4>Real-time Editing</h4>
						<div class="cf-setting-options">
							<label><input type="checkbox" name="cf_permissions[administrator][realtime_editing]" value="1"> Enable/Disable</label>
						</div>
					</div>
								</div>

			
			<div class="cf-settings-role-section cf-role-visible" data-role-index="2">
				<div class="cf-settings-role-section__header">
					<h3>Editor</h3>
										</div>

				<!-- Comment Section -->
				<div class="cf-settings-row">
					<h4>Comment</h4>
					<div class="cf-setting-options">
						<label><input type="checkbox" name="cf_permissions[editor][add_comment]" value="1"> Add</label>
						<label><input type="checkbox" name="cf_permissions[editor][resolved_comment]" value="1"> Resolve</label>
						<label><input type="checkbox" name="cf_permissions[editor][hide_comment]" value="1"> Disable</label>
					</div>
				</div>

				<!-- Suggestion Section -->
										<div class="cf-settings-row">
						<h4>Suggestion</h4>
						<div class="cf-setting-options">
							<label><input type="checkbox" name="cf_permissions[editor][add_suggestion]" value="1"> Add</label>
							<label><input type="checkbox" name="cf_permissions[editor][resolved_suggestion]" value="1"> Accept/Reject</label>
							<label><input type="checkbox" name="cf_permissions[editor][hide_suggestion]" value="1"> Disable</label>
						</div>
					</div>

					<!-- Real-Time Editing -->
					<div class="cf-settings-row">
						<h4>Real-time Editing</h4>
						<div class="cf-setting-options">
							<label><input type="checkbox" name="cf_permissions[editor][realtime_editing]" value="1"> Enable/Disable</label>
						</div>
					</div>
								</div>

			
			<div class="cf-settings-role-section cf-role-visible" data-role-index="3">
				<div class="cf-settings-role-section__header">
					<h3>Author</h3>
										</div>

				<!-- Comment Section -->
				<div class="cf-settings-row">
					<h4>Comment</h4>
					<div class="cf-setting-options">
						<label><input type="checkbox" name="cf_permissions[author][add_comment]" value="1"> Add</label>
						<label><input type="checkbox" name="cf_permissions[author][resolved_comment]" value="1"> Resolve</label>
						<label><input type="checkbox" name="cf_permissions[author][hide_comment]" value="1"> Disable</label>
					</div>
				</div>

				<!-- Suggestion Section -->
										<div class="cf-settings-row">
						<h4>Suggestion</h4>
						<div class="cf-setting-options">
							<label><input type="checkbox" name="cf_permissions[author][add_suggestion]" value="1"> Add</label>
							<label><input type="checkbox" name="cf_permissions[author][resolved_suggestion]" value="1"> Accept/Reject</label>
							<label><input type="checkbox" name="cf_permissions[author][hide_suggestion]" value="1"> Disable</label>
						</div>
					</div>

					<!-- Real-Time Editing -->
					<div class="cf-settings-row">
						<h4>Real-time Editing</h4>
						<div class="cf-setting-options">
							<label><input type="checkbox" name="cf_permissions[author][realtime_editing]" value="1"> Enable/Disable</label>
						</div>
					</div>
								</div>

			
			<div class="cf-settings-role-section cf-role-hidden" data-role-index="4">
				<div class="cf-settings-role-section__header">
					<h3>Contributor</h3>
										</div>

				<!-- Comment Section -->
				<div class="cf-settings-row">
					<h4>Comment</h4>
					<div class="cf-setting-options">
						<label><input type="checkbox" name="cf_permissions[contributor][add_comment]" value="1"> Add</label>
						<label><input type="checkbox" name="cf_permissions[contributor][resolved_comment]" value="1"> Resolve</label>
						<label><input type="checkbox" name="cf_permissions[contributor][hide_comment]" value="1"> Disable</label>
					</div>
				</div>

				<!-- Suggestion Section -->
										<div class="cf-settings-row">
						<h4>Suggestion</h4>
						<div class="cf-setting-options">
							<label><input type="checkbox" name="cf_permissions[contributor][add_suggestion]" value="1"> Add</label>
							<label><input type="checkbox" name="cf_permissions[contributor][resolved_suggestion]" value="1"> Accept/Reject</label>
							<label><input type="checkbox" name="cf_permissions[contributor][hide_suggestion]" value="1"> Disable</label>
						</div>
					</div>

					<!-- Real-Time Editing -->
					<div class="cf-settings-row">
						<h4>Real-time Editing</h4>
						<div class="cf-setting-options">
							<label><input type="checkbox" name="cf_permissions[contributor][realtime_editing]" value="1"> Enable/Disable</label>
						</div>
					</div>
								</div>

			
			<div class="cf-settings-role-section cf-role-hidden" data-role-index="5">
				<div class="cf-settings-role-section__header">
					<h3>Subscriber</h3>
										</div>

				<!-- Comment Section -->
				<div class="cf-settings-row">
					<h4>Comment</h4>
					<div class="cf-setting-options">
						<label><input type="checkbox" name="cf_permissions[subscriber][add_comment]" value="1"> Add</label>
						<label><input type="checkbox" name="cf_permissions[subscriber][resolved_comment]" value="1"> Resolve</label>
						<label><input type="checkbox" name="cf_permissions[subscriber][hide_comment]" value="1"> Disable</label>
					</div>
				</div>

				<!-- Suggestion Section -->
										<div class="cf-settings-row">
						<h4>Suggestion</h4>
						<div class="cf-setting-options">
							<label><input type="checkbox" name="cf_permissions[subscriber][add_suggestion]" value="1"> Add</label>
							<label><input type="checkbox" name="cf_permissions[subscriber][resolved_suggestion]" value="1"> Accept/Reject</label>
							<label><input type="checkbox" name="cf_permissions[subscriber][hide_suggestion]" value="1"> Disable</label>
						</div>
					</div>

					<!-- Real-Time Editing -->
					<div class="cf-settings-row">
						<h4>Real-time Editing</h4>
						<div class="cf-setting-options">
							<label><input type="checkbox" name="cf_permissions[subscriber][realtime_editing]" value="1"> Enable/Disable</label>
						</div>
					</div>
								</div>

			
			<div class="cf-settings-role-section cf-role-hidden" data-role-index="6">
				<div class="cf-settings-role-section__header">
					<h3>Guest / Viewer</h3>
										</div>

				<!-- Comment Section -->
				<div class="cf-settings-row">
					<h4>Comment</h4>
					<div class="cf-setting-options">
						<label><input type="checkbox" name="cf_permissions[guest-Viewer][add_comment]" value="1"> Add</label>
						<label><input type="checkbox" name="cf_permissions[guest-Viewer][resolved_comment]" value="1"> Resolve</label>
						<label><input type="checkbox" name="cf_permissions[guest-Viewer][hide_comment]" value="1"> Disable</label>
					</div>
				</div>

				<!-- Suggestion Section -->
										<div class="cf-settings-row">
						<h4>Suggestion</h4>
						<div class="cf-setting-options">
							<label><input type="checkbox" name="cf_permissions[guest-Viewer][add_suggestion]" value="1"> Add</label>
							<label><input type="checkbox" name="cf_permissions[guest-Viewer][resolved_suggestion]" value="1"> Accept/Reject</label>
							<label><input type="checkbox" name="cf_permissions[guest-Viewer][hide_suggestion]" value="1"> Disable</label>
						</div>
					</div>

					<!-- Real-Time Editing -->
					<div class="cf-settings-row">
						<h4>Real-time Editing</h4>
						<div class="cf-setting-options">
							<label><input type="checkbox" name="cf_permissions[guest-Viewer][realtime_editing]" value="1"> Enable/Disable</label>
						</div>
					</div>
								</div>

			
			<div class="cf-settings-role-section cf-role-hidden" data-role-index="7">
				<div class="cf-settings-role-section__header">
					<h3>Guest / Commenter</h3>
										</div>

				<!-- Comment Section -->
				<div class="cf-settings-row">
					<h4>Comment</h4>
					<div class="cf-setting-options">
						<label><input type="checkbox" name="cf_permissions[guest-Commenter][add_comment]" value="1"> Add</label>
						<label><input type="checkbox" name="cf_permissions[guest-Commenter][resolved_comment]" value="1"> Resolve</label>
						<label><input type="checkbox" name="cf_permissions[guest-Commenter][hide_comment]" value="1"> Disable</label>
					</div>
				</div>

				<!-- Suggestion Section -->
										<div class="cf-settings-row">
						<h4>Suggestion</h4>
						<div class="cf-setting-options">
							<label><input type="checkbox" name="cf_permissions[guest-Commenter][add_suggestion]" value="1"> Add</label>
							<label><input type="checkbox" name="cf_permissions[guest-Commenter][resolved_suggestion]" value="1"> Accept/Reject</label>
							<label><input type="checkbox" name="cf_permissions[guest-Commenter][hide_suggestion]" value="1"> Disable</label>
						</div>
					</div>

					<!-- Real-Time Editing -->
					<div class="cf-settings-row">
						<h4>Real-time Editing</h4>
						<div class="cf-setting-options">
							<label><input type="checkbox" name="cf_permissions[guest-Commenter][realtime_editing]" value="1"> Enable/Disable</label>
						</div>
					</div>
								</div>

			
			<div class="cf-settings-role-section cf-role-hidden" data-role-index="8">
				<div class="cf-settings-role-section__header">
					<h3>Guest / Coeditor</h3>
										</div>

				<!-- Comment Section -->
				<div class="cf-settings-row">
					<h4>Comment</h4>
					<div class="cf-setting-options">
						<label><input type="checkbox" name="cf_permissions[guest-Coeditor][add_comment]" value="1"> Add</label>
						<label><input type="checkbox" name="cf_permissions[guest-Coeditor][resolved_comment]" value="1"> Resolve</label>
						<label><input type="checkbox" name="cf_permissions[guest-Coeditor][hide_comment]" value="1"> Disable</label>
					</div>
				</div>

				<!-- Suggestion Section -->
										<div class="cf-settings-row">
						<h4>Suggestion</h4>
						<div class="cf-setting-options">
							<label><input type="checkbox" name="cf_permissions[guest-Coeditor][add_suggestion]" value="1"> Add</label>
							<label><input type="checkbox" name="cf_permissions[guest-Coeditor][resolved_suggestion]" value="1"> Accept/Reject</label>
							<label><input type="checkbox" name="cf_permissions[guest-Coeditor][hide_suggestion]" value="1"> Disable</label>
						</div>
					</div>

					<!-- Real-Time Editing -->
					<div class="cf-settings-row">
						<h4>Real-time Editing</h4>
						<div class="cf-setting-options">
							<label><input type="checkbox" name="cf_permissions[guest-Coeditor][realtime_editing]" value="1"> Enable/Disable</label>
						</div>
					</div>
								</div>

					
	<!-- Show More/Less Controls -->
				<div class="cf-permissions-controls">
			<button type="button" id="cf-show-more-roles" class="button button-secondary">Show 5 more roles</button>
			<button type="button" id="cf-show-less-roles" class="button button-secondary" style="display: none;">
				Show less                </button>
		</div>
		</div>

<div id="cf-permissions-notice">
	<div class="cf-success notices notice-success" style="display: none">
		<p>Permissions saved!</p>
	</div>
</div>

<div class="cf-submit-button-settings">
	<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save"></p>    </div>

</form>
<script type="text/javascript">
	/* <![CDATA[ */
	jQuery(document).ready(function ($) {
		$(document).on('change', '#cf_hide_comment', function () {
			var isChecked = $(this).prop('checked');
			if(true === isChecked){
				$(this).closest('tr').find('#cf_add_comment').prop("checked", false).attr('disabled', true);
				$(this).closest('tr').find('#cf_resolved_comment').prop("checked", false).attr('disabled', true);   
			}else{
				$(this).closest('tr').find('#cf_add_comment').attr('disabled', false);
				$(this).closest('tr').find('#cf_resolved_comment').attr('disabled', false);   
			}
		})
		$(document).on('change', '#cf_hide_suggestion', function () {
			var isChecked = $(this).prop('checked');
			if(true === isChecked){
				$(this).closest('tr').find('#cf_add_suggestion').prop("checked", false).attr('disabled', true);
				$(this).closest('tr').find('#cf_resolved_suggestion').prop("checked", false).attr('disabled', true);   
			}else{
				$(this).closest('tr').find('#cf_add_suggestion').attr('disabled', false);
				$(this).closest('tr').find('#cf_resolved_suggestion').attr('disabled', false);   
			}
		})
	})
</script>
