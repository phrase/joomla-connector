<?php
/**
 * @copyright Copyright (C) 2019 Memsource a.s. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
?>

<div class="container-fluid container-main" style="top: 82px">
	<section id="content">
		<div class="row-fluid">
			<h2>
				<?php echo JText::_('COM_MEMSOURCE_CONNECTOR_TOKEN'); ?>
			</h2>
			<form method="POST" action="?option=com_memsource_connector&task=generateToken">
				<div class="controls controls-row">
					<span class="input-append" style="margin-bottom: 0; margin-right: 10px;">
						<input id="memsourceToken" type="text" value="<?php echo $this->escape($this->token); ?>" readonly class="input-large">
						<button type="button" onclick="copyToClipboard()" class="btn">
							<span class="icon-copy"></span>
							<?php echo JText::_('COM_MEMSOURCE_CONNECTOR_COPY_TOKEN'); ?>
						</button>
					</span>
					<button class="btn" onclick="return confirm('<?php echo JText::_('COM_MEMSOURCE_CONNECTOR_CONFIRM_NEW_TOKEN'); ?>')">
						<?php echo JText::_('COM_MEMSOURCE_CONNECTOR_GENERATE_NEW_TOKEN'); ?>
					</button>
				</div>
			</form>
		</div>
		<br>

		<div class="row-fluid">
			<h2>
				<?php echo JText::_('COM_MEMSOURCE_CONNECTOR_DEBUG_MODE'); ?>
			</h2>
		</div>

		<div class="row-fluid">
			<div class="controls controls-row">
				<h4>
					<?php echo JText::_('COM_MEMSOURCE_CONNECTOR_DEBUG_MODE'); ?>:
					<?php if ($this->debug) : ?>
						<span class="label label-success">
							<?php echo JText::_('COM_MEMSOURCE_CONNECTOR_DEBUG_MODE_ENABLED'); ?>
						</span>
					<?php else : ?>
						<span class="label">
							<?php echo JText::_('COM_MEMSOURCE_CONNECTOR_DEBUG_MODE_DISABLED'); ?>
						</span>
					<?php endif; ?>
				</h4>
			</div>
		</div>

		<div class="row-fluid">
			<div class="controls controls-row">
				<?php if ($this->debug) : ?>
					<form method="POST" action="?option=com_memsource_connector&task=disableDebug">
						<button class="btn">
							<?php echo JText::_('COM_MEMSOURCE_CONNECTOR_DISABLE_DEBUG_MODE'); ?>
						</button>
					</form>
				<?php else : ?>
					<form method="POST" action="?option=com_memsource_connector&task=enableDebug">
						<button class="btn">
							<?php echo JText::_('COM_MEMSOURCE_CONNECTOR_ENABLE_DEBUG_MODE'); ?>
						</button>
					</form>
				<?php endif; ?>
			</div>
		</div>

		<div class="row-fluid">
			<h4>
				<?php echo JText::_('COM_MEMSOURCE_CONNECTOR_LOGS_SIZE'); ?>:
				<?php echo JHtmlNumber::bytes($this->logFileSize) ?: '0 kB'; ?>
			</h4>
		</div>

		<div class="row-fluid">
			<div class="controls controls-row">
				<form method="POST" action="?option=com_memsource_connector&task=downloadLogs" target="_blank" style="display: inline-block">
					<button class="btn">
						<?php echo JText::_('COM_MEMSOURCE_CONNECTOR_DOWNLOAD_LOGS'); ?>
					</button>
				</form>
				<form method="POST" action="?option=com_memsource_connector&task=sendLogs" style="display: inline-block">
					<button class="btn" onclick="return confirm('<?php echo JText::_('COM_MEMSOURCE_CONNECTOR_CONFIRM_SEND_LOGS'); ?>')">
						<?php echo JText::_('COM_MEMSOURCE_CONNECTOR_SEND_LOGS'); ?>
					</button>
				</form>
			</div>
		</div>
		<br>

		<div class="row-fluid">
			<div class="btn-group">
				<form method="POST" action="?option=com_memsource_connector&task=clearLogs">
					<button class="btn" onclick="return confirm('<?php echo JText::_('COM_MEMSOURCE_CONNECTOR_CONFIRM_CLEAR_LOGS'); ?>')">
						<?php echo JText::_('COM_MEMSOURCE_CONNECTOR_CLEAR_LOGS'); ?>
					</button>
				</form>
			</div>
		</div>
	</section>
</div>

<script>
function copyToClipboard() {
	let token = document.getElementById('memsourceToken');
	token.focus();
	token.select();
	document.execCommand('copy');
}
</script>
