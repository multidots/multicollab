<div class="cf_plugin_deacmodal" tabindex="-1" role="dialog" id="cf_plugin_deacmodal">
	<div class="modal-dialog" role="document">

		<div class="modal-header">
			<h3 class="modal-title">Did Multicollab meet your needs?</h3>
		</div>


		<div id="step-1" class="modal-body">
			<div class="cf_deactivate_form">
				<label>
					<input type="radio" name="fs_deactive_free_plugin_step1" value="yes">
					<span>Yes</span>
				</label>
				<label>
					<input type="radio" name="fs_deactive_free_plugin_step1" value="no">
					<span>No</span>
				</label>
			</div>
		</div>

		<div id="step-2" class="modal-body" style="display: none;">
			<p>If you have a moment, please let us know why you are deactivating:</p>
			<div class="cf_deactivate_form">
				<label>
					<input type="radio" name="fs_deactive_free_plugin" value="Lack of essential features">
					<span>Lack of essential features</span>
				</label>
				<input type="text" class="fs_feedback_message_1 feedback_message" placeholder="Please share important missing features of the Plugin." disabled style="display: none;">
				<span class="cf_deact-required-indicator">*</span>
				<label>
					<input type="radio" name="fs_deactive_free_plugin" value="Performance issues">
					<span>Performance issues</span>
				</label>
				<label>
					<input type="radio" name="fs_deactive_free_plugin" value="User interface/navigation difficulties">
					<span>User interface/navigation difficulties</span>
				</label>
			</div>
			<div class="cf_deactivate_form">
				<label>
					<input type="radio" name="fs_deactive_free_plugin" value="Cost-related reasons">
					<span>Cost-related reasons</span>
				</label>
				<label>
					<input type="radio" name="fs_deactive_free_plugin" value="Found a better plugin">
					<span>Found a better plugin</span>
				</label>
				<input type="text" class="fs_feedback_message_2 feedback_message" placeholder="What's the plugin's name?" disabled style="display: none;">
				<span class="cf_deact-required-indicator">*</span>
			</div>
			<div class="cf_deactivate_form">
				<label>
					<input type="radio" name="fs_deactive_free_plugin" value="No longer need the plugin">
					<span>No longer need the plugin</span>
				</label>
				<label>
					<input type="radio" name="fs_deactive_free_plugin" value="It's temporary deactivation - troubleshooting an issue">
					<span>It's temporary deactivation - troubleshooting an issue</span>
				</label>
				<label>
					<input type="radio" name="fs_deactive_free_plugin" value="Something else">
					<span>Something else.</span>
				</label>
				<input type="text" class="fs_feedback_message_3 feedback_message" placeholder="Something else" disabled style="display: none;">
				<span class="cf_deact-required-indicator">*</span>
			</div>
		</div>

		<div id="step-3" class="modal-body" style="display: none;">

			<span class="free_plugin_deactivate_step3" data-value="Excellent">😊 Excellent</span>
			<span class="free_plugin_deactivate_step3" data-value="Good">🙂 Good</span>
			<span class="free_plugin_deactivate_step3" data-value="Average">😐 Average</span>
			<span class="free_plugin_deactivate_step3" data-value="Poor">😕 Poor</span>
			<span class="free_plugin_deactivate_step3" data-value="Very poor">☹️ Very poor</span>

		</div>

		<div></div>

		<div class="modal-footer" style="display: none;">
			<div class="snooze_option_section" style="display:none">
				<input type="checkbox" class="snooze_option_checkbox" id="snooze_option_checkbox">
				<label for="snooze_option_checkbox">Snooze this panel during troubleshooting.</label>

				<select class="snooze_option_period" style="display:none">
					<option value="<?php echo esc_attr( 60 ); ?>">1 Hour</option>
					<option value="<?php echo esc_attr( 60 * 24 ); ?>">24 Hour</option>
					<option value="<?php echo esc_attr( 60 * 24 * 7 ); ?>">7 Days</option>
					<option value="<?php echo esc_attr( 60 * 24 * 30 ); ?>">30 Days</option>
				</select>
			</div>
			<button type="button" class="btn btn-primary" data-dismiss="modal">Submit &amp; Deactivate</button>
			<button type="button" class="btn btn-secondary btn-cancel">Cancel</button>
		</div>

	</div>
</div>