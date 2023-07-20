<div class="cf_plugin_deacmodal cf_plugin_freedeacmodal" tabindex="-1" role="dialog" id="cf_plugin_deacmodal">
	<div class="modal-dialog" role="document">
		<div class="modal-header">
			<h3 class="modal-title">QUICK FEEDBACK </h3>
		</div>
		<div class="modal-body">
			<p>If you have a moment, please let us know why you are deactivating:</p>
			<div class="cf_deactivate_form">
				<label>
					<input type="radio" name="fs_deactive_free_plugin" value="The plugin didn't work.">
					<span>The plugin didn't work.</span>
				</label>
				<label>
					<input type="radio" name="fs_deactive_free_plugin" value="Not happy with user experience.">
					<span>Not happy with user experience.</span>
				</label>
				<label>
					<input type="radio" name="fs_deactive_free_plugin" value="Missing a few important features.">
					<span>Missing a few important features.</span>
				</label>
				<input type="text" class="fs_feedback_message_1 feedback_message" placeholder="Please share important missing features of the Plugin." disabled>
			</div>
			<div class="cf_deactivate_form">
				<label>
					<input type="radio" name="fs_deactive_free_plugin" value="I am a WordPress expert and exploring it for my client.">
					<span>I am a WordPress expert and exploring it for my client.</span>
				</label>
				<label>
					<input type="radio" name="fs_deactive_free_plugin" value="I found a better plugin.">
					<span>I found a better plugin.</span>
				</label>
				<input type="text" class="fs_feedback_message_2 feedback_message" placeholder="What's the plugin's name?" disabled>
			</div>
			<div class="cf_deactivate_form">
				<label>
					<input type="radio" name="fs_deactive_free_plugin" value="I no longer need the plugin.">
					<span>I no longer need the plugin.</span>
				</label>
				<label>
					<input type="radio" name="fs_deactive_free_plugin" value="It's a temporary deactivation - I'm troubleshooting an issue.">
					<span>It's a temporary deactivation - I'm troubleshooting an issue.</span>
				</label>
				<label>
					<input type="radio" name="fs_deactive_free_plugin" value="Something else.">
					<span>Something else.</span>
				</label>
				<input type="text" class="fs_feedback_message_3 feedback_message" placeholder="Something else." disabled>
			</div>
		</div>
		<div class="modal-footer">
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